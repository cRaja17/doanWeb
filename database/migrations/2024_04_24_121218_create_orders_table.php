<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->nullable();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('message')->nullable();
            $table->string('method_payment')->nullable();
            $table->text('content')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('unit_payment')->nullable();
            $table->string('ip')->nullable();
            $table->string('proxy')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('order_status')->nullable()->default('Chờ xác nhận');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
