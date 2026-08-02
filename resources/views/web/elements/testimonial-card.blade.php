<div class="testimonial-card">
    <i class="fa-solid fa-quote-right testimonial-card__quote-icon"></i>
    <div class="testimonial-card__user">
        <div class="testimonial-card__avatar">{{ $initial }}</div>
        <div>
            <div class="testimonial-card__name">{{ $name }}</div>
            <div class="testimonial-card__role">{{ $role }}</div>
        </div>
    </div>
    <div class="testimonial-card__rating">
        @for($i = 0; $i < ($rating ?? 5); $i++)
            <i class="fa-solid fa-star"></i>
        @endfor
    </div>
    <p class="testimonial-card__content">"{{ $content }}"</p>
</div>
