<?php
// config payment


return [
    'paypal' => [
        'mode'    => env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
        'sandbox' => [
            'client_id'         => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
            'client_secret'     => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
            'app_id'            => 'APP-80W284485P519543T',
        ],
        'live' => [
            'client_id'         => env('PAYPAL_LIVE_CLIENT_ID', ''),
            'client_secret'     => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
            'app_id'            => env('PAYPAL_LIVE_APP_ID', ''),
        ],

        'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
        'currency'       => env('PAYPAL_CURRENCY', 'USD'),
        'notify_url'     => env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
        'locale'         => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
        'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
    ],

    /*
    |--------------------------------------------------------------------------
    | payOS - chuyển khoản ngân hàng qua VietQR (tiền thật)
    |--------------------------------------------------------------------------
    |
    | Lấy 3 khoá dưới đây tại https://my.payos.vn -> Kênh thanh toán -> Thông tin
    | kết nối. Đây là thay thế cho Casso webhook cũ: payOS đối soát giao dịch,
    | ký dữ liệu bằng HMAC-SHA256 với checksum key nên webhook không thể giả mạo.
    |
    | Tài liệu: https://payos.vn/docs
    |
    */
    'payos' => [
        'client_id'     => env('PAYOS_CLIENT_ID', ''),
        'api_key'       => env('PAYOS_API_KEY', ''),
        'checksum_key'  => env('PAYOS_CHECKSUM_KEY', ''),
        // Chỉ dùng khi tích hợp dưới dạng đối tác (partner) của payOS.
        'partner_code'  => env('PAYOS_PARTNER_CODE'),

        'base_url'      => env('PAYOS_BASE_URL', 'https://api-merchant.payos.vn'),

        // URL webhook công khai (bắt buộc HTTPS). Ở local dùng ngrok/cloudflared.
        // Đăng ký với payOS bằng lệnh: php artisan payos:confirm-webhook
        'webhook_url'   => env('PAYOS_WEBHOOK_URL'),

        // Link thanh toán tự hết hạn sau bao nhiêu giây (payOS cho phép tối đa 24h).
        'expired_after' => (int) env('PAYOS_EXPIRED_AFTER', 15 * 60),

        // Timeout khi gọi API payOS (giây).
        'timeout'       => (int) env('PAYOS_TIMEOUT', 20),
    ],

];
