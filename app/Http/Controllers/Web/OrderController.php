<?php

namespace App\Http\Controllers\Web;

use App\Entities\Paypal;
use App\Exceptions\OutOfStockException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\Order;
use App\Services\CartService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends FontEndController
{

    /**
     * @Description : 
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */

    public function checkout(CartService $cart)
    {
        $lines = $cart->lines();

        if ($lines === []) {
            return abort(404);
        }

        $paymentEnabled = Config::paymentEnabled();
        $totalVND = $cart->total($lines, 'vi');
        $totalUSD = $cart->total($lines, 'en');
        $stockProblems = $cart->stockProblems($lines);

        // Giá hiển thị luôn là giá hiện tại trong DB. Nếu nó khác mức khách nhìn
        // thấy lần trước (hoặc khác lúc bấm thêm vào giỏ) thì phải nói rõ trước
        // khi họ bấm thanh toán.
        $fingerprint = $cart->priceFingerprint($lines);
        $seen = session('checkout_price_fingerprint');
        $priceChanged = $seen === null
            ? $cart->hasPriceChanges($lines)
            : $seen !== $fingerprint;

        session(['checkout_price_fingerprint' => $fingerprint]);

        return $this->render(
            'web.order.checkout',
            compact(
                'paymentEnabled',
                'lines',
                'totalVND',
                'totalUSD',
                'stockProblems',
                'priceChanged'
            )
        );
    }


    /**
     * @Description : 
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */

    public function thanks()
    {
        $locale = app()->getLocale();
        // get session 'orderId'
        $orderId = session('orderId');
        // $orderId = "DH2024053011575593";
        $method_payment = session('method_payment');
        $order = Order::where('transaction_code', $orderId)->first();
        if ($order == null) {
            return abort(404);
        }

        $replacements = $this->orderReplacements($order, $locale);

        $format = LanguageLine::where('group', 'payment')
            ->where('key', $method_payment ? 'content_thanks_you' : 'content_thanks_you_nonpayment')
            ->value('text');

        $data = $this->renderOrderTemplate($format, $replacements);

        if ($data === '') {
            // Mẫu chưa được cấu hình trong admin. Khách vừa trả tiền xong nên
            // tuyệt đối không được để rơi vào lỗi 500 - hiện bản tối giản.
            Log::warning('Thiếu mẫu nội dung trang cảm ơn trong language_lines', [
                'order' => $order->transaction_code,
                'paid' => (bool) $method_payment,
            ]);

            $data = '<p>' . e(translate('thanks.fallback', ['id' => $order->transaction_code])) . '</p>'
                . $this->orderLinesTable($order, $locale);
        }

        session()->forget('orderId');

        // Trang này có thể bị tải lại (khách bấm F5, hoặc cổng thanh toán
        // redirect về nhiều lần) nên chỉ gửi email đúng một lần cho mỗi đơn.
        if ($order->markConfirmationSent()) {
            $this->sendEmailOrder($order, $locale, $method_payment);
        }

        Cart::destroy();
        session()->forget('checkout_price_fingerprint');

        return $this->render('web.order.thanks', [
            'data' => $data,
        ]);
    }

    /**
     * @Description : handle checkout
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */


    public function handle_checkout(CheckoutRequest $request, CartService $cart)
    {
        $locale = app()->getLocale();
        $dataReq = $request->validated();
        $method_payment = $dataReq['method_payment'];
        $lines = $cart->lines($locale);

        // Chỉ thu đúng mức giá khách vừa nhìn thấy trên trang checkout. Giá đổi
        // giữa lúc mở trang và lúc bấm nút thì quay lại cho khách xem lại.
        if (session('checkout_price_fingerprint') !== $cart->priceFingerprint($lines)) {
            return $this->backToCheckout(translate('checkout.price_changed'));
        }

        $stockProblems = $cart->stockProblems($lines);

        if ($stockProblems !== []) {
            return $this->backToCheckout(implode(' ', $stockProblems));
        }

        // PayPal chỉ thu USD, payOS chỉ thu VND. Với COD thì theo ngôn ngữ khách
        // đang xem. Dùng mã ISO thay cho ký hiệu hiển thị ('đ'/'$') để
        // `unit_payment` và `order_details.currency_code` luôn đồng nhất.
        [$priceLocale, $currency_code] = match ($method_payment) {
            CheckoutRequest::METHOD_PAYPAL => ['en', 'USD'],
            CheckoutRequest::METHOD_BANK => ['vi', 'VND'],
            default => [$locale, $locale === 'en' ? 'USD' : 'VND'],
        };

        $totalPrice = 0;
        $data = [];

        foreach ($lines as $line) {
            $price = $cart->priceIn($line, $priceLocale);

            // Ngôn ngữ chưa có bản dịch giá (vd 'fr') vẫn dùng chung tiền tệ VND
            // với 'vi' nên rơi về 'vi' được; USD thì không, phải có giá riêng.
            if ($price === null && $currency_code === 'VND') {
                $price = $cart->priceIn($line, 'vi');
            }

            if ($price === null) {
                // Không quy đổi hộ sang tiền tệ khác: 250.000 VND và 250.000 USD
                // là hai con số rất khác nhau.
                Log::error('Checkout: sản phẩm thiếu giá cho tiền tệ thanh toán', [
                    'product' => $line['code'],
                    'currency' => $currency_code,
                    'price_locale' => $priceLocale,
                ]);

                return $this->backToCheckout(translate('payment.price_unavailable'));
            }

            $totalPrice += $price * $line['qty'];

            $data[] = [
                'code' => $line['code'],
                'product_id' => $line['product']?->id,
                'name' => $line['name'],
                'unit_amount' => [
                    'currency_code' => $currency_code,
                    'value' => $price,
                ],
                'quantity' => $line['qty'],
            ];
        }

        // Tạo đơn, sinh mã, ghi dòng hàng và giữ hàng trong cùng một transaction:
        // hỏng giữa chừng thì không để lại đơn thiếu mã, đơn không có dòng hàng,
        // hay tồn kho bị trừ cho một đơn không tồn tại.
        try {
            $order = DB::transaction(function () use ($request, $dataReq, $data, $totalPrice, $currency_code) {
                $order = new Order();
                $order->name = trim($dataReq['firstName'] . ' ' . $dataReq['lastName']);
                $order->email = $dataReq['email'];
                $order->phone = $dataReq['phone_full'];
                $order->address = self::formatAddress($dataReq);
                $order->message = $dataReq['description'] ?? '';
                $order->method_payment = $dataReq['method_payment'];
                $order->content = json_encode($data);
                $order->total = $totalPrice;
                $order->unit_payment = $currency_code;
                $order->ip = $request->ip();
                $order->proxy = $request->server('HTTP_X_FORWARDED_FOR');
                $order->status = Order::STATUS_PENDING;
                $order->order_status = Order::ORDER_STATUS_WAITING;
                $order->save();

                $order->transaction_code = Order::genCode($order->id);
                $order->save();

                $order->syncDetails($data);
                $order->deductStock();

                return $order;
            });
        } catch (OutOfStockException $e) {
            // Có khách khác vừa mua mất hàng trong lúc đang điền form.
            return $this->backToCheckout($e->userMessage());
        }

        if ($method_payment === CheckoutRequest::METHOD_PAYPAL) {
            $response = (new Paypal())->payment($order);

            if (($response['errorCode'] ?? 1) === 0 && !empty($response['url'])) {
                return redirect($response['url']);
            }

            // Không tạo được link thanh toán: đơn vẫn giữ lại để đối soát, còn
            // hàng đã giữ thì trả về kho (markAsFailed lo việc này).
            $order->markAsFailed();

            return $this->backToCheckout(
                translate('payment.failed', ['message' => $response['message'] ?? ''])
            );
        }

        if ($method_payment === CheckoutRequest::METHOD_BANK) {
            session(['orderId' => $order->transaction_code, 'method_payment' => true]);

            return redirect(route('payment.web.bank', [], true));
        }

        session(['orderId' => $order->transaction_code, 'method_payment' => false]);

        return redirect(route('payment.web.thanks', [], true));
    }

    private function backToCheckout(string $message)
    {
        return redirect(route('payment.web.checkout', [], true))->with('error', $message);
    }

    /**
     * Ghép địa chỉ từ những phần có giá trị, tránh chuỗi kiểu ", , , VN" khi
     * tỉnh/thành không lấy được từ API bên thứ ba.
     */
    private static function formatAddress(array $data): string
    {
        $parts = [
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            trim(($data['country'] ?? '') . ' ' . ($data['zipCode'] ?? '')),
        ];

        return implode(', ', array_filter($parts, fn($part) => trim((string) $part) !== ''));
    }
}
