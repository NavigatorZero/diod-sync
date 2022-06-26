<?php

namespace App\Excel\Export;

use App\Models\OzonArticle;
use App\Models\Price;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CalcExport implements FromCollection, WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'ФБС',
            'Коммиссия',
            'Последняя Миля',
            'Минимальная цена',
            'Цена до',
            'Магистраль',
            'Артикул'
        ];
    }

    /**
     * @var Price $row
     */
    public function map($row): array
    {

        return [
            $row->fbs,
            $row->commision,
            $row->last_mile,
            $row->min_price,
            $row->price_after,
            $row->higway,
            '66' . $row->article->article . '02'
        ];
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return OzonArticle::whereHas("price")->get();
    }
}
