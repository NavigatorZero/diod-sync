<?php

namespace App\Excel\Import;

use App\Models\OzonArticle;
use App\Models\Price;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CommissionImport implements ToModel, WithChunkReading
{

    public function model(array $row)
    {
        if (!is_null(($row[0]) && !is_null(substr($row[0], 2)))) {
            $item = OzonArticle::where('article', '=', (int)substr(substr($row[0], 2), 0, -2))
                ->first();

            if ($item) {
                $item->price()->associate(Price::create(['commision' => (int)$row[6]]));
                $item->save();
            }
        }
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
