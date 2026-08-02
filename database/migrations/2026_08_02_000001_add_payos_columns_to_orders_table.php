<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * payOS định danh đơn hàng bằng orderCode dạng số nguyên (duy nhất theo kênh
     * thanh toán), trong khi hệ thống đang dùng transaction_code dạng chuỗi.
     * Vì vậy cần lưu song song mã số của payOS cùng thông tin đối soát.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payos_order_code')->nullable()->unique()->after('transaction_code');
            $table->string('payos_payment_link_id')->nullable()->after('payos_order_code');
            $table->text('payos_checkout_url')->nullable()->after('payos_payment_link_id');
            // Mã giao dịch ngân hàng trả về trong webhook, dùng để đối soát sao kê.
            $table->string('payos_reference')->nullable()->after('payos_checkout_url');
            $table->timestamp('paid_at')->nullable()->after('payos_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payos_order_code']);
            $table->dropColumn([
                'payos_order_code',
                'payos_payment_link_id',
                'payos_checkout_url',
                'payos_reference',
                'paid_at',
            ]);
        });
    }
};
