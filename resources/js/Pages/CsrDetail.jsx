import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';

export default function CsrDetail({ program, topics = [] }) {
    const { props: { t, locale, nav, flash } } = usePage();
    const en = locale === 'en';
    const [active, setActive] = useState(0);
    const hasTopics = topics.length > 0;
    const content = hasTopics ? (topics[active] ?? topics[0]) : program;

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '', email: '', phone: '', subject: '', message: '',
    });
    const submit = (e) => {
        e.preventDefault();
        post(nav.contact, { preserveScroll: true, onSuccess: () => reset() });
    };

    return (
        <>
            <Head title={`${content.title} — Combiphar`} />

            <section
                className="banner banner--about"
                style={program.image ? { backgroundImage: `url('${program.image}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}
            >
                <div className="container">
                    <h1 className="display">{program.title}</h1>
                    <span className="banner__crumb" style={{ marginTop: 14, marginBottom: 0 }}>
                        <Link href={nav.csr}>{t.nav.csr}</Link> &rsaquo; {program.title}
                        {hasTopics && <> &rsaquo; <strong>{content.title}</strong></>}
                    </span>
                </div>
            </section>

            {hasTopics && (
                <nav className="subnav" aria-label="CSR topics">
                    <div className="container subnav__inner">
                        {topics.map((tp, i) => (
                            <button
                                key={tp.slug || i}
                                type="button"
                                className={i === active ? 'on' : ''}
                                onClick={() => setActive(i)}
                            >
                                {tp.title}
                            </button>
                        ))}
                    </div>
                </nav>
            )}

            <section className="section">
                <div className="container">
                    <article className="article-body csr-detail rv">
                        <h2 className="display" style={{ color: 'var(--purple-700)', marginBottom: 28 }}>{content.title}</h2>
                        <div dangerouslySetInnerHTML={{ __html: content.body || '' }} />
                    </article>
                </div>
            </section>

            <section className="section contact-hero-section">
                <div className="container">
                    <div className="contact-intro-grid">
                        <div className="contact-copy rv">
                            <h2 className="display">{en ? 'Have a Question?' : 'Ada Pertanyaan?'}</h2>
                            <p>{en ? 'Contact us directly by filling out the form.' : 'Hubungi kami langsung dengan mengisi formulir.'}</p>
                        </div>

                        <form className="form-wrap rv contact-form-card" onSubmit={submit}>
                            {flash?.contact_success && (
                                <div className="form-success">
                                    {en ? 'Thank you! Your message has been sent.' : 'Terima kasih! Pesan Anda telah terkirim.'}
                                </div>
                            )}
                            <div className="form contact-form contact-form--image3">
                                <div className="field">
                                    <label htmlFor="nm">{en ? 'Your Name' : 'Nama Anda'} *</label>
                                    <input id="nm" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Type your name" required />
                                    {errors.name && <small>{errors.name}</small>}
                                </div>
                                <div className="field">
                                    <label htmlFor="em">Email Address *</label>
                                    <input id="em" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} placeholder="Type your email address" required />
                                    {errors.email && <small>{errors.email}</small>}
                                </div>
                                <div className="field">
                                    <label htmlFor="sb">{en ? 'Subject' : 'Subjek'} *</label>
                                    <input id="sb" value={data.subject} onChange={(e) => setData('subject', e.target.value)} placeholder="Type your subject" />
                                </div>
                                <div className="field">
                                    <label htmlFor="ph">{en ? 'Phone Number' : 'Nomor Telepon'}</label>
                                    <input id="ph" value={data.phone} onChange={(e) => setData('phone', e.target.value)} placeholder="Type your phone number" />
                                </div>
                                <div className="field full">
                                    <label htmlFor="msg">{en ? 'Message' : 'Pesan'} *</label>
                                    <textarea id="msg" value={data.message} onChange={(e) => setData('message', e.target.value)} placeholder="Enter your message here ..." required />
                                    {errors.message && <small>{errors.message}</small>}
                                </div>
                                <div className="full contact-form__meta"></div>

                                <div className="full contact-form__actions">
                                    <label className="contact-consent">
                                        <input type="checkbox" required />
                                        <span>
                                            {en ? 'I have read and agree with ' : 'Saya telah membaca dan menyetujui '}
                                            <a href="/terms-of-use">Terms of Services</a> and <a href="/privacy-notice">Privacy Policy</a>
                                        </span>
                                    </label>
                                    <button className="btn btn--outline contact-submit" type="submit" style={{ justifyContent: 'center' }} disabled={processing}>
                                        {en ? 'Send Message' : 'Kirim Pesan'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </>
    );
}

CsrDetail.layout = (page) => <SiteLayout>{page}</SiteLayout>;
