<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SimaArticle
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimaArticle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SimaArticle extends Model
{
    use HasFactory;
}
