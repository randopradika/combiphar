import { Head, Link, usePage } from '@inertiajs/react';
import SiteLayout from '../Layouts/SiteLayout';

export default function NewsDetail({ article, others }) {
    const { props: { locale, nav } } = usePage();
    const en = locale === 'en';

    return (
        <>
            <Head title={`${article.title} — Combiphar`} />

            <section className="detail-hero" style={article.cover ? { backgroundImage: `url('${article.cover}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}>
                <div className="detail-hero__overlay"></div>
                <div className="container">
                    <span className="detail-hero__cat">{article.category === 'pembaruan_korporasi' ? (en ? 'Investor Updates' : 'Pembaruan Korporasi') : (en ? 'Health Info' : 'Info Kesehatan')}</span>
                    <h1 className="display article__title">{article.title}</h1>
                    <span className="detail-hero__date">{article.date}</span>
                </div>
            </section>

            <section className="section">
                <div className="container article-layout">
                    <article className="article-body rv">
                        {article.excerpt && <p className="article-lead">{article.excerpt}</p>}
                        <div dangerouslySetInnerHTML={{ __html: article.body || '' }} />
                        <Link className="btn btn--outline" href={nav.news} style={{ marginTop: 24 }}>&larr; {en ? 'Back to News' : 'Kembali ke Berita'}</Link>
                    </article>
                    <aside className="article-aside rv">
                        <h2>{en ? 'Other News' : 'Berita Lainnya'}</h2>
                        {others.map((o) => (
                            <Link className="mini-card" href={o.url} key={o.slug}>
                                <div className="mini-card__img" style={o.cover ? { backgroundImage: `url('${o.cover}')` } : {}}></div>
                                <div><span>{o.date}</span><h4>{o.title}</h4></div>
                            </Link>
                        ))}
                    </aside>
                </div>
            </section>
        </>
    );
}

NewsDetail.layout = (page) => <SiteLayout>{page}</SiteLayout>;