import { Head, Link, usePage } from '@inertiajs/react';
import SiteLayout from '../Layouts/SiteLayout';

function CsrList({ items, learnMore }) {
    return (
        <div className="csr-cards">
            {items.map((p, i) => (
                <article className={'csr-card rv' + (i % 2 === 0 ? ' csr-card--right' : '')} key={i}>
                    <div className="csr-card__img" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                    <div className="csr-card__panel">
                        <h3>{p.title}</h3>
                        {p.body && <p>{p.body}</p>}
                        {p.url ? (
                            <Link className="csr-card__btn" href={p.url}>{learnMore}</Link>
                        ) : p.link ? (
                            <a className="csr-card__btn" href={p.link} target="_blank" rel="noopener noreferrer">{learnMore}</a>
                        ) : null}
                    </div>
                </article>
            ))}
        </div>
    );
}

export default function Csr({ page, esg, health, sports }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';
    const learnMore = en ? 'Learn More' : 'Pelajari Lebih Lanjut';

    return (
        <>
            <Head title={page?.metaTitle || `${t.nav.csr} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container banner__row">
                    <h1 className="display">{page?.bannerTitle || t.nav.csr}</h1>
                    {page?.bannerSubtitle && (
                        <p className="banner__row-sub">{page.bannerSubtitle}</p>
                    )}
                </div>
            </section>

            {(page?.bannerTitle2 || page?.intro) && (
                <section className="section"><div className="container">
                    {page?.bannerTitle2 && <h2 className="display rv" style={{ textAlign: 'center', color: 'var(--purple-600)', maxWidth: '18ch', margin: '0 auto' }}>{page.bannerTitle2}</h2>}
                    {page?.intro && <p className="cat-intro rv" style={{ marginTop: 24, maxWidth: 1000 }}>{page.intro}</p>}
                </div></section>
            )}

            {esg.length > 0 && (
                <section className="section"><div className="container">
                    <div className="sec-head sec-head--product rv"><h2 className="display">Environmental, Social, and Governance</h2><p>{en ? 'Combiphar applies ESG principles as part of the company long-term commitment to creating growth that is responsible, sustainable, and impactful for society and the environment.' : 'Combiphar menerapkan prinsip ESG sebagai bagian dari komitmen jangka panjang perusahaan dalam menciptakan pertumbuhan yang bertanggung jawab, berkelanjutan, dan berdampak bagi masyarakat serta lingkungan.'}</p></div>
                    <CsrList items={esg} learnMore={learnMore} />
                </div></section>
            )}

            {health.length > 0 && (
                <section className="section"><div className="container">
                    <div className="sec-head sec-head--product rv"><h2 className="display">Health Campaign</h2><p>{en ? 'Through various Health Campaigns, Combiphar delivers initiatives supporting health, empowerment, education, and an active lifestyle to create a positive impact for Indonesian society.' : 'Melalui berbagai Health Campaign, Combiphar menghadirkan inisiatif yang mendukung kesehatan, pemberdayaan, pendidikan, dan gaya hidup aktif untuk menciptakan dampak positif bagi masyarakat Indonesia.'}</p></div>
                    <CsrList items={health} learnMore={learnMore} />
                </div></section>
            )}

            {sports.length > 0 && (
                <section className="section"><div className="container">
                    <div className="sec-head sec-head--product rv"><h2 className="display">Sports</h2><p>{en ? 'Encouraging an active, achieving spirit through real support for the growth of Indonesian sports.' : 'Mendorong semangat aktif dan berprestasi melalui dukungan nyata terhadap perkembangan olahraga Indonesia.'}</p></div>
                    <div className="sport-grid">
                        {sports.map((p, i) => (
                            <a className="sport-card rv" key={i} href={p.link || undefined} target={p.link ? '_blank' : undefined} rel={p.link ? 'noopener noreferrer' : undefined}>
                                <div className="sport-card__img" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                                <div className="sport-card__overlay"></div>
                                <div className="sport-card__foot">
                                    <h3>{p.title}</h3>
                                    <span className="sport-card__arrow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </span>
                                </div>
                            </a>
                        ))}
                    </div>
                </div></section>
            )}
        </>
    );
}

Csr.layout = (page) => <SiteLayout>{page}</SiteLayout>;