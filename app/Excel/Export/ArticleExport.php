<?php

namespace App\Excel\Export;

use App\Models\OzonArticle;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArticleExport implements FromCollection, WithHeadings, WithMapping
{

    protected $created_at;

    function __construct($created_at) {
        $this->created_at = $created_at;
    }


    public function headings(): array
    {
        return [
            'Артикул',
            'Наименование',
        ];
    }

    /**
     * @var OzonArticle $row
     */
    public function map($row): array
    {
        return [
            '66' . $row->article . '02',
            $row->name,
        ];
    }

    public function collection(): Collection
    {
        $sqlDate = new Carbon($this->created_at);
        $dateS = $sqlDate->startOfDay()->toDateTimeString();
        $dateE = $sqlDate->endOfDay()->toDateTimeString();
        return OzonArticle::whereBetween('created_at', [$dateS, $dateE])->get();
    }
}
