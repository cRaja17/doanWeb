<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 3)->default('vi');
            $table->unsignedBigInteger('user_id');
            $table->text('name');
            $table->string('image', 1000)->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('slug', 250);
            $table->string('tag', 255)->nullable();
            $table->text('meta_title')->nullable();
            $table->string('meta_keyword', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->enum('meta_robots', ['index,follow', 'noindex,nofollow', 'index,nofollow', 'noindex,follow'])->default('index,follow');
            $table->enum('rel', ['nofollow', 'dofollow'])->default('dofollow');
            $table->enum('target', ['_blank', '_self'])->default('_self');
            $table->integer('view')->default(0);
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
