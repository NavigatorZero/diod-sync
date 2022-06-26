<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use Illuminate\Console\Command;

class CalcPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:calc';

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
        $this->output->write('Calc started..');
        $start = microtime(true);
        $ozon = new Ozon();

        $ozon->calcIncome();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        date_default_timezone_set('Asia/Yekaterinburg');
        $rememberTimeInSeconds = 360000;
        $this->output->write('Sync finished..');
        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");

        return 0;
    }
}
