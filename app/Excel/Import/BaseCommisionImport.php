<?php

namespace App\Excel\Import;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithChunkReading;

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

