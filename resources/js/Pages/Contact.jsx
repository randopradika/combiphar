import { Head, useForm, usePage } from "@inertiajs/react"
import { useState, useEffect, Fragment } from "react"
import SiteLayout from "../Layouts/SiteLayout"
import Modal from "../components/Modal"

export default function Contact({ page, vacancies, faqs }) {
  const {
    props: { t, locale, homeUrl, flash },
  } = usePage()
  const en = locale === "en"
  const [tab, setTab] = useState(flash.contact_success ? "kontak" : "karir")
  const [vac, setVac] = useState(null)
  const [mobileOpen, setMobileOpen] = useState(false)
  const { data, setData, post, processing, errors, reset } = useForm({
    name: "",
    email: "",
    phone: "",
    subject: "",
    message: "",
  })
  const [vacancyPage, setVacancyPage] = useState(1)
  const scrollToVacancies = () => {
    document.querySelector(".career-vacancies")?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    })
  }

  const VACANCIES_PER_PAGE = 6
  const totalVacancyPages = Math.max(
    1,
    Math.ceil(vacancies.length / VACANCIES_PER_PAGE),
  )
  const pagedVacancies = vacancies.slice(
    (vacancyPage - 1) * VACANCIES_PER_PAGE,
    vacancyPage * VACANCIES_PER_PAGE,
  )

  const [openFaq, setOpenFaq] = useState(2)

  useEffect(() => {
    const els = document.querySelectorAll(".rv")
    els.forEach((el) => el.classList.add("is-in"))
  }, [tab, vacancyPage])

  useEffect(() => {
    if (vacancyPage > totalVacancyPages) {
      setVacancyPage(1)
    }
  }, [vacancyPage, totalVacancyPages])

  const submit = (e) => {
    e.preventDefault()
    post(window.location.pathname, {
      preserveScroll: true,
      onSuccess: () => reset(),
    })
  }

  const steps = [
    {
      en: "Application Review",
      id: "Seleksi CV",
      icon: (
        <svg
          width="95"
          height="95"
          viewBox="0 0 95 95"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M85.7395 41.3248L81.8603 57.8706C78.5353 72.1602 71.9645 77.9393 59.6145 76.7518C57.6353 76.5935 55.4978 76.2373 53.202 75.6831L46.552 74.0998C30.0458 70.181 24.9395 62.0269 28.8187 45.481L32.6978 28.8956C33.4895 25.531 34.4395 22.6019 35.627 20.1873C40.2583 10.6081 48.1353 8.03518 61.3562 11.1623L67.9666 12.706C84.552 16.5852 89.6187 24.7789 85.7395 41.3248Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M59.6107 76.7521C57.1565 78.4146 54.069 79.8 50.3086 81.0271L44.0544 83.0854C28.3398 88.1521 20.0669 83.9167 14.9607 68.2021L9.89401 52.5667C4.82734 36.8521 9.02318 28.5396 24.7378 23.4729L30.9919 21.4146C32.6148 20.9 34.1586 20.4646 35.6232 20.1875C34.4357 22.6021 33.4857 25.5313 32.694 28.8958L28.8148 45.4813C24.9357 62.0271 30.0419 70.1812 46.5482 74.1L53.1982 75.6833C55.494 76.2375 57.6315 76.5937 59.6107 76.7521Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M50.0312 33.7646L69.2292 38.6334"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M46.1562 49.0835L57.6354 52.0127"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
    {
      en: "HR Interview",
      id: "Wawancara HR",
      icon: (
        <svg
          width="71"
          height="79"
          viewBox="0 0 71 79"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M58.1667 65.57H55.2955C52.2733 65.57 49.4022 66.7488 47.2866 68.8781L40.8266 75.3041C37.8799 78.232 33.0823 78.232 30.1356 75.3041L23.6755 68.8781C21.56 66.7488 18.6511 65.57 15.6667 65.57H12.8333C6.56222 65.57 1.5 60.513 1.5 54.2771V12.793C1.5 6.55706 6.56222 1.5 12.8333 1.5H58.1667C64.4378 1.5 69.5 6.55706 69.5 12.793V54.2771C69.5 60.475 64.4378 65.57 58.1667 65.57Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeMiterlimit="10"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M35.5 31.5C40.4705 31.5 44.5 27.6943 44.5 22.9999C44.5 18.3054 40.4705 14.5 35.5 14.5C30.5295 14.5 26.5 18.3054 26.5 22.9999C26.5 27.6943 30.5295 31.5 35.5 31.5Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M51.5 53.5C51.5 46.322 44.7875 40.5 36.5 40.5C28.2125 40.5 21.5 46.322 21.5 53.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
    {
      en: "User Interview",
      id: "Wawancara User",
      icon: (
        <svg
          width="76"
          height="79"
          viewBox="0 0 76 79"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M28.1126 34.5C27.7297 34.4628 27.2703 34.4628 26.8491 34.5C17.7365 34.2024 10.5 26.9476 10.5 18.0186C10.5 8.90361 18.0811 1.5 27.5 1.5C36.8806 1.5 44.5 8.90361 44.5 18.0186C44.4617 26.9476 37.2252 34.2024 28.1126 34.5Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M55.0014 8.5C62.4835 8.5 68.5 14.78 68.5 22.5C68.5 30.06 62.7149 36.22 55.5028 36.5C55.1942 36.46 54.8471 36.46 54.5 36.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            d="M8.41935 48.912C-0.806452 55.2999 -0.806452 65.7099 8.41935 72.0584C18.9032 79.3139 36.0968 79.3139 46.5806 72.0584C55.8065 65.6705 55.8065 55.2605 46.5806 48.912C36.1349 41.696 18.9413 41.696 8.41935 48.912Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M62.5 69.5C65.2604 68.925 67.8674 67.8133 70.0144 66.165C75.9952 61.68 75.9952 54.2817 70.0144 49.7967C67.9057 48.1867 65.3371 47.1133 62.615 46.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
    {
      en: "Assessment",
      id: "Asesmen",
      icon: (
        <svg
          width="79"
          height="79"
          viewBox="0 0 79 79"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            opacity="0.4"
            d="M40.5 27.5H60.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M18.5 27.5L21.25 30.5L29.5 21.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M40.5 54.5H60.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M18.5 54.5L21.25 57.5L29.5 48.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            d="M28.1 77.5H50.9C69.9 77.5 77.5 69.9 77.5 50.9V28.1C77.5 9.1 69.9 1.5 50.9 1.5H28.1C9.1 1.5 1.5 9.1 1.5 28.1V50.9C1.5 69.9 9.1 77.5 28.1 77.5Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
    {
      en: "Background Check",
      id: "Pemeriksaan Latar Belakang",
      icon: (
        <svg
          width="79"
          height="79"
          viewBox="0 0 79 79"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M69.5 35.5C69.5 54.2756 54.2756 69.5 35.5 69.5C16.7244 69.5 1.5 54.2756 1.5 35.5C1.5 16.7244 16.7244 1.5 35.5 1.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M65.8801 72.6999C67.8881 78.5473 72.4725 79.132 75.996 74.0155C79.2164 69.3376 77.0947 65.5003 71.2601 65.5003C66.941 65.4638 64.5162 68.7164 65.8801 72.6999Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M47.5 12.5H69.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M47.5 24.5H58.5"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
    {
      en: "Offering",
      id: "Penawaran",
      icon: (
        <svg
          width="72"
          height="78"
          viewBox="0 0 72 78"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M42 3H15C10.582 3 7 6.582 7 11V67C7 71.418 10.582 75 15 75H57C61.418 75 65 71.418 65 67V26L42 3Z"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            d="M42 3V20C42 23.314 44.686 26 48 26H65"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            opacity="0.4"
            d="M23 47L32 56L50 38"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      ),
    },
  ]

  return (
    <>
      <Head title={`${t.nav.contact} — Combiphar`} />

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
          <h1 className="display">{page?.bannerTitle || t.nav.contact}</h1>
          <p className="banner__row-sub">{page?.bannerSubtitle || ""}</p>
        </div>
      </section>

      <nav className="subnav" aria-label="Careers and contact">
        <div className="container subnav__inner">
          <div className="subnav__desktop">
            <button
              type="button"
              className={tab === "karir" ? "on" : ""}
              onClick={() => setTab("karir")}
            >
              {en ? "Careers" : "Karir"}
            </button>
            <button
              type="button"
              className={tab === "kontak" ? "on" : ""}
              onClick={() => setTab("kontak")}
            >
              {en ? "Contact" : "Kontak"}
            </button>
          </div>

          <div className={"subnav__mobile" + (mobileOpen ? " is-open" : "")}>
            <button
              type="button"
              className="subnav__trigger"
              onClick={() => setMobileOpen((v) => !v)}
              aria-expanded={mobileOpen}
            >
              {tab === "karir"
                ? en
                  ? "Careers"
                  : "Karir"
                : en
                  ? "Contact"
                  : "Kontak"}
              <span className="subnav__caret">▾</span>
            </button>

            <div className="subnav__menu">
              <button
                type="button"
                className={tab === "karir" ? "on" : ""}
                onClick={() => {
                  setTab("karir")
                  setMobileOpen(false)
                }}
              >
                {en ? "Careers" : "Karir"}
              </button>
              <button
                type="button"
                className={tab === "kontak" ? "on" : ""}
                onClick={() => {
                  setTab("kontak")
                  setMobileOpen(false)
                }}
              >
                {en ? "Contact" : "Kontak"}
              </button>
            </div>
          </div>
        </div>
      </nav>

      {tab === "karir" && (
        <>
          <section className="section career-vacancies">
            <div className="container">
              <div className="career-vacancies__head rv">
                <div>
                  <h2 className="career-vacancies__title">
                    {en ? "Available Vacancies" : "Lowongan Tersedia"}
                  </h2>
                </div>

                <p className="career-vacancies__intro">
                  {en
                    ? "Find the right position and start your career journey with Combiphar."
                    : "Temukan posisi yang tepat dan mulai karirmu bersama Combiphar."}
                </p>
              </div>

              {vacancies.length === 0 && (
                <p className="lead" style={{ marginTop: 36 }}>
                  {en
                    ? "No open positions right now."
                    : "Belum ada lowongan saat ini."}
                </p>
              )}

              <div className="career-vacancies__grid">
                {pagedVacancies.map((v, i) => (
                  <article className="vac-card rv" key={i}>
                    <h3>{v.title}</h3>

                    {v.location && (
                      <p className="vac-card__meta">
                        <strong>{en ? "Location:" : "Lokasi:"}</strong>{" "}
                        {v.location}
                      </p>
                    )}

                    {v.summary && (
                      <p className="vac-card__desc">
                        <strong>
                          {en ? "Responsibilities:" : "Tanggung Jawab:"}
                        </strong>{" "}
                        {v.summary}
                      </p>
                    )}

                    <div className="vac-card__actions">
                      <button
                        type="button"
                        className="vac-card__btn"
                        onClick={() => setVac(v)}
                      >
                        {en ? "Detail" : "Detil"}
                      </button>

                      <a
                        className="vac-card__btn"
                        href={
                          v.applyUrl ||
                          `mailto:recruitment@combiphar.com?subject=${encodeURIComponent(
                            (en ? "Application" : "Lamaran") + ": " + v.title,
                          )}`
                        }
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        {en ? "Apply" : "Daftar"}
                      </a>
                    </div>
                  </article>
                ))}
              </div>

              {vacancies.length > VACANCIES_PER_PAGE && (
                <div
                  className="career-vacancies__pager rv"
                  aria-label="Vacancy pagination"
                >
                  <span>{en ? "Page:" : "Halaman:"}</span>

                  {Array.from({ length: totalVacancyPages }, (_, i) => {
                    const page = i + 1
                    return (
                      <button
                        key={page}
                        type="button"
                        className={vacancyPage === page ? "is-active" : ""}
                        onClick={() => {
                          setVacancyPage(page)
                          scrollToVacancies()
                        }}
                        aria-current={vacancyPage === page ? "page" : undefined}
                      >
                        {page}
                      </button>
                    )
                  })}
                </div>
              )}
            </div>
          </section>
          <section className="section recruitment-process-section">
            <div className="container">
              <div className="sec-head rv">
                <h2 className="display">
                  {en ? "Recruitment Process" : "Proses Rekrutmen"}
                </h2>
              </div>

              <div className="process-cards rv">
                {steps.map((step, i) => (
                  <Fragment key={`${step.en}-${step.id}-${i}`}>
                    <div className="process-flow-item">
                      <div className="process-card">
                        <div className="process-card__icon">{step.icon}</div>
                        <p>{en ? step.en : step.id}</p>
                      </div>
                    </div>

                    {i < steps.length - 1 && (
                      <span className="process-arrow" aria-hidden="true">
                        <svg
                          width="12"
                          height="11"
                          viewBox="0 0 12 11"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M6.83333 9.75L11 5.375L6.83333 1M11 5.375L1 5.375"
                            stroke="#C92B82"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                          />
                        </svg>
                      </span>
                    )}
                  </Fragment>
                ))}
              </div>
            </div>
          </section>
        </>
      )}

      {tab === "kontak" && (
        <>
          <section className="section contact-hero-section">
            <div className="container">
              <div className="contact-intro-grid">
                <div className="contact-copy rv">
                  <h2 className="display">Have a Question?</h2>
                  <p>
                    {en
                      ? "Contact us directly by filling out the form."
                      : "Hubungi kami langsung dengan mengisi formulir."}
                  </p>
                </div>

                <form
                  className="form-wrap rv contact-form-card"
                  onSubmit={submit}
                >
                  {flash.contact_success && (
                    <div className="form-success">
                      {en
                        ? "Thank you! Your message has been sent."
                        : "Terima kasih! Pesan Anda telah terkirim."}
                    </div>
                  )}

                  <div className="form contact-form contact-form--image3">
                    <div className="field">
                      <label htmlFor="nm">
                        {en ? "Your Name" : "Nama Anda"} *
                      </label>
                      <input
                        id="nm"
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        placeholder="Type your name"
                        required
                      />
                      {errors.name && <small>{errors.name}</small>}
                    </div>

                    <div className="field">
                      <label htmlFor="em">Email Address *</label>
                      <input
                        id="em"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData("email", e.target.value)}
                        placeholder="Type your email address"
                        required
                      />
                      {errors.email && <small>{errors.email}</small>}
                    </div>

                    <div className="field">
                      <label htmlFor="sb">{en ? "Subject" : "Subjek"} *</label>
                      <input
                        id="sb"
                        value={data.subject}
                        onChange={(e) => setData("subject", e.target.value)}
                        placeholder="Type your subject"
                      />
                    </div>

                    <div className="field">
                      <label htmlFor="ph">
                        {en ? "Phone Number" : "Nomor Telepon"}
                      </label>
                      <input
                        id="ph"
                        value={data.phone}
                        onChange={(e) => setData("phone", e.target.value)}
                        placeholder="Type your phone number"
                      />
                    </div>

                    <div className="field full">
                      <label htmlFor="msg">{en ? "Message" : "Pesan"} *</label>
                      <textarea
                        id="msg"
                        value={data.message}
                        onChange={(e) => setData("message", e.target.value)}
                        placeholder="Enter your message here ..."
                        required
                      />
                      {errors.message && <small>{errors.message}</small>}
                    </div>

                    <div className="full contact-form__meta"></div>

                    <div className="full contact-form__actions">
                      <label className="contact-consent">
                        <input type="checkbox" required />
                        <span>
                          {en
                            ? "I’ve read and agree with "
                            : "Saya telah membaca dan menyetujui "}
                          <a href="/terms-of-use">Terms of Services</a> and{" "}
                          <a href="/privacy-notice">Privacy Policy</a>
                        </span>
                      </label>
                      <button
                        className="btn btn--outline contact-submit"
                        type="submit"
                        style={{ justifyContent: "center" }}
                        disabled={processing}
                      >
                        {en ? "Send Message" : "Kirim Pesan"}
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </section>

          <section className="section recruitment-process-section">
            <div className="container">
              <div className="sec-head rv">
                <h2 className="display">
                  {en ? "Recruitment Process" : "Proses Rekrutmen"}
                </h2>
              </div>

              <div className="process-cards rv">
                {steps.map((step, i) => (
                  <Fragment key={`${step.en}-${step.id}-${i}`}>
                    <div className="process-flow-item">
                      <div className="process-card">
                        <div className="process-card__icon">{step.icon}</div>
                        <p>{en ? step.en : step.id}</p>
                      </div>
                    </div>

                    {i < steps.length - 1 && (
                      <span className="process-arrow" aria-hidden="true">
                        <svg
                          width="12"
                          height="11"
                          viewBox="0 0 12 11"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M6.83333 9.75L11 5.375L6.83333 1M11 5.375L1 5.375"
                            stroke="#C92B82"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                          />
                        </svg>
                      </span>
                    )}
                  </Fragment>
                ))}
              </div>
            </div>
          </section>

          <section className="section faq-section">
            <div className="container faq-wrap">
              <div className="sec-head rv">
                <h2 className="display">Frequently Asked Question (FAQ)</h2>
              </div>

              <div className="faq-list rv">
                {faqs.map((item, i) => {
                  const isOpen = openFaq === i
                  return (
                    <div
                      key={i}
                      className={`faq-item ${isOpen ? "is-open" : ""}`}
                    >
                      <button
                        type="button"
                        className="faq-q"
                        onClick={() => setOpenFaq(isOpen ? null : i)}
                        aria-expanded={isOpen}
                      >
                        <span>{item.question}</span>
                        <span className="faq-icon" aria-hidden="true">
                          <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            strokeWidth="2.2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                          >
                            <path d="M9 6l6 6-6 6" />
                          </svg>
                        </span>
                      </button>
                      {isOpen && (
                        <div className="faq-a">
                          <p>{item.answer}</p>
                        </div>
                      )}
                    </div>
                  )
                })}
              </div>
            </div>
          </section>
        </>
      )}

      <section className="warning">
        <div className="container warning__inner">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
          </svg>
          <div>
            <h3>
              {en
                ? "Official Combiphar recruitment information is only available here. Don't fall for scams impersonating us."
                : "Informasi rekrutmen resmi Combiphar hanya tersedia di sini. Jangan tertipu penipuan yang mengatasnamakan kami."}
            </h3>
            {/* <p>
              {en
                ? "Combiphar never charges any fees during recruitment. All official recruitment is conducted only through Combiphar official channels."
                : "Combiphar tidak pernah memungut biaya apa pun selama proses rekrutmen. Seluruh proses rekrutmen resmi hanya dilakukan melalui kanal resmi Combiphar."}
            </p> */}
          </div>
        </div>
      </section>

      <Modal
        open={!!vac}
        onClose={() => setVac(null)}
        closeLabel={t.close}
        wide
      >
        {vac && (
          <div className="vac-modal">
            <div className="vac-modal__head">
              <div>
                <h3>{vac.title}</h3>
                {vac.location && (
                  <p className="vac-modal__loc">
                    <strong>{en ? "Location:" : "Lokasi:"}</strong>{" "}
                    {vac.location}
                  </p>
                )}
              </div>
              <a
                className="vac-modal__apply"
                href={
                  vac.applyUrl ||
                  `mailto:recruitment@combiphar.com?subject=${encodeURIComponent(
                    (en ? "Application" : "Lamaran") + ": " + vac.title,
                  )}`
                }
                target="_blank"
                rel="noopener noreferrer"
              >
                {en ? "Apply Now" : "Lamar Sekarang"}
              </a>
            </div>

            <div className="vac-modal__body">
              {vac.description && (
                <div className="vac-modal__section">
                  <h4>{en ? "Responsibilities:" : "Tanggung Jawab:"}</h4>
                  <div
                    className="vac-modal__rich"
                    dangerouslySetInnerHTML={{ __html: vac.description }}
                  />
                </div>
              )}
              {vac.requirements && (
                <div className="vac-modal__section">
                  <h4>
                    {en
                      ? "Skills, Qualifications & Experience Required:"
                      : "Keterampilan, Kualifikasi, dan Pengalaman yang Dibutuhkan:"}
                  </h4>
                  <div
                    className="vac-modal__rich"
                    dangerouslySetInnerHTML={{ __html: vac.requirements }}
                  />
                </div>
              )}
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}

Contact.layout = (page) => <SiteLayout>{page}</SiteLayout>
