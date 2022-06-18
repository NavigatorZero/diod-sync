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
        sleep(600);
        $output->writeln("Getting and parsing Ozon report...");
        $this->postReportInfo($output);
    }

    function postReportInfo($output): void
    {
        $output->writeln("Ozon report key is " . $this->reportKey);
        $result = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->retry(5, 60)
            ->post(self::API_URL . '/v1/report/info',
                [
                    'code' => $this->reportKey
                ]);

        $response = Http::withHeaders(
            self::HEADERS
        )
            ->retry(5, 60)
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

    function recalculatePrice()
    {

        OzonArticle::all()
            ->map(function (OzonArticle $ozonArticle) {
                $weight = $ozonArticle->product_weight;
                $minPrice = null;
                $maxPrice = null;
                $percent = null;
                $price = null;


                if ($weight < 1) {
                    $percent = 4;
                } elseif ($weight > 1 && $weight < 3) {
                    $percent = 4;
                } elseif ($weight > 3 && $weight < 10) {
                    $percent = 5.5;
                } elseif ($weight > 10 && $weight < 25) {
                    $percent = 6;
                } elseif ($weight > 25) {
                    $percent = 8;
                }


                if ($weight < 0.2) {
                    $minPrice = 41;
                    $maxPrice = 50;
                } else if ($weight > 0.2 and $weight < 0.3) {
                    $minPrice = 42;
                    $maxPrice = 50;
                } else if ($weight > 0.3 and $weight < 0.4) {
                    $minPrice = 43;
                    $maxPrice = 60;
                } else if ($weight > 0.4 and $weight < 0.5) {
                    $minPrice = 45;
                    $maxPrice = 65;
                } else if ($weight > 0.5 and $weight < 0.6) {
                    $minPrice = 47;
                    $maxPrice = 70;
                } else if ($weight > 0.6 and $weight < 0.7) {
                    $minPrice = 50;
                    $maxPrice = 70;
                } else if ($weight > 0.7 and $weight < 0.8) {
                    $minPrice = 53;
                    $maxPrice = 75;
                } else if ($weight > 0.8 and $weight < 0.9) {
                    $minPrice = 55;
                    $maxPrice = 75;
                } else if ($weight > 0.9 and $weight < 1) {
                    $minPrice = 55;
                    $maxPrice = 80;
                } else if ($weight > 1 and $weight < 1.1) {
                    $minPrice = 57;
                    $maxPrice = 95;
                } else if ($weight > 1.1 and $weight < 1.2) {
                    $minPrice = 59;
                    $maxPrice = 95;
                } else if ($weight > 1.2 and $weight < 1.3) {
                    $minPrice = 63;
                    $maxPrice = 100;
                } else if ($weight > 1.3 and $weight < 1.4) {
                    $minPrice = 63;
                    $maxPrice = 105;
                } else if ($weight > 1.4 and $weight < 1.5) {
                    $minPrice = 67;
                    $maxPrice = 105;
                } else if ($weight > 1.5 and $weight < 1.6) {
                    $minPrice = 67;
                    $maxPrice = 125;
                } else if ($weight > 1.6 and $weight < 1.7) {
                    $minPrice = 70;
                    $maxPrice = 125;
                } else if ($weight > 1.7 and $weight < 1.8) {
                    $minPrice = 71;
                    $maxPrice = 125;
                } else if ($weight > 1.8 and $weight < 1.9) {
                    $minPrice = 75;
                    $maxPrice = 130;
                } else if ($weight > 1.9 and $weight < 2) {
                    $minPrice = 77;
                    $maxPrice = 130;
                } else if ($weight > 2 and $weight < 3) {
                    $minPrice = 90;
                    $maxPrice = 145;
                } else if ($weight > 3 and $weight < 4) {
                    $minPrice = 115;
                    $maxPrice = 175;
                } else if ($weight > 4 and $weight < 5) {
                    $minPrice = 155;
                    $maxPrice = 215;
                } else if ($weight > 5 and $weight < 6) {
                    $minPrice = 175;
                    $maxPrice = 275;
                } else if ($weight > 6 and $weight < 7) {
                    $minPrice = 200;
                    $maxPrice = 315;
                } else if ($weight > 7 and $weight < 8) {
                    $minPrice = 215;
                    $maxPrice = 350;
                } else if ($weight > 8 and $weight < 9) {
                    $minPrice = 245;
                    $maxPrice = 385;
                } else if ($weight > 9 and $weight < 10) {
                    $minPrice = 270;
                    $maxPrice = 395;
                } else if ($weight > 10 and $weight < 11) {
                    $minPrice = 300;
                    $maxPrice = 400;
                } else if ($weight > 11 and $weight < 12) {
                    $minPrice = 315;
                    $maxPrice = 450;
                } else if ($weight > 12 and $weight < 13) {
                    $minPrice = 345;
                    $maxPrice = 490;
                } else if ($weight > 13 and $weight < 14) {
                    $minPrice = 365;
                    $maxPrice = 510;
                } else if ($weight > 14 and $weight < 15) {
                    $minPrice = 400;
                    $maxPrice = 515;
                } else if ($weight > 15 and $weight < 20) {
                    $minPrice = 485;
                    $maxPrice = 550;
                } else if ($weight > 20 and $weight < 25) {
                    $minPrice = 585;
                    $maxPrice = 650;
                } else if ($weight > 25) {
                    $minPrice = 1000;
                    $maxPrice = 1400;
                }
            });
    }
}
