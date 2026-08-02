<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Cache;


class ProductCategory extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'product_categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'lang',
        'name',
        'image',
        'slug',
        'description',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_robots',
        'rel',
        'target',
        'status',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'description',
    ];


    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }


    public static function getCategories()
    {
        return Cache::rememberForever('product_categories', function () {
            return self::where('status', 1)->get();
        });
    }
}
