<div class="feature-card">
    <div class="feature-card__icon-wrapper" style="background: {{ $bgColor ?? '#eff6ff' }}; color: {{ $iconColor ?? 'var(--color-primary)' }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div>
        <h4 class="feature-card__title">{{ $title }}</h4>
        <p class="feature-card__desc">{{ $desc }}</p>
    </div>
</div>
