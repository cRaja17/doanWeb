<footer class="footer">
    <div class="footer__container">
        <div class="footer__col">
            <h4 class="footer__title">{{ translate('footer.help') }}</h4>
            <ul class="footer__list">
                <li class="footer__list-item"><a href="https://www.unicityscience.org" target="_blank" class="footer__link">{{ translate('footer.certify') }} Unicity Science</a></li>
                @foreach ($information_global as $item)
                    @if ($item->view_footer == 1)
                        <li class="footer__list-item"><a href="{{ route('web.information', [$item->slug]) }}" class="footer__link">{{ translate($item->name) }}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="footer__col">
            <h4 class="footer__title">{{ translate('footer.products') }}</h4>
            <ul class="footer__list">
                @foreach ($postCategories as $item)
                    <li class="footer__list-item"><a href="{{ route('web_list_product', [$item->slug]) }}" class="footer__link">{{ translate($item->name) }}</a></li>
                @endforeach
            </ul>
        </div>
        <div class="footer__col footer__col--social">
            <h4 class="footer__title">{{ translate('footer.follow') }}</h4>
            <div class="footer__social-links">
                <a href="#" class="footer__social-link"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="footer__social-link"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" class="footer__social-link"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="footer__social-link"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="#" class="footer__social-link"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>
    </div>
    <div class="footer__bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ translate('footer.rights') }}</p>
    </div>
</footer>
