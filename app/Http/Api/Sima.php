<?php


namespace App\Http\Api;


use App\Models\OzonArticle;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Console\OutputStyle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Sima
{

    function getItems(OutputStyle $output): void
    {

        $output->writeln("getting Sima goods..");
        $output->progressStart(OzonArticle::query()->where("is_synced", false)->count());

        DB::table("ozon_articles")
            ->orderBy('id')
            ->where("is_synced", false)
            ->chunk(100, function (Collection $chunk) use ($output) {
                try {
                    $barcodesStr = '';

                    /** @var OzonArticle $item */
                    foreach ($chunk as $item) {
                        $barcodesStr .= $item->article . ',';
                    }

                    try {
                    $response = Http::connectTimeout(30)
                        ->retry(5, 10, function ($exception, $request) {
                            return $exception instanceof RequestException;
                        })
                        ->withHeaders([
                            //"Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                            "x-api-key" => getenv('SIMA_X_API_KEY')
                        ])
                        ->get('https://www.sima-land.ru/api/v3/item',
                            [
                                'per-page' => 100,
                                'sid' => substr_replace($barcodesStr, "", -1),
                                'expand' => 'stocks,min_qty'
                            ]);
                         } catch(RequestException $e) {
                            $output->write($e->getMessage());
                         }

                    if (!$response instanceof ConnectException) {
                        DB::beginTransaction();
                        foreach ($response->json()['items'] as $item) {
                            $stocks = Collection::make($item['stocks']);

                            $itemStocks = $stocks->where('stock_id',2)->first();
                            $itemsOverall = !is_null($itemStocks) ? $itemStocks['balance'] : 0;

                            OzonArticle::query()
                                ->where('article', $item['sid'])
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
                    } else {
                        $output->write("connection timeout");
                    }

                    $output->progressAdvance(100);
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
                        $response = Http::acceptJson()->timeout(100000)->withHeaders([
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
            ->retry(5, 10000, function ($exception, $request) {
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
//        $result = Http::withHeaders(
//            self::HEADERS
//        )->asJson()->get($this->fileUrl);
//        dump(Collectio$result));
//
//    }

}
