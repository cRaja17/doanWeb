<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCategoryRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_category_relation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('post_categories')->onDelete('cascade');
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
        Schema::dropIfExists('post_category_relation');
    }
}
