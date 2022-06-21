<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  App\Models\Price
 * @property float|null $commission
 * @property float|null $price_after
 * @property float|null $fbs
 * @property float|null $min_price,
 * @property float|null $last_mile,
 * @mixin \Eloquent
 *
 */
class Price extends Model
{
    use HasFactory;

    protected $table = 'ozon_articles';

    protected $fillable = [
        'commission',
        'price_after',
        'fbs',
        'min_price'
    ];

    public function article()
    {
        return $this->belongsTo(OzonArticle::class);
    }
}
