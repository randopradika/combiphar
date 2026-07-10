import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';
import Modal from '../components/Modal';
import { MilestoneSlider } from '../components/Sliders';

const Arrow = () => <svg viewBox="0 0 48 32" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M2 8h34m0 0-10-6m10 6-10 6"/><path d="M30 24h14"/></svg>;

function BoardGrid({ people, onOpen }) {
    const { props: { locale } } = usePage();
    return (
        <div className="board-grid" style={{ marginTop: 44 }}>
            {people.map((p, i) => (
                <div className="board-card rv" key={i} onClick={() => onOpen(p)}>
                    <div className="board-card__photo" style={p.photo ? { backgroundImage: `url('${p.photo}')` } : {}}></div>
                    <div className="board-card__info">
                        <h4>{p.name}</h4>
                        <span className="board-card__role">{p.role}</span>
                        <span className="board-card__bio-btn">{locale === 'en' ? 'Biography' : 'Biografi'}</span>
                    </div>
                </div>
            ))}
        </div>
    );
}

export default function About({ page, milestones, commissioners, directors, awards, shops, facilities, accreditations, offices }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';
    const [board, setBoard] = useState(null);
    const [facOpen, setFacOpen] = useState(false);
    const [city, setCity] = useState('');
    const [cat, setCat] = useState('');

    const cities = useMemo(() => [...new Set(offices.map((o) => o.city).filter(Boolean))], [offices]);
    const cats = useMemo(() => [...new Set(offices.map((o) => o.category).filter(Boolean))], [offices]);
    const visibleOffices = offices.filter((o) => (!city || o.city === city) && (!cat || o.category === cat));

    const pins = [
        { l: '15.6%', tp: '38%', c: '#ffffff', label: 'North America' },
        { l: '48.4%', tp: '44%', c: '#DCC4F6', label: 'Africa' },
        { l: '76.5%', tp: '41%', c: '#F46EB5', label: 'Asia' },
        { l: '90%', tp: '63%', c: '#F46EB5', label: 'Indonesia' },
        { l: '92.5%', tp: '79%', c: '#9D7EC6', label: 'Australia' },
    ];

    return (
        <>
            <Head title={page?.metaTitle || `${t.nav.about} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `linear-gradient(120deg,rgba(74,31,122,.55),rgba(43,0,90,.5)),url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.about}</span>
                    <h1 className="display">{page?.bannerTitle || 'Championing a Healthy Tomorrow'}</h1>
                </div>
            </section>

            {page?.intro && (
                <section className="section"><div className="container"><p className="about-intro rv">{page.intro}</p></div></section>
            )}

            {milestones.length > 0 && (
                <section className="section darkzone">
                    <div className="container journey-head rv">
                        <span className="eyebrow eyebrow--lavender">{t.nav.about}</span>
                        <h2 className="display">{en ? 'Our History & Purpose' : 'Sejarah & Tujuan Kami'}</h2>
                    </div>
                    <MilestoneSlider items={milestones} />
                </section>
            )}

            {(page?.vision || page?.mission || page?.values) && (
                <section className="section"><div className="container"><div className="vmv-list">
                    {page?.vision && <div className="vmv-row rv"><h3>{en ? 'Our Vision' : 'Visi Kami'}</h3><span className="arrow"><Arrow /></span><p>{page.vision}</p></div>}
                    {page?.mission && <div className="vmv-row rv"><h3>{en ? 'Our Mission' : 'Misi Kami'}</h3><span className="arrow"><Arrow /></span><p>{page.mission}</p></div>}
                    {page?.values && <div className="vmv-row rv"><h3>{en ? 'Our Values' : 'Nilai Kami'}</h3><span className="arrow"><Arrow /></span><p>{page.values}</p></div>}
                </div></div></section>
            )}

            {(commissioners.length > 0 || directors.length > 0) && (
                <section className="section section--purple"><div className="container">
                    {commissioners.length > 0 && (<>
                        <div className="sec-head sec-head--center rv"><span className="eyebrow eyebrow--lavender">{en ? 'Leadership' : 'Kepemimpinan'}</span><h2 className="display">Board of Commissioners</h2></div>
                        <BoardGrid people={commissioners} onOpen={setBoard} />
                    </>)}
                    {directors.length > 0 && (<>
                        <div className="sec-head sec-head--center rv" style={{ marginTop: 'clamp(48px,5vw,80px)' }}><h2 className="display">Board of Directors</h2></div>
                        <BoardGrid people={directors} onOpen={setBoard} />
                    </>)}
                </div></section>
            )}

            {awards.length > 0 && (
                <section className="section"><div className="container">
                    <div className="sec-head sec-head--center rv"><span className="eyebrow eyebrow--magenta">{en ? 'Achievements' : 'Pencapaian'}</span><h2 className="display">{en ? 'Achievements & Awards' : 'Pencapaian & Penghargaan'}</h2><p>{en ? 'Our commitment to quality and sustainability is recognised through national and international awards.' : 'Komitmen kami terhadap kualitas dan keberlanjutan diakui melalui berbagai penghargaan nasional maupun internasional.'}</p></div>
                    <div className="awards rv" style={{ marginTop: 44 }}>
                        {awards.map((a, i) => (
                            <div className="award-tile" key={i}>
                                {a.image ? <img src={a.image} alt={a.title} /> : <span>{a.title}{a.year && <><br /><b>{a.year}</b></>}</span>}
                            </div>
                        ))}
                    </div>
                    {(page?.stat1Value || page?.stat2Value) && (
                        <div className="stat-cards rv">
                            {page?.stat1Value && <div className="stat-card"><div className="num">{page.stat1Value}</div><p>{page.stat1Label}</p></div>}
                            {page?.stat2Value && <div className="stat-card"><div className="num">{page.stat2Value}</div><p>{page.stat2Label}</p></div>}
                        </div>
                    )}
                </div></section>
            )}

            <section className="section" style={{ background: 'var(--surface)' }}><div className="container">
                <div className="sec-head sec-head--center rv"><span className="eyebrow eyebrow--magenta">{en ? 'Global Reach' : 'Jangkauan Global'}</span><h2 className="display">{en ? 'Our Presence' : 'Kehadiran Kami'}</h2><p>{en ? 'From Indonesia to the world - click a pin to see our production facilities.' : 'Dari Indonesia untuk dunia - klik pin untuk melihat fasilitas produksi kami.'}</p></div>
                <div className="world-map rv">
                    <img className="world-map__img" src="/img/world-map.png" alt={en ? 'Combiphar global presence map' : 'Peta kehadiran global Combiphar'} />
                    {pins.map((p, i) => (
                        <button key={i} type="button" className="map-pin" style={{ left: p.l, top: p.tp }} onClick={() => setFacOpen(true)}>
                            <i style={{ '--pin': p.c }}></i><span>{p.label}</span>
                        </button>
                    ))}
                </div>
                <div className="map-legend rv">
                    <span><i style={{ background: '#F46EB5' }}></i>Asia</span>
                    <span><i style={{ background: '#9D7EC6' }}></i>Australia</span>
                    <span><i style={{ background: '#DCC4F6' }}></i>Africa</span>
                    <span><i style={{ background: '#fff', border: '1px solid #ccc' }}></i>North America</span>
                    <button type="button" className="btn btn--outline" style={{ marginLeft: 'auto' }} onClick={() => setFacOpen(true)}>{en ? 'View Production Facilities' : 'Lihat Fasilitas Produksi'}</button>
                </div>
            </div></section>

            {offices.length > 0 && (
                <section className="section"><div className="container">
                    <div className="loc-head rv">
                        <h3 className="display" style={{ fontSize: 'clamp(24px,2.4vw,34px)', color: 'var(--purple-800)' }}>{en ? 'Our Locations' : 'Lokasi Kami'}</h3>
                        <div className="loc-filters">
                            <span className="selectbox"><select value={city} onChange={(e) => setCity(e.target.value)} aria-label="Location"><option value="">{en ? 'Location: All' : 'Lokasi: Semua'}</option>{cities.map((c) => <option key={c} value={c}>{c}</option>)}</select></span>
                            <span className="selectbox"><select value={cat} onChange={(e) => setCat(e.target.value)} aria-label="Category"><option value="">{en ? 'Category: All' : 'Kategori: Semua'}</option>{cats.map((c) => <option key={c} value={c}>{c}</option>)}</select></span>
                        </div>
                    </div>
                    <div className="grid grid--4" style={{ marginTop: 24 }}>
                        {visibleOffices.map((o, i) => (
                            <div className="office-card rv" key={i}>
                                <h4>{o.name}</h4>
                                <p>{o.description}{o.phone && <><br />Phone: {o.phone}</>}</p>
                            </div>
                        ))}
                    </div>
                    {visibleOffices.length === 0 && <p className="toolbar-empty">{en ? 'No offices match your filter.' : 'Tidak ada kantor yang cocok.'}</p>}
                </div></section>
            )}

            {shops.length > 0 && (
                <section className="section darkzone"><div className="container">
                    <div className="sec-head sec-head--center rv"><span className="eyebrow eyebrow--lavender">{en ? 'Official Store' : 'Belanja Resmi'}</span><h2 className="display">{en ? 'Our Official Online Stores' : 'Toko Online Resmi Kami'}</h2><p>{en ? 'Original Combiphar & Combicare products on your favourite marketplaces.' : 'Produk original Combiphar & Combicare di marketplace favorit Anda.'}</p></div>
                    <div className="market rv">
                        {shops.map((s, i) => (
                            <a key={i} href={s.url || '#'} target="_blank" rel="noopener noreferrer" aria-label={s.name}>
                                {s.logo ? <img src={s.logo} alt={s.name} /> : s.name}
                            </a>
                        ))}
                    </div>
                </div></section>
            )}

            <Modal open={!!board} onClose={() => setBoard(null)} closeLabel={t.close}>
                {board && (
                    <div className="pmodal__grid">
                        <div className="pmodal__img" style={{ aspectRatio: '3/4', ...(board.photo ? { backgroundImage: `url('${board.photo}')` } : {}) }}></div>
                        <div><h3>{board.name}</h3><p className="bm-role">{board.role}</p><p>{board.bio}</p></div>
                    </div>
                )}
            </Modal>

            <Modal open={facOpen} onClose={() => setFacOpen(false)} wide closeLabel={t.close}>
                <h3 style={{ textAlign: 'center' }}>{en ? 'Internationally Standardized Production Facilities' : 'Fasilitas Produksi Berstandar Internasional'}</h3>
                <p style={{ textAlign: 'center', color: 'var(--text-muted)' }}>{en ? 'Our facilities serve local & global markets including contract manufacturing.' : 'Fasilitas kami melayani pasar lokal dan global termasuk contract manufacturing.'}</p>
                {facilities.length > 0 && (
                    <div className="fac-grid">
                        {facilities.map((f, i) => (
                            <div className="fac-card" key={i}>
                                <div className="fac-card__img" style={f.image ? { backgroundImage: `url('${f.image}')` } : {}}></div>
                                <h4>{f.name}{f.region && <small>, {f.region}</small>}</h4>
                                <p className="plant">{f.plants}{f.area && <small> &mdash; {f.area}</small>}</p>
                                <p className="dose">{f.detail}</p>
                            </div>
                        ))}
                    </div>
                )}
                {accreditations.length > 0 && (<>
                    <h4 className="accr-title">{en ? 'Various Accreditations' : 'Berbagai Akreditasi'}</h4>
                    <div className="accr-grid">
                        {accreditations.map((a, i) => <div key={i}><b>{a.name}</b>{a.issuer && <span>{a.issuer}</span>}</div>)}
                    </div>
                </>)}
            </Modal>
        </>
    );
}

About.layout = (page) => <SiteLayout>{page}</SiteLayout>;