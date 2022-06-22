<?php

namespace App\Excel\Import;

class CommissionImport
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
