<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 *  App\Models\OzonArticle
 * @property string $name
 * @property integer $article
 * @property integer $ozon_product_id
 * @property float $product_volume,
 * @property float $product_weight,
 * @property int $sima_id,
 * @property int $sima_stocks,
 * @property int $sima_wholesale_price,
 * @mixin \Eloquent
 *
 */
class OzonArticle extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ozon_articles';

    protected $fillable = [
        'name',
        'product_volume',
        'product_weight',
        'article'
    ];

    /**
     * Get the phone associated with the user.
     */
    public function price()
    {
        return $this->hasOne(Price::class);
    }
}
