<div class="article-card">
    <div class="article-card__thumb-wrapper">
        <img src="{{ get_image_url($article->image ?? ($article->thumb ?? '')) }}" alt="{{ translate($article->name ?? '') }}" class="article-card__img">
        @if(isset($categoryName))
            <span class="article-card__badge">{{ $categoryName }}</span>
        @endif
    </div>
    <div class="article-card__body">
        <a href="{{ $article->url ?? (isset($article->slug) ? route('web_post_detail', $article->slug) : '#') }}" class="article-card__title">{{ translate($article->name ?? '') }}</a>
        <p class="article-card__excerpt">{{ translate($article->meta_description ?? $article->summary ?? '') }}</p>
        <div class="article-card__footer">
            <span><i class="fa-regular fa-clock" style="margin-right: 0.3rem;"></i> {{ isset($article->created_at) ? \Carbon\Carbon::parse($article->created_at)->format('d/m/Y') : '' }}</span>
            <a href="{{ $article->url ?? (isset($article->slug) ? route('web_post_detail', $article->slug) : '#') }}" style="color: var(--color-primary); font-weight: 700; text-decoration: none;">{{ translate('article.read_more') }} <i class="fa-solid fa-arrow-right" style="margin-left: 0.2rem; font-size: 0.8rem;"></i></a>
        </div>
    </div>
</div>
