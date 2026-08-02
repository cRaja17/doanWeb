<?php

namespace App\Http\Controllers\Web\Payment;

use App\Entities\PayOS;
use App\Exceptions\PayOSException;
use App\Http\Controllers\Web\FontEndController;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Thanh toán chuyển khoản ngân hàng qua payOS (tiền thật).
 *
 */
class PayOSController extends FontEndController
{
    public function __construct(private readonly PayOS $payOS)
    {
        parent::__construct();
    }

    /**
     * Trang thanh toán: nhúng payOS Embedded Form.
     */
    public function checkout()
    {
        $locale = app()->getLocale();
        $orderId = session('orderId');

        if (!$orderId) {
            return $this->backToCheckout(translate([
                'vi' => 'Phiên thanh toán đã hết hạn, vui lòng đặt hàng lại.',
                'en' => 'Your payment session has expired, please place the order again.',
                'fr' => 'Votre session de paiement a expiré, veuillez repasser la commande.',
            ]));
        }

        $order = Order::where('transaction_code', $orderId)->first();

        if (!$order) {
            return $this->backToCheckout(translate([
                'vi' => 'Không tìm thấy đơn hàng.',
                'en' => 'Order not found.',
                'fr' => 'Commande introuvable.',
            ]));
        }

        // Đơn có thể đã được webhook xác nhận trong lúc khách quay lại trang.
        if ($order->payos_payment_link_id) {
            $this->syncPaymentLink($order);
        }

        if ($order->isPaid()) {
            return $this->redirectToThanks($order);
        }

        try {
            $link = $this->resolvePaymentLink($order);
        } catch (PayOSException $e) {
            Log::error('payOS: không tạo được link thanh toán', [
                'order' => $order->transaction_code,
                'message' => $e->getMessage(),
            ]);

            return $this->backToCheckout(translate([
                'vi' => 'Không kết nối được cổng thanh toán, vui lòng thử lại sau.',
                'en' => 'Could not reach the payment gateway, please try again later.',
                'fr' => 'Impossible de joindre la passerelle de paiement, veuillez réessayer plus tard.',
            ]));
        }

        $products = $this->orderProducts($order);

        $returnUrl = route('payment.web.payos.return', ['orderCode' => $order->payos_order_code], true);
        $cancelUrl = route('payment.web.payos.cancel', ['orderCode' => $order->payos_order_code], true);
        $statusUrl = route('payment.web.payos.status', ['orderCode' => $order->payos_order_code], true);

        return $this->render(
            'web.order.bank',
            compact('order', 'products', 'link', 'returnUrl', 'cancelUrl', 'statusUrl')
        );
    }

    /**
     * Webhook payOS gọi về khi có biến động số dư.
     *
     * Bắt buộc trả HTTP 2xx cho mọi payload có chữ ký hợp lệ - kể cả payload thử
     * nghiệm lúc đăng ký webhook - nếu không payOS sẽ từ chối đăng ký URL.
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $data = $this->payOS->verifyWebhook($request->all());
        } catch (PayOSException $e) {
            Log::warning('payOS webhook bị từ chối: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Invalid webhook'], 400);
        }

        $orderCode = (int) ($data['orderCode'] ?? 0);
        $order = Order::where('payos_order_code', $orderCode)->first();

        if (!$order) {
            // payOS gửi payload mẫu (orderCode 123) khi xác thực URL webhook.
            Log::info('payOS webhook: orderCode không thuộc hệ thống', ['orderCode' => $orderCode]);

            return response()->json(['success' => true]);
        }

        if ((string) ($data['code'] ?? '') !== PayOS::CODE_SUCCESS) {
            Log::warning('payOS webhook: giao dịch không thành công', [
                'order' => $order->transaction_code,
                'code' => $data['code'] ?? null,
                'desc' => $data['desc'] ?? null,
            ]);

            return response()->json(['success' => true]);
        }

        // Chặn trường hợp số tiền nhận được nhỏ hơn giá trị đơn hàng.
        if ((int) ($data['amount'] ?? 0) < $this->orderAmount($order)) {
            Log::warning('payOS webhook: số tiền không khớp đơn hàng', [
                'order' => $order->transaction_code,
                'received' => $data['amount'] ?? null,
                'expected' => $this->orderAmount($order),
            ]);

            return response()->json(['success' => true]);
        }

        $this->markAsPaid($order, $data['reference'] ?? null, $data['transactionDateTime'] ?? null);

        return response()->json(['success' => true]);
    }

    /**
     * payOS redirect khách về đây sau khi thanh toán xong.
     */
    public function handleReturn(Request $request)
    {
        $order = $this->orderFromRequest($request);

        if (!$order) {
            return $this->backToCheckout(translate([
                'vi' => 'Không tìm thấy đơn hàng.',
                'en' => 'Order not found.',
                'fr' => 'Commande introuvable.',
            ]));
        }

        if (! $order->isPaid()) {
            // Webhook có thể chưa về kịp - hỏi thẳng payOS để chốt trạng thái.
            $this->syncPaymentLink($order);
        }

        if ($order->isPaid()) {
            return $this->redirectToThanks($order);
        }

        // Tiền đang trên đường về (PROCESSING): giữ khách ở trang thanh toán.
        session(['orderId' => $order->transaction_code, 'method_payment' => true]);

        return redirect(route('payment.web.bank', [], true))->with('error', translate([
            'vi' => 'Giao dịch đang được xử lý, vui lòng đợi trong giây lát.',
            'en' => 'Your transaction is being processed, please wait a moment.',
            'fr' => 'Votre transaction est en cours de traitement, veuillez patienter.',
        ]));
    }

