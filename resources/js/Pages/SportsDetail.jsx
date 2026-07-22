import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import SiteLayout from '../Layouts/SiteLayout';

const PER_PAGE = 6;

// One team block: header + a gallery paginated 6 photos per page.
function SportTeam({ team }) {
    const { props: { locale } } = usePage();
    const en = locale === 'en';
    const [page, setPage] = useState(1);

    const gallery = team.gallery ?? [];
    const totalPages = Math.max(1, Math.ceil(gallery.length / PER_PAGE));
    const start = (page - 1) * PER_PAGE;
    const shown = gallery.slice(start, start + PER_PAGE);

    return (
        <section className="section sport-detail">
            <div className="container">
                <div className="sport-detail__head rv">
                    <div className="sport-detail__title">
                        {team.logo && <img className="sport-detail__logo" src={team.logo} alt="" />}
                        <h2 className="display">{team.title}</h2>
                    </div>
                    {team.body && (
                        <div className="sport-detail__desc" dangerouslySetInnerHTML={{ __html: team.body }} />
                    )}
                </div>

                {gallery.length > 0 && (
                    <>
                        <div className="sport-gallery rv">
                            {shown.map((img, j) => (
                                <div
                                    className="sport-gallery__item"
                                    key={start + j}
                                    style={{ backgroundImage: `url('${img}')` }}
                                ></div>
                            ))}
                        </div>

                        {totalPages > 1 && (
                            <div className="career-vacancies__pager rv">
                                <span>{en ? 'Page:' : 'Halaman:'}</span>
                                {Array.from({ length: totalPages }, (_, k) => {
                                    const p = k + 1;
                                    return (
                                        <button
                                            key={p}
                                            type="button"
                                            className={page === p ? 'is-active' : ''}
                                            onClick={() => setPage(p)}
                                            aria-label={`${en ? 'Page' : 'Halaman'} ${p}`}
                                            aria-current={page === p ? 'page' : undefined}
                                        >
                                            {p}
                                        </button>
                                    );
                                })}
                            </div>
                        )}
                    </>
                )}
            </div>
        </section>
    );
}

export default function SportsDetail({ program, teams = [] }) {
    return (
        <>
            <Head title={`${program.title} — Combiphar`} />

            <section
                className="banner banner--about banner--detail"
                style={program.image ? { backgroundImage: `url('${program.image}')`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}
            >
                <div className="container">
                    {/* Title left + description right, no breadcrumb (matches CsrDetail). */}
                    <div className="banner__row">
                        <h1 className="display">{program.title}</h1>
                        {program.subtitle && (
                            <p className="banner__row-sub">{program.subtitle}</p>
                        )}
                    </div>
                </div>
            </section>

            {teams.map((team, i) => (
                <SportTeam team={team} key={i} />
            ))}
        </>
    );
}

SportsDetail.layout = (page) => <SiteLayout>{page}</SiteLayout>;
