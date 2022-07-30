<?php

namespace App\Http\Controllers;

use App\Excel\Export\CalcExport;
use App\Excel\Export\StocksExport;
use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use App\Models\ObjectNotation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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


    public function commission(Request $req): Application|Factory|View
    {
        if ($file = $req->file('excel_commission')) {
            $file->storeAs('public/', "1.xlsx");
        }

        return view('home', ["json" => json_decode(ObjectNotation::where("key", "sync")->first()->value), "file_msg" => "Файл загружен успешно!"]);
    }

    public function calcPrice(): BinaryFileResponse
    {
        return Excel::download(new CalcExport(), 'prices.xlsx');
    }

    public function index(): Application|Factory|View
    {
        return view('home', ["json" => json_decode(ObjectNotation::where("key", "sync")->first()->value)]);
    }

    public function changeSyncSettings(Request $req): Factory|View|Application
    {
        $jsonModel = ObjectNotation::where("key", "sync")->first();
        $item = json_decode($jsonModel->value);
        $item->first_sync = (int)$req->get("first_sync_input");
        $item->second_sync = (int)$req->get("second_sync_input");
        $jsonModel->value = json_encode($item);
        $jsonModel->save();

        return view('home', ["json" => json_decode(ObjectNotation::where("key", "sync")->first()->value), "msg" => "Настройки сохранены успешно!"]);
    }
}
