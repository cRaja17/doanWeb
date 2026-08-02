<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('contact')->nullable();
            $table->string('locale', 10)->default('vi');
            $table->text('question');
            $table->text('context')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('staff_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
