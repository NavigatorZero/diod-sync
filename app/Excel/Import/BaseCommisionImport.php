<?php

namespace App\Excel\Import;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaseCommisionImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Товары и цены' => new CommissionImport(),
        ];
    }
}

