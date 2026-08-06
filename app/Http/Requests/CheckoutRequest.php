<?php

namespace App\Http\Requests;

use App\Models\Config;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Kiểm tra dữ liệu đặt hàng ở phía server.
 *
 * Trước đây trang checkout chỉ validate bằng JavaScript (Foundation Abide),
 * nghĩa là mọi ràng buộc đều bỏ qua được. Form request này là lớp chặn thật:
 * thiếu trường, phương thức thanh toán lạ, hay giỏ hàng rỗng đều không tạo
 * được đơn.
 */
class CheckoutRequest extends FormRequest
{
    public const METHOD_BANK = 'bank';

    public const METHOD_PAYPAL = 'paypal';

    /** Đặt hàng không qua cổng thanh toán (COD). */
    public const METHOD_NONE = 'none';

    /** Các phương thức đi qua cổng thanh toán trực tuyến. */
    public const ONLINE_METHODS = [self::METHOD_BANK, self::METHOD_PAYPAL];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $methods = array_merge(self::ONLINE_METHODS, [self::METHOD_NONE]);

        return [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            // state/city đổ từ API bên thứ ba nên có thể rỗng khi API lỗi.
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zipCode' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            // Ô ẩn do intl-tel-input điền, đã gồm mã quốc gia.
            'phone_full' => ['required', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:1000'],
            'method_payment' => ['required', 'string', 'in:' . implode(',', $methods)],
        ];
    }

    /**
     * Nhãn tiếng Việt/Anh/Pháp cho thông báo lỗi, dùng lại đúng chuỗi đang hiển
     * thị trên form để khách không thấy tên biến kỹ thuật.
     */
    public function attributes(): array
    {
        return [
            'firstName' => translate('checkout.first_name'),
            'lastName' => translate('checkout.last_name'),
            'country' => translate('checkout.country'),
            'state' => translate('checkout.state'),
            'city' => translate('checkout.city'),
            'zipCode' => 'ZIP',
            'address' => translate('checkout.address'),
            'email' => 'Email',
            'phone_full' => translate('checkout.phone'),
            'description' => translate('checkout.mess'),
            'method_payment' => translate('checkout.method_payment'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (Cart::content()->isEmpty()) {
                // Giỏ rỗng mà vẫn POST được nghĩa là request không đến từ form
                // (hoặc session đã hết hạn) - dừng luôn, tạo đơn 0 đồng vô nghĩa.
                $validator->errors()->add('cart', translate('checkout.cart_empty'));

                return;
            }

            $method = (string) $this->input('method_payment');
            $paymentEnabled = Config::paymentEnabled();

            if (! $paymentEnabled && in_array($method, self::ONLINE_METHODS, true)) {
                $validator->errors()->add('method_payment', translate('checkout.payment_disabled'));
            }

            if ($paymentEnabled && $method === self::METHOD_NONE) {
                $validator->errors()->add('method_payment', translate('checkout.method_not_allowed'));
            }
        });
    }
}
