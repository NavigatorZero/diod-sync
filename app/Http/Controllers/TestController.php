<?php

namespace App\Http\Controllers;

use App\Classes\ObjectNotations\Sync;
use App\Excel\Export\ArticleExport;
use App\Excel\Export\CalcExport;
use App\Excel\Export\StocksExport;
use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use App\Http\Api\TelegramBot;
use App\Http\Api\Wildberries;
use App\Models\ObjectNotation;
use App\Models\OzonArticle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TestController extends Controller
{

    public function test(Request $request, Wildberries $wildberries): bool|string
    {
        //$wildberries->uploadStocks();

        dump(OzonArticle::where('is_synced',true)
            ->where('sima_stocks','<=', '10')
            ->whereNotNull('willdberries_barcode')->first());
        return json_encode($request->all());
    }


}
