<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->text('name');
            $table->string('code',100)->nullable();
            $table->text('model')->nullable();
            $table->text('image')->nullable();
            $table->text('multiple_image')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->text('price')->default(0);
            $table->string('slug')->nullable();
            $table->text('tag')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_robots')->nullable();
            $table->text('rel')->nullable();
            $table->text('target')->nullable();
            $table->string('made')->nullable();
            $table->text('unit')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('link_affiliate')->nullable();
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
        Schema::dropIfExists('products');
    }
}
