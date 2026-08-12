import { Head, Link, usePage } from "@inertiajs/react"
import { useState, useEffect } from "react"
import SiteLayout from "../Layouts/SiteLayout"

const PER_PAGE = 9

function Cards({ items, readMore, empty }) {
  if (!items.length) return <p className="lead">{empty}</p>
  return (
    <div className="grid grid--3" style={{ marginTop: 40 }}>
      {items.map((a) => (
        <article className="ncard rv" key={a.slug}>
          <div
            className="ncard__img"
            style={a.cover ? { backgroundImage: `url('${a.cover}')` } : {}}
          ></div>
          <div className="ncard__body">
            <span className="ncard__date">{a.date}</span>
            <h3 className="ncard__title">{a.title}</h3>
            <hr />
            <p className="ncard__excerpt">{a.excerpt}</p>
            <Link className="ncard__btn" href={a.url}>
              {readMore}
            </Link>
          </div>
        </article>
      ))}
    </div>
  )
}

// Windowed page list: always 1 and last, plus current ±1, with ellipses.
function pageList(total, current) {
  const out = []
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
      out.push(i)
    } else if (out[out.length - 1] !== "…") {
      out.push("…")
    }
  }
  return out
}

export default function News({
  page,
  investor,
  health,
  product,
  others,
  investorUnderDevelopment = false,
  showInvestorTab = false,
}) {
  const {
    url,
    props: { t, locale, homeUrl },
  } = usePage()
  const en = locale === "en"
  // A nav link may open a specific tab: /news?tab=product. Read from the
  // Inertia url (not window) so the server render picks the same tab.
  const [tab, setTab] = useState(() => {
    const asked = new URLSearchParams(url.split("?")[1] || "").get("tab")

    // "investor" is only reachable while its tab is switched on in the CMS.
    const allowed = showInvestorTab
      ? ["health", "product", "investor", "others"]
      : ["health", "product", "others"]

    return allowed.includes(asked) ? asked : "health"
  })
  const [pageNum, setPageNum] = useState(1)
  const [mobileOpen, setMobileOpen] = useState(false)

  const tabs = [
    {
      key: "health",
      label: en ? "Health Information" : "Informasi Kesehatan",
      items: health,
      desc: en
        ? "The latest health information and education to support a healthy lifestyle."
        : "Informasi dan edukasi kesehatan terkini untuk mendukung gaya hidup sehat.",
    },
    {
      key: "product",
      label: en ? "Product Info" : "Info Produk",
      items: product,
      desc: en
        ? "News and information about Combiphar products."
        : "Berita dan informasi seputar produk Combiphar.",
    },
    {
      key: "investor",
      label: en
        ? "Investor Update (under development)"
        : "Investor Update (dalam pengembangan)",
      items: investor,
      desc: en
        ? "The latest corporate updates and investor information from Combiphar."
        : "Pembaruan korporasi dan informasi investor terkini dari Combiphar.",
    },
    {
      key: "others",
      label: "Others",
      items: others,
      desc: en
        ? "Other news and information from Combiphar."
        : "Berita dan informasi lainnya dari Combiphar.",
    },
    // The CMS gate (Artikel list header action) drops the Investor Update tab
    // from the bar, the mobile dropdown, and the content switch all at once.
  ].filter((x) => x.key !== "investor" || showInvestorTab)

  const active = tabs.find((x) => x.key === tab) || tabs[0]
  const totalPages = Math.max(1, Math.ceil(active.items.length / PER_PAGE))
  const paged = active.items.slice((pageNum - 1) * PER_PAGE, pageNum * PER_PAGE)

  // Reveal-on-scroll + clamp page when tab/count changes.
  useEffect(() => {
    document.querySelectorAll(".rv").forEach((el) => el.classList.add("is-in"))
  }, [tab, pageNum])
  useEffect(() => {
    setPageNum(1)
  }, [tab])

  const readMore = en ? "Read More" : "Selengkapnya"
  const empty = en ? "No articles yet." : "Belum ada artikel."

  return (
    <>
      <Head title={page?.metaTitle || `${t.nav.news} — Combiphar`} />

      <section
        className="banner banner--about"
        style={
          page?.bannerImage
            ? {
                backgroundImage: `url('${page.bannerImage}')`,
                backgroundSize: "cover",
                backgroundPosition: "center",
              }
            : {}
        }
      >
        <div className="container">
          <h1 className="display">
            {page?.bannerTitle || (en ? "Latest News" : "Berita Terkini")}
          </h1>
        </div>
      </section>

      <nav className="subnav" aria-label="News categories">
        <div className="container subnav__inner">
          <div className="subnav__desktop">
            {tabs.map((x) => (
              <button
                key={x.key}
                type="button"
                className={tab === x.key ? "on" : ""}
                onClick={() => setTab(x.key)}
              >
                {x.label}
              </button>
            ))}
          </div>

          <div className={"subnav__mobile" + (mobileOpen ? " is-open" : "")}>
            <button
              type="button"
              className="subnav__trigger"
              aria-expanded={mobileOpen}
              aria-haspopup="menu"
              onClick={() => setMobileOpen((v) => !v)}
            >
              {tabs.find((x) => x.key === tab)?.label ?? "Categories"}
              <span className="subnav__caret">▾</span>
            </button>

            <div className="subnav__menu" role="menu">
              {tabs.map((x) => (
                <button
                  key={x.key}
                  type="button"
                  className={tab === x.key ? "on" : ""}
                  onClick={() => {
                    setTab(x.key)
                    setMobileOpen(false)
                  }}
                >
                  {x.label}
                </button>
              ))}
            </div>
          </div>
        </div>
      </nav>

      <section className="section">
        <div className="container">
          {tab === "investor" && investorUnderDevelopment ? (
            // Investor Update under development (CMS toggle on the Investor page)
            // — show the same "Segera Hadir" block as the Investor page (icon +
            // title + description) in place of the article grid.
            <div className="news-coming-soon">
              <div className="coming-soon-icon">
                <svg
                  width="36"
                  height="36"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
              </div>
              <h2>{en ? "Coming Soon" : "Segera Hadir"}</h2>
              <p>
                {en
                  ? "We are currently updating our Investor Relations portal to serve you better. Please check back soon!"
                  : "Kami sedang melakukan pembaruan pada portal Hubungan Investor untuk memberikan informasi yang lebih baik."}
              </p>
            </div>
          ) : (
            <>
              <div className="sec-head sec-head--product rv">
                <h2 className="display">{active.label}</h2>
                <p>{active.desc}</p>
              </div>

              <Cards items={paged} readMore={readMore} empty={empty} />

              {totalPages > 1 && (
                <div className="news-pager rv" aria-label="Pagination">
                  <span className="news-pager__label">
                    {en ? "Page:" : "Halaman:"}
                  </span>
                  {pageList(totalPages, pageNum).map((p, i) =>
                    p === "…" ? (
                      <span key={`gap-${i}`} className="news-pager__gap">
                        …
                      </span>
                    ) : (
                      <button
                        key={p}
                        type="button"
                        className={
                          "news-pager__num" + (pageNum === p ? " is-active" : "")
                        }
                        onClick={() => setPageNum(p)}
                        aria-current={pageNum === p ? "page" : undefined}
                      >
                        {p}
                      </button>
                    ),
                  )}
                </div>
              )}
            </>
          )}
        </div>
      </section>
    </>
  )
}

News.layout = (page) => <SiteLayout>{page}</SiteLayout>
