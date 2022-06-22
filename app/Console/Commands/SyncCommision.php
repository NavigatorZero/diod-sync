<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use App\Models\OzonArticle;
use App\Models\Price;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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

        /** Create a new Xls Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setLoadSheetsOnly('Товары и цены');
        $spreadsheet = $reader->load(Storage::path('public/1.xlsx'));

        foreach ($spreadsheet->getActiveSheet()->toArray() as $row) {
            $item = OzonArticle::where('article', '=', (int)substr(substr($row[0], 2), 0, -2))
                ->first();

            if ($item) {
                $price = new Price(['commission' => (int)$row[6]]);
                $price->article()->associate($item);
                $price->save();
            }
        }


//        setlocale(LC_TIME, 'ru_RU.UTF-8');
//        date_default_timezone_set('Asia/Yekaterinburg');
//        $rememberTimeInSeconds = 360000;
//        Cache::put('last_sync', Carbon::now()->format('Y-m-d h:i:s'), $rememberTimeInSeconds);
//        $this->output->write('Sync finished..');
//        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");


        return 0;
    }
}
