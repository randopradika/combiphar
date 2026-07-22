import { Head, usePage } from "@inertiajs/react"
import { useMemo, useState } from "react"
import SiteLayout from "../Layouts/SiteLayout"
import Modal from "../components/Modal"
import BoardGrid from "../components/BoardGrid"
import { MilestoneSlider } from "../components/Sliders"

const Arrow = () => (
  <svg viewBox="0 0 78 78" fill="none" stroke="currentColor" strokeWidth="1.6">
    <path d="M74 4 4 74" />
    <path d="M4 44v30h30" />
  </svg>
)

// Brand colours for the marketplace tiles (Figma 577:2954).
const SHOP_COLORS = {
  shopee: "#F1592D",
  blibli: "#1B91D0",
  tokopedia: "#84C468",
  lazada: "#0C0F84",
  tiktok: "#000000",
  orami: "#FF5556",
}
const shopColor = (name = "") => {
  const key = Object.keys(SHOP_COLORS).find((k) =>
    name.toLowerCase().includes(k),
  )
  return key ? SHOP_COLORS[key] : "#5B2D8E"
}

export default function About({
  page,
  milestones,
  commissioners,
  directors,
  auditCommittee = [],
  corporateSecretary = [],
  awards,
  shops,
  facilities,
  accreditations,
  offices,
}) {
  const {
    props: { t, locale, homeUrl },
  } = usePage()
  const en = locale === "en"
  const [board, setBoard] = useState(null)
  const [facOpen, setFacOpen] = useState(false)
  const [city, setCity] = useState("Jabodetabek");
  const [cat, setCat] = useState("Branch");

  const cities = useMemo(
    () => [...new Set(offices.map((o) => o.city).filter(Boolean))],
    [offices],
  )
  const cats = useMemo(
    () => [...new Set(offices.map((o) => o.category).filter(Boolean))],
    [offices],
  )

  // Pencapaian & Penghargaan splits in two: hero awards render as logos on the
  // page (fall back to all awards when none are flagged, so logos never vanish),
  // the rest open in a "detail" popup grouped by year (Figma 946:44).
  const [awardsOpen, setAwardsOpen] = useState(false)
  const heroAwards = useMemo(() => {
    const hero = awards.filter((a) => a.is_hero)
    // Hero row shows at most 7 logos; falls back to all awards when none flagged.
    return (hero.length ? hero : awards).slice(0, 7)
  }, [awards])
  // The "detail" popup always lists every award grouped by year (regardless of
  // which are hero), so the "Click here for details" link is always available.
  const awardYears = useMemo(
    () =>
      [...new Set(awards.map((a) => a.year).filter(Boolean))].sort((a, b) => b - a),
    [awards],
  )
  const [awardYear, setAwardYear] = useState(() =>
    awardYears.length ? String(awardYears[0]) : "all",
  )
  const shownAwards = useMemo(
    () =>
      awardYear === "all"
        ? awards
        : awards.filter((a) => String(a.year) === awardYear),
    [awards, awardYear],
  )
  const visibleOffices = offices.filter(
    (o) => (!city || o.city === city) && (!cat || o.category === cat),
  )

  const pins = [
    {
      l: "15.6%",
      tp: "50%",
      icon: "/img/pin-northamerica.svg",
      label: "North America",
    },
    { l: "48%", tp: "52%", icon: "/img/pin-africa.svg", label: "Africa" },
    { l: "76.5%", tp: "48%", icon: "/img/pin-asia.svg", label: "Asia" },
    { l: "81%", tp: "62%", icon: "/img/pin-asia.svg", label: "Indonesia" },
    { l: "92%", tp: "70%", icon: "/img/pin-australia.svg", label: "Australia" },
  ]

  return (
    <div className="page-about">
      <Head title={page?.metaTitle || `${t.nav.about} — Combiphar`} />

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
          <p className="banner__intro">
            {page?.bannerSubtitle ||
              (en
                ? "With over 53 years of professional experience in the pharmaceutical industry, marked by sustainable growth and the acquisition of major companies, Combiphar represents the purpose of:"
                : "Dengan pengalaman lebih dari 53 tahun di industri farmasi, ditandai dengan pertumbuhan berkelanjutan dan akuisisi berbagai perusahaan besar, Combiphar mewakili tujuan:")}
          </p>
          <h1 className="banner__title display">
            <span className="banner__title-l1">
              {page?.bannerTitle || "Championing a"}
            </span>
            <span className="banner__title-l2">
              {page?.bannerTitle2 || "Healthy Tomorrow"}
            </span>
          </h1>
        </div>
      </section>

      <section className="section">
        <div className="container">
          <p className="about-intro rv">
            {page?.intro ||
              (en
                ? "Since 1971, Combiphar has been part of people’s everyday lives through a wide range of health products, faithfully accompanying the journey to a healthier life through integrated health solutions — from prevention and recovery to a healthy lifestyle. Rooted in genuine care and enduring trust, we continue to grow together with Indonesia."
                : "Sejak 1971, Combiphar telah menjadi bagian dari kehidupan sehari-hari masyarakat melalui ragam produk kesehatan dan setia menemani perjalanan hidup sehat, melalui solusi kesehatan terintegrasi mulai dari pencegahan, pemulihan, hingga gaya hidup sehat. Berakar pada kepedulian yang tulus dan kepercayaan yang tak lekang oleh waktu, kami terus bertumbuh bersama Indonesia.")}
          </p>
        </div>
      </section>

      {milestones.length > 0 && (
        <section className="section section--history">
          <div className="container journey-head rv">
            <span className="eyebrow eyebrow--magenta">{t.nav.about}</span>
            <h2 className="display">
              {en ? "Our History & Purpose" : "Sejarah & Tujuan Kami"}
            </h2>
          </div>
          <MilestoneSlider items={milestones} variant="card" />
        </section>
      )}

      {(page?.vision || page?.mission || page?.values) && (
        <section className="section">
          <div className="container">
            <div className="vmv-list">
              {page?.vision && (
                <div className="vmv-row rv">
                  <h3>{en ? "Our Vision" : "Visi Kami"}</h3>
                  <span className="arrow">
                    <svg
                      width="78"
                      height="78"
                      viewBox="0 0 78 78"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M12.5058 77L77 77L76.9991 11.5753M77 77L1 1"
                        stroke="#CC2A7F"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                  <p>{page.vision}</p>
                </div>
              )}
              {page?.mission && (
                <div className="vmv-row rv">
                  <h3>{en ? "Our Mission" : "Misi Kami"}</h3>
                  <span className="arrow">
                    <svg
                      width="78"
                      height="78"
                      viewBox="0 0 78 78"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M12.5058 77L77 77L76.9991 11.5753M77 77L1 1"
                        stroke="#CC2A7F"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                  <p>{page.mission}</p>
                </div>
              )}
              {page?.values && (
                <div className="vmv-row rv">
                  <h3>{en ? "Our Values" : "Nilai Kami"}</h3>
                  <span className="arrow">
                    <svg
                      width="78"
                      height="78"
                      viewBox="0 0 78 78"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M12.5058 77L77 77L76.9991 11.5753M77 77L1 1"
                        stroke="#CC2A7F"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                  <p dangerouslySetInnerHTML={{ __html: page.values }} />
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {(commissioners.length > 0 ||
        directors.length > 0 ||
        auditCommittee.length > 0 ||
        corporateSecretary.length > 0) && (
        <section className="section">
          <div className="container">
            {commissioners.length > 0 && (
              <>
                <div className="sec-head sec-head--center rv">
                  <span className="eyebrow eyebrow--magenta">
                    {en ? "Leadership" : "Kepemimpinan"}
                  </span>
                  <h2 className="display">Board of Commissioners</h2>
                </div>
                <BoardGrid people={commissioners} onOpen={setBoard} />
              </>
            )}
            {directors.length > 0 && (
              <>
                <div
                  className="sec-head sec-head--center rv"
                  style={{ marginTop: "clamp(48px,5vw,80px)" }}
                >
                  <h2 className="display">Board of Directors</h2>
                </div>
                <BoardGrid people={directors} onOpen={setBoard} />
              </>
            )}
            {/* Audit Committee + Corporate Secretary are hidden on the About page
                by request. To show them again, remove the comment markers around
                the block below. (They still appear on the CSR Komite Audit tab.) */}
            {/*
            {auditCommittee.length > 0 && (
              <>
                <div
                  className="sec-head sec-head--center rv"
                  style={{ marginTop: "clamp(48px,5vw,80px)" }}
                >
                  <h2 className="display">
                    {en ? "Audit Committee" : "Komite Audit"}
                  </h2>
                </div>
                <BoardGrid people={auditCommittee} onOpen={setBoard} />
              </>
            )}
            {corporateSecretary.length > 0 && (
              <>
                <div
                  className="sec-head sec-head--center rv"
                  style={{ marginTop: "clamp(48px,5vw,80px)" }}
                >
                  <h2 className="display">Corporate Secretary</h2>
                </div>
                <BoardGrid people={corporateSecretary} onOpen={setBoard} />
              </>
            )}
            */}
          </div>
        </section>
      )}

      {awards.length > 0 && (
        <section className="section">
          <div className="container">
            <div className="sec-head sec-head--center rv">
              <span className="eyebrow eyebrow--magenta">
                {en ? "Achievements" : "Pencapaian"}
              </span>
              <h2 className="display">
                {en ? "Achievements & Awards" : "Pencapaian & Penghargaan"}
              </h2>
              <p>
                {en
                  ? "Our commitment to quality and sustainability is recognised through national and international awards."
                  : "Komitmen kami terhadap kualitas dan keberlanjutan diakui melalui berbagai penghargaan nasional maupun internasional."}
              </p>
              <button
                type="button"
                className="awards-detail-link"
                onClick={() => setAwardsOpen(true)}
              >
                {en ? "Click here for details" : "Klik di sini untuk detail"}
              </button>
            </div>
            <div className="awards rv" style={{ marginTop: 44 }}>
              {heroAwards.map((a, i) => (
                <div className="award-tile" key={i}>
                  {a.image ? (
                    <img src={a.image} alt={a.title} />
                  ) : (
                    <span>
                      {a.title}
                      {a.year && (
                        <>
                          <br />
                          <b>{a.year}</b>
                        </>
                      )}
                    </span>
                  )}
                </div>
              ))}
            </div>
            {(page?.stat1Value || page?.stat2Value) && (
              <div className="stat-cards rv">
                {page?.stat1Value && (
                  <div className="stat-card">
                    <div className="num">{page.stat1Value}</div>
                    <p>{page.stat1Label}</p>
                  </div>
                )}
                {page?.stat2Value && (
                  <div className="stat-card">
                    <div className="num">{page.stat2Value}</div>
                    <p>{page.stat2Label}</p>
                    {facilities.length > 0 && (
                      <button
                        type="button"
                        className="presence-popup-link stat-card__link"
                        onClick={() => setFacOpen(true)}
                      >
                        {en ? "View Facilities" : "Lihat Fasilitas"}
                      </button>
                    )}
                  </div>
                )}
              </div>
            )}
          </div>
        </section>
      )}

      <Modal
        open={awardsOpen}
        onClose={() => setAwardsOpen(false)}
        closeLabel={t.close}
        wide
      >
        <div className="awards-modal">
          <h3 className="awards-modal__title display">
            {en ? "Achievements & Awards" : "Pencapaian & Penghargaan"}
          </h3>
          {awardYears.length > 0 && (
            <div className="awards-modal__filter">
              <span className="awards-modal__filter-label">
                {en ? "Year:" : "Tahun:"}
              </span>
              <span className="selectbox">
                <select
                  value={awardYear}
                  onChange={(e) => setAwardYear(e.target.value)}
                  aria-label={en ? "Filter by year" : "Saring berdasarkan tahun"}
                >
                  {awardYears.map((y) => (
                    <option key={y} value={String(y)}>
                      {y}
                    </option>
                  ))}
                  <option value="all">
                    {en ? "All Years" : "Semua Tahun"}
                  </option>
                </select>
              </span>
            </div>
          )}
          <div className="awards-modal__grid">
            {shownAwards.map((a, i) => (
              <figure className="awards-modal__tile" key={i}>
                <div className="awards-modal__img">
                  {a.image ? (
                    <img src={a.image} alt={a.title} />
                  ) : (
                    <span>{a.title}</span>
                  )}
                </div>
                <figcaption>{a.title}</figcaption>
              </figure>
            ))}
          </div>
          {shownAwards.length === 0 && (
            <p className="awards-modal__empty">
              {en
                ? "No achievements for this year."
                : "Tidak ada pencapaian untuk tahun ini."}
            </p>
          )}
        </div>
      </Modal>

      <section className="section" style={{ background: "var(--surface)" }}>
        <div className="container">
          <div className="sec-head sec-head--center rv">
            <span className="eyebrow eyebrow--magenta">
              {en ? "Our Scale" : "Skala Kami"}
            </span>
            <h2 className="display">
              {en ? "Our Presence" : "Kehadiran Kami"}
            </h2>
            <p>
              {page?.presenceDesc ||
                (en
                  ? "From Indonesia to the world — our products and partnerships now reach communities across four continents."
                  : "Setiap penghargaan adalah cerminan kepercayaan, dari konsumen, investor, dan mitra yang telah menemani perjalanan kami sejak 2016.")}
            </p>
          </div>
          <div className="world-map rv">
            <img
              className="world-map__img"
              src={page?.presenceImage || "/img/world-map.png"}
              alt={
                en
                  ? "Combiphar global presence map"
                  : "Peta kehadiran global Combiphar"
              }
            />
            {pins.map((p, i) => (
              <span
                key={i}
                className="map-pin"
                style={{ left: p.l, top: p.tp }}
                aria-hidden="true"
              >
                <img src={p.icon} alt="" />
              </span>
            ))}
          </div>
          <div className="map-legend rv">
            <span className="map-legend__item">
              <img src="/img/pin-northamerica.svg" alt="" />
              <span>
                <b>North America:</b> Mexico
              </span>
            </span>
            <span className="map-legend__item">
              <img src="/img/pin-africa.svg" alt="" />
              <span>
                <b>Africa:</b> Togo, Nigeria
              </span>
            </span>
            <span className="map-legend__item">
              <img src="/img/pin-asia.svg" alt="" />
              <span>
                <b>Asia:</b> Indonesia, Myanmar, Singapore, Taiwan, Malaysia,
                Philipines, Cambodia, Brunei Darussalam
              </span>
            </span>
            <span className="map-legend__item">
              <img src="/img/pin-australia.svg" alt="" />
              <span>
                <b>Australia:</b> Australia, New Zealand
              </span>
            </span>
          </div>
        </div>
      </section>

      {offices.length > 0 && (
        <section className="section">
          <div className="container">
            <div className="loc-head rv">
              <h3
                className="display"
                style={{
                  fontSize: "clamp(24px,2.4vw,34px)",
                  color: "var(--purple-800)",
                }}
              >
                {en ? "Our Locations" : "Lokasi Kami"}
              </h3>
              <div className="loc-filters">
                <span className="selectbox">
                  <select
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    aria-label="Location"
                  >
                    <option value="">
                      {en ? "Location: All" : "Lokasi: Semua"}
                    </option>
                    {cities.map((c) => (
                      <option key={c} value={c}>
                        {c}
                      </option>
                    ))}
                  </select>
                </span>
                <span className="selectbox">
                  <select
                    value={cat}
                    onChange={(e) => setCat(e.target.value)}
                    aria-label="Category"
                  >
                    <option value="">
                      {en ? "Category: All" : "Kategori: Semua"}
                    </option>
                    {cats.map((c) => (
                      <option key={c} value={c}>
                        {c}
                      </option>
                    ))}
                  </select>
                </span>
              </div>
            </div>

            <div
              className="grid grid--4 grid--offices"
              style={{ marginTop: 24 }}
            >
              {visibleOffices.map((o, i) => (
                <div className="office-card" key={i}>
                  <h4>{o.name}</h4>
                  {o.category && (
                    <span className="office-card__label">{o.category}</span>
                  )}
                  <p>
                    {o.description}
                    {o.phone?.trim() && (
                      <>
                        <br />
                        <br />
                        <strong>Phone:</strong> {o.phone}
                      </>
                    )}
                  </p>
                </div>
              ))}
            </div>

            {visibleOffices.length === 0 && (
              <p className="toolbar-empty">
                {en
                  ? "No offices match your filter."
                  : "Tidak ada kantor yang cocok."}
              </p>
            )}
          </div>
        </section>
      )}

      <section className="section">
        <div className="container">
          <div className="about-feature rv">
            <div className="about-feature__body">
              <span className="eyebrow">ABOUT US</span>
              <h2 className="display">
                {page?.manufacturingTitle || "Manufacturing Solutions"}
              </h2>
              <p>
                {page?.manufacturingBody ||
                  (en
                    ? "Through Manufacturing Solutions, we provide end-to-end manufacturing services for partners who need reliable, high-quality, and scalable production support. Backed by a strong quality system, regulatory expertise, technical capabilities, and integrated manufacturing infrastructure, we help bring products to market efficiently and dependably."
                    : "Melalui Manufacturing Solutions, kami menghadirkan layanan manufaktur end-to-end bagi para mitra yang membutuhkan dukungan produksi yang andal, berkualitas, dan scalable. Didukung oleh sistem kualitas yang kuat, keahlian regulatori, kapabilitas teknis, serta infrastruktur manufaktur yang terintegrasi, kami membantu menghadirkan produk ke pasar secara efisien dan terpercaya.")}
              </p>
            </div>
            <div className="about-feature__media">
              <img
                src={
                  page?.manufacturingImage ||
                  "/storage/seed/about/manufacturing.jpg"
                }
                alt="Manufacturing Solutions"
                loading="lazy"
              />
            </div>
          </div>
          <div className="about-feature about-feature--flip rv">
            <div className="about-feature__body">
              <span className="eyebrow">ABOUT US</span>
              <h2 className="display">
                {page?.internationalTitle || "International Business"}
              </h2>
              <p>
                {page?.internationalBody ||
                  (en
                    ? "With a growing international footprint, we are expanding our reach into global markets through regulatory capabilities, distribution networks, and deep market understanding. This international presence reflects our commitment to delivering quality health solutions while strengthening the Group’s credibility and growth on a global scale."
                    : "Dengan jejak internasional yang terus berkembang, kami memperluas jangkauan bisnis ke berbagai pasar global melalui kapabilitas regulatori, jaringan distribusi, dan pemahaman pasar yang kuat. Kehadiran internasional ini mencerminkan komitmen kami dalam menghadirkan berbagai solusi kesehatan berkualitas sekaligus memperkuat kredibilitas dan pertumbuhan Grup di tingkat global.")}
              </p>
            </div>
            <div className="about-feature__media">
              <img
                src={
                  page?.internationalImage ||
                  "/storage/seed/about/international.jpg"
                }
                alt="International Business"
                loading="lazy"
              />
            </div>
          </div>
        </div>
      </section>

      {shops.length > 0 && (
        <section className="section">
          <div className="container">
            <div className="sec-head sec-head--center rv">
              <span className="eyebrow eyebrow--magenta">
                {en ? "Official Store" : "Belanja Resmi"}
              </span>
              <h2 className="display">
                {en ? "Our Official Online Stores" : "Toko Online Resmi Kami"}
              </h2>
              <p>
                {en
                  ? "Original Combiphar & Combicare products on your favourite marketplaces."
                  : "Produk original Combiphar & Combicare di marketplace favorit Anda."}
              </p>
            </div>
            <div className="market market--tiles rv">
              {shops.map((s, i) => (
                <a
                  key={i}
                  href={s.url || "#"}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label={s.name}
                  style={{ background: shopColor(s.name) }}
                >
                  {s.logo ? <img src={s.logo} alt={s.name} /> : s.name}
                </a>
              ))}
            </div>
          </div>
        </section>
      )}

      <Modal
        open={!!board}
        onClose={() => setBoard(null)}
        flush
        closeLabel={t.close}
      >
        {board && (
          <div className="bmodal">
            <div
              className="bmodal__img"
              style={
                board.photo ? { backgroundImage: `url('${board.photo}')` } : {}
              }
            ></div>
            <div className="bmodal__body">
              <h3 className="bmodal__name">{board.name}</h3>
              {board.role && <p className="bmodal__role">{board.role}</p>}
              <div
                className="bmodal__bio"
                dangerouslySetInnerHTML={{ __html: board.bio || "" }}
              />
            </div>
          </div>
        )}
      </Modal>

      <Modal
        open={facOpen}
        onClose={() => setFacOpen(false)}
        wide
        closeLabel={t.close}
      >
        <div className="fac-modal">
          <h3 className="fac-modal__title">
            {en
              ? "Internationally Standardized Production Facility"
              : "Fasilitas Produksi Berstandar Internasional"}
          </h3>
          <p className="fac-modal__sub">
            {en ? (
              <>
                Our manufacturing{" "}
                <b>facilities serve local &amp; global markets</b> including
                contract manufacturing
              </>
            ) : (
              <>
                Fasilitas kami <b>melayani pasar lokal &amp; global</b> termasuk
                contract manufacturing
              </>
            )}
          </p>
          {facilities.length > 0 && (
            <div className="fac-grid">
              {facilities.map((f, i) => (
                <div className="fac-card" key={i}>
                  <div
                    className="fac-card__img"
                    style={
                      f.image ? { backgroundImage: `url('${f.image}')` } : {}
                    }
                  ></div>
                  <h4>
                    {f.name}
                    {f.region && <>, {f.region}</>}
                  </h4>
                  <p className="plant">
                    {f.plants}
                    {f.area && <small> - {f.area}</small>}
                  </p>
                  {f.category && <p className="cat">{f.category}</p>}
                  {f.detail && <p className="dose">{f.detail}</p>}
                </div>
              ))}
            </div>
          )}
          {accreditations.length > 0 && (
            <>
              <h4 className="accr-title">
                {en ? "Various Accreditation" : "Berbagai Akreditasi"}
              </h4>
              <div className="accr-grid">
                {accreditations.map((a, i) => (
                  <div key={i}>
                    <b>{a.name}</b>
                    {a.issuer && <span>{a.issuer}</span>}
                  </div>
                ))}
              </div>
            </>
          )}
        </div>
      </Modal>
    </div>
  )
}

About.layout = (page) => <SiteLayout>{page}</SiteLayout>
