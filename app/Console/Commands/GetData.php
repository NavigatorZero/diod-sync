<?php

namespace App\Console\Commands;

use App\Http\Api\Ozon;
use App\Http\Api\Sima;
use App\Models\OzonArticle;
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
    protected $signature = 'diod:sync {keyword}';

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
        $start = microtime(true);
        $ozon = new Ozon();
        $sima = new Sima();
        $skip = $this->argument('keyword');


        if (!$skip) {
            $ozon->generateReport($this->output);
            $sima->getItems($this->output);
            for ($i = 1; $i <= (int)(OzonArticle::count() / 2000); $i++) {
                if (OzonArticle::whereNull("sima_id")->count('*') > (int)(OzonArticle::count() / 100)) {
                    $this->output->writeln("getting Sima goods again to get missing items..");
                    $sima->getItems($this->output);
                }
            }
            $ozon->sendStocks($this->output);
        }

        $this->call('diod:commission');
        $this->call('diod:calc');
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        date_default_timezone_set('Asia/Yekaterinburg');
        $rememberTimeInSeconds = 360000;
        Cache::put('last_sync', Carbon::now()->format('Y-m-d h:i:s'), $rememberTimeInSeconds);
        $this->output->write('Sync finished..');
        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");

        return 0;
    }
}
