<?php

/*
|--------------------------------------------------------------------------
| Livewire - chỉ ghi đè phần cần thiết
|--------------------------------------------------------------------------
|
| LivewireServiceProvider dùng mergeConfigFrom (array_merge nông) nên chỉ những
| key khai báo ở đây mới ghi đè; các thiết lập còn lại vẫn lấy mặc định từ
| vendor/livewire/livewire/config/livewire.php.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Payload Guards
    |--------------------------------------------------------------------------
    |
    | Giới hạn chống payload độc hại / quá khổ. Mặc định của Livewire là 10 mức,
    | quá chặt với RichEditor: Filament chuyển HTML mô tả sản phẩm sang TipTap
    | JSON nên property path có dạng
    |   data.description.content.7.content.2.content.0.content.2.attrs
    | Mỗi cấp phần tử HTML tốn 2 mức path, mô tả hiện có lồng tới 5 cấp (~13 mức)
    | nên form sửa sản phẩm ném MaxNestingDepthExceededException.
    |
    | Đặt 25 để còn dư chỗ cho bảng/danh sách lồng nhau mà vẫn chặn payload vô lý.
    | Các giá trị khác giữ nguyên mặc định vì array_merge thay cả mảng 'payload'.
    |
    */

    'payload' => [
        'max_size' => 1024 * 1024,   // 1MB
        'max_nesting_depth' => 25,
        'max_calls' => 50,
        'max_components' => 200,
    ],

];
