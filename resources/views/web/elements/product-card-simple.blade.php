<div class="product-card">
    <div class="product-card__img-wrapper" style="height: 180px;">
        <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}" class="product-card__img">
    </div>
    <div class="product-card__body" style="padding: 1rem;">
        <a href="{{ $product->url }}" class="product-card__title" style="font-size: 0.95rem; -webkit-line-clamp: 2; height: 2.6rem; margin-bottom: 0.35rem;">{{ $product->name }}</a>
        <div class="product-card__price" style="font-size: 1.05rem; margin-bottom: 0.75rem;">
            {{ $product->formatted_price ?? number_format($product->price, 0, ',', '.') . ' đ' }}
        </div>
        <button type="button" class="product-card__add-btn" onclick="add_to_cart({{ $product->id }}, 1)" style="width: 100%; justify-content: center; padding: 0.45rem;">
            <i class="fa-solid fa-bag-shopping"></i> {{ translate('product.add_to_cart') }}
        </button>
    </div>
</div>
