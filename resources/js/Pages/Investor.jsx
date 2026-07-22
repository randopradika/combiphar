import { Head, usePage } from "@inertiajs/react"
import { useState, useEffect } from "react"
import SiteLayout from "../Layouts/SiteLayout"

const HubArrow = () => (
  <span className="arrow-btn arrow-btn--dark">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M13 6l6 6-6 6" />
    </svg>
  </span>
)

const DocIcon = () => (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
    <path d="M7 3h7l4 4v14H7z" />
    <path d="M14 3v5h5M9 13h7M9 17h7" />
  </svg>
)

function DocFileViewIcon() {
  return (
    <svg
      width="26"
      height="26"
      viewBox="0 0 26 26"
      fill="none"
      aria-hidden="true"
    >
      <path
        d="M25 10.6V16.6C25 22.6 22.6 25 16.6 25H9.4C3.4 25 1 22.6 1 16.6V9.4C1 3.4 3.4 1 9.4 1H15.4"
        stroke="#BD106F"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M25 10.5H20.25C16.6875 10.5 15.5 9.3125 15.5 5.75V1L25 10.5Z"
        stroke="#BD106F"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        opacity="0.4"
        d="M7 14H14"
        stroke="#292D32"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        opacity="0.4"
        d="M7 19H12"
        stroke="#292D32"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  )
}

function DocFileDownloadIcon() {
  return (
    <svg
      width="26"
      height="26"
      viewBox="0 0 26 26"
      fill="none"
      aria-hidden="true"
    >
      <path
        d="M25 10.6V16.6C25 22.6 22.6 25 16.6 25H9.4C3.4 25 1 22.6 1 16.6V9.4C1 3.4 3.4 1 9.4 1H15.4"
        stroke="#BD106F"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path
        d="M25 10.5H20.25C16.6875 10.5 15.5 9.3125 15.5 5.75V1L25 10.5Z"
        stroke="#BD106F"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <g opacity="0.4">
        <path
          d="M9.39844 11V18.2L11.7984 15.8"
          stroke="#292D32"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
        <path
          d="M9.4 18.1998L7 15.7998"
          stroke="#292D32"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </g>
    </svg>
  )
}

function ComingSoonOverlay({ en }) {
  return (
    <div className="coming-soon-overlay">
      <div className="coming-soon-modal">
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
    </div>
  )
}

