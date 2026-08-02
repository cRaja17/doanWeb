<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    use HasFactory;

    protected $table = 'support_requests';

    protected $fillable = [
        'conversation_id',
        'name',
        'contact',
        'locale',
        'question',
        'context',
        'status',
        'staff_reply',
        'replied_at',
        'ip',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];
}
