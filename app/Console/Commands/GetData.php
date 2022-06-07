<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use Illuminate\Console\Command;

class GetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:parse';

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
