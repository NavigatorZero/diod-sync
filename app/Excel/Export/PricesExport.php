<?php

namespace App\Excel\Export;

use App\Models\OzonArticle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PricesExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Артикул',
            'Оптовая цена',
        ];
    }

    /**
     * @var OzonArticle $ozonArticle
     */
    public function map($ozonArticle): array
    {
//        $stocks = 0;
//
//        if ($ozonArticle->sima_stocks >= 3 && $ozonArticle->sima_stocks <= 9) {
//            $stocks = 2;
//        }
//
//        if ($ozonArticle->sima_stocks >= 10 && $ozonArticle->sima_stocks <= 15) {
//            $stocks = 5;
//        }
//
//        if ($ozonArticle->sima_stocks > 15) {
//            $stocks = 10;
//        }

        return [
            '66' . $ozonArticle->article . '02',
            $ozonArticle->sima_wholesale_price,
        ];
    }

    public function collection()
    {
        return OzonArticle::whereNotNull('sima_wholesale_price')->where('is_synced' ,'=', true)->get();
    }
}
