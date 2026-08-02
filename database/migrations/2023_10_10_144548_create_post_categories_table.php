<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 3)->default('vi');
            $table->string('name');
            $table->string('image',1000)->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('rel')->nullable();
            $table->string('target')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('view_home')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_categories');
    }
}
