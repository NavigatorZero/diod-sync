<?php

namespace App\Excel\Export;

use App\Models\OzonArticle;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StocksExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Название склада',
            'Артикул',
            'Имя(необьязательно)',
            'Колличество',
            'Заполнение обьязтаельных ячеек'
        ];
    }

    /**
     * @var OzonArticle $ozonArticle
     */
    public function map($ozonArticle): array
    {
        $stocks = 0;

        if ($ozonArticle->sima_stocks >= 3 && $ozonArticle->sima_stocks <= 9) {
            $stocks = 2;
        }

        if ($ozonArticle->sima_stocks >= 10 && $ozonArticle->sima_stocks <= 15) {
            $stocks = 5;
        }

        if ($ozonArticle->sima_stocks > 15) {
            $stocks = 10;
        }

        return [
            'Курьер (21858285092000)',
            '66' . $ozonArticle->article . '02',
            '',
            $stocks === 0 ? '0' : $stocks,
            'Заполнены'
        ];
    }

    public function collection()
    {
        return OzonArticle::whereNotNull('sima_stocks')->where('is_synced' ,'=', true)->get();
    }
}
