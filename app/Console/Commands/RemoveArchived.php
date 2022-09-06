<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use App\Models\ObjectNotation;
use Illuminate\Console\Command;

class RemoveArchived extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:remove-archived';

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
        $this->output->write('Remove archived started..');
        $start = microtime(true);
        $ozon = new Ozon();
        $ozon->getArtcileList($this->output, "ARCHIVED");

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        date_default_timezone_set('Asia/Yekaterinburg');
        $this->output->write('Remove archived finished..');
        $this->output->write('Remove archived elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");
        return 0;
    }
}
