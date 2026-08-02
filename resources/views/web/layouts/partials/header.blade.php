@php
    $langs = [];
    $routeObj = request()->route();
    $routeName = $routeObj ? $routeObj->getName() : '';
    $route = $routeName;
    $langKeys = is_array($languages) ? array_keys($languages) : collect($languages ?? [])->keys()->toArray();
    if ($routeName && strpos($routeName, '.') !== false && in_array(substr($routeName, 0, strpos($routeName, '.')), $langKeys)) {
        $route = substr($routeName, strpos($routeName, '.') + 1);
    }
    $slug = $routeObj ? ($routeObj->parameters()['slug'] ?? null) : null;
    $unit = 'VND';
    $flagDefault = '';
    $langDefault = app()->getLocale();
    $activeLangName = 'VI';
    foreach ($languages as $key => $value) {
        $flagHtml = '';
        if (!empty($value->flag) && (str_contains($value->flag, '<svg') || str_contains($value->flag, '<img'))) {
            $flagHtml = $value->flag;
        } elseif (!empty($value->flag)) {
            $flagHtml = '<img src="' . \App\Support\ImageUrl::resolve($value->flag) . '" alt="' . $value->abbr . '" style="width: 22px; height: 15px; object-fit: cover; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); vertical-align: middle;">';
        } else {
            $flagHtml = '<img src="' . asset('flags/' . $value->abbr . '.webp') . '" alt="' . $value->abbr . '" style="width: 22px; height: 15px; object-fit: cover; border-radius: 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); vertical-align: middle;">';
        }

        $langName = $value->native;
        if ($value->abbr == app()->getLocale()) {
            $flagDefault = $flagHtml;
            $activeLangName = strtoupper($value->abbr);
            $unit = !empty($value->unit) ? $value->unit : ($value->abbr == 'vi' ? 'VND' : 'USD');
            $langDefault = $value->abbr;
        } else {
            $langs[$key]['flag'] = $flagHtml;
            $langs[$key]['abbr'] = $value->abbr;
            $langs[$key]['name'] = $langName;
            $langs[$key]['url'] = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($value->abbr, null, [], true);
        }
    }
@endphp

<script>
    var currencyCode = '{{ $unit }}';
    var formatter;
    try {
        formatter = new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'currency',
            currency: currencyCode
        });
    } catch (e) {
        formatter = new Intl.NumberFormat('{{ app()->getLocale() }}');
    }
</script>






