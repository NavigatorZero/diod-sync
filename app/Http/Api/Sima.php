<?php


namespace App\Http\Api;


use App\Models\OzonArticle;
use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Sima
{

    public static function getItems(OutputStyle $output, Collection $ids = null): void
    {
        $output->writeln("getting Sima goods..");
        $output->progressStart(OzonArticle::query()->where("is_synced", false)->count());

        OzonArticle::query()
            ->orderBy('id')
            ->when(!is_null($ids), function (Builder $builder) use ($ids) {
                return $builder->where('is_synced', true)->whereIn('article', $ids);
            })
            ->when(is_null($ids), function (Builder $builder) {
                return $builder->where("is_synced", false);
            })
            ->chunk(1000, function (Collection $chunk) use ($output) {

                $barcodes = [];

                for ($i = 1; $i <= 11; $i++) {
                    $barcodesStr = '';
                    $chunk->forPage($i,100)->each(function (OzonArticle $item) use (&$barcodesStr) {
                        $barcodesStr .= $item->article . ',';
                    });
                    $barcodes[] = $barcodesStr;
                }

                try {
                    /** @var Response  $response */
                    foreach (static::generateGetItemsPool($barcodes) as $response) {
                        if ($response instanceof Response) {
                            if($response->ok()) {
                                self::handleGetItemResponse($response);
                            } else {
                                $output->write('error!');
                                $output->write($response->status());
                            }
                        } else {
                            $output->write('error!');
                            $output->write(get_class($response));
                            die();
                        }
                    }

                    $output->progressAdvance(1000);
                } catch (Exception $exception) {
                    $output->write("Erorr: ". $exception->getMessage());
                }
            });
        $output->progressFinish();
    }


    /** @deprecated  */
    function getStocks(OutputStyle $output): void
    {

        $output->writeln("getting Sima stocks..");
        DB::table("ozon_articles")
            ->orderBy('id')
            ->whereNotNull('sima_id')
            ->chunk(100, function (Collection $chunk) use ($output) {

                $chunk->map(function ($ozonArticle) use ($output) {
                    try {
                        $response = Http::acceptJson()->timeout(100)->withHeaders([
                            //"Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                            "x-api-key" => getenv('SIMA_X_API_KEY')
                        ])
                            ->get('https://www.sima-land.ru/api/v3/item/' . $ozonArticle->sima_id . '/',
                                [
                                    'expand' => 'stocks'
                                ]);

                        $test = $response->json()['stocks'];
                        $itemsOverall = 0;

                        foreach ($test as $stock) {
                            $itemsOverall += $stock['balance'];
                        }
                        OzonArticle::query()
                            ->where('sima_id', $ozonArticle->sima_id)
                            ->update([
                                'sima_stocks' => (int)$itemsOverall,
                            ]);

                    } catch (\Exception $exception) {
                        $output->writeln($exception->getMessage());
                    }
                });
            });
    }

    public static function getOneItemInfo(OzonArticle $article)
    {
        $response = Http::connectTimeout(30)
            ->retry(5, 1000, function ($exception, $request) {
                return $exception instanceof Exception;
            })
            ->withHeaders([
                //"Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                "x-api-key" => getenv('SIMA_X_API_KEY')
            ])
            ->get("https://www.sima-land.ru/api/v3/item/$article->sima_id",
                [
                    'expand' => 'weight,product_volume'
                ]);

        return $response->json();
    }

    public static function getOneItemInfoByBarcodeV5(int | string $simaBarcode) {
        $response = Http::withHeaders([
                //"Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                "x-api-key" => getenv('SIMA_X_API_KEY')
            ])
            ->get("https://www.sima-land.ru/api/v5/item/". $simaBarcode ."?by_sid=true");

        return $response->json();

    }


    public static function auth()
    {
        $response = Http::asJson()->post('https://www.sima-land.ru/api/v3/login-form/',
        [
            "entity" => 'diod.ekb@mail.ru',
            "password"=>'qaska1990'
        ]);

        $id = $response->json()['id'];

        $test = Http::withBasicAuth(urlencode('diod.ekb@mail.ru'), 'qaska1990')
            ->acceptJson()
            ->get("https://www.sima-land.ru/api/v3/auth/$id/");

        var_dump($test->json(), urlencode('diod.ekb@mail.ru:qaska1990'));
    }
//    function downloadReport()
//    {
//            self::HEADERS
//        )->asJson()->get($this->fileUrl);
//        dump(Collectio$result));
//
//    }

    public static function generateGetItemsPool(array $barcodesChunk): array {
        return Http::pool(function(Pool $pool) use($barcodesChunk) {
            foreach ($barcodesChunk as $barcodeStr) {
                $pool->timeout(120)->retry(3, 100)
                    ->withHeaders([
                        //"Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                        "x-api-key" => getenv('SIMA_X_API_KEY')
                    ])
                    ->get('https://www.sima-land.ru/api/v3/item',
                        [
                            'per-page' => 100,
                            'sid' => substr_replace($barcodeStr, "", -1),
                            'expand' => 'stocks,min_qty'
                        ]);
            }
        });
    }

    public static function handleGetItemResponse(Response $response) {
        try {
            DB::beginTransaction();
            foreach ($response->json()['items'] as $item) {
                $stocks = Collection::make($item['stocks']);

                $itemStocks = $stocks->where('stock_id',2)->first();
                $itemsOverall = !is_null($itemStocks) ? $itemStocks['balance'] : 0;

                OzonArticle::query()
                    ->where('article', (int)$item['sid'])
                    ->update([
                        'sima_price' => (float)$item['price'],
                        'sima_wholesale_price' => (float)$item['wholesale_price'],
                        'sima_order_minimum' => (int)$item['minimum_order_quantity'],
                        'sima_id' => (int)$item['id'],
                        'sima_stocks' => (int)$itemsOverall,
                        'is_synced' => true,
                        'per_package' => (int)$item['min_qty']
                    ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            dump($e->getMessage());
        }
    }
}
