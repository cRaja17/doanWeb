<?php

namespace App\Http\Controllers\Web\Payment;

use App\Http\Controllers\Web\FontEndController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\PayPalFacadeAccessor;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * Xử lý khách quay về sau khi thanh toán bằng PayPal.
 *
 * Nguyên tắc chung với luồng payOS: chỉ đánh dấu đã thanh toán khi PayPal xác
 * nhận capture hoàn tất VÀ số tiền khớp đơn hàng; mọi trường hợp còn lại chỉ ghi
 * log và đổi trạng thái, không bao giờ xoá đơn.
 */
class PaypalController extends FontEndController
{
    /** PayPal coi giao dịch là xong khi order và capture đều ở trạng thái này. */
    private const STATUS_COMPLETED = 'COMPLETED';

    /** Lỗi PayPal trả về khi capture lại một đơn đã thu tiền. */
    private const ISSUE_ALREADY_CAPTURED = 'ORDER_ALREADY_CAPTURED';

    /** Sai lệch làm tròn phía PayPal, không coi là thiếu tiền. */
    private const AMOUNT_TOLERANCE = 0.01;

    public function success(Request $request)
    {
        $orderId = $request->query('orderId');

        if (!$orderId) {
            Log::error('PayPal: thiếu orderId khi khách quay lại từ cổng thanh toán');

            return $this->backToCheckout(translate('payment.order_id_not_found'));
        }

        $order = Order::where('transaction_code', $orderId)->first();

        if (!$order) {
            Log::error('PayPal: không tìm thấy đơn hàng', ['orderId' => $orderId]);

            return $this->backToCheckout(translate('payment.order_not_found'));
        }

        // Khách bấm F5 trang này: đơn đã ghi nhận thanh toán rồi thì đi thẳng
        // sang trang cảm ơn, không gọi capture lần nữa.
        if ($order->isPaid()) {
            return $this->redirectToThanks($order);
        }

        $token = $request->query('token');

        if (!$token) {
            Log::error('PayPal: thiếu token capture', ['order' => $order->transaction_code]);

            return $this->backToCheckout(translate('payment.not_completed'));
        }

        try {
            $response = $this->capture($token);
        } catch (Throwable $e) {
            // Lỗi mạng hoặc cấu hình: giữ đơn ở `pending` để còn đối soát, vì
            // rất có thể tiền của khách đã bị trừ.
            Log::error('PayPal: không capture được giao dịch', [
                'order' => $order->transaction_code,
                'message' => $e->getMessage(),
            ]);

            return $this->backToCheckout(translate('payment.gateway_unreachable'));
        }

        if (($response['status'] ?? null) !== self::STATUS_COMPLETED) {
            Log::warning('PayPal: giao dịch chưa hoàn tất', [
                'order' => $order->transaction_code,
                'status' => $response['status'] ?? null,
            ]);

            $order->markAsFailed();

            return $this->backToCheckout(translate('payment.not_completed'));
        }

        $capture = $this->completedCapture($response);

        if (!$capture || !$this->amountMatches($capture, $order)) {
            // Tiền có thể đã bị trừ nhưng không khớp giá trị đơn: giữ nguyên
            // `pending` và báo log để xử lý tay, tuyệt đối không đánh dấu đã
            // thanh toán.
            Log::error('PayPal: số tiền thu được không khớp đơn hàng', [
                'order' => $order->transaction_code,
                'expected' => $order->total . ' ' . $order->unit_payment,
                'received' => $capture['amount'] ?? null,
            ]);

            return $this->backToCheckout(translate('payment.amount_mismatch'));
        }

        $order->markAsPaid($capture['id'] ?? null, $capture['create_time'] ?? null);

        Log::info('PayPal: đơn hàng đã thanh toán', [
            'order' => $order->transaction_code,
            'reference' => $capture['id'] ?? null,
        ]);

        return $this->redirectToThanks($order);
    }

    public function cancel(Request $request)
    {
        $orderId = $request->query('orderId');
        $order = $orderId ? Order::where('transaction_code', $orderId)->first() : null;

        // Đổi trạng thái và trả hàng đã giữ về kho.
        $order?->markAsCancelled();

        return $this->backToCheckout(translate('payment.cancelled_by_user'));
    }

    /**
     * Gọi capture. Nếu PayPal báo đơn đã được capture trước đó (khách tải lại
     * trang, hoặc capture đầu trả về chậm) thì đọc lại chi tiết đơn thay vì coi
     * là giao dịch thất bại - đây chính là chỗ trước đây làm mất đơn đã trả tiền.
     *
     * @throws Throwable
     */
    private function capture(string $token): array
    {
        $provider = $this->provider();
        $response = $provider->capturePaymentOrder($token);

        if (is_array($response) && ($response['status'] ?? null) === self::STATUS_COMPLETED) {
            return $response;
        }

        if (str_contains((string) json_encode($response), self::ISSUE_ALREADY_CAPTURED)) {
            $details = $provider->showOrderDetails($token);

            return is_array($details) ? $details : [];
        }

        return is_array($response) ? $response : [];
    }

    /**
     * Giao dịch capture đã hoàn tất trong phản hồi của PayPal.
     */
    private function completedCapture(array $response): ?array
    {
        foreach ($response['purchase_units'] ?? [] as $unit) {
            foreach ($unit['payments']['captures'] ?? [] as $capture) {
                if (($capture['status'] ?? null) === self::STATUS_COMPLETED) {
                    return $capture;
                }
            }
        }

        return null;
    }

    /**
     * Số tiền PayPal thu được phải đúng loại tiền và không nhỏ hơn giá trị đơn.
     */
    private function amountMatches(array $capture, Order $order): bool
    {
        $currency = strtoupper((string) ($capture['amount']['currency_code'] ?? ''));

        if ($currency !== strtoupper((string) $order->unit_payment)) {
            return false;
        }

        $value = (float) ($capture['amount']['value'] ?? 0);

        return $value + self::AMOUNT_TOLERANCE >= (float) $order->total;
    }

    private function provider(): PayPalClient
    {
        $provider = PayPalFacadeAccessor::setProvider();
        $provider->setApiCredentials(config('payment.paypal'));
        $provider->getAccessToken();

        return $provider;
    }

    private function redirectToThanks(Order $order)
    {
        session(['orderId' => $order->transaction_code, 'method_payment' => true]);

        return redirect(route('payment.web.thanks', [], true));
    }

    private function backToCheckout(string $message)
    {
        return redirect(route('payment.web.checkout', [], true))->with('error', $message);
    }
}
