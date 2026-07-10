import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';
import { ImpactSlider, MilestoneSlider } from '../components/Sliders';

export default function Home({ page, impacts, milestones, categories, articles }) {
    const { props: { t, nav } } = usePage();
    const [cueHidden, setCueHidden] = useState(false);

    useEffect(() => {
        const onScroll = () => setCueHidden(window.scrollY > 30);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <>
            <Head title="Combiphar — Championing a Healthy Tomorrow" />

            <section className={'hero' + (page?.heroImage ? ' hero--photo' : '')} aria-label="Hero">
                {page?.heroImage && <img className="hero__img" src={page.heroImage} alt="Championing a Healthy Tomorrow" />}
                <div className="hero__inner">
                    <h1 className="hero__title display">
                        <span className="l1">{page?.heroLine1 || 'Championing a'}</span>
                        <span className="l2">{page?.heroLine2 || 'Healthy Tomorrow'}</span>
                    </h1>
                </div>
                <div className={'hero__scroll' + (cueHidden ? ' is-hidden' : '')} aria-hidden="true">
                    <span className="mouse"></span><span>{t.scroll}</span>
                </div>
            </section>

            {impacts.length > 0 && (
                <section className="section impact-section">
                    <div className="container">
                        <ImpactSlider items={impacts} />
                    </div>
                </section>
            )}

            <section
                className={'manifesto' + (page?.manifestoImage ? ' manifesto--photo' : '')}
            >
                {page?.manifestoImage && <img className="manifesto__img" src={page.manifestoImage} alt="" />}
                <div className="container manifesto__content rv">
                    <h2 className="display">{page?.manifestoTitle || 'Manifesto 55 Years of Combiphar'}</h2>
                    {page?.manifestoVideo ? (
                        <a className="manifesto__play" href={page.manifestoVideo} target="_blank" rel="noopener noreferrer" aria-label="Play video">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                        </a>
                    ) : (
                        <button className="manifesto__play" aria-label="Play video">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                        </button>
                    )}
                    <span className="manifesto__label">Play Video</span>
                </div>
            </section>

            <section className="section darkzone">
                <div className="container journey-head rv">
                    <span className="eyebrow eyebrow--lavender">{t.nav.about}</span>
                    <h2 className="display">Sekilas Perjalanan</h2>
                </div>
                {milestones.length > 0 && <MilestoneSlider items={milestones} />}
                <div className="container" style={{ marginTop: 'clamp(56px,7vw,104px)' }}>
                    <div className="products__head rv">
                        <span className="eyebrow eyebrow--lavender">{t.nav.products}</span>
                        <h2 className="display">Produk Berstandar Terbaik untuk Indonesia</h2>
                        <p>Diformulasikan dengan teknologi terkini dan memenuhi standar Cara Pembuatan Obat yang Baik (CPOB), setiap produk Combiphar dirancang untuk memberikan kualitas terbaik yang bisa Anda andalkan.</p>
                        <Link className="btn btn--outline-light" href={nav.products}>{t.learn_more}</Link>
                    </div>
                    <div className="bento rv">
                        {categories.map((c, i) => (
                            <Link key={i} className={'bcard' + (i === 0 ? ' bcard--wide' : '') + (i === 2 ? ' bcard--sq' : '')} href={nav.products}>
                                {c.image && <div className="bcard__art" style={{ backgroundImage: `url('${c.image}')` }}></div>}
                                <h3>{c.name}</h3>
                                <span className="bcard__arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                            </Link>
                        ))}
                    </div>
                </div>
            </section>

            {articles.length > 0 && (
                <section className="section" style={{ background: 'var(--surface)' }}>
                    <div className="container">
                        <div className="sec-head sec-head--center rv">
                            <span className="eyebrow eyebrow--magenta">Berita &amp; Info Kesehatan</span>
                            <h2 className="display">Berita Terkini</h2>
                        </div>
                        <div className="grid grid--3" style={{ marginTop: 44 }}>
                            {articles.map((a) => (
                                <article className="ncard rv" key={a.slug}>
                                    <div className="ncard__img" style={a.cover ? { backgroundImage: `url('${a.cover}')` } : {}}></div>
                                    <div className="ncard__body">
                                        <span className="ncard__date">{a.date}</span>
                                        <h3 className="ncard__title">{a.title}</h3>
                                        <hr />
                                        <p className="ncard__excerpt">{a.excerpt}</p>
                                        <Link className="btn btn--fill" href={a.url}>{t.read_more}</Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            <section
                className="cta"
                aria-label="CTA"
                style={page?.ctaImage ? { backgroundImage: `linear-gradient(140deg,rgba(58,24,96,.7),rgba(42,0,90,.85)),url('${page.ctaImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}
            >
                <h2 className="display rv">{page?.ctaTitle || 'Growing Through a Journey to Build a Healthy Tomorrow'}</h2>
            </section>
        </>
    );
}

Home.layout = (page) => <SiteLayout navMode="overlay">{page}</SiteLayout>;