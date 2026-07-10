import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';
import Modal from '../components/Modal';

export default function Contact({ page, vacancies }) {
    const { props: { t, locale, homeUrl, flash } } = usePage();
    const en = locale === 'en';
    const [tab, setTab] = useState(flash.contact_success ? 'kontak' : 'karir');
    const [vac, setVac] = useState(null);
    const { data, setData, post, processing, errors, reset } = useForm({ name: '', email: '', phone: '', subject: '', message: '' });

    const submit = (e) => {
        e.preventDefault();
        post(window.location.pathname, { preserveScroll: true, onSuccess: () => reset() });
    };

    const steps = ['Application Review', 'HR Interview', 'User Interview', 'Assessment', 'Offering', 'Onboarding'];

    return (
        <>
            <Head title={`${t.nav.contact} — Combiphar`} />

            <section className="banner banner--about" style={page?.bannerImage ? { backgroundImage: `url('${page.bannerImage}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="container">
                    <span className="banner__crumb"><a href={homeUrl}>Home</a> &rsaquo; {t.nav.contact}</span>
                    <h1 className="display">{t.nav.contact}</h1>
                </div>
            </section>

            <nav className="subnav" aria-label="Careers and contact">
                <div className="container subnav__inner">
                    <button type="button" className={tab === 'karir' ? 'on' : ''} onClick={() => setTab('karir')}>{en ? 'Careers' : 'Karir'}</button>
                    <button type="button" className={tab === 'kontak' ? 'on' : ''} onClick={() => setTab('kontak')}>{en ? 'Contact' : 'Kontak'}</button>
                </div>
            </nav>

            {tab === 'karir' && (
                <section className="section"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--magenta">{en ? 'Join Us' : 'Bergabung Bersama Kami'}</span><h2 className="display">Available Vacancies</h2><p>{en ? 'Join Combiphar in building a healthier future for Indonesia.' : 'Bergabunglah bersama Combiphar membangun masa depan yang lebih sehat untuk Indonesia.'}</p></div>
                    <div className="grid" style={{ gap: 14, marginTop: 36 }}>
                        {vacancies.length === 0 && <p className="lead">{en ? 'No open positions right now.' : 'Belum ada lowongan saat ini.'}</p>}
                        {vacancies.map((v, i) => (
                            <div className="vac-row rv" key={i} onClick={() => setVac(v)}>
                                <h3>{v.title}</h3>
                                {v.location && <span className="loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>{v.location}</span>}
                                {v.department && <span className="loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>{v.department}</span>}
                                <button type="button" className="btn btn--purple">{en ? 'View Detail' : 'Lihat Detail'}</button>
                            </div>
                        ))}
                    </div>
                    <div className="sec-head sec-head--center rv" style={{ marginTop: 'clamp(48px,5vw,80px)' }}><span className="eyebrow eyebrow--magenta">{en ? 'Selection Process' : 'Proses Seleksi'}</span><h2 className="display">Recruitment Process</h2></div>
                    <div className="steps rv">
                        {steps.map((s, i) => (
                            <span key={s} style={{ display: 'contents' }}>
                                {i > 0 && <span className="sep"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>}
                                <div className="step"><span className="n">{i + 1}</span><p>{s}</p></div>
                            </span>
                        ))}
                    </div>
                </div></section>
            )}

            {tab === 'kontak' && (
                <section className="section"><div className="container">
                    <div className="sec-head rv"><span className="eyebrow eyebrow--magenta">{en ? 'Contact Us' : 'Hubungi Kami'}</span><h2 className="display">Have a Question?</h2><p>{en ? 'Reach us directly by filling in the form below.' : 'Hubungi kami dengan mengisi formulir di bawah.'}</p></div>
                    <div className="grid grid--2" style={{ alignItems: 'start', marginTop: 36 }}>
                        <div className="grid" style={{ gap: 18 }}>
                            <div className="office-card rv" style={{ background: 'var(--grad-card-light)' }}><h4>Combiphar Head Office</h4><p>Jl. Jenderal Sudirman Kav. 52-53, Senayan, Kebayoran Baru, Jakarta Pusat, DKI Jakarta 12190.</p><p style={{ marginTop: 6, fontWeight: 600, color: 'var(--text)' }}>Phone: 0800-1-800088 (Toll Free)</p></div>
                            <div className="office-card rv"><h4>Combi Care Center</h4><p>{en ? 'Consumer information service for product and health questions.' : 'Layanan informasi konsumen untuk pertanyaan seputar produk dan kesehatan.'}</p><p style={{ marginTop: 6, fontWeight: 600, color: 'var(--text)' }}>care@combiphar.com</p></div>
                        </div>
                        <form className="form-wrap rv" onSubmit={submit}>
                            {flash.contact_success && <div className="form-success">{en ? 'Thank you! Your message has been sent.' : 'Terima kasih! Pesan Anda telah terkirim.'}</div>}
                            <div className="form">
                                <div className="field full"><label htmlFor="nm">{en ? 'Your Name' : 'Nama Anda'} *</label><input id="nm" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Type your name" required />{errors.name && <small>{errors.name}</small>}</div>
                                <div className="field"><label htmlFor="em">Email *</label><input id="em" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} placeholder="Type your email" required />{errors.email && <small>{errors.email}</small>}</div>
                                <div className="field"><label htmlFor="ph">{en ? 'Phone' : 'Telepon'}</label><input id="ph" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="Type your phone" /></div>
                                <div className="field full"><label htmlFor="sb">{en ? 'Subject' : 'Subjek'}</label><input id="sb" value={data.subject} onChange={(e) => setData('subject', e.target.value)} placeholder="Type your subject" /></div>
                                <div className="field full"><label htmlFor="msg">{en ? 'Message' : 'Pesan'} *</label><textarea id="msg" value={data.message} onChange={(e) => setData('message', e.target.value)} placeholder="Enter your message here ..." required />{errors.message && <small>{errors.message}</small>}</div>
                                <div className="full"><button className="btn btn--fill" type="submit" disabled={processing}>{en ? 'Send Message' : 'Kirim Pesan'}</button></div>
                            </div>
                        </form>
                    </div>
                </div></section>
            )}

            <section className="warning">
                <div className="container warning__inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                    <div>
                        <h3>{en ? 'Beware of Recruitment Fraud' : 'Waspada Penipuan Rekrutmen'}</h3>
                        <p>{en ? 'Combiphar never charges any fees during recruitment. All official recruitment is conducted only through Combiphar official channels.' : 'Combiphar tidak pernah memungut biaya apa pun selama proses rekrutmen. Seluruh proses rekrutmen resmi hanya dilakukan melalui kanal resmi Combiphar.'}</p>
                    </div>
                </div>
            </section>

            <Modal open={!!vac} onClose={() => setVac(null)} closeLabel={t.close}>
                {vac && (
                    <div className="vac-modal">
                        <h3>{vac.title}</h3>
                        <p style={{ fontWeight: 600, color: 'var(--magenta)', marginBottom: 14 }}>{[vac.location, vac.department].filter(Boolean).join('  ·  ')}</p>
                        <div style={{ whiteSpace: 'pre-line', color: 'var(--text-muted)', lineHeight: 1.7 }}>{vac.description}</div>
                    </div>
                )}
            </Modal>
        </>
    );
}

Contact.layout = (page) => <SiteLayout>{page}</SiteLayout>;