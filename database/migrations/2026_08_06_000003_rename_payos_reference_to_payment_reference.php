<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mã giao dịch của cổng thanh toán nay được cả PayPal ghi vào (trước đây chỉ có
 * payOS), nên đổi sang tên trung lập để việc đối soát không bị hiểu nhầm là chỉ
 * dành cho một cổng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('payos_reference', 'payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('payment_reference', 'payos_reference');
        });
    }
};
