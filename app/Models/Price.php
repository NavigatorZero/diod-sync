<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Price
 *
 * @property float|null $commision
 * @property float|null $price_after
 * @property float|null $fbs
 * @mixin \Eloquent
 * @property int $id
 * @property float|null $min_price
 * @property float|null $last_mile
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OzonArticle|null $article
 * @method static Builder|Price newModelQuery()
 * @method static Builder|Price newQuery()
 * @method static Builder|Price query()
 * @method static Builder|Price whereCommision($value)
 * @method static Builder|Price whereCreatedAt($value)
 * @method static Builder|Price whereFbs($value)
 * @method static Builder|Price whereId($value)
 * @method static Builder|Price whereLastMile($value)
 * @method static Builder|Price whereMinPrice($value)
 * @method static Builder|Price wherePriceAfter($value)
 * @method static Builder|Price whereUpdatedAt($value)
 * @property float|null $highway
 * @property float|null $income
 * @method static Builder|Price whereHighway($value)
 * @method static Builder|Price whereIncome($value)
 */
class Price extends Model
{
    use HasFactory;

    protected $table = 'price';

    protected $fillable = [
        'commision',
        'price_after',
        'fbs',
        'min_price',
        'last_mile',
        'highway'
    ];

    public function article()
    {
        return $this->hasOne(OzonArticle::class);
    }
}