function DocSection({ title, docs, en }) {
  if (!docs?.length) {
    return (
      <section className="section investor-panel">
        <div className="container">
          <div className="doc-head rv">
            <h2>{title}</h2>
          </div>

          <div className="doc-list doc-list--investor rv">
            <div className="doc-row doc-row--investor">
              <h3>
                {en
                  ? "No documents available yet."
                  : "Belum ada dokumen tersedia."}
              </h3>
              <div className="doc-row__actions">
                <span className="doc-act" style={{ opacity: 0.5 }}>
                  {en ? "File coming soon" : "Berkas segera hadir"}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>
    )
  }

  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>{title}</h2>
        </div>

        <div className="doc-list doc-list--investor rv">
          {docs.map((d, i) => (
            <div className="doc-row doc-row--investor" key={i}>
              <h3>{d.title || `${d.year ? `${d.year} ` : ""}${title}`}</h3>

              <div className="doc-row__actions">
                {d.fileId && (
                  <>
                    <a
                      className="doc-act"
                      href={d.fileId}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <DocFileViewIcon />
                      <span>View ID</span>
                    </a>

                    <a
                      className="doc-act"
                      href={d.fileId}
                      target="_blank"
                      rel="noopener noreferrer"
                      download
                    >
                      <DocFileDownloadIcon />
                      <span>Download ID</span>
                    </a>
                  </>
                )}

                {d.fileEn && (
                  <>
                    <a
                      className="doc-act"
                      href={d.fileEn}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      <DocFileViewIcon />
                      <span>View EN</span>
                    </a>

                    <a
                      className="doc-act"
                      href={d.fileEn}
                      target="_blank"
                      rel="noopener noreferrer"
                      download
                    >
                      <DocFileDownloadIcon />
                      <span>Download EN</span>
                    </a>
                  </>
                )}

                {!d.fileEn && !d.fileId && (
                  <span className="doc-act" style={{ opacity: 0.5 }}>
                    {en ? "File coming soon" : "Berkas segera hadir"}
                  </span>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}

function StockSection({ title }) {
  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>{title}</h2>
        </div>
        <article className="stock stock--flat rv">
          <p className="stock-note">Widget here.</p>
        </article>
      </div>
    </section>
  )
}

function ShareholderSection({ en, title }) {
  const shareholders = []

  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>{title}</h2>
        </div>

        <div className="doc-list rv">
          {shareholders.map((item, i) => (
            <div className="doc-row" key={i}>
              <h3>{item.name}</h3>
              <div className="doc-row__actions">
                <span className="doc-act">{item.portion}</span>
              </div>
            </div>
          ))}
        </div>

        <p
          className="rv"
          style={{
            marginTop: 18,
            color: "var(--text-muted)",
            fontSize: 14,
          }}
        >
          {en
            ? "Replace this sample shareholder structure with actual shareholder data."
            : "Ganti struktur pemegang saham contoh ini dengan data pemegang saham aktual."}
        </p>
      </div>
    </section>
  )
}

function ContactSection({ en, title }) {
  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>{title}</h2>
        </div>

        <div className="ir-contact-grid rv">
          <div className="ir-address">
            <h3>{en ? "Contact Us" : "Hubungi Kami"}</h3>
            <p>
              <b>Combiphar Head Office</b>
            </p>
            <p>
              Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta
              Pusat, DKI Jakarta 12190.
            </p>
            <p>
              <b>Phone:</b> 0800-1-800088 (Toll Free)
            </p>
          </div>

          <div className="ir-people">
            <h3>IR Contact</h3>

            <div className="ir-person">
              <div className="ir-avatar">IR</div>
              <div>
                <b>Investor Relations</b>
                <br />
                <a href="mailto:investor.relations@combiphar.com">
                  investor.relations@combiphar.com
                </a>
              </div>
            </div>

            <div className="ir-person">
              <div className="ir-avatar">CS</div>
              <div>
                <b>Corporate Secretary</b>
                <br />
                <a href="mailto:corpsec@combiphar.com">corpsec@combiphar.com</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}

/** Used when the CMS has no cards yet (fresh DB before the migration seed). */
const DEFAULT_HUB_CARDS = [
  { key: "stock", label: "Stock Information" },
  { key: "financial", label: "Financial Information" },
  { key: "annual", label: "Annual Report" },
  { key: "sustainability", label: "Sustainability Report" },
  { key: "presentation", label: "Company Presentation" },
  { key: "disclosures", label: "Information Disclosures" },
  { key: "shareholder", label: "Shareholder" },
  { key: "contact", label: "IR Contact" },
]

export default function Investor({
  page,
  hubCards = [],
  annual = [],
  sustainability = [],
  financial = [],
  disclosures = [],
  presentations = [],
}) {
  const {
    props: { t, locale, homeUrl },
  } = usePage()
  const [mobileOpen, setMobileOpen] = useState(false)

  const en = locale === "en"

  // One CMS-driven list feeds the hub cards, the sub-nav tabs and the section
  // headings, so a renamed card can never drift out of sync with its tab.
  const cards = hubCards.length ? hubCards : DEFAULT_HUB_CARDS

  const investorTabs = [
    { key: "hub", label: page?.bannerTitle || "Investor Relations" },
    ...cards,
  ]

  const titleFor = (key) =>
    cards.find((c) => c.key === key)?.label ||
    DEFAULT_HUB_CARDS.find((c) => c.key === key)?.label

  const [activeTab, setActiveTab] = useState("hub")

  useEffect(() => {
    const els = document.querySelectorAll(".rv")
    els.forEach((el) => el.classList.add("is-in"))
  }, [activeTab])

  return (
    <>
      <Head title={page?.metaTitle || "Investor Relations — Combiphar"} />

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
        <div className="container banner__row">
          <h1 className="display">
            {page?.bannerTitle || "Investor Relations"}
          </h1>
          <p className="banner__row-sub">{page?.bannerSubtitle || ""}</p>
        </div>
      </section>

      {/* Main Relative Container to Anchor the Overlay */}
      <div style={{ position: "relative", minHeight: "450px" }}>
        {/* Blurred Content */}
        <div className="content-blurred">

      <nav className="subnav" aria-label="Investor submenu">
        <div className="container subnav__inner">
          <div className="subnav__desktop">
            {investorTabs.map((item) => (
              <button
                key={item.key}
                type="button"
                className={activeTab === item.key ? "on" : ""}
                onClick={() => setActiveTab(item.key)}
              >
                {item.label}
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
              {investorTabs.find((item) => item.key === activeTab)?.label}
              <span className="subnav__caret">▾</span>
            </button>

            <div className="subnav__menu" role="menu">
              {investorTabs.map((item) => (
                <button
                  key={item.key}
                  type="button"
                  className={activeTab === item.key ? "on" : ""}
                  onClick={() => {
                    setActiveTab(item.key)
                    setMobileOpen(false)
                  }}
                >
                  {item.label}
                </button>
              ))}
            </div>
          </div>
        </div>
      </nav>

      
      {activeTab === "hub" && (
        <section className="section investor-hub">
          <div className="container">
            {/* <div className="investor-hub-grid rv">
              {cards.map((item) => (
                <button
                  key={item.key}
                  type="button"
                  className={"hub-card" + (item.image ? " hub-card--img" : "")}
                  style={
                    item.image
                      ? { backgroundImage: `url('${item.image}')` }
                      : undefined
                  }
                  onClick={() => setActiveTab(item.key)}
                >
                  <div className="hub-card__bottom">
                    <h3>{item.label}</h3>
                    <HubArrow />
                  </div>
                </button>
              ))}
            </div> */}
          </div>
        </section>
      )}

      {activeTab === "stock" && <StockSection title={titleFor("stock")} />}

      {activeTab === "financial" && (
        <DocSection title={titleFor("financial")} docs={financial} en={en} />
      )}

      {activeTab === "annual" && (
        <DocSection title={titleFor("annual")} docs={annual} en={en} />
      )}

      {activeTab === "sustainability" && (
        <DocSection
          title={titleFor("sustainability")}
          docs={sustainability}
          en={en}
        />
      )}

      {activeTab === "presentation" && (
        <DocSection
          title={titleFor("presentation")}
          docs={presentations}
          en={en}
        />
      )}

      {activeTab === "disclosures" && (
        <DocSection
          title={titleFor("disclosures")}
          docs={disclosures}
          en={en}
        />
      )}

      {activeTab === "shareholder" && (
        <ShareholderSection en={en} title={titleFor("shareholder")} />
      )}

      {activeTab === "contact" && (
        <ContactSection en={en} title={titleFor("contact")} />
      )}
      {/* Coming Soon Glassmorphism Overlay */}
      </div>
        <ComingSoonOverlay en={en} />
      </div>
    </>
  )
}

Investor.layout = (page) => <SiteLayout>{page}</SiteLayout>
