<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\OzonArticle
 *
 * @property string $name
 * @property integer $article
 * @property integer $ozon_product_id
 * @property float $product_volume,
 * @property float $product_weight,
 * @property int $sima_id,
 * @property int $sima_stocks,
 * @property int $sima_wholesale_price,
 * @property int $id
 * @property boolean $is_synced
 * @property int|null $sima_order_minimum
 * @property float|null $sima_price
 * @property float|null $ozon_old_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Price|null $price
 * @method static Builder|OzonArticle newModelQuery()
 * @method static Builder|OzonArticle newQuery()
 * @method static Builder|OzonArticle query()
 * @method static Builder|OzonArticle whereArticle($value)
 * @method static Builder|OzonArticle whereCreatedAt($value)
 * @method static Builder|OzonArticle whereId($value)
 * @method static Builder|OzonArticle whereName($value)
 * @method static Builder|OzonArticle whereOzonProductId($value)
 * @method static Builder|OzonArticle wherePriceId($value)
 * @method static Builder|OzonArticle whereProductVolume($value)
 * @method static Builder|OzonArticle whereProductWeight($value)
 * @method static Builder|OzonArticle whereSimaId($value)
 * @method static Builder|OzonArticle whereSimaOrderMinimum($value)
 * @method static Builder|OzonArticle whereSimaPrice($value)
 * @method static Builder|OzonArticle whereSimaStocks($value)
 * @method static Builder|OzonArticle whereSimaWholesalePrice($value)
 * @method static Builder|OzonArticle whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $price_id
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
        'article',
        'ozon_product_id',
        'is_synced',
        'ozon_old_price'
    ];


    /**
     * Get the phone associated with the user.
     */
    public function price()
    {
        return $this->belongsTo(Price::class);
    }
}
