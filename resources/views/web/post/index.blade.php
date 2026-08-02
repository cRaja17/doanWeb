@extends('web.layouts.index')

@php
    view()->share('title', translate($post->name ?? ''));
    view()->share('keywords', translate($post->name ?? ''));
    view()->share('description', strip_tags($post->summary ?? ''));
    view()->share('robots', 'index, follow');
    view()->share('canonical', url()->current());
    view()->share('og_image', isset($post->image) ? get_image_url($post->image) : asset('images/default-seo.jpg'));
    view()->share('og_url', url()->current());
@endphp

@section('css')
    <style>
        /* Reading Progress Bar */
        #reading-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            width: 0%;
            z-index: 99999;
            transition: width 0.1s linear;
        }

        .article-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 4rem;
        }



        /* Layout Grid */
        .article-layout {
            display: grid;
            grid-template-columns: 80px 1fr 300px;
            gap: 2rem;
            align-items: start;
        }
        @media (max-width: 991px) {
            .article-layout {
                grid-template-columns: 1fr;
            }
            .social-share-sticky {
                display: none !important;
            }
        }

        /* Sticky Social Share Side Bar */
        .social-share-sticky {
            position: sticky;
            top: 100px;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            align-items: center;
        }
        .share-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        .share-btn:hover {
            transform: scale(1.1);
        }
        .share-fb { background: #1877f2; }
        .share-zalo { background: #0068ff; }
        .share-tw { background: #000000; }
        .share-copy { background: #475569; }

        /* Article Header Header */
        .article-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .article-cat-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            margin-bottom: 1rem;
        }
        .article-h1-title {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.3;
            color: #0f172a;
            margin-bottom: 1.25rem;
        }
        .article-lead {
            font-size: 1.15rem;
            font-weight: 600;
            line-height: 1.6;
            color: #475569;
            background: #f8fafc;
            padding: 1.25rem 1.5rem;
            border-left: 4px solid #2563eb;
            border-radius: 0 12px 12px 0;
            margin-bottom: 1.5rem;
        }

        /* Author Meta Bar */
        .author-meta-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.88rem;
            color: #64748b;
        }
        .author-info-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .author-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
        }
        .author-name {
            font-weight: 700;
            color: #0f172a;
        }

        /* Table of Contents (TOC) Box */
        .toc-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
        }
        .toc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.05rem;
            color: #0f172a;
            cursor: pointer;
        }
        .toc-list {
            margin-top: 1rem;
            padding-left: 1.25rem;
            line-height: 1.8;
            font-size: 0.92rem;
        }
        .toc-list a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .toc-list a:hover {
            text-decoration: underline;
        }

        /* Rich Article Content Body Typography */
        .article-body-content {
            font-size: 1.1rem;
            line-height: 1.85;
            color: #334155;
        }
        .article-body-content p {
            margin-bottom: 1.5rem;
        }
        .article-body-content h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
            margin: 2.25rem 0 1rem;
            scroll-margin-top: 100px;
        }
        .article-body-content h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin: 1.75rem 0 0.85rem;
            scroll-margin-top: 100px;
        }
        .article-body-content blockquote {
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 1.25rem 1.5rem;
            margin: 1.75rem 0;
            font-style: italic;
            color: #1e3a8a;
            border-radius: 0 12px 12px 0;
        }
        .article-body-content img {
            max-width: 100%;
            height: auto;
            border-radius: 14px;
            margin: 1.5rem 0 0.5rem;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        }

        /* Callout Box */
        .callout-box-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin: 1.75rem 0;
            color: #92400e;
        }

        /* Author Bio Footer Box */
        .author-bio-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 3rem 0;
            display: flex;
            gap: 1.25rem;
            align-items: center;
        }
        .author-bio-img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        /* Prev / Next Article Nav */
        .prev-next-nav {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .prev-next-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 14px;
            padding: 1.25rem;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .prev-next-card:hover {
            border-color: #2563eb;
            transform: translateY(-2px);
        }

        /* Comments Section */
        .comments-section {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 18px rgba(0,0,0,0.03);
            margin-bottom: 3rem;
        }
        .comment-input-box {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            outline: none;
            font-size: 0.92rem;
            margin-bottom: 1rem;
            min-height: 100px;
        }
        .comment-submit-btn {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-weight: 700;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }


    </style>
@endsection

@section('content')
    <!-- Top Scroll Reading Progress Bar -->
    <div id="reading-progress-bar"></div>

    <div class="article-wrapper">
        <!-- Breadcrumbs -->
        @include('web.elements.breadcrumb', ['items' => [
            ['label' => $firstCategory ? translate($firstCategory->name) : translate('post.news'), 'url' => $firstCategory ? route('web_list_post', $firstCategory->slug) : 'javascript:;'],
            ['label' => translate($post->name)]
        ]])

        <div class="article-layout">
            <!-- Left Sticky Social Share -->
            <div class="social-share-sticky">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="share-btn share-fb" title="{{ translate('post.share_fb') }}">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://zalo.me/share" target="_blank" class="share-btn share-zalo" title="{{ translate('post.share_zalo') }}">
                    <i class="fa-solid fa-comment-dots"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" target="_blank" class="share-btn share-tw" title="{{ translate('post.share_tw') }}">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
                <button type="button" class="share-btn share-copy" onclick="copyArticleLink()" title="{{ translate('post.copy_link') }}">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </div>

            <!-- Middle Article Main Body -->
            <div>
                <!-- Article Header -->
                <div class="article-header">
                    <span class="article-cat-badge">{{ $firstCategory ? translate($firstCategory->name) : translate('post.news') }}</span>
                    <h1 class="article-h1-title">{{ translate($post->name) }}</h1>

                    <div class="article-lead">
                        {{ translate($post->meta_description) }}
                    </div>

                    <div class="author-meta-bar">
                        <div class="author-info-left">
                            <img src="{{ asset('images/products/multivitamin_daily.png') }}" alt="Avatar" class="author-avatar">
                            <div>
                                <div class="author-name">{{ translate('post.editor') }}</div>
                                <div style="font-size: 0.78rem; color: #94a3b8;">{{ translate('post.editor_desc') }}</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span><i class="fa-regular fa-calendar"></i> {{ $post->updated_at->format('d/m/Y') }}</span>
                            <span><i class="fa-regular fa-clock"></i> {{ max(1, ceil(str_word_count(strip_tags(translate($post->description))) / 250)) }} {{ translate('post.read_time') }}</span>
                            <span><i class="fa-regular fa-eye"></i> {{ $post->view }} {{ translate('post.views') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Table of Contents (TOC) -->
                <div class="toc-box" style="display: none;" id="toc-container">
                    <div class="toc-header" onclick="$('#toc-content-list').slideToggle()">
                        <span><i class="fa-solid fa-list-ol" style="color: #2563eb; margin-right: 0.5rem;"></i> {{ translate('post.toc') }}</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <ol class="toc-list" id="toc-content-list">
                    </ol>
                </div>

                <!-- Main Content Body -->
                <div class="article-body-content" id="article-body">
                    {!! translate($post->description) !!}
                </div>

                <!-- Author Bio Footer Box -->
                <div class="author-bio-card">
                    <img src="{{ asset('images/products/multivitamin_daily.png') }}" alt="Author" class="author-bio-img">
                    <div>
                        <h4 style="font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.3rem;">{{ translate('post.editor') }}</h4>
                        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 0.6rem;">{{ translate('post.editor_bio') }}</p>
                        <a href="{{ route('web_news_all') }}" style="color: #2563eb; font-weight: 700; font-size: 0.85rem; text-decoration: none;">{{ translate('post.view_all') }} &rarr;</a>
                    </div>
                </div>

                <!-- Prev / Next Articles Nav -->
                <div class="prev-next-nav">
                    @if ($prevPost)
                        <a href="{{ route('web_post_detail', $prevPost->slug) }}" class="prev-next-card">
                            <span style="font-size: 0.78rem; color: #94a3b8; font-weight: 700;">&larr; {{ translate('post.prev_post') }}</span>
                            <div style="font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-top: 0.3rem;">{{ translate($prevPost->name) }}</div>
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if ($nextPost)
                        <a href="{{ route('web_post_detail', $nextPost->slug) }}" class="prev-next-card" style="text-align: right;">
                            <span style="font-size: 0.78rem; color: #94a3b8; font-weight: 700;">{{ translate('post.next_post') }} &rarr;</span>
                            <div style="font-weight: 700; font-size: 0.92rem; color: #0f172a; margin-top: 0.3rem;">{{ translate($nextPost->name) }}</div>
                        </a>
                    @else
                        <div></div>
                    @endif
                </div>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h3 style="font-weight: 700; font-size: 1.25rem; color: #0f172a; margin-bottom: 1.25rem;">
                        <i class="fa-regular fa-comments" style="color: #2563eb;"></i> {{ translate('post.discussion') }}
                    </h3>
                    <textarea class="comment-input-box" placeholder="{{ translate('post.comment_placeholder') }}"></textarea>
                    <button type="button" class="comment-submit-btn">{{ translate('post.submit_comment') }}</button>
                </div>
            </div>

            <!-- Right Sidebar Container -->
            <div class="sidebar-container">
                <!-- Featured Articles Widget -->
                <div class="sidebar-card">
                    <h4 class="sidebar-title">
                        <i class="fa-solid fa-fire-flame-curved" style="color: #ef4444;"></i>
                        <span>{{ translate('post.featured_posts') }}</span>
                    </h4>
                    @if (isset($featuredArticle))
                        @include('web.elements.sidebar-hot-news', ['articles' => $featuredArticle->take(5)])
                    @endif
                </div>

                <!-- Newsletter Card -->
                @include('web.elements.newsletter-card', ['dark' => false])
            </div>
        </div>

        <!-- Bottom Related Articles Section -->
        @if (isset($relatedPosts) && count($relatedPosts) > 0)
            <div style="margin-top: 4rem; padding-top: 2rem; border-top: 2px solid #f1f5f9;">
                <h3 style="font-weight: 700; font-size: 1.5rem; color: #0f172a; margin-bottom: 1.5rem; text-align: center;">
                    {{ translate('post.related_posts') }}
                </h3>
                <div class="related-posts-grid">
                    @foreach ($relatedPosts->take(4) as $rel)
                        @include('web.elements.article-card', ['article' => $rel])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@section('js')
    <script>
        // Reading Progress Bar Indicator Script
        window.onscroll = function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = (winScroll / height) * 100;
            document.getElementById("reading-progress-bar").style.width = scrolled + "%";
        };

        function copyArticleLink() {
            navigator.clipboard.writeText(window.location.href);
            alert("{{ translate('post.copied_msg') }}");
        }
        // Auto Generate Table of Contents (TOC)
        document.addEventListener('DOMContentLoaded', function() {
            const articleBody = document.getElementById('article-body');
            const headings = articleBody.querySelectorAll('h2, h3');
            const tocContainer = document.getElementById('toc-container');
            const tocList = document.getElementById('toc-content-list');

            if (headings.length > 0) {
                tocContainer.style.display = 'block';
                headings.forEach(function(heading, index) {
                    // Assign ID to heading if it doesn't have one
                    if (!heading.id) {
                        heading.id = 'heading-' + index;
                    }

                    // Create TOC link
                    const li = document.createElement('li');
                    if (heading.tagName.toLowerCase() === 'h3') {
                        li.style.marginLeft = '1.5rem';
                        li.style.listStyleType = 'circle';
                    }

                    const a = document.createElement('a');
                    a.href = '#' + heading.id;
                    a.textContent = heading.textContent;
                    
                    li.appendChild(a);
                    tocList.appendChild(li);
                });
            }
        });
    </script>
@endsection
