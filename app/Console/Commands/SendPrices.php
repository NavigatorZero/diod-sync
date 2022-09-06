<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Models\ObjectNotation;
use Illuminate\Console\Command;

class SendPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:sendPrices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Calculated price to ozon';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->output->write('Send Prices started..');
        $start = microtime(true);
        $ozon = new Ozon();

        $ozon->sendPrices();

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        date_default_timezone_set('Asia/Yekaterinburg');
        $this->output->write('Calc finished..');
        $this->output->write('Calc elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");
        return 0;
    }
}
