<a href="{{ $category->url ?? '#' }}" class="category-card">
    <img src="{{ get_image_url($category->image) }}" alt="{{ $category->name }}" class="category-card__img">
    <div class="category-card__overlay">
        <h3 class="category-card__title">{{ $category->name }}</h3>
    </div>
</a>