@extends('web.layouts.index')

@php
    view()->share('title', translate('payment.bank_transfer') . ' - ' . config('app.name'));
    view()->share('keywords', '');
    view()->share('description', '');
    view()->share('robots', 'noindex, nofollow');
    view()->share('canonical', url()->current());
    view()->share('og_image', asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('css')
    <style>
        /* payOS render form thanh toán trong iframe height:100% nên container phải có chiều cao cố định. */
        #payos-embedded-form {
            width: 100%;
            height: 520px;
            border: 1px solid #e6e6e6;
            border-radius: 6px;
            overflow: hidden;
        }

        @media screen and (max-width: 39.9375em) {
            #payos-embedded-form {
                height: 620px;
            }
        }

        .payos-note {
            font-size: .85rem;
            color: #767676;
            margin-top: .75rem;
        }
    </style>
@endsection

@section('content')
    <div class="payment cell grid-x">
        @if (session('error'))
            <div data-abide-error class="alert callout">
                <p><i class="fi-alert"></i>{{ session('error') }}</p>
            </div>
        @endif

        <div class="cell small-12 medium-7 large-8">
            <h6>{{ translate('checkout.information') }}</h6>
            <ul>
                <li>{{ translate('checkout.name') }}: {{ $order->name }}</li>
                <li>Email: {{ $order->email }}</li>
                <li>{{ translate('checkout.phone') }}: {{ $order->phone }}</li>
                <li>{{ translate('checkout.address') }}: {{ $order->address }}</li>
                <li>{{ translate('checkout.mess') }}: {{ $order->message }}</li>
            </ul>
            <div class="multi-step-product-step-subheader cell">
                <h6 class="multi-step-product-step-subheader">{{ translate('checkout.order') }}</h6>

                <table class="order-table-content stack">
                    <thead>
                        <tr>
                            <th width="120" class="text-center">{{ translate('cart.product') }}</th>
                            <th width="350" class="text-center">{{ translate('cart.information') }}</th>
                            <th width="80" class="text-center">{{ translate('cart.quantity') }}</th>
                            <th width="100" class="text-center">{{ translate('cart.price') }}</th>
                            <th width="5" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $item)
                            <tr class="text-center">
                                <td><img class="order-product-image" src="{{ get_image_url($item['image']) }}"></td>
                                <td><span>{{ is_array($item['name']) ? $item['name'][app()->getLocale()] ?? reset($item['name']) : $item['name'] }}</span>
                                    <ul class="order-product-info">
                                        <li> {{ translate('cart.code') }}:{{ $item['code'] }}</li>
                                    </ul>
                                </td>
                                <td>
                                    <span>{{ $item['qty'] }}</span>
                                </td>
                                <td><span
                                        class="order-product-price">{{ FormatCurrency($item['price'], $item['unit'] ?? 'VND') }}
                                    </span><br class="hide-for-small-only">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <br>
            </div>
        </div>

        <div class="cell grid-container small-12 medium-5 large-4">
            <div class="multi-step-checkout-shipping-options multi-step-checkout-step-section">
                <h6>{{ translate(['vi' => 'Quét mã để thanh toán', 'en' => 'Scan to pay', 'fr' => 'Scannez pour payer']) }}</h6>

                {{-- payOS Embedded Form được nhúng vào đây bằng JS --}}
                <div id="payos-embedded-form"></div>

                <p class="payos-note">
                    {{ translate([
                        'vi' => 'Mở app ngân hàng và quét mã VietQR. Trang sẽ tự chuyển khi nhận được tiền, vui lòng không đóng cửa sổ này.',
                        'en' => 'Open your banking app and scan the VietQR code. This page redirects automatically once the payment is received, please keep it open.',
                        'fr' => 'Ouvrez votre application bancaire et scannez le code VietQR. La page redirigera automatiquement une fois le paiement reçu.',
                    ]) }}
                </p>

                <a href="{{ $cancelUrl }}" class="secondary button expanded">
                    {{ translate(['vi' => 'Huỷ thanh toán', 'en' => 'Cancel payment', 'fr' => 'Annuler le paiement']) }}
                </a>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.payos.vn/payos-checkout/v1/stable/payos-initialize.js"></script>
    <script>
        (function () {
            var returnUrl = @json($returnUrl);
            var cancelUrl = @json($cancelUrl);
            var statusUrl = @json($statusUrl);

            var checkout = PayOSCheckout.usePayOS({
                RETURN_URL: returnUrl,
                ELEMENT_ID: 'payos-embedded-form',
                CHECKOUT_URL: @json($link['checkoutUrl']),
                embedded: true,
                onSuccess: function () {
                    window.location.href = returnUrl;
                },
                onCancel: function () {
                    window.location.href = cancelUrl;
                },
                onExit: function () {
                    // Khách đóng form: để nguyên trang, họ có thể tải lại để mở lại mã QR.
                }
            });

            checkout.open();

            // Dự phòng: khách quét QR bằng điện thoại khác nên iframe không nhận được
            // sự kiện. Hỏi server mỗi 5 giây cho tới khi webhook payOS xác nhận.
            var polling = setInterval(function () {
                fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) {
                        if (data && data.paid && data.redirect) {
                            clearInterval(polling);
                            window.location.href = data.redirect;
                        }
                    })
                    .catch(function () { /* mất mạng tạm thời: thử lại ở lượt sau */ });
            }, 5000);

            window.addEventListener('beforeunload', function () {
                clearInterval(polling);
            });
        })();
    </script>
@endsection
