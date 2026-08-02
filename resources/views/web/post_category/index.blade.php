@extends('web.layouts.index')

@php
    view()->share('title', translate($category->name ?? ''));
    view()->share('keywords', translate($category->name ?? ''));
    view()->share('description', strip_tags($category->description ?? ''));
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', isset($category->image) ? get_image_url($category->image) : asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('content')
    <div class="news-wrapper">
        <!-- 1. Breadcrumbs -->
        @include('web.elements.breadcrumb', ['items' => [
            ['label' => translate('post_category.news'), 'url' => '#'],
            ['label' => translate($category->name)]
        ]])

        <!-- 2. Category Nav Pills Bar (Horizontal Scroll) -->
        <div class="category-nav-bar">
            @foreach ($categories as $cat)
                <a href="{{ route('web_list_post', $cat->slug) }}" class="category-pill {{ $cat->slug == $category->slug ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper" style="font-size: 0.8rem;"></i>
                    <span>{{ translate($cat->name) }}</span>
                </a>
            @endforeach
        </div>

        <!-- 3. Featured / Hero Section -->
        @if (isset($featuredArticle) && count($featuredArticle) > 0)
            @php $mainHero = $featuredArticle->first(); @endphp
            <div class="hero-section">
                <!-- Main Feature -->
                <div class="main-hero-card">
                    <img src="{{ get_image_url($mainHero->image) }}" alt="{{ translate($mainHero->name) }}">
                    <div class="hero-overlay">
                        <span class="hero-cat-badge">{{ translate($category->name) }}</span>
                        <a href="{{ route('web_post_detail', $mainHero->slug) }}" class="hero-title">
                            {{ translate($mainHero->name) }}
                        </a>
                        <p class="hero-excerpt">{{ translate($mainHero->meta_description) }}</p>
                        <div class="hero-meta">
                            <span><i class="fa-regular fa-user"></i> {{ translate('post_category.editor') }}</span>
                            <span><i class="fa-regular fa-calendar"></i> {{ $mainHero->updated_at->format('d/m/Y') }}</span>
                            <span><i class="fa-regular fa-clock"></i> 5 {{ translate('post_category.read_time') }}</span>
                            <span><i class="fa-regular fa-eye"></i> {{ $mainHero->view }} {{ translate('post_category.views') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Sub-Features Stack -->
                <div class="sub-features-stack">
                    @foreach ($featuredArticle->skip(1)->take(3) as $subItem)
                        <a href="{{ route('web_post_detail', $subItem->slug) }}" class="sub-feature-card">
                            <img src="{{ get_image_url($subItem->image) }}" alt="{{ translate($subItem->name) }}" class="sub-feature-img">
                            <div>
                                <h4 class="sub-feature-title">{{ translate($subItem->name) }}</h4>
                                <div class="sub-feature-meta">
                                    <i class="fa-regular fa-calendar"></i> {{ $subItem->updated_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. Main Article Grid & Sidebar -->
        <div class="news-layout">
            <!-- Article Cards Grid -->
            <div>
                <h3 style="font-size: 1.35rem; font-weight: 700; color: #0f172a; margin-bottom: 1.5rem;">
                    {{ translate('post_category.newly_updated') }}
                </h3>

                <div class="article-cards-grid">
                    @foreach ($posts as $post)
                        @include('web.elements.article-card', ['article' => $post, 'categoryName' => translate($category->name)])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div style="display: flex; align-items: center; justify-content: center; margin-top: 2rem;">
                    {{ $posts->links('web.layouts.partials.pagination') }}
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Search Box -->
                <div class="sidebar-card">
                    <h4 class="sidebar-title">
                        <i class="fa-solid fa-magnifying-glass" style="color: #2563eb;"></i>
                        <span>{{ translate('post_category.search_article') }}</span>
                    </h4>
                    <input type="text" class="search-box-input" placeholder="{{ translate('post_category.search_placeholder') }}">
                </div>

                <!-- Hot News -->
                <div class="sidebar-card">
                    <h4 class="sidebar-title">
                        <i class="fa-solid fa-fire" style="color: #ef4444;"></i>
                        <span>{{ translate('post_category.most_viewed') }}</span>
                    </h4>
                    @if(isset($featuredArticle))
                        @include('web.elements.sidebar-hot-news', ['articles' => $featuredArticle->take(5), 'numbered' => true])
                    @endif
                </div>

                <!-- Newsletter Card -->
                @include('web.elements.newsletter-card', ['dark' => true])
            </div>
        </div>
    </div>
@endsection
