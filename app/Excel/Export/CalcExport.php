<?php

namespace App\Excel\Export;

use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
            'Цена на озон до синхронизации',
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
            $row->highway,
            $row->article->ozon_old_price,
            '66' . $row->article->article . '02'
        ];
    }

    public function collection(): \Illuminate\Support\Collection
    {

        return Price::with("article")->whereHas('article',fn(Builder $builder)=>$builder->where('is_synced', true))->get();
    }
}
