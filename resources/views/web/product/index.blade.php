@php
    $lang = app()->getLocale();

    $resolveImg = fn($path) => get_image_url($path);

    // Ảnh chính đứng đầu, sau đó tới các ảnh phụ (multiple_image là chuỗi JSON).
    $gallery = collect([$product->image])
        ->merge((array) json_decode($product->multiple_image, true))
        ->filter()
        ->map($resolveImg)
        ->unique()
        ->values();

    if ($gallery->isEmpty()) {
        $gallery = collect([$resolveImg(null)]);
    }

    $stock = (int) $product->quantity;
    $inStock = $stock > 0;
    $maxQty = $inStock ? min($stock, 99) : 1;

    view()->share('title', translate($product->name ?? ''));
    view()->share('keywords', translate($product->name ?? ''));
    view()->share('description', strip_tags($product->summary ?? ''));
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', get_image_url($product->image ?? ''));
    view()->share('og_url', url()->current());
@endphp


@section('content')
    <div class="pdp">
        <div aria-label="You are here:" role="navigation">
            @include('web.elements.breadcrumb', [
                'items' => [
                    ['label' => translate($category->name), 'url' => route('web_list_product', $category->slug, true, $lang)],
                    ['label' => translate($product->name)]
                ]
            ])
        </div>

        <div class="pdp__main">
            <!-- Gallery -->
            <div class="pdp__gallery">
                <div class="pdp__stage">
                    @if ($product->made)
                        <span class="pdp__stage-badge">{{ $product->made }}</span>
                    @endif
                    <a href="{{ $gallery->first() }}" data-fancybox="pdp-gallery" id="pdp-stage-link">
                        <img src="{{ $gallery->first() }}" alt="{{ translate($product->name) }}" id="pdp-stage-img">
                    </a>
                    {{-- Các ảnh còn lại vẫn đăng ký với Fancybox để lướt được trong lightbox. --}}
                    @foreach ($gallery->slice(1) as $img)
                        <a href="{{ $img }}" data-fancybox="pdp-gallery" hidden></a>
                    @endforeach
                </div>

                @if ($gallery->count() > 1)
                    <div class="pdp__thumbs">
                        @foreach ($gallery as $i => $img)
                            <button type="button" class="pdp__thumb {{ $i === 0 ? 'pdp__thumb--active' : '' }}"
                                data-src="{{ $img }}" aria-label="{{ translate($product->name) }} {{ $i + 1 }}">
                                <img src="{{ $img }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Info -->
            <div class="pdp__info">
                <h1 class="pdp__title">{{ translate($product->name) }}</h1>

                <div class="pdp__meta">
                    @if ($product->code)
                        <span>{{ translate('product.sku') }}: <b>{{ $product->code }}</b></span>
                    @endif
                    @if ($product->made)
                        <span>{{ translate('product.origin') }}: <b>{{ $product->made }}</b></span>
                    @endif
                    @if ($product->model)
                        <span>{{ translate('product.model') }}: <b>{{ $product->model }}</b></span>
                    @endif
                </div>

                <span class="pdp__stock {{ $inStock ? 'pdp__stock--in' : 'pdp__stock--out' }}">
                    <i class="fa-solid {{ $inStock ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                    {{ $inStock ? strtr(translate('product.available'), [':count' => $stock]) : translate('product.out_of_stock') }}
                </span>

                <div class="pdp__price-box">
                    <span class="pdp__price-label">{{ translate('product.price') }}</span>
                    <span class="pdp__price">{{ FormatCurrency(translate($product->price)) }}</span>
                </div>

                @if ($product->summary)
                    <div class="pdp__summary">{!! $product->summary !!}</div>
                @endif

                <div class="pdp__actions">
                    <div class="pdp__qty">
                        <button type="button" class="pdp__qty-minus" aria-label="-">&minus;</button>
                        <input type="number" id="quantity" value="1" min="1" max="{{ $maxQty }}"
                            aria-label="{{ translate('product.quantity') }}" {{ $inStock ? '' : 'disabled' }}>
                        <button type="button" class="pdp__qty-plus" aria-label="+">&plus;</button>
                    </div>

                    <button type="button" class="pdp__cart-btn" id="pdp-add-to-cart" {{ $inStock ? '' : 'disabled' }}
                        data-id="{{ $product->id }}">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="pdp__cart-label">
                            {{ $inStock ? translate('product.add_to_cart') : translate('product.out_of_stock') }}
                        </span>
                    </button>

                    @if ($product->link_affiliate)
                        <a href="{{ $product->link_affiliate }}" class="pdp__affiliate" target="_blank" rel="noopener">
                            <i class="fa-solid fa-bolt"></i> {{ translate('product.affiliate') }}
                        </a>
                    @endif
                </div>

                <ul class="pdp__trust">
                    <li><i class="fa-solid fa-shield-halved"></i> {{ translate('product.genuine') }}</li>
                    <li><i class="fa-solid fa-truck-fast"></i> {{ translate('product.shipping') }}</li>
                    <li><i class="fa-solid fa-rotate-left"></i> {{ translate('product.return') }}</li>
                </ul>

                <div class="pdp__share">
                    <span>{{ translate('product.share') }}:</span>
                    <button type="button" id="pdp-share-fb" aria-label="Facebook"><i
                            class="fa-brands fa-facebook-f"></i></button>
                    <button type="button" id="pdp-share-copy" aria-label="{{ translate('product.share') }}"><i
                            class="fa-solid fa-link"></i></button>
                </div>
            </div>
        </div>

        @if ($product->description)
            <div class="pdp__section">
                <h2 class="pdp__section-title">{{ translate('product.product_description') }}</h2>
                <div class="pdp__description">{!! $product->description !!}</div>
            </div>
        @endif

        @if (count($relatedProducts) > 0)
            <div class="pdp__section">
                <h2 class="pdp__section-title">{{ translate('product.related_products') }}</h2>
                <div class="plp-products-grid">
                    @foreach ($relatedProducts as $item)
                        @include('web.elements.product-card', ['product' => $item, 'showSummary' => false])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
    <link rel="stylesheet" href="{{ asset('js/fancybox/fancybox.min.css') }}" />
    <script src="{{ asset('js/fancybox/fancybox.min.js') }}"></script>
    <script>
        (function () {
            var maxReachedStr = "{{ translate('product.max_reached') }}";
            var copiedStr = "{{ translate('product.copied') }}";

            // --- Gallery: đổi ảnh chính khi bấm thumbnail ---
            var stageImg = document.getElementById('pdp-stage-img');
            var stageLink = document.getElementById('pdp-stage-link');

            document.querySelectorAll('.pdp__thumb').forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    var src = this.dataset.src;
                    stageImg.src = src;
                    stageLink.href = src;
                    document.querySelectorAll('.pdp__thumb').forEach(function (t) {
                        t.classList.remove('pdp__thumb--active');
                    });
                    this.classList.add('pdp__thumb--active');
                });
            });

            if (window.Fancybox) {
                Fancybox.bind('[data-fancybox="pdp-gallery"]', {});
            }

            // --- Bộ chọn số lượng, giới hạn theo tồn kho thật ---
            var input = document.getElementById('quantity');
            var minusBtn = document.querySelector('.pdp__qty-minus');
            var plusBtn = document.querySelector('.pdp__qty-plus');

            function clampQty() {
                var max = parseInt(input.max, 10) || 1;
                var value = parseInt(input.value, 10);
                if (isNaN(value) || value < 1) { value = 1; }
                if (value > max) {
                    value = max;
                    if (typeof showToast === 'function') { showToast(maxReachedStr); }
                }
                input.value = value;
                minusBtn.disabled = value <= 1;
                plusBtn.disabled = value >= max;
            }

            if (input && !input.disabled) {
                minusBtn.addEventListener('click', function () {
                    input.value = (parseInt(input.value, 10) || 1) - 1;
                    clampQty();
                });
                plusBtn.addEventListener('click', function () {
                    input.value = (parseInt(input.value, 10) || 1) + 1;
                    clampQty();
                });
                input.addEventListener('input', clampQty);
                clampQty();
            } else if (minusBtn && plusBtn) {
                minusBtn.disabled = true;
                plusBtn.disabled = true;
            }

            // --- Thêm vào giỏ---
            var cartBtn = document.getElementById('pdp-add-to-cart');
            if (cartBtn && !cartBtn.disabled) {
                cartBtn.addEventListener('click', function () {
                    add_to_cart(cartBtn.dataset.id, input.value);
                    cartBtn.classList.add('is-added');
                    setTimeout(function () { cartBtn.classList.remove('is-added'); }, 2000);
                });
            }

            // --- Chia sẻ ---
            var fbBtn = document.getElementById('pdp-share-fb');
            if (fbBtn) {
                fbBtn.addEventListener('click', function () {
                    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href),
                        '_blank', 'width=600,height=500,noopener');
                });
            }

            var copyBtn = document.getElementById('pdp-share-copy');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var done = function () {
                        if (typeof showToast === 'function') { showToast(copiedStr); }
                    };
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(location.href).then(done);
                    } else {
                        var tmp = document.createElement('textarea');
                        tmp.value = location.href;
                        document.body.appendChild(tmp);
                        tmp.select();
                        document.execCommand('copy');
                        document.body.removeChild(tmp);
                        done();
                    }
                });
            }
        })();
    </script>
@endsection