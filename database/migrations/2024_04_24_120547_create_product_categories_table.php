<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('image')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_robots')->nullable();
            $table->text('rel')->nullable();
            $table->text('target')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('product_categories');
    }
}
