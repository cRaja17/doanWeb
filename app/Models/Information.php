<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Information extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'information';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_robots',
        'rel',
        'target',
        'status',
        'view_header',
        'view_footer',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'description',
    ];

    public static function getInformation()
    {
        return Cache::rememberForever('information', function () {
            return self::where('status', 1)->get();
        });
    }
}
