import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function useReveal(url) {
    useEffect(() => {
        const els = document.querySelectorAll('.rv:not(.is-in)');
        if (!('IntersectionObserver' in window)) {
            els.forEach((el) => el.classList.add('is-in'));
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.12 });
        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, [url]);
}

export default function SiteLayout({ children, navMode = 'solid' }) {
    const { props, url } = usePage();
    const { t, nav, homeUrl, altUrls, locale, routeName } = props;
    const [scrolled, setScrolled] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    useReveal(url);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 40);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        document.body.style.overflow = menuOpen ? 'hidden' : '';
    }, [menuOpen]);

    const menu = ['about', 'products', 'csr', 'investor', 'news', 'contact'];
    const navClass = 'nav' + (navMode === 'overlay' ? ' nav--overlay' : '') + (scrolled ? ' nav--scrolled' : '');

    return (
        <>
            <a className="skip-link" href="#main">Skip to content</a>
            <header className={navClass} id="nav">
                <div className="container nav__inner">
                    <Link className="nav__logo" href={homeUrl} aria-label="Combiphar">
                        <img className="logo-color" src="/img/logo-header.svg" alt="Combiphar" />
                    </Link>
                    <nav className="nav__menu" aria-label="Main menu">
                        {menu.map((s) => (
                            <Link key={s} href={nav[s]} className={routeName === s ? 'active' : ''}>{t.nav[s]}</Link>
                        ))}
                    </nav>
                    <div className="nav__tools">
                        <span className="nav__lang" aria-label="Language">
                            <a href={altUrls.id} className={locale === 'id' ? 'active' : ''}>ID</a>
                            <span className="sep"></span>
                            <a href={altUrls.en} className={locale === 'en' ? 'active' : ''}>EN</a>
                        </span>
                        <button className="nav__search" aria-label={t.search}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                            <span>{t.search}</span>
                        </button>
                        <button className="nav__burger" aria-label={t.menu} aria-expanded={menuOpen} onClick={() => setMenuOpen(true)}>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                        </button>
                    </div>
                </div>
            </header>

            <div className={'mobilemenu' + (menuOpen ? ' open' : '')}>
                <div className="mobilemenu__top">
                    <img src="/img/logo-combiphar-white.svg" alt="Combiphar" />
                    <button className="mobilemenu__close" aria-label={t.close} onClick={() => setMenuOpen(false)}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <nav aria-label="Mobile menu">
                    {menu.map((s) => (
                        <Link key={s} href={nav[s]} onClick={() => setMenuOpen(false)}>{t.nav[s]}</Link>
                    ))}
                </nav>
                <div className="mobilemenu__lang">
                    <a href={altUrls.id}>ID</a><span className="sep"></span><a href={altUrls.en}>EN</a>
                </div>
            </div>

            <main id="main">{children}</main>

            <footer className="footer">
                <div className="container">
                    <nav className="footer__links" aria-label="Footer">
                        {menu.map((s) => <Link key={s} href={nav[s]}>{t.nav[s]}</Link>)}
                        <a href="#">{t.terms}</a>
                        <a href="#">{t.privacy}</a>
                    </nav>
                    <hr className="footer__divider" />
                    <div className="footer__bottom">
                        <div className="footer__social">
                            <span>{t.follow_us}</span>
                            <a className="ic" href="#" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.5c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2H8.1V14h2.4v7h3Z"/></svg></a>
                            <a className="ic" href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg></a>
                            <a className="ic" href="#" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8c1.5.4 7.8.4 7.8.4s6.3 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8ZM10 15.2V8.8l5.5 3.2-5.5 3.2Z"/></svg></a>
                            <a className="ic" href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.5 8.8H3.6V20h2.9V8.8ZM5 7.4a1.7 1.7 0 1 0 0-3.4 1.7 1.7 0 0 0 0 3.4ZM20.4 20v-5.9c0-3.2-1.7-4.7-4-4.7a3.4 3.4 0 0 0-3.1 1.7V8.8h-2.9V20h2.9v-5.9c0-1.5.7-2.4 2-2.4s1.9.9 1.9 2.4V20h3.2Z"/></svg></a>
                        </div>
                        <div className="footer__brand">
                            <img src="/img/logo-combiphar-white.svg" alt="Combiphar" />
                            <img src="/img/logo-combicare-white.svg" alt="Combi Care Center" />
                        </div>
                    </div>
                    <p className="footer__copy">&copy; {new Date().getFullYear()} Combiphar, Inc. {t.rights}.</p>
                </div>
            </footer>
        </>
    );
}