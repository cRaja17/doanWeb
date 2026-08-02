<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PostCategory extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'post_categories';
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
        'view_home',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'description',
    ];


    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_category_relation', 'category_id', 'post_id');
    }
}
