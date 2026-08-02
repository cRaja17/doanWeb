<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('flag')->nullable();
            $table->string('abbr')->nullable();
            $table->string('script')->nullable();
            $table->string('native')->nullable();
            $table->string('unit', 10)->nullable()->default('VND');
            $table->tinyInteger('active')->default(0);
            $table->tinyInteger('default')->default(0);
            $table->timestamps();
            $table->softDeletes(); //Thêm trường deleted_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
