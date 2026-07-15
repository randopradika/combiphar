import { Link, usePage } from "@inertiajs/react"
import { useEffect, useState } from "react"

function useReveal(url) {
  useEffect(() => {
    const reveal = (el) => el.classList.add("is-in")
    const els = Array.from(document.querySelectorAll(".rv:not(.is-in)"))
    if (!els.length) return
    if (!("IntersectionObserver" in window)) {
      els.forEach(reveal)
      return
    }
    // threshold:0 fires as soon as any part enters — height-independent, so tall
    // sections (which happen on narrow/mobile viewports) reveal reliably.
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            reveal(e.target)
            io.unobserve(e.target)
          }
        })
      },
      { rootMargin: "0px 0px -10% 0px", threshold: 0 },
    )
    els.forEach((el) => io.observe(el))
    // Safety net: reveal anything already on-screen the observer didn't catch.
    const t = setTimeout(() => {
      els.forEach((el) => {
        if (el.classList.contains("is-in")) return
        const r = el.getBoundingClientRect()
        if (r.top < window.innerHeight && r.bottom > 0) {
          reveal(el)
          io.unobserve(el)
        }
      })
    }, 600)
    return () => {
      io.disconnect()
      clearTimeout(t)
    }
  }, [url])
}

export default function SiteLayout({ children, navMode = "solid" }) {
  const { props, url } = usePage()
  const { t, nav, homeUrl, altUrls, locale, routeName, footer } = props
  const [scrolled, setScrolled] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)
  const isHome = url === "/" + locale

  useReveal(url)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40)
    onScroll()
    window.addEventListener("scroll", onScroll, { passive: true })
    return () => window.removeEventListener("scroll", onScroll)
  }, [])

  useEffect(() => {
    document.body.style.overflow = menuOpen ? "hidden" : ""
  }, [menuOpen])

  const menu = ["about", "products", "csr", "investor", "news", "contact"]
  const navClass =
    "nav" +
    (navMode === "overlay" ? " nav--overlay" : "") +
    (scrolled ? " nav--scrolled" : "") +
    (isHome ? " nav--home" : "")

  return (
    <>
      <a className="skip-link" href="#main">
        Skip to content
      </a>
      <header className={navClass} id="nav">
        <div className="container nav__inner">
          <Link className="nav__logo" href={homeUrl} aria-label="Combiphar">
            <img
              className="logo-color"
              src="/img/logo-header.svg"
              alt="Combiphar"
            />
          </Link>
          <nav className="nav__menu" aria-label="Main menu">
            {menu.map((s) => (
              <Link
                key={s}
                href={nav[s]}
                className={routeName === s ? "active" : ""}
              >
                {t.nav[s]}
              </Link>
            ))}
          </nav>
          <div className="nav__tools">
            <span className="nav__lang" aria-label="Language">
              <svg
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M2.125 6.75C1.57272 6.75 1.125 7.19772 1.125 7.75C1.125 8.30229 1.57272 8.75 2.125 8.75V7.75V6.75ZM17.875 8.75C18.4273 8.75 18.875 8.30229 18.875 7.75C18.875 7.19772 18.4273 6.75 17.875 6.75V7.75V8.75ZM2.21948 11.8124C1.66719 11.8124 1.21948 12.2601 1.21948 12.8124C1.21948 13.3647 1.66719 13.8124 2.21948 13.8124V12.8124V11.8124ZM17.9695 13.8124C18.5218 13.8124 18.9695 13.3647 18.9695 12.8124C18.9695 12.2601 18.5218 11.8124 17.9695 11.8124V12.8124V13.8124ZM19 10H18C18 14.4183 14.4183 18 10 18V19V20C15.5228 20 20 15.5228 20 10H19ZM10 19V18C5.58172 18 2 14.4183 2 10H1H0C0 15.5228 4.47715 20 10 20V19ZM1 10H2C2 5.58172 5.58172 2 10 2V1V0C4.47715 0 0 4.47715 0 10H1ZM10 1V2C14.4183 2 18 5.58172 18 10H19H20C20 4.47715 15.5228 0 10 0V1ZM10 19V18C9.78721 18 9.50566 17.9056 9.16241 17.5738C8.81434 17.2373 8.45521 16.7021 8.13192 15.9631C7.48665 14.4882 7.0625 12.3807 7.0625 10H6.0625H5.0625C5.0625 12.5898 5.51979 14.9823 6.29961 16.7648C6.68887 17.6545 7.1782 18.4373 7.77228 19.0117C8.37118 19.5907 9.12548 20 10 20V19ZM6.0625 10H7.0625C7.0625 7.61928 7.48665 5.51177 8.13192 4.03686C8.45521 3.29792 8.81434 2.76272 9.16241 2.42621C9.50566 2.09436 9.78721 2 10 2V1V0C9.12548 0 8.37118 0.409315 7.77228 0.988315C7.1782 1.56266 6.68887 2.34548 6.29961 3.23522C5.51979 5.01767 5.0625 7.41015 5.0625 10H6.0625ZM10 19V20C10.8745 20 11.6288 19.5907 12.2277 19.0117C12.8218 18.4373 13.3111 17.6545 13.7004 16.7648C14.4802 14.9823 14.9375 12.5898 14.9375 10H13.9375H12.9375C12.9375 12.3807 12.5133 14.4882 11.8681 15.9631C11.5448 16.7021 11.1857 17.2373 10.8376 17.5738C10.4943 17.9056 10.2128 18 10 18V19ZM13.9375 10H14.9375C14.9375 7.41015 14.4802 5.01767 13.7004 3.23522C13.3111 2.34548 12.8218 1.56266 12.2277 0.988315C11.6288 0.409315 10.8745 0 10 0V1V2C10.2128 2 10.4943 2.09436 10.8376 2.42621C11.1857 2.76272 11.5448 3.29792 11.8681 4.03686C12.5133 5.51177 12.9375 7.61928 12.9375 10H13.9375ZM2.125 7.75V8.75H17.875V7.75V6.75H2.125V7.75ZM2.21948 12.8124V13.8124H17.9695V12.8124V11.8124H2.21948V12.8124Z"
                  fill="#C92B82"
                />
              </svg>
              <a href={altUrls.id} className={locale === "id" ? "active" : ""}>
                ID
              </a>
              <span className="sep"></span>
              <a href={altUrls.en} className={locale === "en" ? "active" : ""}>
                EN
              </a>
            </span>
            <button className="nav__search" aria-label={t.search}>
              <svg
                width="19"
                height="19"
                viewBox="0 0 19 19"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M14.3269 14.44L17.8 17.8M8.8 4.6C10.7882 4.6 12.4 6.21177 12.4 8.2M16.68 8.84C16.68 13.1699 13.1699 16.68 8.84 16.68C4.51009 16.68 1 13.1699 1 8.84C1 4.51009 4.51009 1 8.84 1C13.1699 1 16.68 4.51009 16.68 8.84Z"
                  stroke="#C92B82"
                  stroke-width="2"
                  stroke-linecap="round"
                />
              </svg>

              <span>{t.search}</span>
            </button>
            <button
              className="nav__burger"
              aria-label={t.menu}
              aria-expanded={menuOpen}
              onClick={() => setMenuOpen((v) => !v)}
            >
              {menuOpen ? (
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                >
                  <path d="M6 6l12 12M18 6L6 18" />
                </svg>
              ) : (
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                >
                  <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </header>

      <div className={"mobilemenu" + (menuOpen ? " open" : "")}>
        <div className="mobilemenu__panel">
          <nav aria-label="Mobile menu">
            {menu.map((s) => (
              <Link
                key={s}
                href={nav[s]}
                className={routeName === s ? "active" : ""}
                onClick={() => setMenuOpen(false)}
              >
                {t.nav[s]}
              </Link>
            ))}
          </nav>

          <div className="mobilemenu__lang" aria-label="Language switcher">
            <div className="mobilemenu__langlinks">
              <a
                href={altUrls.id}
                lang="id"
                hrefLang="id"
                className={locale === "id" ? "active" : ""}
                aria-current={locale === "id" ? "true" : undefined}
                onClick={() => setMenuOpen(false)}
              >
                ID
              </a>
              <a
                href={altUrls.en}
                lang="en"
                hrefLang="en"
                className={locale === "en" ? "active" : ""}
                aria-current={locale === "en" ? "true" : undefined}
                onClick={() => setMenuOpen(false)}
              >
                EN
              </a>
            </div>
          </div>
          <div className="mobilemenu__foot">
            <div className="mobilemenu__brand">
              <img src="/img/logo-combiphar-white.svg" alt="Combiphar" />
              <img
                src="/img/logo-combicare-white.svg"
                alt="Combi Care Center"
              />
            </div>
            <hr className="mobilemenu__divider" />
            <div className="footer__social">
              <span>{t.follow_us}</span>
              <div className="footer__icons">
                {(footer?.socials ?? []).map((s, i) => (
                  <a
                    key={i}
                    className="ic"
                    href={s.url || "#"}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={s.name}
                  >
                    {s.icon && <img src={s.icon} alt={s.name || ""} />}
                  </a>
                ))}
              </div>
            </div>
            <hr className="mobilemenu__divider" />
            <p className="mobilemenu__copy">
              {footer?.copyright || (
                <>
                  All Rights Reserved to <strong>Combiphar</strong>
                </>
              )}
            </p>
          </div>
        </div>
      </div>

      <main id="main">{children}</main>

      <footer className="footer">
        <div className="container">
          <nav className="footer__links" aria-label="Footer">
            {menu.map((s) => (
              <Link key={s} href={nav[s]}>
                {t.nav[s]}
              </Link>
            ))}
            <a href="#">{t.terms}</a>
            <a href="#">{t.privacy}</a>
          </nav>
        </div>
        <hr className="footer__divider" />
        <div className="container">
          <div className="footer__bottom">
            <div className="footer__social">
              <span>{t.follow_us}</span>
              <div className="footer__icons">
                {(footer?.socials ?? []).map((s, i) => (
                  <a
                    key={i}
                    className="ic"
                    href={s.url || "#"}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={s.name}
                  >
                    {s.icon && <img src={s.icon} alt={s.name || ""} />}
                  </a>
                ))}
              </div>
            </div>
            <p className="footer__rights">
              {footer?.copyright || (
                <>
                  All Rights Reserved to <strong>Combiphar</strong>
                </>
              )}
            </p>
            <div className="footer__brand">
              <img src="/img/logo-combiphar-white.svg" alt="Combiphar" />
              <img
                src="/img/logo-combicare-white.svg"
                alt="Combi Care Center"
              />
            </div>
          </div>
        </div>
      </footer>
    </>
  )
}
