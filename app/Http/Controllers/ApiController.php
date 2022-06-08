<?php

namespace App\Http\Controllers;

use App\Excel\Export\StocksExport;
use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApiController extends Controller
{

    public function getStocks(Request $request, Ozon $ozon, Sima $sima): BinaryFileResponse
    {
        return Excel::download(new StocksExport(), 'stocks.xlsx');


        // $ozon->downloadReport();


//        $test1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NTMyMjY4ODAsIm5iZiI6MTY1MzIyNjg4MCwiZXhwIjoxNjg0NzYyODgwLCJqdGkiOjM3NDk0NjR9.c0QNvrJfgkzWQhnkR0xwTn_fWcTRX2D1tlsIfwUjzv0';
//
//        $response = Http::withHeaders([
//            "Authorization"=>"Bearer ".$test1
//        ])->get('https://www.sima-land.ru/api/v3/order/?page=35');
//
//        $url = 'https://api-seller.ozon.ru/v1/report/info';
//        $headers = array(
//            'Content-Type: application/json',
//            'Host: api-seller.ozon.ru',
//            'Client-Id: 161605',
//            'Api-Key: 81d5f89e-c7a9-4046-aa24-d11944654ed7'
//        );

//        Http::
//        $ch = curl_init();
//        $options = array(
//            CURLOPT_URL => $url,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_POSTFIELDS => '{
//    "code": "722c692f-3ec9-47a6-a0df-ba77eb5c37d5"
//}',
//            CURLOPT_HTTPHEADER => $headers
//        );
//        curl_setopt_array($ch, $options);
//        $html = curl_exec($ch);
//        curl_close($ch);
//    dump(json_encode($html));

    }


    public function calc()
    {

    }

    public function index()
    {
        return view('index');
    }

}
