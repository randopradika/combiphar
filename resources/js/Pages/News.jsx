import { Head, Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import SiteLayout from '../Layouts/SiteLayout';

function Cards({ items, readMore }) {
    return (
        <div className="grid grid--3" style={{ marginTop: 40 }}>
            {items.map((a) => (
                <article className="ncard rv" key={a.slug}>
                    <div className="ncard__img" style={a.cover ? { backgroundImage: `url('${a.cover}')` } : {}}></div>
                    <div className="ncard__body">
                        <span className="ncard__date">{a.date}</span>
                        <h3 className="ncard__title">{a.title}</h3>
                        <hr />
                        <p className="ncard__excerpt">{a.excerpt}</p>
                        <Link className="btn btn--fill" href={a.url}>{readMore}</Link>
                    </div>
                </article>
            ))}
        </div>
    );
}

export default function News({ page, health, corporate }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';
    const [tab, setTab] = useState('health');
    useEffect(() => {
        const els = document.querySelectorAll(".rv")
        els.forEach((el) => el.classList.add("is-in"))
      }, [tab])

    return (
        <>
            <Head title={page?.metaTitle || `${t.nav.news} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.news}</span>
                    <h1 className="display">{page?.bannerTitle || (en ? 'News & Health Info' : 'Berita & Info Kesehatan')}</h1>
                </div>
            </section>

            <nav className="subnav" aria-label="News categories">
                <div className="container subnav__inner">
                    <button type="button" className={tab === 'health' ? 'on' : ''} onClick={() => setTab('health')}>{en ? 'Health Info' : 'Info Kesehatan'}</button>
                    <button type="button" className={tab === 'invest' ? 'on' : ''} onClick={() => setTab('invest')}>Investor Updates</button>
                </div>
            </nav>

            {tab === 'health' && (
                <section className="section"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--magenta">{en ? 'Health Info' : 'Info Kesehatan'}</span><h2 className="display">{en ? 'Education & Healthy Lifestyle' : 'Edukasi & Gaya Hidup Sehat'}</h2></div>
                    {health.length > 0 ? <Cards items={health} readMore={en ? 'Read More' : 'Selengkapnya'} /> : <p className="lead">{en ? 'No articles yet.' : 'Belum ada artikel.'}</p>}
                </div></section>
            )}

            {tab === 'invest' && (
                <section className="section" style={{ background: 'var(--surface)' }}><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--magenta">Investor Updates</span><h2 className="display">{en ? 'Corporate Updates & Actions' : 'Pembaruan & Aksi Korporasi'}</h2></div>
                    {corporate.length > 0 ? <Cards items={corporate} readMore={en ? 'Read More' : 'Selengkapnya'} /> : <p className="lead">{en ? 'No updates yet.' : 'Belum ada pembaruan.'}</p>}
                </div></section>
            )}
        </>
    );
}

News.layout = (page) => <SiteLayout>{page}</SiteLayout>;