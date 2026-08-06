<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `transaction_code` là mã khách nhìn thấy và là khoá tra cứu của mọi luồng
 * thanh toán (trang cảm ơn, PayPal return, email), nhưng lại chưa có index -
 * vừa chậm vừa không có gì đảm bảo không trùng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unique('transaction_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['transaction_code']);
        });
    }
};
