import { Head, usePage } from "@inertiajs/react"
import { useMemo, useState, useEffect } from "react"
import SiteLayout from "../Layouts/SiteLayout"
import Modal from "../components/Modal"

export default function Products({ page, categories, socialIcons = {} }) {
  const {
    props: { t, locale, homeUrl },
    url,
  } = usePage()

  const en = locale === "en"
  const [active, setActive] = useState(0)
  const [subFilter, setSubFilter] = useState("all")
  const [query, setQuery] = useState("")
  const [sort, setSort] = useState("az")
  const [detail, setDetail] = useState(null)
  const [pageNo, setPageNo] = useState(1)
  const [mobileOpen, setMobileOpen] = useState(false)

  const perPage = 8

  useEffect(() => {
    const els = document.querySelectorAll(".rv")
    els.forEach((el) => el.classList.add("is-in"))
  }, [active, subFilter, query, sort, pageNo])

  // Deep-link: /products?product={slug} (e.g. from global search) opens that product's modal.
  const allProducts = useMemo(
    () =>
      categories.flatMap((c) => [
        ...(c.products ?? []).map((p) => ({ ...p, cat: c.name })),
        ...(c.children ?? []).flatMap((s) =>
          (s.products ?? []).map((p) => ({ ...p, cat: c.name })),
        ),
      ]),
    [categories],
  )

  useEffect(() => {
    const slug = new URLSearchParams(url.split("?")[1] || "").get("product")
    if (!slug) return
    const found = allProducts.find((p) => p.slug === slug)
    if (found) setDetail(found)
  }, [url, allProducts])

  const cat = categories[active]
  const subs = cat?.children ?? []
  const activeSub = subFilter !== "all" ? subs[Number(subFilter)] : null
  const heading = activeSub ? activeSub.name : cat?.name

  // Default: every product in the category (its own + all sub-categories).
  // A sub-category filter narrows to just that sub-category's products.
  const source = useMemo(() => {
    if (!cat) return []
    if (activeSub) return activeSub.products ?? []
    return [...(cat.products ?? []), ...subs.flatMap((s) => s.products ?? [])]
  }, [cat, subs, activeSub])

  const visible = useMemo(() => {
    const q = query.toLowerCase().trim()
    const list = source.filter((p) => p.name.toLowerCase().includes(q))
    list.sort((a, b) => a.name.localeCompare(b.name))
    if (sort === "za") list.reverse()
    return list
  }, [source, query, sort])

  const totalPages = Math.max(1, Math.ceil(visible.length / perPage))

  const pagedItems = useMemo(() => {
    const start = (pageNo - 1) * perPage
    return visible.slice(start, start + perPage)
  }, [visible, pageNo])

  useEffect(() => {
    setSubFilter("all")
  }, [active])

  useEffect(() => {
    setPageNo(1)
  }, [active, subFilter, query, sort])

  useEffect(() => {
    if (pageNo > totalPages) setPageNo(totalPages)
  }, [pageNo, totalPages])

  return (
    <>
      <Head title={page?.metaTitle || `${t.nav.products} — Combiphar`} />

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
          <h1 className="display">{page?.bannerTitle || t.nav.products}</h1>
          {page?.bannerSubtitle && (
            <p className="banner__row-sub">{page.bannerSubtitle}</p>
          )}
        </div>
      </section>

      {categories.length > 0 && (
        <nav className="subnav" aria-label="Product categories">
          <div className="container subnav__inner">
            <div className="subnav__desktop">
              {categories.map((c, i) => (
                <button
                  key={c.slug}
                  type="button"
                  className={i === active ? "on" : ""}
                  onClick={() => {
                    setActive(i)
                    setQuery("")
                    setPageNo(1)
                  }}
                >
                  {c.name}
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
                {categories[active]?.name ?? "Categories"}
                <span className="subnav__caret">▾</span>
              </button>

              <div className="subnav__menu" role="menu">
                {categories.map((c, i) => (
                  <button
                    key={c.slug}
                    type="button"
                    className={i === active ? "on" : ""}
                    onClick={() => {
                      setActive(i)
                      setQuery("")
                      setPageNo(1)
                      setMobileOpen(false)
                    }}
                  >
                    {c.name}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </nav>
      )}

      {cat && (
        <>
          <section
            className="cat-hero"
            style={
              cat.image
                ? {
                    backgroundImage: `url('${cat.image}')`,
                    backgroundSize: "cover",
                    backgroundPosition: "center",
                  }
                : {}
            }
          >
            <div className="cat-hero__overlay"></div>
            <h2 className="cat-hero__title display">{cat.name}</h2>
          </section>

          <section className="section">
            <div className="container">
              {cat.description && (
                <p className="cat-intro rv">{cat.description}</p>
              )}

              <div className="sec-head rv">
                <h2 className="display">{heading}</h2>
              </div>

              <div className="toolbar toolbar--products rv">
                {subs.length > 0 && (
                  <div className="toolbar__sort toolbar__category">
                    <span className="toolbar__label">
                      {en ? "Category:" : "Kategori:"}
                    </span>
                    <span className="selectbox selectbox--products">
                      <select
                        value={subFilter}
                        onChange={(e) => {
                          setSubFilter(e.target.value)
                          setQuery("")
                          setPageNo(1)
                        }}
                        aria-label={en ? "Sub-category" : "Sub-kategori"}
                      >
                        <option value="all">
                          {en ? "All Categories" : "Semua Kategori"}
                        </option>
                        {subs.map((s, i) => (
                          <option key={s.slug} value={i}>
                            {s.name}
                          </option>
                        ))}
                      </select>
                    </span>
                  </div>
                )}
                <div className="toolbar__sort">
                  <span className="toolbar__label">
                    {en ? "Sort by:" : "Urutkan:"}
                  </span>
                  <span className="selectbox selectbox--products">
                    <select
                      value={sort}
                      onChange={(e) => setSort(e.target.value)}
                      aria-label={en ? "Sort" : "Urutkan"}
                    >
                      <option value="az">{en ? "A - Z" : "A - Z"}</option>
                      <option value="za">{en ? "Z - A" : "Z - A"}</option>
                    </select>
                  </span>
                </div>

                <label className="searchbox searchbox--products">
                  <input
                    type="search"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder={en ? "Search products ..." : "Cari produk ..."}
                  />
                  <span className="searchbox__btn" aria-hidden="true">
                    <svg
                      width="23"
                      height="23"
                      viewBox="0 0 23 23"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M17.6586 17.8L22 22M10.75 5.5C13.2353 5.5 15.25 7.51472 15.25 10M20.6 10.8C20.6 16.2124 16.2124 20.6 10.8 20.6C5.38761 20.6 1 16.2124 1 10.8C1 5.38761 5.38761 1 10.8 1C16.2124 1 20.6 5.38761 20.6 10.8Z"
                        stroke="#F8F8F8"
                        strokeWidth="2"
                        strokeLinecap="round"
                      />
                    </svg>
                  </span>
                </label>
              </div>

              <div className="grid grid--4" style={{ marginTop: 80 }}>
                {pagedItems.map((p, i) => (
                  <article
                    className="pcard rv"
                    key={i}
                    onClick={() => setDetail({ ...p, cat: cat.name })}
                  >
                    <div className="pcard__body">
                      <h3>{p.name}</h3>
                      {p.summary && <p className="pcard__desc">{p.summary}</p>}
                    </div>

                    <div className="pcard__img">
                      {p.image && <img src={p.image} alt={p.name} />}
                    </div>
                  </article>
                ))}
              </div>

              {visible.length === 0 && (
                <p className="toolbar-empty">
                  {en
                    ? "No products match your search."
                    : "Tidak ada produk yang cocok."}
                </p>
              )}

              {visible.length > perPage && (
                <div className="career-vacancies__pager rv">
                  <span>{en ? "Page:" : "Halaman:"}</span>

                  {Array.from({ length: totalPages }, (_, i) => {
                    const page = i + 1
                    return (
                      <button
                        key={page}
                        type="button"
                        className={pageNo === page ? "is-active" : ""}
                        onClick={() => setPageNo(page)}
                        aria-label={`${en ? "Page" : "Halaman"} ${page}`}
                        aria-current={pageNo === page ? "page" : undefined}
                      >
                        {page}
                      </button>
                    )
                  })}
                </div>
              )}
            </div>
          </section>
        </>
      )}

      <Modal
        open={!!detail}
        onClose={() => setDetail(null)}
        closeLabel={t.close}
        wide
        flush
      >
        {detail && (
          <div className="pmodal">
            <div
              className="pmodal__img"
              style={
                detail.image
                  ? { backgroundImage: `url('${detail.image}')` }
                  : {}
              }
            ></div>
            <div className="pmodal__panel">
              <h3>{detail.name}</h3>
              {detail.description && <p>{detail.description}</p>}

              {detail.shops?.length > 0 && (
                <div className="pmodal__shops">
                  <h4>{en ? "Available at" : "Tersedia di"}</h4>
                  <div className="market market--sm market--color">
                    {detail.shops.map((s, i) => (
                      <a
                        key={i}
                        href={s.url || "#"}
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label={s.name}
                        style={{ background: s.color }}
                      >
                        {s.logo ? <img src={s.logo} alt={s.name} /> : s.name}
                      </a>
                    ))}
                  </div>
                </div>
              )}

              {(detail.website || detail.instagram || detail.facebook) && (
                <div className="pmodal__more">
                  <h4>
                    {en ? "More Information" : "Informasi Lebih Lanjut"}
                  </h4>

                  {(detail.instagram || detail.facebook) && (
                    <div className="pmodal__socials">
                      {detail.instagram && (
                        <a
                          className="pmodal__social"
                          href={detail.instagram}
                          target="_blank"
                          rel="noopener noreferrer"
                          aria-label="Instagram"
                        >
                          {socialIcons.instagram ? (
                            <img src={socialIcons.instagram} alt="Instagram" />
                          ) : (
                            <svg
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              strokeWidth="2"
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              aria-hidden="true"
                            >
                              <rect x="2" y="2" width="20" height="20" rx="5" />
                              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z" />
                              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                            </svg>
                          )}
                        </a>
                      )}
                      {detail.facebook && (
                        <a
                          className="pmodal__social"
                          href={detail.facebook}
                          target="_blank"
                          rel="noopener noreferrer"
                          aria-label="Facebook"
                        >
                          {socialIcons.facebook ? (
                            <img src={socialIcons.facebook} alt="Facebook" />
                          ) : (
                            <svg
                              viewBox="0 0 24 24"
                              fill="none"
                              stroke="currentColor"
                              strokeWidth="2"
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              aria-hidden="true"
                            >
                              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                            </svg>
                          )}
                        </a>
                      )}
                    </div>
                  )}

                  {detail.website && (
                    <a
                      className="pmodal__web"
                      href={detail.website}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      {en ? "Visit Website" : "Kunjungi Website"}
                      <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        aria-hidden="true"
                      >
                        <path d="M7 17 17 7" />
                        <path d="M8 7h9v9" />
                      </svg>
                    </a>
                  )}
                </div>
              )}
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}

Products.layout = (page) => <SiteLayout>{page}</SiteLayout>
