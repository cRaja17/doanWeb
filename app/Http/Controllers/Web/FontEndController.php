<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class FontEndController extends Controller
{
    public $cacheTime;

    public $lang;

    public function __construct()
    {
        // Trước đây là 1 năm, mà không có chỗ nào xoá cache - sửa sản phẩm trong
        // admin thì ngoài web không đổi. Nay nội dung thay đổi sẽ được đẩy sang
        // phiên bản cache mới ngay (xem App\Support\FrontendCache), TTL này chỉ
        // còn là lưới an toàn để bản cũ tự rụng.
        $this->cacheTime = now()->addHour();
        $this->layout = 'web.layouts.index';
    }

    public function sendEmailOrder($order, $locale, $method_payment)
    {
        try {
            $replacements = $this->orderReplacements($order, $locale);

            $formatUser = LanguageLine::where('group', 'payment')
                ->where('key', $method_payment ? 'content_email_user' : 'content_email_user_nonpayment')
                ->value('text');

            $formatAdmin = Config::where('name', 'content_email_admin')->value('value');
            $adminEmail = Config::where('name', 'email_payment')->value('value');

            $emailContentUser = $this->renderOrderTemplate($formatUser, $replacements);
            $emailContentAdmin = $this->renderOrderTemplate($formatAdmin, $replacements);

            // Mẫu chưa được cấu hình trong admin thì không có gì để gửi - ghi log
            // để người quản trị biết, thay vì ném lỗi làm hỏng cả trang cảm ơn.
            if ($emailContentUser === '') {
                Log::warning('Thiếu mẫu email gửi khách trong language_lines', [
                    'order' => $order->transaction_code,
                ]);
            } else {
                $this->sendEmail($order->email, $this->subjectFrom($emailContentUser), $emailContentUser);
            }

            if ($emailContentAdmin === '' || empty($adminEmail)) {
                Log::warning('Thiếu mẫu hoặc địa chỉ email quản trị trong config', [
                    'order' => $order->transaction_code,
                ]);

                return;
            }

            $this->sendEmail($adminEmail, $this->subjectFrom($emailContentAdmin), $emailContentAdmin);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }

    /**
     * Bảng dòng hàng, dùng chung cho email và trang cảm ơn.
     */
    protected function orderLinesTable(Order $order, string $locale): string
    {
        $html = '<table><thead><tr>'
            . '<th>' . e((string) config('config.product.name.' . $locale)) . '</th>'
            . "<th width='150'>" . e((string) config('config.product.quantity.' . $locale)) . '</th>'
            . "<th width='150'>" . e((string) config('config.product.price.' . $locale)) . '</th>'
            . "<th width='150'>" . e((string) config('config.product.total.' . $locale)) . '</th>'
            . '</tr></thead><tbody>';

        foreach ($order->details as $detail) {
            // FormatCurrency giữ phần thập phân cho USD (49.99 $) thay vì
            // number_format() làm tròn thành "50 USD" như trước.
            $html .= '<tr>'
                . '<td>' . e((string) $detail->name) . '</td>'
                . '<td>' . (int) $detail->quantity . '</td>'
                . '<td>' . e(FormatCurrency($detail->price, $detail->currency_code)) . '</td>'
                . '<td>' . e(FormatCurrency($detail->subtotal, $detail->currency_code)) . '</td>'
                . '</tr>';
        }

        return $html . '</tbody></table>';
    }

    /**
     * Giá trị thay vào mẫu nội dung đơn hàng.
     *
     * Những trường do khách tự nhập (tên, email, số điện thoại, địa chỉ) được
     * escape trước khi ghép vào HTML - nếu không, khách chỉ cần điền thẻ script
     * vào ô Họ tên là nó chạy trên trang cảm ơn và trong email gửi quản trị.
     */
    protected function orderReplacements(Order $order, string $locale): array
    {
        return [
            '[id]' => e((string) $order->transaction_code),
            '[name]' => e((string) $order->name),
            '[email]' => e((string) $order->email),
            '[phone]' => e((string) $order->phone),
            '[address]' => e((string) $order->address),
            '[date]' => e((string) ($order->created_at?->format('d/m/Y H:i') ?? '')),
            '[list_product]' => $this->orderLinesTable($order, $locale),
            '[total_price]' => e(FormatCurrency($order->total, $order->unit_payment)),
            '[method_payment]' => e((string) $order->method_payment),
            '[status_payment]' => e($order->statusLabel($locale)),
        ];
    }

    /**
     * Ghép mẫu với dữ liệu đơn hàng.
     *
     * Mẫu lưu trong DB có thể chứa HTML đã bị mã hoá thực thể nên phải giải mã
     * TRƯỚC khi thay dữ liệu vào. Làm ngược lại (thay trước, giải mã sau như
     * code cũ) sẽ gỡ luôn phần escape ở orderReplacements() và mở lại lỗ XSS.
     */
    protected function renderOrderTemplate(?string $template, array $replacements): string
    {
        if ($template === null || trim($template) === '') {
            return '';
        }

        return strtr(html_entity_decode($template), $replacements);
    }

    /**
     * Dòng đầu của nội dung được dùng làm tiêu đề email.
     */
    protected function subjectFrom(string $content): string
    {
        $firstLine = trim(explode("\n", strip_tags($content))[0] ?? '');

        return $firstLine !== '' ? $firstLine : 'Order Confirmation';
    }
}
