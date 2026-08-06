<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Sản phẩm có thể bị xoá sau khi khách đã mua, nên FK cho phép null
            // và các cột dưới đây giữ bản chụp tại thời điểm đặt hàng.
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('product_code', 100)->nullable();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('currency_code', 10)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);

            $table->timestamps();

            $table->index('product_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
