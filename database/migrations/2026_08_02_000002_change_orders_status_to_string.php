<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Đổi `orders.status` từ tinyint (0/1/2/3) sang chuỗi có nghĩa, và chuẩn hoá
 * `orders.order_status` về đúng 3 tình trạng đang dùng.
 */
return new class extends Migration
{
    /**
     * Ánh xạ giá trị tinyint cũ -> chuỗi mới.
     *
     * Cố ý dùng mảng cặp [cũ, mới] thay vì [cũ => mới]: PHP tự ép key mảng dạng
     * "0"/"1" thành int, khiến MySQL so sánh cột varchar theo kiểu số và báo lỗi
     * "Truncated incorrect DECIMAL value" ngay khi vài dòng đã đổi sang chuỗi.
     */
    private const STATUS_MAP = [
        ['0', Order::STATUS_PENDING],
        ['1', Order::STATUS_PAID],
        ['2', Order::STATUS_FAILED],
        ['3', Order::STATUS_CANCELLED],
    ];

    /** Các tình trạng cũ ngoài 3 giá trị mới -> giá trị tương đương gần nhất. */
    private const ORDER_STATUS_MAP = [
        ['Đã xác nhận', Order::ORDER_STATUS_CONTACTED],
        ['Đang giao hàng', Order::ORDER_STATUS_CONTACTED],
        ['Đã thanh toán', Order::ORDER_STATUS_CONTACTED],
    ];

    public function up(): void
    {
        // Đổi kiểu trước: MySQL biến 0/1/2/3 thành chuỗi "0"/"1"/"2"/"3".
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default(Order::STATUS_PENDING)->change();
        });

        foreach (self::STATUS_MAP as [$old, $new]) {
            DB::table('orders')->where('status', $old)->update(['status' => $new]);
        }

        // Giá trị lạ (nếu có) coi như chưa thanh toán để không đơn nào rơi ra ngoài.
        DB::table('orders')
            ->whereNotIn('status', array_column(self::STATUS_MAP, 1))
            ->update(['status' => Order::STATUS_PENDING]);

        foreach (self::ORDER_STATUS_MAP as [$old, $new]) {
            DB::table('orders')->where('order_status', $old)->update(['order_status' => $new]);
        }

        // Đơn từng đánh dấu "Đã huỷ": thông tin huỷ chuyển sang cột `status`
        // (nay đã có trạng thái cancelled), tình trạng xử lý về mặc định.
        DB::table('orders')->where('order_status', 'Đã huỷ')->update([
            'order_status' => Order::ORDER_STATUS_WAITING,
            'status' => Order::STATUS_CANCELLED,
        ]);

        DB::table('orders')
            ->where(function ($query) {
                $query->whereNotIn('order_status', array_keys(Order::orderStatusOptions()))
                    ->orWhereNull('order_status');
            })
            ->update(['order_status' => Order::ORDER_STATUS_WAITING]);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status')->default(Order::ORDER_STATUS_WAITING)->change();
        });
    }

    public function down(): void
    {
        foreach (self::STATUS_MAP as [$old, $new]) {
            DB::table('orders')->where('status', $new)->update(['status' => $old]);
        }

        DB::table('orders')
            ->whereNotIn('status', array_column(self::STATUS_MAP, 0))
            ->update(['status' => '0']);

        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->change();
        });
    }
};
