<?php

namespace App\Excel\Import;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaseCommisionImport implements WithMultipleSheets, WithChunkReading
{
    public function sheets(): array
    {
        return [
            'Товары и цены' => new CommissionImport(),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

