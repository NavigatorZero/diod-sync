<?php

namespace App\Excel\Import;

use App\Models\OzonArticle;
use App\Models\Price;
use Maatwebsite\Excel\Concerns\ToCollection;

class CommissionImport implements ToCollection
{

    public function collection(\Illuminate\Support\Collection $rows)
    {
        foreach ($rows as $row) {
            $item = OzonArticle::where('ozon_product_id', '=', substr(substr($row[0], 0, 2), 0, -2))
                ->first();

            $item?->price()->save(new Price(['commission' => $row]));
        }
    }
}
