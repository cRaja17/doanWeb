<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'id';

    protected $fillable = [
        'transaction_code',
        'payos_order_code',
        'payos_payment_link_id',
        'payos_checkout_url',
        'payos_reference',
        'paid_at',
        'name',
        'gender',
        'email',
        'phone',
        'address',
        'message',
        'method_payment',
        'content',
        'total',
        'unit_payment',
        'ip',
        'proxy',
        'status',
        'order_status'
    ];

    protected $casts = [
        'payos_order_code' => 'integer',
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Trạng thái thanh toán - cột `status`
    |--------------------------------------------------------------------------
    |
    | Các hằng dưới đây đồng thời là key trong config('config.payment.status'),
    | nơi chứa nhãn đa ngôn ngữ hiển thị cho khách ở trang cảm ơn và email.
    |
    */
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    /*
    |--------------------------------------------------------------------------
    | Tình trạng xử lý đơn - cột `order_status`
    |--------------------------------------------------------------------------
    */
    public const ORDER_STATUS_WAITING = 'Chờ xác nhận';

    public const ORDER_STATUS_CONTACTED = 'Đã liên hệ';

    public const ORDER_STATUS_DELIVERED = 'Đã giao hàng';

    /**
     * Tuỳ chọn trạng thái thanh toán cho form/bảng admin (giá trị => nhãn).
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ thanh toán',
            self::STATUS_PAID => 'Đã thanh toán',
            self::STATUS_FAILED => 'Thanh toán thất bại',
            self::STATUS_CANCELLED => 'Đã huỷ thanh toán',
        ];
    }

    /**
     * Tuỳ chọn tình trạng xử lý đơn cho form/bảng admin.
     */
    public static function orderStatusOptions(): array
    {
        return [
            self::ORDER_STATUS_WAITING => self::ORDER_STATUS_WAITING,
            self::ORDER_STATUS_CONTACTED => self::ORDER_STATUS_CONTACTED,
            self::ORDER_STATUS_DELIVERED => self::ORDER_STATUS_DELIVERED,
        ];
    }

    /**
     * Nhãn trạng thái thanh toán theo ngôn ngữ đang xem (dùng cho khách).
     */
    public function statusLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return config('config.payment.status.' . $this->status . '.' . $locale)
            ?? (self::statusOptions()[$this->status] ?? (string) $this->status);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public static function genCode($id)
    {
        $now = Carbon::now();
        $orderCode = 'DH' . $now->format('YmdHis') . $id;
        return $orderCode;
    }
}
