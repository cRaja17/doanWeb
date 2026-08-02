@php
    // Model Product không có thuộc tính `url`; chỉ dữ liệu trả về từ AJAX mới có sẵn.
    // Vì vậy dựng link từ slug để thẻ luôn trỏ đúng trang chi tiết theo ngôn ngữ.
    $cardUrl = $product->url
        ?? (!empty($product->slug)
            ? route('web_product_detail', $product->slug, true, app()->getLocale())
            : null);
@endphp
<div class="product-card">
    <div class="product-card__img-wrapper">
        @if ($cardUrl)
            <a href="{{ $cardUrl }}" class="product-card__img-link" aria-label="{{ $product->name }}">
        @endif
        <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}" class="product-card__img">
        @if ($cardUrl)
            </a>
        @endif
        @if(isset($product->made) && $product->made)
            <span class="product-card__badge">{{ $product->made }}</span>
        @endif
        <div class="product-card__quick-actions">
            <button type="button" class="product-card__quick-btn" title="{{ translate('product.favorite') }}"><i class="fa-regular fa-heart"></i></button>
            <button type="button" class="product-card__quick-btn" title="{{ translate('product.quick_view') }}"><i class="fa-regular fa-eye"></i></button>
        </div>
    </div>
    <div class="product-card__body">
        @if ($cardUrl)
            <a href="{{ $cardUrl }}" class="product-card__title">{{ $product->name }}</a>
        @else
            <span class="product-card__title">{{ $product->name }}</span>
        @endif
        <div class="product-card__rating">
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <i class="fa-solid fa-star"></i>
            <span style="color: var(--color-text-muted); font-size: 0.8rem; margin-left: 0.2rem;">(48)</span>
        </div>
        @if(isset($showSummary) && $showSummary && isset($product->summary))
        <div class="product-card__summary">
            {!! $product->summary !!}
        </div>
        @endif
        <div class="product-card__footer">
            @php
                // Cột unit lưu đơn vị tiền tệ theo từng ngôn ngữ (vi => đ, en => $),
                // nên không được hardcode "đ" ở đây.
                $cardUnit = trim((string) ($product->unit ?? ''));
                $cardPrice = $product->formatted_price
                    ?? trim(number_format((float) $product->price, 0, ',', '.') . ' ' . $cardUnit);
            @endphp
            <span class="product-card__price">{{ $cardPrice }}</span>
            <button type="button" class="product-card__add-btn product-card__add-btn--icon" onclick="add_to_cart({{ $product->id }}, 1)" title="{{ translate('cart.add_to_cart') }}">
                <i class="fa-solid fa-bag-shopping"></i>
            </button>
        </div>
    </div>
</div>
