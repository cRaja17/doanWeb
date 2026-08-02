@if(isset($articles) && count($articles) > 0)
    @foreach($articles as $key => $article)
        <a href="{{ $article->url ?? (isset($article->slug) ? route('web_post_detail', $article->slug) : '#') }}" class="sidebar-hot-news__item {{ isset($numbered) && $numbered ? 'sidebar-hot-news__item--numbered' : '' }}">
            @if(isset($numbered) && $numbered)
                <div class="sidebar-hot-news__num">{{ $key + 1 }}</div>
            @endif
            
            <div class="sidebar-hot-news__img-wrapper">
                <img src="{{ get_image_url($article->image ?? ($article->thumb ?? '')) }}" alt="{{ translate($article->name ?? '') }}" class="sidebar-hot-news__img">
            </div>
            
            <div class="sidebar-hot-news__content">
                <h4 class="sidebar-hot-news__title">{{ translate($article->name ?? '') }}</h4>
                <div class="sidebar-hot-news__meta">
                    <i class="fa-regular fa-clock"></i> {{ isset($article->created_at) ? \Carbon\Carbon::parse($article->created_at)->format('d/m/Y') : '' }}
                </div>
            </div>
        </a>
    @endforeach
@endif
