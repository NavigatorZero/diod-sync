<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:sync';

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
        $this->output->write('hello world');
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        date_default_timezone_set('Asia/Yekaterinburg');
        $rememberTimeInSeconds = 360000;
        Cache::put('last_sync', Carbon::now()->format('Y-m-d h:i:s'), $rememberTimeInSeconds);

        return;
        $ozon = new Ozon();
        $sima = new Sima();

        if ($this->needToDownload) {

            $ozon->generateReport($this->output);

            $sima->getItems($this->output);
        }

        $sima->getStocks($this->output);
        return 0;
    }
}
