<div class="newsletter-card {{ isset($dark) && $dark ? 'newsletter-card--dark' : '' }}">
    <h3 class="newsletter-card__title">{{ translate('newsletter.title') }}</h3>
    <p class="newsletter-card__desc">{{ translate('newsletter.desc') }}</p>
    <form action="#" method="POST" style="position: relative; z-index: 1;">
        <input type="email" placeholder="{{ translate('newsletter.email_placeholder') }}" required style="width: 100%; padding: 0.85rem 1rem; border-radius: var(--radius-pill); border: none; outline: none; margin-bottom: 1rem; color: #1e293b;">
        <button type="submit" class="newsletter-card__btn">{{ translate('newsletter.subscribe_btn') }}</button>
    </form>
</div>
