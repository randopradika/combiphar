import { Head } from '@inertiajs/react';
import SiteLayout from '../Layouts/SiteLayout';

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
                <section className="section sport-detail" key={i}>
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

                        {team.gallery.length > 0 && (
                            <div className="sport-gallery rv">
                                {team.gallery.map((img, j) => (
                                    <div
                                        className="sport-gallery__item"
                                        key={j}
                                        style={{ backgroundImage: `url('${img}')` }}
                                    ></div>
                                ))}
                            </div>
                        )}
                    </div>
                </section>
            ))}
        </>
    );
}

SportsDetail.layout = (page) => <SiteLayout>{page}</SiteLayout>;
