<?php

namespace App\Console\Commands;

use App\Http\Api\Wildberries;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cmd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $wildbberies = App::make(Wildberries::class);
        $wildbberies->getArticleList();
        $wildbberies->sendStocks($this->output);
        $this->output->write('ok');
        return 0;
    }
}
