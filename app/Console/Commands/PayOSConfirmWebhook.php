<?php

namespace App\Console\Commands;

use App\Entities\PayOS;
use App\Exceptions\PayOSException;
use Illuminate\Console\Command;

/**
 * Đăng ký URL webhook với payOS.
 *
 * payOS sẽ gọi thử URL này ngay trong lúc đăng ký; nếu endpoint không trả HTTP 2xx
 * thì việc đăng ký thất bại. URL bắt buộc phải công khai và dùng HTTPS - ở local
 * hãy mở tunnel (ngrok, cloudflared) rồi truyền URL tunnel vào.
 */
class PayOSConfirmWebhook extends Command
{
    protected $signature = 'payos:confirm-webhook {url? : URL webhook, mặc định lấy từ PAYOS_WEBHOOK_URL}';

    protected $description = 'Đăng ký URL nhận webhook thanh toán với payOS';

    public function handle(PayOS $payOS): int
    {
        $url = $this->argument('url') ?: config('payment.payos.webhook_url');

        if (! $url) {
            $this->error('Chưa có URL webhook. Đặt PAYOS_WEBHOOK_URL trong .env hoặc truyền vào: php artisan payos:confirm-webhook https://.../payos/webhook');

            return self::FAILURE;
        }

        if (! $payOS->isConfigured()) {
            $this->error('Chưa cấu hình PAYOS_CLIENT_ID / PAYOS_API_KEY / PAYOS_CHECKSUM_KEY trong .env.');

            return self::FAILURE;
        }

        $this->info('Đang đăng ký webhook: '.$url);

        try {
            $payOS->confirmWebhook($url);
        } catch (PayOSException $e) {
            $this->error('Đăng ký thất bại: '.$e->getMessage());
            $this->line('Kiểm tra lại: URL phải là HTTPS công khai, trả HTTP 200 và không bị chặn bởi CSRF.');

            return self::FAILURE;
        }

        $this->info('Đăng ký webhook thành công.');

        return self::SUCCESS;
    }
}
