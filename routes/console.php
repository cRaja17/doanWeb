<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Đóng các đơn khách bỏ dở giữa chừng và trả hàng đang giữ về kho.
// Cần một cron gọi `php artisan schedule:run` mỗi phút thì lịch này mới chạy.
Schedule::command('orders:cancel-stale')->hourly()->withoutOverlapping();
