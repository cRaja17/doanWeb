@extends('web.layouts.index')

@php
    view()->share('title', isset($page) ? translate($page->name) : env('APP_NAME', 'Website'));
    view()->share('keywords', isset($page) ? translate($page->name) : '');
    view()->share('description', isset($page) ? strip_tags($page->description ?? '') : '');
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', isset($page) && isset($page->image) ? get_image_url($page->image) : asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('css')
@endsection

@section('content')
    <div class="grid-container" style="max-width: 1280px; padding: 1.5rem 1rem;">

        <!-- 1. Hero Slider Banner -->
        <div class="hero-slider__wrapper">
            <div class="orbit" role="region" aria-label="Hero Banners" data-orbit
                data-options="animInFromRight:fade-in; animOutToLeft:fade-out;">
                <div class="orbit-wrapper">
                    <ul class="orbit-container">
                        @if (!empty($getSlides) && count($getSlides) > 0)
                            @foreach ($getSlides as $key => $slide)
                                <li class="orbit-slide {{ $key == 0 ? 'is-active' : '' }}">
                                    <div class="hero-slider__slide-item" style="background-image: url('{{ asset($slide) }}');">
                                        <div class="hero-slider__slide-overlay"></div>
                                        <div class="hero-slider__content">
                                            <span class="hero-slider__badge">{{ translate('home.badge_organic') }}</span>
                                            <h1 class="hero-slider__title">{{ translate('home.slider_title1') }}</h1>
                                            <p class="hero-slider__desc">{!! translate('home.description_slide') !!}</p>
                                            <a href="#featured-products" class="hero-slider__btn">
                                                <span>{{ translate('home.button_banner') }}</span>
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            <li class="orbit-slide is-active">
                                <div class="hero-slider__slide-item"
                                    style="background-image: url('/storage/images/products/omega3_fish_oil.png');">
                                    <div class="hero-slider__slide-overlay"></div>
                                    <div class="hero-slider__content">
                                        <span class="hero-slider__badge">{{ translate('home.badge_organic') }}</span>
                                        <h1 class="hero-slider__title">{{ translate('home.slider_title2') }}</h1>
                                        <p class="hero-slider__desc">{{ translate('home.slider_desc2') }}</p>
                                        <a href="#featured-products" class="hero-slider__btn">
                                            <span>{{ translate('home.button_banner') }}</span>
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- 2. Trust Signals (Thanh Cam Kết & Chứng Nhận) -->
        <div class="features-grid">
            @include('web.elements.feature-card', ['icon' => 'fa-solid fa-certificate', 'bgColor' => '#ecfdf5', 'iconColor' => '#10b981', 'title' => translate('home.feature1_title'), 'desc' => translate('home.feature1_desc')])
            @include('web.elements.feature-card', ['icon' => 'fa-solid fa-file-medical', 'bgColor' => '#eff6ff', 'iconColor' => '#2563eb', 'title' => translate('home.feature2_title'), 'desc' => translate('home.feature2_desc')])
            @include('web.elements.feature-card', ['icon' => 'fa-solid fa-shield-halved', 'bgColor' => '#fef2f2', 'iconColor' => '#ef4444', 'title' => translate('home.feature3_title'), 'desc' => translate('home.feature3_desc')])
            @include('web.elements.feature-card', ['icon' => 'fa-solid fa-truck-fast', 'bgColor' => '#fff7ed', 'iconColor' => '#f97316', 'title' => translate('home.feature4_title'), 'desc' => translate('home.feature4_desc')])
        </div>

        <!-- 3. Slideshow Giới Thiệu Sản Phẩm Mới Nhất -->
        @include('web.elements.section-header', ['subtitle' => translate('home.suggest_expert'), 'title' => translate('home.new_featured_products')])

        <div class="product-slideshow-wrapper"
            style="margin-bottom: 4rem; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); background: #ffffff; border: 1px solid #f1f5f9;">
            <div class="orbit" role="region" aria-label="Newest Products" data-orbit
                data-options="animInFromRight:fade-in; animOutToLeft:fade-out; autoPlay:true; timerDelay:5000;">
                <div class="orbit-wrapper">
                    <div class="orbit-controls" style="z-index: 10;">
                        <button class="orbit-previous"
                            style="color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; width: 44px; height: 44px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.2s ease;"
                            onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'"><span
                                class="show-for-sr">Previous</span>&#9664;&#xFE0E;</button>
                        <button class="orbit-next"
                            style="color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; width: 44px; height: 44px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.2s ease;"
                            onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#64748b'"><span
                                class="show-for-sr">Next</span>&#9654;&#xFE0E;</button>
                    </div>
                    <ul class="orbit-container" style="min-height: 420px;">
                        @foreach ($featuredProducts->take(5) as $key => $product)
                            <li class="orbit-slide {{ $key == 0 ? 'is-active' : '' }}">
                                <div class="grid-x align-middle" style="padding: 3.5rem 3rem;">
                                    <div class="cell small-12 medium-6" style="padding-right: 2rem;">
                                        <span
                                            style="display: inline-block; background: #eff6ff; color: #2563eb; font-weight: 700; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.85rem; margin-bottom: 1.25rem;">{{ translate('home.recommended') }}</span>
                                        <h3
                                            style="font-size: 2.2rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem; line-height: 1.3;">
                                            {{ translate($product->name) }}
                                        </h3>

                                        <div class="product-rating" style="margin-bottom: 1rem; font-size: 1.2rem;">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <span
                                                style="color: #64748b; font-size: 0.9rem; font-weight: 600; margin-left: 0.5rem;">(4.9/5)</span>
                                        </div>

                                        <p style="color: #475569; font-size: 1.05rem; line-height: 1.7; margin-bottom: 1.5rem;">
                                            {!! translate($product->summary) ?? translate('home.default_product_summary', ['origin' => translate($product->made)]) !!}
                                        </p>
                                        <div style="font-size: 1.8rem; font-weight: 700; color: #ef4444; margin-bottom: 2rem;">
                                            {{ FormatCurrency(translate($product->price)) }}
                                        </div>
                                        <div style="display: flex; gap: 1rem;">
                                            <button type="button" onclick="add_to_cart({{ $product->id }}, 1)"
                                                style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; border: none; padding: 0.85rem 1.8rem; border-radius: 50px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);"
                                                onmouseover="this.style.transform='translateY(-2px)'"
                                                onmouseout="this.style.transform='none'">
                                                <i class="fa-solid fa-cart-plus"></i> {{ translate('home.add_to_cart') }}
                                            </button>
                                            <a href="{{ route('web_product_detail', $product->slug, true, app()->getLocale()) }}"
                                                style="background: #f1f5f9; display: inline-flex; align-items: center; gap: 0.5rem; color: #334155; padding: 0.85rem 1.8rem; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease;"
                                                onmouseover="this.style.background='#e2e8f0'"
                                                onmouseout="this.style.background='#f1f5f9'">
                                                <span>{{ translate('home.view_detail') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="cell small-12 medium-6" style="text-align: center;">
                                        <img src="{{ get_image_url($product->image) }}" alt="{{ translate($product->name) }}"
                                            style="max-height: 360px; object-fit: contain; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.1));">
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- 3.5 Featured Sales Products Section (Grid) -->
        <div id="featured-products">
            @include('web.elements.section-header', ['subtitle' => translate('home.comprehensive_care'), 'title' => translate('home.best_selling_products')])
        </div>

        <div class="products-grid">
            @if (!empty($featuredProducts) && count($featuredProducts) > 0)
                @foreach ($featuredProducts as $product)
                    @include('web.elements.product-card', ['product' => $product, 'showSummary' => true])
                @endforeach
            @endif
        </div>

        <!-- 4. Product Categories Showcase (Mua theo nhu cầu) -->
        @if(isset($productCategories) && count($productCategories) > 0)
            @include('web.elements.section-header', ['subtitle' => translate('home.just_for_you'), 'title' => translate('home.shop_by_health_need')])

            <div class="cat-grid">
                @foreach ($productCategories as $category)
                    @include('web.elements.category-card', ['category' => $category])
                @endforeach
            </div>
        @endif

        <!-- 5. Testimonials (Khách hàng nói gì?) -->
        <div style="margin-top: 2rem;">
            @include('web.elements.section-header', ['subtitle' => translate('home.real_feedback'), 'title' => translate('home.customer_testimonials')])
        </div>

        <div class="testimonials-grid">
            @include('web.elements.testimonial-card', [
                'initial' => 'A',
                'name' => translate('home.testi1_name'),
                'role' => translate('home.testi1_role'),
                'rating' => 5,
                'content' => translate('home.testi1_content')
            ])
            @include('web.elements.testimonial-card', [
                'initial' => 'B',
                'name' => translate('home.testi2_name'),
                'role' => translate('home.testi2_role'),
                'rating' => 5,
                'content' => translate('home.testi2_content')
            ])
            @include('web.elements.testimonial-card', [
                'initial' => 'M',
                'name' => translate('home.testi3_name'),
                'role' => translate('home.testi3_role'),
                'rating' => 4,
                'content' => translate('home.testi3_content')
            ])
        </div>

        <!-- 6. Info & Brand Mission Banner Section -->
        <div class="info-banner-section">
            <div class="info-banner-grid">
                <div>
                    <h2 class="info-banner-title">{{ translate('home.info_title') }}</h2>
                    <p class="info-banner-desc">{{ translate('home.info_desc') }}</p>
                    <ul class="info-list">
                        <li><i class="fa-solid fa-circle-check"></i> {{ translate('home.info_li1') }}</li>
                        <li><i class="fa-solid fa-circle-check"></i> {{ translate('home.info_li2') }}</li>
                        <li><i class="fa-solid fa-circle-check"></i> {{ translate('home.info_li3') }}</li>
                    </ul>
                    <a href="{{ route('web_home') }}#featured-products" class="hero-btn"
                        style="background: linear-gradient(135deg, #0ea5e9, #0284c7);">
                        <span>{{ translate('home.shop_now') }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
                <div style="text-align: center;">
                    <img src="/images/products/multivitamin_daily.png" alt="Supplement Health"
                        style="max-width: 100%; max-height: 320px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>

        <!-- 7. Blog Articles Section (Góc Chuyên Gia) -->
        @include('web.elements.section-header', ['subtitle' => translate('home.health_knowledge'), 'title' => translate('home.expert_corner')])

        <div class="blog-grid">
            @if (!empty($featuredArticle) && count($featuredArticle) > 0)
                @foreach ($featuredArticle as $article)
                    @include('web.elements.blog-card', ['article' => $article, 'categoryName' => translate('home.solution')])
                @endforeach
            @endif
        </div>

    </div>
@endsection