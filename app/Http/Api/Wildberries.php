<?php

namespace App\Http\Api;

use App\Models\OzonArticle;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use stdClass;

class Wildberries
{
    private static array $headers = [
        'Authorization' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6IjM2MWQ1ZjI2LWY2NGUtNGNiNi1iYjdhLTRiNDVmZGFiOTQwNCJ9.G7UhJdn0YIjH7PVuZCyMamz-8BxU9w2J7h6mbXlq3Gs',
        'Content-Type' => 'application/json',
        'accept' => 'application/json'
    ];

    private static $warehouseId = 759664;

    private static $apiUrl = 'https://suppliers-api.wildberries.ru';

    private $authKeyDefault = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6IjM2MWQ1ZjI2LWY2NGUtNGNiNi1iYjdhLTRiNDVmZGFiOTQwNCJ9.G7UhJdn0YIjH7PVuZCyMamz-8BxU9w2J7h6mbXlq3Gs';

    private $authKeyCustom = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6ImUwOTVhYzUyLWU3ODEtNDQ1ZS04MjZiLTY4MGIwZTVkYzkyNSJ9.LlhwTn4Y64XK4MoEQ_RAUaxmVdkq20HwSmTpQDLIOlw';

    public function getArticleList($nmID = null, $updatedAt = null): string
    {
        $params = [
            'sort' => [
                'cursor' => ['limit' => 1000],
                'filter' => ['withPhoto' => -1]
            ]
        ];

        if (isset($nmID) && isset($updatedAt)) {
            $params['sort']['cursor']['updatedAt'] = $updatedAt;
            $params['sort']['cursor']['nmID'] = $nmID;
        }

        $test = Http::withHeaders(self::$headers)->post(self::$apiUrl . '/content/v1/cards/cursor/list', $params)->json();

        foreach ($test['data']['cards'] as $card) {
            $barcode = (int)substr(substr(trim($card['vendorCode']), 2), 0, -2);
            $item = OzonArticle::withoutglobalScopes()->where('article', '=', $barcode)
                ->first();
            if (!$item) {
                $item = new OzonArticle();
                $item->article = $barcode;
                try {
                    $simaInfo = Sima::getOneItemInfoByBarcodeV5($item->article);
                    $item->sima_stocks = $simaInfo['balance'];
                    $item->sima_id = $simaInfo['id'];
                    $item->name = $simaInfo['name'];
                    $item->product_volume = 0;
                    $item->product_weight = 0;
                    $item->sima_wholesale_price = $simaInfo['wholesale_price'];
                    $item->is_synced = true;
                } catch (\Exception $exception) {
                    dump($exception->getMessage());
                }
            }

            if ($item->trashed()) $item->restore();
            $item->willdberries_id = (int)$card['sizes'][0]['skus'][0];
            $item->willdberries_barcode = (int)$card['nmID'];
            $item->is_synced = true;
            $item->save();
        }

        $pagination = $test['data']['cursor'];
        if ($pagination['total'] === 1000) {
            $this->getArticleList($pagination['nmID'], $pagination['updatedAt']);
        }

        return 'ok';
    }

    function sendStocks(OutputStyle $outputStyle, Collection $ids = null): void
    {
        $outputStyle->writeln("Sending stocks to Wildbberies...");
        DB::table("ozon_articles")
            ->where('is_synced', true)
            ->when(!is_null($ids), function (Builder $builder) use ($ids) {
                return $builder->whereIn("article", $ids->toArray());
            })
            ->whereNotNull('willdberries_barcode')
            ->orderBy('id',"DESC")
            ->chunk(1, function (Collection $chunk) use ($outputStyle) {
                $res = ['stocks' => []];

                $chunk->map(function (stdClass $item) use (&$res) {
                    $res['stocks'][] = [
                        "sku" => (string)$item->willdberries_id,
                        "amount" => $item->sima_stocks
                    ];
                });
                try {
                    $response = Http::withHeaders(self::$headers)
                        ->put(self::$apiUrl . "/api/v3/stocks/" . self::$warehouseId, $res);

                    $status = $response->status();
                    if ($status !== 204) {
                        //TODO remove wb articles which hasn't been found
                        $outputStyle->write('error on  sending stocks!');
                        dump($response->json());
                    }
                    $outputStyle->writeln($response->status());

                } catch (\Exception $exception) {
                    $outputStyle->write($exception->getMessage());
                }
            });
        $outputStyle->writeln("stocks has been uploaded");
    }

    function updateLowStocks(OutputStyle $output)
    {
        $articles = OzonArticle::select()->where('is_synced', true)
            ->where('sima_stocks', '<=', '10')
            ->whereNotNull('willdberries_barcode')->pluck('article');

        Sima::getItems($output, $articles);

        $this->sendStocks($output, $articles);
    }

}
