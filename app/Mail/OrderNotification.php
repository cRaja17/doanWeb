<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email đơn hàng, gửi cho cả khách lẫn admin.
 *
 * Nội dung là HTML đã dựng sẵn từ mẫu trong `language_lines` / `config` nên ở
 * đây chỉ cần bọc lại thành Mailable. Điểm chính là `ShouldQueue`: với
 * QUEUE_CONNECTION=sync thì vẫn chạy ngay như trước, nhưng khi đổi sang driver
 * `database`/`redis` thì việc gửi mail sẽ không còn bắt khách đợi SMTP nữa.
 */
class OrderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Tên hai thuộc tính này cố ý không phải `$subject`/`$html`: Mailable đã có
     * sẵn hai thuộc tính đó và việc khai báo đè sẽ làm hỏng cả class.
     */
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $htmlBody
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody);
    }
}
