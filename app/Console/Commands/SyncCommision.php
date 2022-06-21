<?php

namespace App\Console\Commands;

use App\Excel\Import\CommissionImport;
use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SyncCommision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected bool $needToDownload = true;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->output->write('Sync started..');
        $start = microtime(true);;
        $ozon = new Ozon();
        $sima = new Sima();

        Excel::import(new CommissionImport, Storage::path('public/1.xlsx'), null, \Maatwebsite\Excel\Excel::XLSX);
//
//        setlocale(LC_TIME, 'ru_RU.UTF-8');
//        date_default_timezone_set('Asia/Yekaterinburg');
//        $rememberTimeInSeconds = 360000;
//        Cache::put('last_sync', Carbon::now()->format('Y-m-d h:i:s'), $rememberTimeInSeconds);
//        $this->output->write('Sync finished..');
//        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");


        return 0;
    }
}
