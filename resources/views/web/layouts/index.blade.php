<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">


<head>
    @include('web.layouts.partials.head')
    @yield('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G0PP8HJP1E"></script>
    <link rel="stylesheet" href="{{ asset('css/foundation.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/fontawesome.min.js') }}" crossorigin="anonymous"></script>

</head>

<body>
    @include('web.layouts.partials.header')
    <div class="main grid-container">
        <br>
        @yield('content')
    </div>

    @include('web.layouts.partials.footer')

    <script src="{{ asset('js/vendor/jquery.js') }}"></script>
    <script src="{{ asset('js/vendor/foundation.js') }}"></script>
    <script src="{{ asset('js/vendor/what-input.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script>

        $(document).ready(function () {
            showCart();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('.lang-selected').on('click', function (ev) {
                ev.preventDefault();
                $('.lang-selector').toggle();
            });
            $('.lang-selector li a').on('click', function (ev) {
                ev.preventDefault();
                var lang = $(this).text();
                $('.lang-selected').html(lang);
                $('.lang-selector').toggle();
            });
        });

        function getCartItems() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: "{{ route('ajax_get_cart') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {
                        locale: '{{ app()->getLocale() }}',
                    },
                    dataType: 'json',
                    success: function (response) {
                        resolve(response);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        reject(error);
                    }
                });
            });
        }

        async function showCart(openDrawer = false) {
            try {
                var response = await getCartItems();
                var items = Object.values(response.cartData);

                if (items.length == 0) {
                    $('.modern-cart-body ul').html(`
                        <li style="padding: 4rem 1.5rem; text-align: center;">
                            <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                            <p style="color: #64748b; font-weight: 600; font-size: 0.95rem; margin-bottom: 0;">{{ translate("cart.empty") }}</p>
                        </li>
                    `);
                    $('.cart-badge-count').text('0');
                    $('.cart-subtotal-val').text('0 đ');
                } else {
                    var products = '';
                    var totalCalc = 0;
                    items.forEach(item => {
                        var itemTotal = item.price * item.qty;
                        totalCalc += itemTotal;
                        products += `
                            <li class="modern-cart-item" data-id="${item.rowId}">
                                <img src="${item.image}" alt="${item.name}" class="modern-cart-img">
                                <div class="modern-cart-info">
                                    <a href="${item.url}" class="modern-cart-name">${item.name}</a>
                                    <div class="modern-cart-meta">
                                        <span class="modern-cart-price">${formatter.format(item.price)}</span>
                                        <div class="modern-cart-qty-ctrl" style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                            <button type="button" onclick="updateDrawerQty('${item.rowId}', ${item.qty - 1})" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 4px; cursor: pointer; color: #64748b; font-size: 14px;">&minus;</button>
                                            <span class="modern-cart-qty" style="font-size: 0.9rem; font-weight: 600; min-width: 20px; text-align: center;">${item.qty}</span>
                                            <button type="button" onclick="updateDrawerQty('${item.rowId}', ${item.qty + 1})" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 4px; cursor: pointer; color: #64748b; font-size: 14px;">&plus;</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="modern-cart-remove" onclick="removeDrawerItem('${item.rowId}')" title="{{ translate('cart.remove') }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </li>
                        `;
                    });
                    $('.modern-cart-body ul').html(products);
                    $('.cart-badge-count').text(response.total);
                    $('.cart-subtotal-val').text(formatter.format(totalCalc));
                }

                if (openDrawer) {
                    $('.modern-cart-drawer').addClass('active');
                }
            } catch (error) {
                console.error('Error fetching cart items:', error);
            }
        }

        function updateDrawerQty(rowId, qty) {
            if (qty < 1) {
                removeDrawerItem(rowId);
                return;
            }
            $.ajax({
                url: "{{ route('ajax_update_cart') }}",
                type: 'POST',
                data: {
                    rowId: rowId,
                    status: 'update',
                    qty: qty
                },
                success: function (data) {
                    if (data.status == 200) {
                        showCart(false);
                        if (typeof show_cart_list === 'function') {
                            show_cart_list();
                        }
                    }
                },
                error: function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message;
                    if (message) {
                        showToast(message);
                    }
                    showCart(false);
                }
            });
        }

        function removeDrawerItem(rowId) {
            $.ajax({
                url: "{{ route('ajax_update_cart') }}",
                type: 'POST',
                data: {
                    rowId: rowId,
                    status: 'delete'
                },
                success: function (data) {
                    if (data.status == 200) {
                        showCart(false);
                        if (typeof show_cart_list === 'function') {
                            show_cart_list();
                        }
                    }
                }
            });
        }

        function showToast(message) {
            var toast = $('<div style="position: fixed; bottom: 30px; right: 30px; background: #0f172a; color: #ffffff; padding: 0.9rem 1.4rem; border-radius: 12px; font-weight: 600; font-size: 0.95rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 99999; display: flex; align-items: center; gap: 0.75rem; animation: slideUp 0.3s ease;"><i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i><span>' + message + '</span></div>');
            $('body').append(toast);
            setTimeout(function () {
                toast.fadeOut(400, function () { toast.remove(); });
            }, 3000);
        }

        function add_to_cart(id, qty = 1) {
            $.ajax({
                url: "{{ route('ajax_add_to_cart') }}",
                type: 'POST',
                data: {
                    id: id,
                    qty: qty
                },
                success: function (data) {
                    if (data.status == 200) {
                        showCart(true);
                        if (typeof show_cart_list === 'function') {
                            show_cart_list();
                        }
                        showToast('{{ translate('cart.added_to_cart_success') }}');
                    }
                },
                error: function (xhr, status, error) {
                    if (xhr.status === 419) {
                        location.reload();
                        return;
                    }
                    // Hết hàng / sản phẩm ngừng bán: server trả 422 kèm lời nhắn,
                    // phải cho khách thấy chứ không im lặng như trước.
                    var message = xhr.responseJSON && xhr.responseJSON.message;
                    if (message) {
                        showToast(message);
                    } else {
                        console.error('Add to cart error:', error);
                    }
                }
            });
        }

        $(document).on('click', '.modern-cart-trigger', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.modern-cart-drawer').toggleClass('active');
        });

        $(document).on('click', '.modern-cart-close, .modern-cart-overlay', function (e) {
            e.preventDefault();
            $('.modern-cart-drawer').removeClass('active');
        });
    </script>

    @yield('js')

    @include('web.layouts.partials.chatbot')

</body>


</html>