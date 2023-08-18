<?php

namespace App\Http\Api;

use App\Models\OzonArticle;
use Illuminate\Console\OutputStyle;
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
            $item = OzonArticle::where('article', '=', (int)substr(substr($card['vendorCode'], 2), 0, -2))
                ->first();
            if ($item) {
                $item->willdberries_id = (int)$card['sizes'][0]['skus'][0];
                $item->willdberries_barcode = (int)$card['nmID'];
                $item->save();
            }
        }

        $pagination = $test['data']['cursor'];
        if ($pagination['total'] === 1000) {
            $this->getArticleList($pagination['nmID'], $pagination['updatedAt']);
        }

        return 'ok';
    }

    function sendStocks(OutputStyle $outputStyle): void
    {
        $outputStyle->writeln("Sending stocks to Wildbberies...");
        DB::table("ozon_articles")
            ->where('is_synced', true)
            ->whereNotNull('willdberries_barcode')
            ->orderBy('id')
            ->chunk(500, function (Collection $chunk) use ($outputStyle) {
                $status = 500;
                $res = ['stocks' => []];

                $chunk->map(function (stdClass $item) use (&$res) {
                    $res['stocks'][] = [
                        "sku" => $item->willdberries_barcode,
                        "amount" => $item->sima_stocks
                    ];
                });
                try {
                    $status = Http::withHeaders(self::$headers)
                        ->put(self::$apiUrl . "/api/v3/stocks/" . self::$warehouseId, $res)->status();

                } catch (\Exception $exception) {
                    $outputStyle->write($exception->getMessage());
                }
                return $status === 204;
            });
    }

    function getLowStocksGoods(OutputStyle $output)
    {
        $articleIds = OzonArticle::where('is_synced', true)
            ->where('sima_stocks', '<=', '10')
            ->whereNotNull('willdberries_barcode')->pluck('article');

        Sima::getItems($output, $articleIds);

    }

}
