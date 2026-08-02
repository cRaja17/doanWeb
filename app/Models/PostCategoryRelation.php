<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PostCategoryRelation extends Model
{
    use HasFactory;

    protected $table = 'post_category_relation';

    protected $fillable = [
        'post_id',
        'category_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class);
    }
}
