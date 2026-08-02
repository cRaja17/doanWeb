<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LanguageLine extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'language_lines';

    protected $primaryKey = 'id';

    protected $fillable = [
        'group',
        'key',
        'text',
    ];

    public $translatable = [
        'text'
    ];
}