    /**
     * Khách bấm huỷ trong form thanh toán.
     */
    public function cancel(Request $request)
    {
        $order = $this->orderFromRequest($request);

        if ($order && $order->status === Order::STATUS_PENDING) {
            try {
                $this->payOS->cancelPaymentLink(
                    $order->payos_payment_link_id ?: $order->payos_order_code,
                    'Khách hàng huỷ thanh toán'
                );
            } catch (PayOSException $e) {
                // Link có thể đã tự hết hạn phía payOS - không cản trở khách quay lại.
                Log::info('payOS: không huỷ được link thanh toán - ' . $e->getMessage());
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->save();
        }

        return $this->backToCheckout(translate([
            'vi' => 'Bạn đã huỷ thanh toán. Giỏ hàng vẫn được giữ nguyên.',
            'en' => 'You cancelled the payment. Your cart is still saved.',
            'fr' => 'Vous avez annulé le paiement. Votre panier est conservé.',
        ]));
    }

    /**
     * Trang thanh toán poll endpoint này để biết khi nào tiền đã vào.
     */
    public function status(Request $request): JsonResponse
    {
        $order = $this->orderFromRequest($request);

        if (!$order) {
            return response()->json(['paid' => false, 'found' => false], 404);
        }

        if ($order->status === Order::STATUS_PENDING) {
            $this->syncPaymentLink($order);
        }

        $paid = $order->isPaid();

        return response()->json([
            'found' => true,
            'paid' => $paid,
            'redirect' => $paid
                ? route('payment.web.payos.return', ['orderCode' => $order->payos_order_code], true)
                : null,
        ]);
    }

    /**
     * Dùng lại link cũ nếu còn hiệu lực, ngược lại tạo link mới.
     *
     * @throws PayOSException
     */
    private function resolvePaymentLink(Order $order): array
    {
        if ($order->payos_checkout_url && $order->payos_payment_link_id) {
            try {
                $existing = $this->payOS->getPaymentLink($order->payos_payment_link_id);

                if (($existing['status'] ?? null) === PayOS::STATUS_PENDING) {
                    return [
                        'checkoutUrl' => $order->payos_checkout_url,
                        'orderCode' => (int) $order->payos_order_code,
                    ];
                }
            } catch (PayOSException $e) {
                Log::info('payOS: không đọc được link cũ, sẽ tạo link mới - ' . $e->getMessage());
            }
        }

        return $this->createPaymentLink($order);
    }

    /**
     * @throws PayOSException
     */
    private function createPaymentLink(Order $order): array
    {
        $locale = app()->getLocale();
        $amount = $this->orderAmount($order);

        if ($amount <= 0) {
            throw new PayOSException('Giá trị đơn hàng không hợp lệ.');
        }

        $orderCode = $this->generateOrderCode($order);

        $data = $this->payOS->createPaymentLink([
            'orderCode' => $orderCode,
            'amount' => $amount,
            // payOS giới hạn 25 ký tự và dùng chuỗi này làm nội dung chuyển khoản.
            'description' => mb_substr((string) $order->transaction_code, 0, PayOS::DESCRIPTION_MAX_LENGTH),
            'returnUrl' => route('payment.web.payos.return', ['orderCode' => $orderCode], true),
            'cancelUrl' => route('payment.web.payos.cancel', ['orderCode' => $orderCode], true),
            'items' => $this->paymentItems($order),
            'buyerName' => $order->name,
            'buyerEmail' => $order->email,
            'buyerPhone' => $order->phone,
            'buyerAddress' => $order->address,
            'expiredAt' => now()->addSeconds((int) config('payment.payos.expired_after', 900))->getTimestamp(),
        ]);

        $order->payos_order_code = $orderCode;
        $order->payos_payment_link_id = $data['paymentLinkId'] ?? null;
        $order->payos_checkout_url = $data['checkoutUrl'] ?? null;
        $order->save();

        return [
            'checkoutUrl' => $data['checkoutUrl'] ?? null,
            'orderCode' => $orderCode,
        ];
    }

    /**
     * Hỏi payOS trạng thái hiện tại của link và cập nhật đơn hàng tương ứng.
     */
    private function syncPaymentLink(Order $order): ?string
    {
        $reference = $order->payos_payment_link_id ?: $order->payos_order_code;

        if (!$reference) {
            return null;
        }

        try {
            $link = $this->payOS->getPaymentLink($reference);
        } catch (PayOSException $e) {
            Log::info('payOS: không tra cứu được trạng thái link - ' . $e->getMessage());

            return null;
        }

        $status = $link['status'] ?? null;

        if ($status === PayOS::STATUS_PAID && (int) ($link['amountPaid'] ?? 0) >= $this->orderAmount($order)) {
            $transaction = $link['transactions'][0] ?? [];
            $this->markAsPaid($order, $transaction['reference'] ?? null, $transaction['transactionDateTime'] ?? null);
        }

        return $status;
    }

    /**
     * Đánh dấu đơn đã thanh toán. Khoá dòng để webhook và return URL không ghi đè nhau.
     */
    private function markAsPaid(Order $order, ?string $reference, ?string $paidAt): void
    {
        DB::transaction(function () use ($order, $reference, $paidAt) {
            $locked = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (!$locked || $locked->isPaid()) {
                return;
            }

            $locked->status = Order::STATUS_PAID;
            $locked->payos_reference = $reference;
            $locked->paid_at = $paidAt ? Carbon::parse($paidAt) : now();
            $locked->save();

            $order->setRawAttributes($locked->getAttributes(), true);
        });

        Log::info('payOS: đơn hàng đã thanh toán', [
            'order' => $order->transaction_code,
            'reference' => $reference,
        ]);
    }

    /**
     * payOS yêu cầu orderCode là số nguyên duy nhất theo kênh thanh toán.
     * Ghép thời điểm tạo link với id đơn hàng để không đụng nhau khi tạo lại link.
     */
    private function generateOrderCode(Order $order): int
    {
        return (int) (now()->format('ymdHis') . str_pad((string) ($order->id % 1000), 3, '0', STR_PAD_LEFT));
    }

    /**
     * payOS chỉ hỗ trợ VND và số tiền phải là số nguyên.
     */
    private function orderAmount(Order $order): int
    {
        return (int) round((float) $order->total);
    }

    /**
     * Danh sách hàng hoá hiển thị trong form thanh toán payOS.
     */
    private function paymentItems(Order $order): array
    {
        $items = [];

        foreach ($this->orderContent($order) as $line) {
            $items[] = [
                'name' => mb_substr((string) ($line['name'] ?? ''), 0, 100),
                'quantity' => (int) ($line['quantity'] ?? 1),
                'price' => (int) round((float) ($line['unit_amount']['value'] ?? 0)),
            ];
        }

        return $items;
    }

    /**
     * Dữ liệu sản phẩm để render bảng đơn hàng trên trang thanh toán.
     */
    private function orderProducts(Order $order): array
    {
        $products = [];

        foreach ($this->orderContent($order) as $key => $line) {
            $product = Product::where('code', $line['code'] ?? null)->first();

            $products[$key] = [
                'code' => $line['code'] ?? '',
                'image' => $product?->image,
                'name' => $product ? $product->getTranslations('name') : ($line['name'] ?? ''),
                'price' => $line['unit_amount']['value'] ?? 0,
                'unit' => $line['unit_amount']['currency_code'] ?? 'VND',
                'qty' => $line['quantity'] ?? 1,
            ];
        }

        return $products;
    }

    private function orderContent(Order $order): array
    {
        $content = is_array($order->content) ? $order->content : json_decode((string) $order->content, true);

        return is_array($content) ? $content : [];
    }

    private function orderFromRequest(Request $request): ?Order
    {
        $orderCode = (int) ($request->route('orderCode') ?? $request->query('orderCode'));

        return $orderCode > 0 ? Order::where('payos_order_code', $orderCode)->first() : null;
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
