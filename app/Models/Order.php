<?php

namespace App\Models;

use App\Exceptions\OutOfStockException;
use App\Models\Concerns\PreventsDeletion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Throwable;

class Order extends Model
{
    use HasFactory, PreventsDeletion;

    /**
     * Đơn hàng là chứng từ của một giao dịch có thật, kèm theo dòng hàng sẽ bị
     * xoá theo (order_details cascade). Cần đóng đơn thì đổi trạng thái sang
     * "Đã huỷ thanh toán" - xem markAsCancelled().
     */
    public function deletionBlockedReason(): string
    {
        return 'Đơn hàng không được phép xoá. Dùng trạng thái "Đã huỷ thanh toán" nếu cần đóng đơn.';
    }

    protected $table = 'orders';

    protected $primaryKey = 'id';

    protected $fillable = [
        'transaction_code',
        'payos_order_code',
        'payos_payment_link_id',
        'payos_checkout_url',
        'payment_reference',
        'paid_at',
        'stock_deducted_at',
        'confirmation_sent_at',
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
        'stock_deducted_at' => 'datetime',
        'confirmation_sent_at' => 'datetime',
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
     * Các dòng hàng của đơn. Thay cho cột `content` (JSON) trước đây.
     */
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Ghi lại dòng hàng của đơn từ mảng item theo cấu trúc purchase_unit của
     * PayPal - cấu trúc này đang được dùng chung ở cả luồng PayPal lẫn payOS:
     * ['code' => ..., 'name' => ..., 'quantity' => ..., 'unit_amount' => ['currency_code' => ..., 'value' => ...]]
     */
    public function syncDetails(array $items): void
    {
        $this->details()->delete();

        $rows = [];

        foreach ($items as $item) {
            $code = $item['code'] ?? null;
            $price = (float) ($item['unit_amount']['value'] ?? 0);
            $quantity = max((int) ($item['quantity'] ?? 1), 1);

            $rows[] = [
                // Nơi gọi biết sẵn sản phẩm thì truyền thẳng id vào, khỏi tra lại.
                'product_id' => $item['product_id'] ?? self::resolveProductId($code),
                'product_code' => $code !== null ? (string) $code : null,
                'name' => (string) ($item['name'] ?? ''),
                'price' => $price,
                'quantity' => $quantity,
                'currency_code' => $item['unit_amount']['currency_code'] ?? null,
                'subtotal' => $price * $quantity,
            ];
        }

        if ($rows !== []) {
            $this->details()->createMany($rows);
        }
    }

    private static function resolveProductId($code): ?int
    {
        return Product::findByCodeOrId($code)?->id;
    }

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

    /**
     * Ghi nhận đơn đã thanh toán.
     *
     * Khoá dòng trong transaction vì cùng một đơn có thể được chốt đồng thời từ
     * nhiều nguồn: webhook của cổng thanh toán, URL khách được redirect về, và
     * endpoint mà trang thanh toán poll. Trả về true khi chính lần gọi này
     * chuyển trạng thái - các lần sau (khách bấm F5) trả về false, nhờ đó nơi
     * gọi biết có nên gửi email xác nhận hay không.
     */
    public function markAsPaid(?string $reference = null, ?string $paidAt = null): bool
    {
        $applied = false;

        DB::transaction(function () use ($reference, $paidAt, &$applied) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->isPaid()) {
                return;
            }

            $locked->status = self::STATUS_PAID;
            $locked->payment_reference = $reference;
            $locked->paid_at = self::parsePaidAt($paidAt);
            $locked->save();

            $this->setRawAttributes($locked->getAttributes(), true);
            $applied = true;
        });

