<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class LookupOrder implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Tra cứu tình trạng một đơn hàng. Bắt buộc phải có CẢ mã đơn hàng VÀ email hoặc số điện thoại '
            .'đã dùng khi đặt hàng để xác minh danh tính. Nếu khách chưa cung cấp đủ, hãy hỏi khách trước khi gọi công cụ này.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $code = trim($request->string('transaction_code', '')->value());
        $contact = trim($request->string('contact', '')->value());

        if ($code === '' || $contact === '') {
            return 'Cần cả mã đơn hàng và email (hoặc số điện thoại) đã dùng khi đặt hàng để tra cứu.';
        }

        $order = Order::query()
            ->where('transaction_code', $code)
            ->where(fn ($q) => $q->where('email', $contact)->orWhere('phone', $contact))
            ->first();

        if (! $order) {
            return 'Không tìm thấy đơn hàng khớp với mã và thông tin liên hệ đã cung cấp. '
                .'Hãy đề nghị khách kiểm tra lại, hoặc chuyển sang nhân viên hỗ trợ.';
        }

        // Chỉ trả về thông tin trạng thái, không trả về địa chỉ / email / số điện thoại.
        return json_encode([
            'transaction_code' => $order->transaction_code,
            'order_status' => $order->order_status,
            // Trả nhãn tiếng Việt thay vì key kỹ thuật để trợ lý trả lời khách trực tiếp.
            'payment_status' => $order->statusLabel('vi'),
            'method_payment' => $order->method_payment,
            'total' => trim($order->total.' '.$order->unit_payment),
            'ordered_at' => $order->created_at?->format('d/m/Y H:i'),
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'transaction_code' => $schema->string()->required()
                ->description('Mã đơn hàng, thường bắt đầu bằng "DH".'),
            'contact' => $schema->string()->required()
                ->description('Email hoặc số điện thoại khách đã dùng khi đặt đơn, để xác minh.'),
        ];
    }
}
