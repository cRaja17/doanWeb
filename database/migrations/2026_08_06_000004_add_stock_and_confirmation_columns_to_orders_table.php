<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thời điểm đã trừ tồn kho cho đơn này. Null nghĩa là chưa trừ hoặc
            // đã hoàn lại - nhờ đó việc hoàn kho gọi bao nhiêu lần cũng an toàn.
            $table->timestamp('stock_deducted_at')->nullable()->after('paid_at');

            // Thời điểm đã gửi email xác nhận. Trang cảm ơn có thể bị tải lại
            // nhiều lần nên cần cột này để không gửi trùng.
            $table->timestamp('confirmation_sent_at')->nullable()->after('stock_deducted_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stock_deducted_at', 'confirmation_sent_at']);
        });
    }
};
