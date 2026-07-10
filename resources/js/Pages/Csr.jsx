import { Head, usePage } from '@inertiajs/react';
import SiteLayout from '../Layouts/SiteLayout';

function CsrList({ items }) {
    return (
        <div className="csr-list">
            {items.map((p, i) => (
                <article className={'csr-item rv' + (i % 2 === 1 ? ' csr-item--flip' : '')} key={i}>
                    <div className="csr-item__media" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                    <div className="csr-item__body"><h3>{p.title}</h3><p>{p.body}</p></div>
                </article>
            ))}
        </div>
    );
}

export default function Csr({ page, esg, health, sports }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';

    return (
        <>
            <Head title={page?.metaTitle || `${t.nav.csr} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `linear-gradient(150deg,rgba(63,110,59,.5),rgba(58,24,96,.6)),url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.csr}</span>
                    <h1 className="display">{page?.bannerTitle || t.nav.csr}</h1>
                </div>
            </section>

            {(page?.bannerSubtitle || page?.intro) && (
                <section className="section"><div className="container">
                    {page?.bannerSubtitle && <h2 className="display rv" style={{ color: 'var(--purple-600)', fontSize: 'clamp(28px,2.9vw,50px)', maxWidth: '20ch' }}>{page.bannerSubtitle}</h2>}
                    {page?.intro && <p className="rv" style={{ marginTop: 24, maxWidth: 1273, fontSize: 'clamp(16px,1.25vw,20px)', lineHeight: 1.7, color: 'var(--text-muted)' }}>{page.intro}</p>}
                </div></section>
            )}

            {esg.length > 0 && (
                <section className="section section--purple"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--lavender">ESG</span><h2 className="display">Environmental, Social, and Governance</h2><p>{en ? 'Combiphar applies ESG principles as part of its long-term commitment to responsible, sustainable growth.' : 'Combiphar menerapkan prinsip ESG sebagai bagian dari komitmen jangka panjang dalam menciptakan pertumbuhan yang bertanggung jawab dan berkelanjutan.'}</p></div>
                    <CsrList items={esg} />
                </div></section>
            )}

            {health.length > 0 && (
                <section className="section"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--magenta">Health Campaign</span><h2 className="display">Health Campaign</h2><p>{en ? 'Initiatives supporting health, empowerment, education, and an active lifestyle for Indonesian society.' : 'Inisiatif yang mendukung kesehatan, pemberdayaan, pendidikan, dan gaya hidup aktif untuk masyarakat Indonesia.'}</p></div>
                    <CsrList items={health} />
                </div></section>
            )}

            {sports.length > 0 && (
                <section className="section section--dark"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--lavender">Sports</span><h2 className="display">Sports</h2><p>{en ? 'Encouraging an active spirit through real support for Indonesian sports.' : 'Mendorong semangat aktif melalui dukungan nyata terhadap olahraga Indonesia.'}</p></div>
                    <div className="sport-grid">
                        {sports.map((p, i) => (
                            <div className="sport-card rv" key={i}>
                                <div className="sport-card__img" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                                <h3>{p.title}</h3>
                            </div>
                        ))}
                    </div>
                </div></section>
            )}
        </>
    );
}

Csr.layout = (page) => <SiteLayout>{page}</SiteLayout>;