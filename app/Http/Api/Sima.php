<?php


namespace App\Http\Api;


use App\Models\OzonArticle;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Console\OutputStyle;
use Illuminate\Http\Client\ConnectionException;
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

                    $response = Http::connectTimeout(30)
                        ->retry(5, 10000, function ($exception, $request) {
                            return $exception instanceof Exception;
                        })
                        ->withHeaders([
                            "Authorization" => "Bearer " . getenv('SIMA_API_KEY')
                        ])
                        ->get('https://www.sima-land.ru/api/v3/item',
                            [
                                'per-page' => 100,
                                'sid' => substr_replace($barcodesStr, "", -1),
                                'expand' => 'stocks'
                            ]);

                    if (!$response instanceof ConnectException) {
                        DB::beginTransaction();
                        foreach ($response->json()['items'] as $item) {

                            $itemsOverall = 0;

                            foreach ($item['stocks'] as $stock) {
                                $itemsOverall += $stock['balance'];
                            }

                            OzonArticle::query()
                                ->where('article', $item['sid'])
                                ->update([
                                    'sima_price' => (float)$item['price'],
                                    'sima_wholesale_price' => (float)$item['wholesale_price'],
                                    'sima_order_minimum' => (int)$item['minimum_order_quantity'],
                                    'sima_id' => (int)$item['id'],
                                    'sima_stocks' => (int)$itemsOverall,
                                    'is_synced' => true
                                ]);
                        }
                        DB::commit();
                    } else {
                        $output->writeln("connection timeout");
                    }

                    $output->progressAdvance(100);
                } catch (Exception $exception) {
                    dump($exception->getMessage());
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
                            "Authorization" => "Bearer " . getenv('SIMA_API_KEY')
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


//    function downloadReport()
//    {
//        $result = Http::withHeaders(
//            self::HEADERS
//        )->asJson()->get($this->fileUrl);
//        dump(Collectio$result));
//
//    }

}
