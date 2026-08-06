<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Nhóm route `admin/*` trước đây trỏ tới App\Http\Controllers\Api\AppController
| (lấy danh sách bài viết / sản phẩm, dịch nội dung, sinh slug, xoá cache...).
| Class đó không tồn tại trong dự án - thư mục app/Http/Controllers/Api rỗng -
| nên mọi route trong nhóm đều lỗi 500 ngay khi được gọi, và cũng không có chỗ
| nào ở giao diện gọi tới. Vì vậy nhóm route đã được gỡ bỏ.
|
| Nếu sau này cần lại: viết App\Http\Controllers\Api\AppController rồi khai báo
| route mới ở đây.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
