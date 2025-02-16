<?php


namespace App\Http\Api;

use App\Models\OzonArticle;
use Illuminate\Console\OutputStyle;
use App\Enum\AutoActionEnabledStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Ozon
{

    private const API_URL = 'https://api-seller.ozon.ru';

    private const HEADERS = [
        'Client-Id' => '360163',
        'Api-Key' => 'a84527fc-32e8-4a2e-a96c-1d480ec255dd'
    ];

    private const HEADERS2 = [
        'Client-Id' => '360163',
        'Api-Key' => '8f23718b-adfc-44c3-9c6b-63670412fc52'
    ];

    private const CATEGORIES = [];

    public string $reportKey = '';

    public string $lastId = '';


    /** @var int|\Closure самовывоз */
    private int $pickup = 23890889223000;

    /** @var int|\Closure курьер */
    private int $courier = 23890904458000;


    function getArtcileList(OutputStyle $output, $visibility = "ARCHIVED")
    {
        $output->writeln("Getting Ozon items...");

        $limit = 1000;

        $articleResponse = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->post(self::API_URL . '/v2/product/list',
                [
                    "filter" => [
                        "visibility" => $visibility
                    ],
                    "last_id" => $this->lastId,
                    "limit" => $limit
                ]);

        if ($articleResponse->status() === 200) {
            $items = $articleResponse->json()['result']['items'];
            foreach ($items as $key => $value) {
                if ($item = OzonArticle::where('ozon_product_id', $value['product_id'])->first()) {
                    $item->forceDelete();
                }
            }
            $this->lastId = $articleResponse->json()['result']['last_id'];
            if (count($items) !== $limit) {
                $output->writeln("Getting Ozon items finished...");
            } else {
                $this->getArtcileList($output, $visibility);
            }
        } else {
            $output->write($articleResponse->status());
        }
    }

    function generateReport(OutputStyle $output, string $visibility = "ALL")
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
                    "visibility" => $visibility
                ]);

        $this->reportKey = $result->json()['result']['code'];
        $this->postReportInfo($output);
    }

    function postReportInfo(OutputStyle $output): void
    {
        $output->writeln("Ozon report key is " . $this->reportKey);
        $result = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->retry(5, 100)
            ->post(self::API_URL . '/v1/report/info',
                [
                    'code' => $this->reportKey
                ]);

        if ($result->json()['result']['status'] === 'success') {
            try {
                $output->writeln("Ozon report parsing..");
                $response = Http::withHeaders(
                    self::HEADERS
                )
                    ->get($result->json()['result']['file']);

                $goods = str_getcsv($response, PHP_EOL);
                unset($goods[0]);

                for ($i = 1; $i <= count($goods); $i++) {
                    $vendorItem = str_getcsv($goods[$i], ";", '"','"');

                    if(str_starts_with($vendorItem[0], "'УТМ") || str_starts_with($vendorItem[0], "УТМ" )) {
                        continue;
                    }

                    $vendorCode = strlen($vendorItem[0]) === 12 ? (int)substr($vendorItem[0], 3, 7) : (int)substr($vendorItem[0], 3, 6);

                    if ($vendorCode !== 0) {
                        $volume = isset($vendorItem[15]) ? str_replace(['"', "'"], "", $vendorItem[15]) : 0;
                        $weight = isset($vendorItem[16]) ? str_replace(['"', "'"], "", $vendorItem[16]) : 0;
                        $price = isset($vendorItem[22]) ? str_replace(['"', "'"], "", $vendorItem[22]) : 0;
                        $productId = isset($vendorItem[1]) ? str_replace(['"', "'"], "", $vendorItem[1]) : 0;

                        $values = [
                            'ozon_product_id' => (int)$productId,
                            'name' => $vendorItem[5] ?? "",
                            'product_volume' => (float)$volume,
                            'product_weight' => (float)$weight,
                            'ozon_old_price' => (float)$price
                        ];

                        if ($item = OzonArticle::withoutGlobalScopes()->where('article', $vendorCode)->first()) {
                            if (!isset($vendorItem[15]) || !isset($vendorItem[16])) {
                                $simaArr = Sima::getOneItemInfo($item);
                                if (!isset($vendorItem[15])) $values['product_volume'] = $simaArr['product_volume'];
                                if (!isset($vendorItem[16])) $values['product_weight'] = $simaArr['weight'] / 1000;
                            }

                            if($item->trashed()) $item->restore();

                            $item->update($values);
                        } else {
                            $item = new OzonArticle();
                            $item->article = $vendorCode;
                            $item->fill($values)->save();
                        }
                    }
                }

                $output->writeln("Ozon report parsed successfully");
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        } else {
            $output->writeln("Report with status " . $result->json()['result']['status'] . " repeating..");

            if ($result->json()['result']['status'] === 'failed') {
                $this->generateReport($output);
            }

            sleep(300);
            $this->postReportInfo($output);
        }
    }

    function sendStocks(OutputStyle $outputStyle)
    {
        $outputStyle->writeln("Sending stocks...");
        DB::table("ozon_articles")// TODO add global scope
        ->where('is_synced', true)
            ->orderBy('id')
            ->chunk(100, function (Collection $chunk) use ($outputStyle) {

                $res = [];

                $chunk->map(function (\stdClass $item) use (&$res) {
                   // $stocks = 0;

                    if($item->raketa_stocks > 0) {
                        $stocks = $item->raketa_stocks;
                    } else {
                        $stocks = $item->sima_stocks;

//                        if ($item->sima_stocks >= 3 && $item->sima_stocks <= 9) {
//                            $stocks = 2;
//                        }
//
//                        if ($item->sima_stocks >= 10 && $item->sima_stocks <= 15) {
//                            $stocks = 5;
//                        }
//
//                        if ($item->sima_stocks > 15) {
//                            $stocks = 10;
//                        }
                    }

                    $res[] = [
                        "offer_id" => '66' . $item->article . '02',
                        "product_id" => $item->ozon_product_id,
                        "stock" => $stocks === 0 ? '0' : $stocks,
                        "warehouse_id" => $this->pickup
                    ];
                });
                try {
                    $response = Http::withHeaders(
                        self::HEADERS
                    )
                        ->asJson()
                        ->retry(3, 5000)
                        ->post(self::API_URL . '/v2/products/stocks',
                            [
                                "stocks" => $res,
                            ]);

//                    $response2 = Http::withHeaders(
//                        self::HEADERS2
//                    )
//                        ->asJson()
//                        ->post(self::API_URL . '/v2/products/stocks',
//                            [
//                                "stocks" => $res,
//                            ]);

                    if ($response->status() !== 200) {
                        $outputStyle->write('sending stocks error: ' . $response->json()['message']);
                    }

                } catch (\Exception $exception) {
                    $outputStyle->write($exception->getMessage());
                }
            });
    }

    function getOzonHighway(OzonArticle $ozonArticle, float $income): float|int|null
    {
        $weight = $ozonArticle->product_weight;
        $minPrice = null;
        $maxPrice = null;
        $percent = null;

        if ($weight < 1) {
            $percent = 0.04;
        } elseif ($weight >= 1 and $weight < 3) {
            $percent = 0.05;
        } elseif ($weight >= 3 and $weight < 10) {
            $percent = 0.055;
        } elseif ($weight >= 10 and $weight < 25) {
            $percent = 0.06;
        } elseif ($weight > 25) {
            $percent = 0.08;
        }

        if ($weight < 0.2) {
            $minPrice = 41;
            $maxPrice = 50;
        } else if ($weight >= 0.2 and $weight < 0.3) {
            $minPrice = 42;
            $maxPrice = 50;
        } else if ($weight >= 0.3 and $weight < 0.4) {
            $minPrice = 43;
            $maxPrice = 60;
        } else if ($weight >= 0.4 and $weight < 0.5) {
            $minPrice = 45;
            $maxPrice = 65;
        } else if ($weight >= 0.5 and $weight < 0.6) {
            $minPrice = 47;
            $maxPrice = 70;
        } else if ($weight >= 0.6 and $weight < 0.7) {
            $minPrice = 50;
            $maxPrice = 70;
        } else if ($weight >= 0.7 and $weight < 0.8) {
            $minPrice = 53;
            $maxPrice = 75;
        } else if ($weight >= 0.8 and $weight < 0.9) {
            $minPrice = 55;
            $maxPrice = 75;
        } else if ($weight >= 0.9 and $weight < 1) {
            $minPrice = 55;
            $maxPrice = 80;
        } else if ($weight >= 1 and $weight < 1.1) {
            $minPrice = 57;
            $maxPrice = 95;
        } else if ($weight >= 1.1 and $weight < 1.2) {
            $minPrice = 59;
            $maxPrice = 95;
        } else if ($weight >= 1.2 and $weight < 1.3) {
            $minPrice = 63;
            $maxPrice = 100;
        } else if ($weight >= 1.3 and $weight < 1.4) {
            $minPrice = 63;
            $maxPrice = 105;
        } else if ($weight >= 1.4 and $weight < 1.5) {
            $minPrice = 67;
            $maxPrice = 105;
        } else if ($weight >= 1.5 and $weight < 1.6) {
            $minPrice = 67;
            $maxPrice = 125;
        } else if ($weight >= 1.6 and $weight < 1.7) {
            $minPrice = 70;
            $maxPrice = 125;
        } else if ($weight >= 1.7 and $weight < 1.8) {
            $minPrice = 71;
            $maxPrice = 125;
        } else if ($weight >= 1.8 and $weight < 1.9) {
            $minPrice = 75;
            $maxPrice = 130;
        } else if ($weight >= 1.9 and $weight < 2) {
            $minPrice = 77;
            $maxPrice = 130;
        } else if ($weight >= 2 and $weight < 3) {
            $minPrice = 90;
            $maxPrice = 145;
        } else if ($weight >= 3 and $weight < 4) {
            $minPrice = 115;
            $maxPrice = 175;
        } else if ($weight >= 4 and $weight < 5) {
            $minPrice = 155;
            $maxPrice = 215;
        } else if ($weight >= 5 and $weight < 6) {
            $minPrice = 175;
            $maxPrice = 275;
        } else if ($weight >= 6 and $weight < 7) {
            $minPrice = 200;
            $maxPrice = 315;
        } else if ($weight >= 7 and $weight < 8) {
            $minPrice = 215;
            $maxPrice = 350;
        } else if ($weight >= 8 and $weight < 9) {
            $minPrice = 245;
            $maxPrice = 385;
        } else if ($weight >= 9 and $weight < 10) {
            $minPrice = 270;
            $maxPrice = 395;
        } else if ($weight >= 10 and $weight < 11) {
            $minPrice = 300;
            $maxPrice = 400;
        } else if ($weight >= 11 and $weight < 12) {
            $minPrice = 315;
            $maxPrice = 450;
        } else if ($weight >= 12 and $weight < 13) {
            $minPrice = 345;
            $maxPrice = 490;
        } else if ($weight >= 13 and $weight < 14) {
            $minPrice = 365;
            $maxPrice = 510;
        } else if ($weight >= 14 and $weight < 15) {
            $minPrice = 400;
            $maxPrice = 515;
        } else if ($weight >= 15 and $weight < 20) {
            $minPrice = 485;
            $maxPrice = 550;
        } else if ($weight >= 20 and $weight < 25) {
            $minPrice = 585;
            $maxPrice = 650;
        } else if ($weight >= 25) {
            $minPrice = 1000;
            $maxPrice = 1400;
        }

        $highway = $income * $percent;


        if ($highway < $minPrice) {
            return $minPrice * 1.3;
        } elseif ($highway > $maxPrice) {
            return $maxPrice * 1.3;
        }

        return $highway * 1.3;
    }


    function calcIncome()
    {
        OzonArticle::with("price")
            ->where('is_synced', true)
            ->get()
            ->map(function (OzonArticle $ozonArticle) {
                $wholeasale = $ozonArticle->provider_whole_sale_price;
                $multiplicator = null;

                if ($wholeasale < 10) {
                    $multiplicator = 2700;
                } else if ($wholeasale >= 10 and $wholeasale < 20) {
                    $multiplicator = 980;
                } else if ($wholeasale >= 20 and $wholeasale < 30) {
                    $multiplicator = 630;
                } else if ($wholeasale >= 30 and $wholeasale < 40) {
                    $multiplicator = 445;
                } else if ($wholeasale >= 40 and $wholeasale < 50) {
                    $multiplicator = 355;
                } else if ($wholeasale >= 50 and $wholeasale < 75) {
                    $multiplicator = 265;
                } else if ($wholeasale >= 75 and $wholeasale < 100) {
                    $multiplicator = 195;
                } else if ($wholeasale >= 100 and $wholeasale < 150) {
                    $multiplicator = 140;
                } else if ($wholeasale >= 150 and $wholeasale < 200) {
                    $multiplicator = 105;
                } else if ($wholeasale >= 200 and $wholeasale < 250) {
                    $multiplicator = 85;
                } else if ($wholeasale >= 250 and $wholeasale < 300) {
                    $multiplicator = 70;
                } else if ($wholeasale >= 300 and $wholeasale < 400) {
                    $multiplicator = 60;
                } else if ($wholeasale >= 400 and $wholeasale < 500) {
                    $multiplicator = 50;
                } else if ($wholeasale >= 500 and $wholeasale < 750) {
                    $multiplicator = 40;
                } else if ($wholeasale >= 750 and $wholeasale < 1000) {
                    $multiplicator = 35;
//                } else if ($wholeasale >= 1000 and $wholeasale < 1500) {
//                    $multiplicator = 31;
                } else if ($wholeasale >= 1000 and $wholeasale < 2000) {
                    $multiplicator = 31;
//                } else if ($wholeasale >= 2000 and $wholeasale < 2500) {
//                    $multiplicator = 40;
                } else if ($wholeasale >= 2000 and $wholeasale < 3000) {
                    $multiplicator = 27;
//                } else if ($wholeasale >= 3000 and $wholeasale < 4000) {
//                    $multiplicator = 35;
//                } else if ($wholeasale >= 4000 and $wholeasale < 5000) {
//                    $multiplicator = 35;
//                } else if ($wholeasale >= 5000 and $wholeasale < 6000) {
//                    $multiplicator = 35;
                } else if ($wholeasale >= 3000 and $wholeasale < 7500) {
                    $multiplicator = 25;
//                } else if ($wholeasale >= 7500 and $wholeasale < 10000) {
//                    $multiplicator = 30;
                } else if ($wholeasale >= 7500 and $wholeasale < 12500) {
                    $multiplicator = 22;
//                } else if ($wholeasale >= 12500 and $wholeasale < 15000) {
//                    $multiplicator = 30;
                } else if ($wholeasale >= 15000 and $wholeasale < 20000) {
                    $multiplicator = 20;
                } else if ($wholeasale >= 20000 and $wholeasale < 25000) {
                    $multiplicator = 18;
                } else if ($wholeasale >= 25000 and $wholeasale < 30000) {
                    $multiplicator = 16;
                } else if ($wholeasale >= 30000 and $wholeasale < 40000) {
                    $multiplicator = 15;
                } else if ($wholeasale >= 40000) {
                    $multiplicator = 13;
                }

                $income = $wholeasale + $wholeasale / 100 * $multiplicator + 45;

                $lastMile = $this->calcLastMile($income);
                $highway = $this->getOzonHighway($ozonArticle, $income);

                $fbs = $income / 100 * ($ozonArticle->price?->commision ?? 9) + 25 + $lastMile + $highway;
                $minPrice = $fbs + $wholeasale + 45;

                if ($income < $minPrice) {
                    $income = $minPrice + 100;
                }

                if ($income < 500) {
                    $multiplicator = 8;
                } else if ($income >= 500 and $income < 1000) {
                    $multiplicator = 7;
                } else if ($income >= 1000 and $income < 2000) {
                    $multiplicator = 6;
                } else if ($income >= 2000 and $income < 3000) {
                    $multiplicator = 5;
                } else if ($income >= 3000 and $income < 4000) {
                    $multiplicator = 4;
                } else if ($income >= 4000) {
                    $multiplicator = 3;
                }

                $incomeFull = $income + $income * $multiplicator / 100;
                $priceBefore = $incomeFull + $incomeFull * 115 / 100;

                $lastMile = $this->calcLastMile($incomeFull);
                $highway = $this->getOzonHighway($ozonArticle, $incomeFull);
                $fbs = $incomeFull / 100 * ($ozonArticle->price?->commision ?? 9) + 25 + $lastMile + $highway;
                $minPrice = $fbs + $wholeasale + 45;
//                $fbs = $incomeFull * (($ozonArticle->price->commision ?? 9) / 100) + 25 + $lastMile + $highway;
//
//                $minPrice = $fbs + $ozonArticle->sima_wholesale_price + 0.93 * $ozonArticle->product_volume + 70 + (40 + $highway);
//
//                if ($incomeFull / 2 > $minPrice) {
//                    $minPrice = $incomeFull / 2 + 10;
//                }

                $ozonArticle->price()->update([
                    'price_after' => !is_null($priceBefore) ? round($priceBefore, 2) : 0,
                    'fbs' => !is_null($fbs) ? round($fbs, 2) : 0,
                    'min_price' => !is_null($minPrice) ? round($minPrice, 2) : 0,
                    'last_mile' => !is_null($lastMile) ? round($lastMile, 2) : 0,
                    'highway' => !is_null($highway) ? round($highway, 2) : 0,
                    'income' => !is_null($incomeFull) ? round($incomeFull, 2) : 0
                ]);
            });
    }


    function calcLastMile(float $income): float|int
    {
        $lastMile = $income * 0.05;
        if ($lastMile < 60) {
            return 60;
        }

        if ($lastMile > 60 && $lastMile < 350) {
            return $lastMile;
        }

        return 350;
    }


    public function sendPrices()
    {

        $test = [];
        $prices = OzonArticle::whereIn('ozon_product_id',
            [
                221676608,
                322706381
//                895734,
//                895732,
//                1377352,
//                6922382,
//                7461778,
//                2827193,
//                5139481,
//                5084434,
//                5084433,
//                5084436,
            ])->with('price')
            ->get()
            ->map(function (OzonArticle $ozonArticle) use (&$test) {
                $test[] = [
                    'auto_action_enabled' => AutoActionEnabledStatus::ENABLED,
                    'min_price' => (string)  $ozonArticle->price->min_price,
                 //   'offer_id' => (string) 66 .$ozonArticle->article. 02,
                    'old_price' => (string)  $ozonArticle->price->price_after,
                    'price' => (string)  $ozonArticle->price->income,
                    'product_id' => $ozonArticle->ozon_product_id
                ];
            })->toArray();


        $response = Http::withHeaders(
            self::HEADERS
        )
            ->asJson()
            ->post(self::API_URL . '/v1/product/import/prices',
                [
                    "prices" => $test,
                ]);

    }
}
