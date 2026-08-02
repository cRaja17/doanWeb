@extends('web.layouts.index')

@php
    $lang = app()->getLocale();
    view()->share('title', translate($category->name ?? ''));
    view()->share('keywords', translate($category->name ?? ''));
    view()->share('description', strip_tags($category->description ?? ''));
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', isset($category->image) ? get_image_url($category->image) : asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('content')
    <div class="plp-wrapper">
        <!-- 1. Breadcrumbs -->
        @include('web.elements.breadcrumb', ['items' => [['label' => translate($category->name)]]])

        <!-- Mobile Filter Bar Button -->
        <div class="plp-mobile-filter">
            <button type="button" class="plp-toolbar__mobile-btn" id="open-mobile-filter">
                <span><i class="fa-solid fa-filter" style="color: var(--color-primary, #2563eb); margin-right: 0.5rem;"></i> {{ translate('product_category.mobile_filters') }}</span>
                <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>

        <div class="plp-grid">
            <!-- 2. Filter Sidebar -->
            <form action="{{ url()->current() }}" method="GET" class="filter-sidebar" id="filter-form">
                <!-- Keep sorting state -->
                <input type="hidden" name="sort" id="sort-hidden" value="{{ request('sort', 'newest') }}">
                <input type="hidden" name="per_page" id="per-page-hidden" value="{{ request('per_page', 12) }}">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div class="filter-sidebar__header">
                    <h3 class="filter-sidebar__title">
                        <i class="fa-solid fa-sliders" style="color: var(--color-primary, #2563eb);"></i>
                        <span>{{ translate('product_category.filters') }}</span>
                    </h3>
                    <a href="{{ url()->current() }}" class="filter-sidebar__clear-btn" style="text-decoration:none;">{{ translate('product_category.clear') }}</a>
                </div>

                <!-- Categories Group -->
                <div class="filter-sidebar__group">
                    <div class="filter-sidebar__label">
                        <span>{{ translate('product_category.categories') }}</span>
                    </div>
                    <ul class="filter-sidebar__list">
                        @foreach ($categories as $cat)
                            <li class="filter-sidebar__item">
                                <a href="{{ route('web_list_product', $cat->slug, true, app()->getLocale()) }}"
                                   class="filter-sidebar__link {{ $cat->slug == $category->slug ? 'filter-sidebar__link--active' : '' }}">
                                    <span>{{ translate($cat->name) }}</span>
                                    <span class="filter-sidebar__count">{{ $cat->products_count ?? $cat->products->count() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Price Range Group -->
                <div class="filter-sidebar__group">
                    <div class="filter-sidebar__label">
                        <span>{{ translate('product_category.price') }}</span>
                    </div>
                    <div class="filter-sidebar__price-inputs">
                        <input type="number" name="min_price" class="filter-sidebar__price-input" placeholder="{{ translate('product_category.from') }}" value="{{ request('min_price') }}" onchange="document.getElementById('filter-form').submit()">
                        <input type="number" name="max_price" class="filter-sidebar__price-input" placeholder="{{ translate('product_category.to') }}" value="{{ request('max_price') }}" onchange="document.getElementById('filter-form').submit()">
                    </div>
                </div>

                <!-- Origin Group -->
                @if (!empty($origins) && count($origins) > 0)
                    <div class="filter-sidebar__group">
                        <div class="filter-sidebar__label">
                            <span>{{ translate('product_category.origin') }}</span>
                        </div>
                        <ul class="filter-sidebar__list">
                            @foreach ($origins as $origin)
                                <li class="filter-sidebar__checkbox-item">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;">
                                        <input type="radio" name="made" value="{{ $origin }}" onchange="document.getElementById('filter-form').submit()" {{ request('made') == $origin ? 'checked' : '' }}>
                                        <span>{{ $origin }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Stock Filter -->
                <div class="filter-sidebar__group">
                    <div class="filter-sidebar__label">
                        <span>{{ translate('product_category.status') }}</span>
                    </div>
                    <ul class="filter-sidebar__list">
                        <li class="filter-sidebar__checkbox-item">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="in_stock" value="1" onchange="document.getElementById('filter-form').submit()" {{ request('in_stock') ? 'checked' : '' }}>
                                <span>{{ translate('product_category.in_stock') }}</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </form>

            <!-- 3. Right Main Content Area -->
            <div>
                <!-- Toolbar Header -->
                <div class="plp-toolbar">
                    <div class="plp-toolbar__info">
                        <h1 class="plp-toolbar__title">{{ translate($category->name) }}</h1>
                        <span class="plp-toolbar__count">
                            {{ strtr(translate('product_category.showing'), [
                                ':from' => $products->total() ? $products->firstItem() : 0,
                                ':to' => $products->total() ? $products->lastItem() : 0,
                                ':total' => $products->total(),
                            ]) }}
                        </span>
                    </div>

                    <div class="plp-toolbar__actions">
                        <!-- Per Page Selector -->
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 0.85rem; color: var(--color-text-muted, #64748b); margin-right: 0.5rem; white-space: nowrap;">{{ translate('product_category.show') }}</span>
                            <select class="plp-toolbar__select" onchange="document.getElementById('per-page-hidden').value = this.value; document.getElementById('filter-form').submit();">
                                @foreach ([12, 24, 48] as $pp)
                                    <option value="{{ $pp }}" {{ (int) request('per_page', 12) === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sort By Selector -->
                        <select class="plp-toolbar__select" onchange="document.getElementById('sort-hidden').value = this.value; document.getElementById('filter-form').submit();">
                            @foreach (['newest', 'name_asc', 'price_asc', 'price_desc'] as $sortKey)
                                <option value="{{ $sortKey }}" {{ request('sort', 'newest') === $sortKey ? 'selected' : '' }}>{{ translate('product_category.' . $sortKey) }}</option>
                            @endforeach
                        </select>

                        <!-- View Mode Switcher -->
                        <div class="plp-toolbar__view-mode">
                            <button type="button" class="plp-toolbar__view-btn plp-toolbar__view-btn--active" id="btn-grid-view" onclick="setViewMode('grid')" title="{{ translate('product_category.grid') }}">
                                <i class="fa-solid fa-border-all"></i>
                            </button>
                            <button type="button" class="plp-toolbar__view-btn" id="btn-list-view" onclick="setViewMode('list')" title="{{ translate('product_category.list') }}">
                                <i class="fa-solid fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="plp-products-grid" id="products-list-container">
                    @forelse ($products as $item)
                        @include('web.elements.product-card', ['product' => $item, 'showSummary' => true])
                    @empty
                        <div class="empty-plp-card">
                            <div class="empty-plp-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-secondary, #0f172a); margin-bottom: 0.5rem;">{{ translate('product_category.empty_title') }}</h3>
                            <p style="color: var(--color-text-muted, #64748b); font-size: 0.95rem; margin-bottom: 1.5rem;">{{ translate('product_category.empty_text') }}</p>
                            <a href="{{ url()->current() }}" class="filter-sidebar__clear-btn" style="text-decoration:none; display:inline-block;">{{ translate('product_category.reset') }}</a>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination Footer -->
                @if ($products->hasPages())
                    <div style="display: flex; align-items: center; justify-content: center; margin-top: 2rem;">
                        {{ $products->links('web.layouts.partials.pagination') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            setViewMode(localStorage.getItem('plp_view_mode') || 'grid');

            // Trên mobile sidebar bị ẩn qua CSS, nút này bật/tắt lại.
            $('#open-mobile-filter').on('click', function () {
                var $sidebar = $('#filter-form');
                var isHidden = $sidebar.css('display') === 'none';
                $sidebar.css('display', isHidden ? 'block' : 'none');
                $(this).find('.fa-chevron-down').toggleClass('fa-rotate-180', isHidden);
            });
        });

        function setViewMode(mode) {
            var isList = mode === 'list';
            $('#products-list-container').toggleClass('plp-products-grid--list-view', isList);
            $('#btn-list-view').toggleClass('plp-toolbar__view-btn--active', isList);
            $('#btn-grid-view').toggleClass('plp-toolbar__view-btn--active', !isList);
            localStorage.setItem('plp_view_mode', mode);
        }
    </script>
@endsection
