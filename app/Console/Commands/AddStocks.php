<?php

namespace App\Console\Commands;

use App\Models\OzonArticle;
use App\Models\Price;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class AddStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diod:stocks';

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
        $this->output->write('sync stocks started..');
        DB::connection()->disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        if(Storage::exists('public/stocks.xlsx')) {
            $reader = ReaderEntityFactory::createReaderFromFile(Storage::path('public/stocks.xlsx'));
            $reader->open(Storage::path('public/stocks.xlsx'));
            foreach ($reader->getSheetIterator() as $key => $sheet) {
                /** @var Row $item */
                foreach ($sheet->getRowIterator() as $item) {
                    $article = $item->getCellAtIndex(0)->getValue();
                    $stocks = $item->getCellAtIndex(1)->getValue();

                    if (!is_null(($article) && !is_null(substr($article, 2)))) {
                        $item = OzonArticle::where('article', '=', (int)substr(substr($article, 2), 0, -2))
                            ->first();

                        if ($item) {
                            $item->raketa_stocks += (int)$stocks;
                            $item->save();
                        }
                    }
                }
            }

            $reader->close();
            $this->output->write('sync stocks finished');

        }
        return 0;
    }
}
