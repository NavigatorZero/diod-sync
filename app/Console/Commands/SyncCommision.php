<?php

namespace App\Console\Commands;

use App\Models\OzonArticle;
use App\Models\Price;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        $this->output->write('Sync commission started..');
        DB::connection()->disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        $start = microtime(true);
        $reader = ReaderEntityFactory::createReaderFromFile(Storage::path('public/1.xlsx'));

        $reader->open(Storage::path('public/1.xlsx'));

        foreach ($reader->getSheetIterator() as $key => $sheet) {

            if ($key === 2) {
                /** @var Row $item */
                foreach ($sheet->getRowIterator() as $item) {
                    // do stuff with the row
                    $article = $item->getCellAtIndex(0)->getValue();
                    $comission = $item->getCellAtIndex(6)->getValue();

                    if (!is_null(($article) && !is_null(substr($article, 2)))) {
                        $item = OzonArticle::where('article', '=', (int)substr(substr($article, 2), 0, -2))
                            ->first();

                        if ($item) {
                            if (!$item->price()->first()) {
                                $price = new Price(['commision' => (int)$comission]);
                                $price->save();
                                $item->price()->associate($price)->save();
                            } else {
                                $price = $item->price()->first();
                                $price->commision = (int)$comission;
                                $price->save();
                            }
                        }
                    }
                }
            }
        }

        $reader->close();
        $this->output->write('Sync commission finished..');
        $this->output->write('sync elapsed time: ' . round((microtime(true) - $start) / 60, 4) . " min");


        return 0;
    }
}