        return $applied;
    }

    /**
     * Thanh toán không thành công.
     *
     * Cố ý chỉ đổi trạng thái chứ không xoá đơn: đơn vẫn là bằng chứng khách đã
     * đặt hàng và là đầu mối đối soát nếu tiền vẫn về sau đó. Đơn đã thanh toán
     * thì không bao giờ bị hạ trạng thái.
     */
    public function markAsFailed(): bool
    {
        return $this->closePending(self::STATUS_FAILED);
    }

    /**
     * Khách chủ động huỷ thanh toán.
     */
    public function markAsCancelled(): bool
    {
        return $this->closePending(self::STATUS_CANCELLED);
    }

    /**
     * Đóng một đơn đang chờ thanh toán và trả hàng đã giữ về kho.
     */
    private function closePending(string $status): bool
    {
        $applied = false;

        DB::transaction(function () use ($status, &$applied) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->status !== self::STATUS_PENDING) {
                return;
            }

            $locked->status = $status;
            $locked->save();
            $locked->restoreStock();

            $this->setRawAttributes($locked->getAttributes(), true);
            $applied = true;
        });

        return $applied;
    }

    /**
     * Giữ hàng cho đơn: trừ tồn kho theo từng dòng hàng.
     *
     * Phải gọi bên trong transaction tạo đơn - khi thiếu hàng sẽ ném
     * OutOfStockException để toàn bộ đơn được rollback thay vì tạo ra một đơn
     * không giao được. Khoá từng sản phẩm để hai khách đặt cùng lúc không cùng
     * nhìn thấy một lượng tồn.
     *
     * @throws OutOfStockException
     */
    public function deductStock(): void
    {
        if ($this->stock_deducted_at !== null) {
            return;
        }

        foreach ($this->details()->whereNotNull('product_id')->get() as $detail) {
            $product = Product::whereKey($detail->product_id)->lockForUpdate()->first();

            if (! $product) {
                continue;
            }

            if ($product->quantity < $detail->quantity) {
                throw new OutOfStockException($detail->name, max($product->quantity, 0));
            }

            $product->decrement('quantity', $detail->quantity);
        }

        $this->stock_deducted_at = Carbon::now();
        $this->save();
    }

    /**
     * Trả lại phần tồn kho đã giữ. Idempotent nhờ cột `stock_deducted_at`, nên
     * huỷ đơn nhiều lần cũng không cộng khống hàng vào kho.
     *
     * Gọi bên trong transaction đang khoá dòng đơn hàng.
     */
    public function restoreStock(): void
    {
        if ($this->stock_deducted_at === null) {
            return;
        }

        foreach ($this->details()->whereNotNull('product_id')->get() as $detail) {
            $product = Product::whereKey($detail->product_id)->lockForUpdate()->first();

            if ($product) {
                $product->increment('quantity', $detail->quantity);
            }
        }

        $this->stock_deducted_at = null;
        $this->save();
    }

    /**
     * Đánh dấu đã gửi email xác nhận, trả về true đúng một lần cho mỗi đơn.
     *
     * Dùng UPDATE có điều kiện thay vì đọc-rồi-ghi để hai request đồng thời
     * (khách bấm F5 liên tục) không cùng thấy cột còn trống rồi gửi hai email.
     */
    public function markConfirmationSent(): bool
    {
        $updated = static::query()
            ->whereKey($this->getKey())
            ->whereNull('confirmation_sent_at')
            ->update(['confirmation_sent_at' => Carbon::now()]);

        return $updated === 1;
    }

    /**
     * Mốc thời gian trả tiền do cổng thanh toán gửi về. Mỗi cổng một định dạng
     * (payOS: "Y-m-d H:i:s", PayPal: ISO 8601) nên nếu parse hỏng thì lấy thời
     * điểm hiện tại: sai lệch vài giây tốt hơn là làm hỏng cả việc ghi nhận
     * khách đã thanh toán.
     */
    private static function parsePaidAt(?string $paidAt): Carbon
    {
        if ($paidAt === null || trim($paidAt) === '') {
            return Carbon::now();
        }

        try {
            return Carbon::parse($paidAt);
        } catch (Throwable) {
            return Carbon::now();
        }
    }

    public static function genCode($id)
    {
        $now = Carbon::now();
        $orderCode = 'DH' . $now->format('YmdHis') . $id;
        return $orderCode;
    }
}
