<div class="blog-card">
    <div class="blog-card__img-wrapper">
        <img src="{{ get_image_url($article->image) }}" alt="{{ translate($article->name ?? '') }}" class="blog-card__img">
        @if(isset($categoryName))
            <span class="blog-card__badge">{{ $categoryName }}</span>
        @endif
    </div>
    <div class="blog-card__body">
        <a href="{{ $article->url ?? (isset($article->slug) ? route('web_post_detail', $article->slug) : '#') }}" class="blog-card__title">{{ translate($article->name ?? '') }}</a>
        <p class="blog-card__desc">{!! translate($article->summary ?? '') !!}</p>
        <a href="{{ $article->url ?? (isset($article->slug) ? route('web_post_detail', $article->slug) : '#') }}" class="blog-card__read-more">{{ translate('blog.view_detail') }} <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i></a>
    </div>
</div>
