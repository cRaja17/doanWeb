<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Các bảng hạ tầng của Laravel còn thiếu.
 *
 * Dự án có sẵn bộ migration cũ (2014_*, 2019_*) tạo `users`,
 * `password_reset_tokens` và `failed_jobs`, đồng thời lại có thêm bộ skeleton
 * `0001_01_01_*` của Laravel mới tạo trùng đúng những bảng đó - nên
 * `php artisan migrate` luôn dừng ở lỗi "table already exists". Bộ skeleton đã
 * được gỡ bỏ và thay bằng migration này: chỉ tạo phần thực sự còn thiếu.
 *
 * `sessions`, `cache`, `jobs` chưa dùng tới với SESSION/CACHE/QUEUE driver hiện
 * tại là `file`/`sync`, nhưng có sẵn thì đổi sang driver `database` chỉ còn là
 * việc sửa .env - đặc biệt là `jobs`, cần cho việc gửi email đơn hàng dưới nền.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
    }
};
