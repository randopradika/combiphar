import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';
import Modal from '../components/Modal';

export default function Products({ page, categories, shops }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';
    const [active, setActive] = useState(0);
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState('az');
    const [detail, setDetail] = useState(null);

    const cat = categories[active];
    const visible = useMemo(() => {
        if (!cat) return [];
        const q = query.toLowerCase().trim();
        const list = cat.products.filter((p) => p.name.toLowerCase().includes(q));
        list.sort((a, b) => a.name.localeCompare(b.name));
        if (sort === 'za') list.reverse();
        return list;
    }, [cat, query, sort]);

    return (
        <>
            <Head title={page?.metaTitle || `${t.nav.products} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `linear-gradient(150deg,rgba(91,45,142,.55),rgba(58,24,96,.6)),url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.products}</span>
                    <h1 className="display">{page?.bannerTitle || t.nav.products}</h1>
                </div>
            </section>

            {categories.length > 0 && (
                <nav className="subnav" aria-label="Product categories">
                    <div className="container subnav__inner">
                        {categories.map((c, i) => (
                            <button key={c.slug} type="button" className={i === active ? 'on' : ''} onClick={() => { setActive(i); setQuery(''); }}>{c.name}</button>
                        ))}
                    </div>
                </nav>
            )}

            {cat && (
                <section className="section">
                    <div className="container">
                        <div className="sec-head rv">
                            <span className="eyebrow eyebrow--magenta">{en ? 'Category' : 'Kategori'}</span>
                            <h2 className="display">{cat.name}</h2>
                            {cat.description && <p>{cat.description}</p>}
                        </div>
                        <div className="toolbar rv">
                            <label className="searchbox">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                                <input type="search" value={query} onChange={(e) => setQuery(e.target.value)} placeholder={en ? 'Search products…' : 'Cari produk…'} />
                            </label>
                            <span className="selectbox">
                                <select value={sort} onChange={(e) => setSort(e.target.value)} aria-label={en ? 'Sort' : 'Urutkan'}>
                                    <option value="az">A–Z</option>
                                    <option value="za">Z–A</option>
                                </select>
                            </span>
                        </div>
                        <div className="grid grid--4" style={{ marginTop: 24 }}>
                            {visible.map((p, i) => (
                                <article className="pcard rv" key={i} onClick={() => setDetail({ ...p, cat: cat.name })}>
                                    <div className="pcard__img" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                                    <div className="pcard__body"><span className="cat">{cat.name}</span><h3>{p.name}</h3></div>
                                </article>
                            ))}
                        </div>
                        {visible.length === 0 && <p className="toolbar-empty">{en ? 'No products match your search.' : 'Tidak ada produk yang cocok.'}</p>}
                    </div>
                </section>
            )}

            <Modal open={!!detail} onClose={() => setDetail(null)} closeLabel={t.close}>
                {detail && (
                    <div className="pmodal__grid">
                        <div className="pmodal__img" style={detail.image ? { backgroundImage: `url('${detail.image}')` } : {}}></div>
                        <div>
                            <span className="eyebrow eyebrow--magenta">{detail.cat}</span>
                            <h3>{detail.name}</h3>
                            <p>{detail.description}</p>
                            {shops.length > 0 && (
                                <div className="pmodal__shops">
                                    <h4>{en ? 'Available at' : 'Tersedia di'}</h4>
                                    <div className="market market--sm">
                                        {shops.map((s, i) => (
                                            <a key={i} href={s.url || '#'} target="_blank" rel="noopener noreferrer" aria-label={s.name}>
                                                {s.logo ? <img src={s.logo} alt={s.name} /> : s.name}
                                            </a>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </Modal>
        </>
    );
}

Products.layout = (page) => <SiteLayout>{page}</SiteLayout>;