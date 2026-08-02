<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'product_category_id',
        'name',
        'code',
        'model',
        'image',
        'multiple_image',
        'summary',
        'description',
        'quantity',
        'price',
        'slug',
        'tag',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_robots',
        'rel',
        'target',
        'made',
        'unit',
        'link_afiliate',
        'status',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'summary',
        'description',
        'price',
        'unit',
        'made',
    ];

    // Nếu sản phẩm chỉ có một danh mục:
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
