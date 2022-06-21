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

    private const API_URL = 'https://api-seller.ozon.ru';
    private const HEADERS = [
        'Client-Id' => '161605',
        'Api-Key' => '81d5f89e-c7a9-4046-aa24-d11944654ed7'
    ];

    private const API_KEY = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NTMyMjY4ODAsIm5iZiI6MTY1MzIyNjg4MCwiZXhwIjoxNjg0NzYyODgwLCJqdGkiOjM3NDk0NjR9.c0QNvrJfgkzWQhnkR0xwTn_fWcTRX2D1tlsIfwUjzv0';

    function getItems(OutputStyle $output): void
    {

        $output->writeln("getting Sima goods..");
        $output->progressStart(OzonArticle::query()->whereNull("sima_id")->count());
        
        DB::table("ozon_articles")
            ->orderBy('id')
            ->whereNull("sima_id")
            ->chunk(100, function (Collection $chunk) use ($output) {
                try {
                    $barcodesStr = '';
                    /** @var OzonArticle $item */
                    foreach ($chunk as $item) {
                        $barcodesStr .= $item->article . ',';
                    }

                    $response = Http::connectTimeout(30)
                        ->retry(3, 1000, function ($exception, $request) {
                            return $exception instanceof ConnectionException;
                        })
                        ->withHeaders([
                            "Authorization" => "Bearer " . self::API_KEY
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
                                    'sima_stocks' => (int)$itemsOverall
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


    function getStocks(OutputStyle $output)
    {

        $output->writeln("getting Sima stocks..");
        DB::table("ozon_articles")
            ->orderBy('id')
            ->whereNotNull('sima_id')
            ->chunk(100, function (Collection $chunk) use ($output) {

                $chunk->map(function ($ozonArticle) use ($output) {
                    try {
                        $response = Http::acceptJson()->timeout(100000)->withHeaders([
                            "Authorization" => "Bearer " . self::API_KEY
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
