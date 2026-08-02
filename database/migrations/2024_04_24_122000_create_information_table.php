<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('information', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->string('slug')->nullable();
            $table->longText('description')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('rel')->nullable();
            $table->string('target')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('view_header')->default(0);
            $table->tinyInteger('view_footer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
