<?php

namespace App\Console\Commands;

use App\Http\Api\Wildberries;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;


class UpdateLowStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:wildberries:stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync low stocks on wildberries';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $this->output->write('Sync started in ' . Carbon::now()->format('Y-m-d h:i:s'));
        $start = microtime(true);
        $this->output->write('sync low stocks on wildberries');
        /** @var Wildberries $wildberries */
        $wildberries = App::make(Wildberries::class);
        try {
            $wildberries->updateLowStocks($this->output);
        } catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }

        $this->output->write('Sync finished..');
        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");

        return 0;
    }
}
