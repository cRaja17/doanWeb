<?php

namespace App\Models;

use App\Models\Concerns\FlushesFrontendCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use FlushesFrontendCache, HasFactory;

    protected $table = 'config';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];

    /**
     * Cửa hàng có đang bật thanh toán trực tuyến hay không (cờ `status_payment`).
     * Thiếu cấu hình thì coi như tắt: mở nhầm cổng thanh toán tai hại hơn là
     * tạm thời chỉ cho đặt hàng không thanh toán.
     */
    public static function paymentEnabled(): bool
    {
        return (string) static::query()->where('name', 'status_payment')->value('value') === '1';
    }
}
