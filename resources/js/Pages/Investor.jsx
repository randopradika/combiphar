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

function StockSection() {
  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>Stock Information</h2>
        </div>

        <article className="stock stock--flat rv">
          <div className="stock-meta">PT Pyridam Farma Tbk / IDX</div>

          <div className="ir-price">
            <div className="ticker">PYFA</div>
            <div className="exchange">Indonesia Stock Exchange</div>
            <div className="change">-1.24%</div>
          </div>

          <div className="stock-grid">
            <div className="stock-cell">
              <div className="k">Last Price</div>
              <div className="v">Rp 710</div>
            </div>
            <div className="stock-cell">
              <div className="k">Open</div>
              <div className="v">Rp 720</div>
            </div>
            <div className="stock-cell">
              <div className="k">High</div>
              <div className="v">Rp 735</div>
            </div>
            <div className="stock-cell">
              <div className="k">Low</div>
              <div className="v down">Rp 705</div>
            </div>
          </div>

          <div className="stock-chart" aria-hidden="true">
            <svg viewBox="0 0 1000 220" preserveAspectRatio="none">
              <defs>
                <linearGradient id="stockFill" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="rgba(214,46,135,.24)" />
                  <stop offset="100%" stopColor="rgba(214,46,135,0)" />
                </linearGradient>
              </defs>
              <path
                d="M0,160 C70,150 110,120 170,126 C240,133 275,90 340,94 C400,98 460,136 520,130 C585,123 635,80 700,86 C770,92 820,146 885,136 C930,130 965,112 1000,116 L1000,220 L0,220 Z"
                fill="url(#stockFill)"
              />
              <path
                d="M0,160 C70,150 110,120 170,126 C240,133 275,90 340,94 C400,98 460,136 520,130 C585,123 635,80 700,86 C770,92 820,146 885,136 C930,130 965,112 1000,116"
                fill="none"
                stroke="currentColor"
                strokeWidth="4"
                style={{ color: "var(--magenta)" }}
              />
            </svg>
          </div>

          <p className="stock-note">
            Stock data above is placeholder content for layout preview.
          </p>
        </article>
      </div>
    </section>
  )
}

function ShareholderSection({ en }) {
  const shareholders = [
    { name: "PT Example Investama", portion: "35.20%" },
    { name: "Public", portion: "28.45%" },
    { name: "Founding Shareholders", portion: "21.10%" },
    { name: "Institutional Investors", portion: "15.25%" },
  ]

  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>Shareholder</h2>
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

function ContactSection({ en }) {
  return (
    <section className="section investor-panel">
      <div className="container">
        <div className="doc-head rv">
          <h2>Investor Relations Contact</h2>
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

export default function Investor({
  page,
  annual = [],
  sustainability = [],
  financial = [],
  disclosures = [],
  presentations = [],
}) {
  const {
    props: { t, locale, homeUrl },
  } = usePage()

  const en = locale === "en"

  const investorTabs = [
    { key: "stock", label: "Stock Information" },
    { key: "financial", label: "Financial Information" },
    { key: "annual", label: "Annual Report" },
    { key: "sustainability", label: "Sustainability Report" },
    { key: "presentation", label: "Company Presentation" },
    { key: "disclosures", label: "Information Disclosures" },
    { key: "shareholder", label: "Shareholder" },
    { key: "contact", label: "IR Contact" },
  ]

  const hubCards = [
    { key: "stock", label: "Stock Information" },
    { key: "financial", label: "Financial Information" },
    { key: "annual", label: "Annual Report" },
    { key: "sustainability", label: "Sustainability Report" },
    { key: "presentation", label: "Company Presentation" },
    { key: "disclosures", label: "Information Disclosures" },
    { key: "shareholder", label: "Shareholder" },
    { key: "contact", label: "IR Contact" },
  ]

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
        <div className="container">
          <span className="banner__crumb">
            <a href={homeUrl}>Home</a> &rsaquo; {t.nav.investor}
          </span>
          <h1 className="display">Investor Relations</h1>
        </div>
      </section>

      <nav className="subnav" aria-label="Investor submenu">
        <div className="container subnav__inner">
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
      </nav>

      {activeTab === "hub" && (
        <section className="section investor-hub">
          <div className="container">
            <div className="investor-hub-grid rv">
              {hubCards.map((item) => (
                <button
                  key={item.key}
                  type="button"
                  className="hub-card"
                  onClick={() => setActiveTab(item.key)}
                >
                  <div className="hub-card__bottom">
                    <h3>{item.label}</h3>
                    <HubArrow />
                  </div>
                </button>
              ))}
            </div>
          </div>
        </section>
      )}

      {activeTab === "stock" && <StockSection />}

      {activeTab === "financial" && (
        <DocSection title="Financial Information" docs={financial} en={en} />
      )}

      {activeTab === "annual" && (
        <DocSection title="Annual Reports" docs={annual} en={en} />
      )}

      {activeTab === "sustainability" && (
        <DocSection
          title="Sustainability Report"
          docs={sustainability}
          en={en}
        />
      )}

      {activeTab === "presentation" && (
        <DocSection title="Company Presentation" docs={presentations} en={en} />
      )}

      {activeTab === "disclosures" && (
        <DocSection
          title="Information Disclosures"
          docs={disclosures}
          en={en}
        />
      )}

      {activeTab === "shareholder" && <ShareholderSection en={en} />}

      {activeTab === "contact" && <ContactSection en={en} />}
    </>
  )
}

Investor.layout = (page) => <SiteLayout>{page}</SiteLayout>
