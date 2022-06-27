<?php

namespace App\Excel\Import;

use App\Models\OzonArticle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CommissionImport implements ToModel, WithChunkReading
{

    public function model(array $row)
    {
        $item = OzonArticle::where('article', '=', (int)substr(substr($row[0], 2), 0, -2))
            ->first();

        $item?->price()->create(['commision' => (int)$row[6]]);

    }

    public function chunkSize(): int
    {
        return 5;
    }
}