<!-- 2. Main Navigation Bar -->
<div class="modern-navbar-wrapper">
    <div class="modern-navbar">
        <!-- Logo -->
        <a href="{{ route('web_home') }}" class="modern-brand" aria-label="HealthNutri">
            <div class="brand-logo-icon">
                <i class="fa-solid fa-leaf"></i>
            </div>
            <span>HealthNutri</span>
        </a>

        <!-- Desktop Navigation Menu -->
        <ul class="modern-nav-links">
            <!-- 1. Trang Chủ -->
            <li class="modern-nav-item {{ request()->routeIs('web_home') ? 'active' : '' }}">
                <a href="{{ route('web_home') }}">
                    <i class="fa-solid fa-house" style="font-size: 0.85rem;"></i>
                    <span>{{ translate('header.home') }}</span>
                </a>
            </li>

            <!-- 2. Sản Phẩm (Dropdown Category) -->
            <li
                class="modern-nav-item {{ request()->routeIs('web_all_products') || request()->routeIs('web_list_product') || request()->routeIs('web_product_detail') ? 'active' : '' }}">
                <a href="{{ route('web_all_products') }}">
                    <i class="fa-solid fa-capsules" style="font-size: 0.85rem;"></i>
                    <span>{{ translate('header.products') }}</span>
                </a>
            </li>

            <!-- 3. Tin Tức & Mẹo Sức Khỏe -->
            <li
                class="modern-nav-item {{ request()->routeIs('web_all_posts') || request()->routeIs('web_news_all') || request()->routeIs('web_list_post') || request()->routeIs('web_post_detail') ? 'active' : '' }}">
                <a href="{{ route('web_all_posts') }}">
                    <i class="fa-solid fa-newspaper" style="font-size: 0.85rem;"></i>
                    <span>{{ translate('header.news') }}</span>
                </a>
            </li>

            <!-- 4. Giới Thiệu (Information Dropdown) -->
            <li class="modern-nav-item {{ request()->routeIs('web.information') ? 'active' : '' }}">
                <a href="javascript:;">
                    <i class="fa-solid fa-circle-info" style="font-size: 0.85rem;"></i>
                    <span>{{ translate('header.about') }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; color: #94a3b8;"></i>
                </a>
                <ul class="nav-dropdown">
                    @foreach ($information_global as $item)
                        @if ($item->view_header == 1)
                            <li>
                                <a href="{{ route('web.information', $item->slug) }}" class="nav-dropdown-link">
                                    <i class="fa-solid fa-circle-check" style="font-size: 0.75rem; color: #10b981;"></i>
                                    <span>{{ translate($item->name) }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </li>

        </ul>

        <!-- Right Side Actions -->
        <div class="modern-actions">
            <!-- Header Cart Trigger Button -->
            <a href="javascript:;" class="modern-cart-trigger header-cart-trigger" title="{{ translate('cart.name') }}">
                <i class="fa-solid fa-bag-shopping"></i>
                <span class="cart-badge-count">0</span>
            </a>

            <!-- Language Selector Pill -->
            <button class="lang-pill-btn" type="button" data-toggle="example-dropdown-bottom-left">
                {!! $flagDefault !!}
                <span>{{ $activeLangName }}</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; color: #64748b;"></i>
            </button>

            <!-- Mobile Burger Trigger -->
            <button type="button" class="mobile-burger-btn" data-toggle="menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Language Dropdown Menu Pane -->
    <div class="dropdown-pane lang-dropdown-menu" data-position="bottom" data-alignment="right"
        id="example-dropdown-bottom-left" data-dropdown data-auto-focus="true" data-close-on-click="true">
        @foreach ($langs as $lang)
            <a href="{{ $lang['url'] }}" class="lang-dropdown-item">
                <div style="display: flex; align-items: center;">
                    {!! $lang['flag'] !!}
                </div>
                <span>{{ $lang['name'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Mobile Drawer Menu Pane -->
    <div class="dropdown-pane bottom header-menu text-left" data-allow-bottom-overlap="true" data-alignment="right"
        data-h-offset="-20" data-v-offset="15" id="menu" data-position="bottom" data-dropdown
        data-close-on-click="true">
        <ul style="margin:0; padding: 0.5rem 0; list-style: none;">
            <li><a class="link-menu" href="{{ route('web_home') }}">
                    <p>🏠 {{ translate('header.home') }}</p>
                </a></li>
            <li><a class="link-menu" href="#featured-products">
                    <p>💊 {{ translate('product.product') }}</p>
                </a></li>
            <li><a class="link-menu" href="{{ route('web_list_post', 'suc-khoe-doi-song', true, app()->getLocale()) }}">
                    <p>📰 {{ translate('header.news') }}</p>
                </a></li>
            <li><a class="link-menu modern-cart-trigger" href="javascript:;">
                    <p>🛒 {{ translate('cart.name') }}</p>
                </a></li>
            <li><a class="link-menu" href="{{ route('payment.web.checkout') }}">
                    <p>💳 {{ translate('cart.checkout') }}</p>
                </a></li>
            @foreach ($information_global as $item)
                @if ($item->view_header == 1)
                    <li><a class="link-menu" href="{{ route('web.information', $item->slug) }}">
                            <p>ℹ️ {{ translate($item->name) }}</p>
                        </a></li>
                @endif
            @endforeach
        </ul>
    </div>
</div>

<div class="overlay"></div>

@if (
        !request()->route() ||
        request()->route()->getName() != 'payment.web.checkout'
    )
    <!-- Modern Slideout Cart Drawer -->
    <div class="modern-cart-drawer">
        <!-- Backdrop Overlay -->
        <div class="modern-cart-overlay"></div>

        <!-- Slideout Panel -->
        <div class="modern-cart-panel">
            <div class="modern-cart-header">
                <div class="modern-cart-title">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>{{ translate('cart.name') }}</span>
                </div>
                <button type="button" class="modern-cart-close" title="{{ translate('header.close') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modern-cart-body">
                <ul>
                    <!-- Dynamic products rendered via JavaScript -->
                </ul>
            </div>

            <div class="modern-cart-footer">
                <div class="modern-cart-subtotal">
                    <span>{{ translate('cart.subtotal') }}</span>
                    <span class="cart-subtotal-val">0</span>
                </div>
                <div class="modern-cart-actions">
                    <a href="{{ route('payment.web.checkout') }}" class="modern-btn-checkout">
                        <span>{{ translate('cart.checkout') }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif