import { Head, usePage } from '@inertiajs/react';
import SiteLayout from '../Layouts/SiteLayout';

const HubArrow = () => <span className="arrow-btn arrow-btn--dark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>;
const DocIcon = () => <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5M9 13h7M9 17h7"/></svg>;

function DocSection({ id, title, docs, en }) {
    if (!docs.length) return null;
    return (
        <section className="section" id={id} style={{ paddingTop: 0 }}>
            <div className="container">
                <div className="doc-head rv"><DocIcon /><h2>{title}</h2></div>
                <div className="doc-list rv">
                    {docs.map((d, i) => (
                        <div className="doc-row" key={i}>
                            <h3>{d.title || `${d.year ? d.year + ' ' : ''}${title}`}</h3>
                            {d.fileEn && <><a className="doc-act" href={d.fileEn} target="_blank" rel="noopener noreferrer">View EN</a><a className="doc-act" href={d.fileEn} download>Download EN</a></>}
                            {d.fileId && <><a className="doc-act" href={d.fileId} target="_blank" rel="noopener noreferrer">View ID</a><a className="doc-act" href={d.fileId} download>Download ID</a></>}
                            {!d.fileEn && !d.fileId && <span className="doc-act" style={{ opacity: 0.5 }}>{en ? 'File coming soon' : 'Berkas segera hadir'}</span>}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export default function Investor({ page, annual, sustainability, financial, disclosures }) {
    const { props: { t, locale, homeUrl } } = usePage();
    const en = locale === 'en';
    const hub = [['#stock', 'Stock Information'], ['#annual', 'Annual Reports'], ['#sustainability', 'Sustainability Report'], ['#disclosures', 'Information Disclosures']];

    return (
        <>
            <Head title={page?.metaTitle || 'Investor Relations — Combiphar'} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `linear-gradient(120deg,rgba(107,90,142,.5),rgba(58,24,96,.6)),url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.investor}</span>
                    <h1 className="display">Investor Relations</h1>
                </div>
            </section>

            <nav className="subnav" aria-label="Investor submenu">
                <div className="container subnav__inner">
                    <a href="#overview" className="on">Overview</a>
                    <a href="#stock">Stock Information</a>
                    <a href="#annual">Annual Reports</a>
                    <a href="#sustainability">Sustainability</a>
                    <a href="#disclosures">Disclosures</a>
                    <a href="#ir-contact">IR Contact</a>
                </div>
            </nav>

            <section className="section" id="overview">
                <div className="container">
                    <div className="sec-head sec-head--center rv"><span className="eyebrow eyebrow--magenta">Investor Relations</span><h2 className="display">{en ? 'Investor Information Center' : 'Pusat Informasi Investor'}</h2><p>{en ? 'Transparent access to Combiphar financial information, stock performance, and disclosures.' : 'Akses informasi keuangan, kinerja saham, dan keterbukaan informasi Combiphar secara transparan.'}</p></div>
                    <div className="grid grid--4 rv" style={{ marginTop: 44 }}>
                        {hub.map(([href, label]) => (
                            <a className="hub-card" href={href} key={href}><h3>{label}</h3><HubArrow /></a>
                        ))}
                    </div>
                </div>
            </section>

            <section className="section" id="stock" style={{ paddingTop: 0 }}>
                <div className="container">
                    <div className="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M4 19V5m0 14h17M8 16v-5m5 5V8m5 8v-3"/></svg><h2>Stock Information</h2></div>
                    <article className="stock rv">
                        <p className="stock__meta"><b>Bursa Efek Indonesia</b></p>
                        <div className="ir-price"><span className="ticker">COMB</span><span className="exchange">IDX &middot; IDR</span><span className="change">2.290 &#9662; -10 (-0,44%)</span></div>
                        <p className="stock__meta">Quotes Delayed: 10 Minutes &nbsp;&middot;&nbsp; Last Transaction: 6 May 2026, 16:14</p>
                        <div className="stock__grid">
                            <div className="stock__cell"><div className="k">Volume</div><div className="v">574.559.700</div></div>
                            <div className="stock__cell"><div className="k">Day's Range</div><div className="v">2.240 &ndash; 2.430</div></div>
                            <div className="stock__cell"><div className="k">52 Weeks' Range</div><div className="v">765 &ndash; 4.530</div></div>
                            <div className="stock__cell"><div className="k">% Change</div><div className="v down">-0,44%</div></div>
                        </div>
                        <div className="stock__chart">
                            <svg viewBox="0 0 900 260" preserveAspectRatio="none"><defs><linearGradient id="area" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor="#D62E87" stopOpacity=".35"/><stop offset="1" stopColor="#D62E87" stopOpacity="0"/></linearGradient></defs><path d="M0 195 C85 180 95 130 180 142 S320 212 420 160 S560 70 650 100 S790 170 900 88 L900 260 L0 260 Z" fill="url(#area)"/><path d="M0 195 C85 180 95 130 180 142 S320 212 420 160 S560 70 650 100 S790 170 900 88" fill="none" stroke="#D62E87" strokeWidth="5" strokeLinecap="round"/></svg>
                        </div>
                        <p className="stock__note">{en ? 'Stock information is generated from official Bursa Efek Indonesia (BEI) data.' : 'Informasi saham bersumber dari data resmi Bursa Efek Indonesia (BEI).'}</p>
                    </article>
                </div>
            </section>

            <DocSection id="annual" title="Annual Reports" docs={annual} en={en} />
            <DocSection id="sustainability" title="Sustainability Report" docs={sustainability} en={en} />
            <DocSection id="financial" title="Financial Information" docs={financial} en={en} />

            {disclosures.length > 0 && (
                <section className="section" id="disclosures">
                    <div className="container">
                        <div className="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M12 3v18M4 8h16M6 13h12M8 18h8"/></svg><h2>Information Disclosures</h2></div>
                        <div className="grid grid--3 rv">
                            {disclosures.map((d, i) => (
                                <article className="disc-card" key={i}>
                                    <div className="date">{d.year || ''}</div>
                                    <div>
                                        <h3>{d.title}</h3>
                                        {(d.fileId || d.fileEn) && <a className="link-more" href={d.fileId || d.fileEn} target="_blank" rel="noopener noreferrer">Read More</a>}
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            <section className="section" id="ir-contact" style={{ background: 'var(--surface)' }}>
                <div className="container">
                    <div className="doc-head rv"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8"><path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/></svg><h2>Investor Relations Contact</h2></div>
                    <div className="ir-contact-grid rv">
                        <div className="ir-address"><h3>{en ? 'Contact Us' : 'Hubungi Kami'}</h3><p><b>Combiphar Head Office</b></p><p>Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Pusat, DKI Jakarta 12190.</p><p><b>Phone:</b> 0800-1-800088 (Toll Free)</p></div>
                        <div className="ir-people"><h3>IR Contact</h3><div className="ir-person"><div className="ir-avatar">IR</div><div><b>Investor Relations</b><br /><a href="mailto:investor.relations@combiphar.com">investor.relations@combiphar.com</a></div></div></div>
                    </div>
                </div>
            </section>
        </>
    );
}

Investor.layout = (page) => <SiteLayout>{page}</SiteLayout>;