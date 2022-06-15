<?php


namespace App\Http\Api;

use App\Models\OzonArticle;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Ozon
{

    private const API_URL = 'https://api-seller.ozon.ru';
    private const HEADERS = [
        'Client-Id' => '161605',
        'Api-Key' => '81d5f89e-c7a9-4046-aa24-d11944654ed7'
    ];

    public string $reportKey = '';

    function generateReport(OutputStyle $output)
    {
        $output->writeln("Generating Ozon report...");
        $result = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->post(self::API_URL . '/v1/report/products/create',
                [
                    "language" => "DEFAULT",
                    "offer_id" => [],
                    "search" => "",
                    "sku" => [],
                    "visibility" => "ALL"
                ]);

        $this->reportKey = $result->json()['result']['code'];
        //need time to generate report
        sleep(400);
        $output->writeln("Getting and parsing Ozon report...");
        $this->postReportInfo();
    }

    function postReportInfo(): void
    {

        $result = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->post(self::API_URL . '/v1/report/info',
                [
                    'code' => $this->reportKey
                ]);

        $response = Http::withHeaders(
            self::HEADERS
        )
            ->get($result->json()['result']['file']);

        $goods = str_getcsv($response, PHP_EOL);
        unset($goods[0]);

        try {
            DB::table('ozon_articles')->truncate();
            $items = [];
            for ($i = 1; $i <= count($goods); $i++) {
                $vendorItem = explode(';', $goods[$i]);
                $vendorCode = strlen($vendorItem[0]) === 10 ? (int)substr($vendorItem[0], 2, 6) : (int)substr($vendorItem[0], 2, 7);
                if ($vendorCode !== 0) {
                    $volume = isset($vendorItem[15]) ? str_replace(['"', "'"], "", $vendorItem[15]) : 0;
                    $weight = isset($vendorItem[16]) ? str_replace(['"', "'"], "", $vendorItem[16]) : 0;
                    $items[] = [
                        'article' => $vendorCode,
                        'ozon_product_id' => (int)$vendorItem[1],
                        'name' => $vendorItem[5] ?? "",
                        'product_volume' => (float)$volume,
                        'product_weight' => (float)$weight
                    ];
                }
                if ($i % 1000 === 0) {
                    OzonArticle::insert($items);
                    $items = [];
                }
            }
        } catch (\Exception $exception) {
            var_dump($exception);
        }
    }


    function sendStocks(OutputStyle $outputStyle)
    {
        $outputStyle->writeln("Sending stocks...");
        DB::table("ozon_articles")
            ->orderBy('id')
            ->chunk(100, function (Collection $chunk) use ($outputStyle) {

                $res = [];

                $chunk->map(function ($item) use (&$res) {
                    $stocks = 0;

                    if ($item->sima_stocks >= 3 && $item->sima_stocks <= 9) {
                        $stocks = 2;
                    }

                    if ($item->sima_stocks >= 10 && $item->sima_stocks <= 15) {
                        $stocks = 5;
                    }

                    if ($item->sima_stocks > 15) {
                        $stocks = 10;
                    }
                    $res[] = [
                        "offer_id" => '66' . $item->article . '02',
                        "product_id" => $item->ozon_product_id,
                        "stock" => $stocks === 0 ? '0' : $stocks,
                        "warehouse_id" => 21858285092000
                    ];
                });
                try {
                    $res = Http::withHeaders(
                        self::HEADERS
                    )
                        ->asJson()
                        ->post(self::API_URL . '/v2/products/stocks',
                            [
                                "stocks" => $res,
                            ]);

                } catch (\Exception $exception) {
                    $outputStyle->write($exception->getMessage());
                }
            });
    }
}
