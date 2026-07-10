<footer class="footer">
    <div class="container">
        <nav class="footer__links" aria-label="Footer">
            <a href="{{ route('about') }}">{{ __('site.nav.about') }}</a>
            <a href="{{ route('products') }}">{{ __('site.nav.products') }}</a>
            <a href="{{ route('csr') }}">{{ __('site.nav.csr') }}</a>
            <a href="{{ route('investor') }}">{{ __('site.nav.investor') }}</a>
            <a href="{{ route('news') }}">{{ __('site.nav.news') }}</a>
            <a href="{{ route('contact') }}">{{ __('site.nav.contact') }}</a>
            <a href="#">{{ __('site.terms') }}</a>
            <a href="#">{{ __('site.privacy') }}</a>
        </nav>
        <hr class="footer__divider">
        <div class="footer__bottom">
            <div class="footer__social">
                <span>{{ __('site.follow_us') }}</span>
                <a class="ic" href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.5c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2H8.1V14h2.4v7h3Z"/></svg></a>
                <a class="ic" href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg></a>
                <a class="ic" href="#" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.5.4 7.8.4 7.8.4s6.3 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8ZM10 15.2V8.8l5.5 3.2-5.5 3.2Z"/></svg></a>
                <a class="ic" href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.5 8.8H3.6V20h2.9V8.8ZM5 7.4a1.7 1.7 0 1 0 0-3.4 1.7 1.7 0 0 0 0 3.4ZM20.4 20v-5.9c0-3.2-1.7-4.7-4-4.7a3.4 3.4 0 0 0-3.1 1.7V8.8h-2.9V20h2.9v-5.9c0-1.5.7-2.4 2-2.4s1.9.9 1.9 2.4V20h3.2Z"/></svg></a>
            </div>
            <div class="footer__brand">
                <img src="{{ asset('img/logo-combiphar-white.svg') }}" alt="Combiphar">
                <img src="{{ asset('img/logo-combicare-white.svg') }}" alt="Combi Care Center">
            </div>
        </div>
        <p class="footer__copy">&copy; {{ date('Y') }} Combiphar, Inc. {{ __('site.rights') }}.</p>
    </div>
</footer>
