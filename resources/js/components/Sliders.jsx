import { useEffect, useRef, useState } from 'react';

const ArrowLeft = () => <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M15 18l-6-6 6-6"/></svg>;
const ArrowRight = () => <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 6l6 6-6 6"/></svg>;

function useAutoplay(cb, delay, deps = []) {
    const ref = useRef(null);
    useEffect(() => {
        if (!delay) return;
        ref.current = setInterval(cb, delay);
        return () => clearInterval(ref.current);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps);
    return {
        pause: () => clearInterval(ref.current),
        resume: () => { clearInterval(ref.current); ref.current = setInterval(cb, delay); },
    };
}

export function ImpactSlider({ items }) {
    const track = useRef(null);
    const [active, setActive] = useState(0);

    const go = (i) => {
        const el = track.current;
        if (!el) return;
        const n = ((i % items.length) + items.length) % items.length;
        const slide = el.children[n];
        if (slide) el.scrollTo({ left: slide.offsetLeft - el.offsetLeft, behavior: 'smooth' });
    };
    const { pause, resume } = useAutoplay(() => go(currentRef.current + 1), 3000, [items.length]);
    const currentRef = useRef(0);
    useEffect(() => { currentRef.current = active; }, [active]);

    const onScroll = () => {
        const el = track.current;
        if (!el) return;
        let idx = 0, min = Infinity;
        Array.from(el.children).forEach((s, i) => {
            const d = Math.abs(s.offsetLeft - el.offsetLeft - el.scrollLeft);
            if (d < min) { min = d; idx = i; }
        });
        setActive(idx);
    };

    return (
        <div className="impact slider rv" onMouseEnter={pause} onMouseLeave={resume}>
            <div className="impact__track slider__track" ref={track} onScroll={onScroll}>
                {items.map((p, i) => (
                    <article className="impact-card" key={i}>
                        <div className="impact-card__media" style={p.image ? { backgroundImage: `url('${p.image}')` } : {}}></div>
                        <div className="impact-card__panel">
                            <h3 className="display">{p.title}</h3>
                            <p>{p.body}</p>
                        </div>
                    </article>
                ))}
            </div>
            <div className="dots impact__dots">
                {items.map((_, i) => (
                    <button key={i} type="button" className={'dot' + (i === active ? ' active' : '')} aria-label={`Slide ${i + 1}`} onClick={() => go(i)} />
                ))}
            </div>
        </div>
    );
}

export function MilestoneSlider({ items }) {
    const track = useRef(null);
    const [active, setActive] = useState(0);
    const currentRef = useRef(0);
    useEffect(() => { currentRef.current = active; }, [active]);

    const go = (i) => {
        const el = track.current;
        if (!el) return;
        const n = Math.max(0, Math.min(items.length - 1, i));
        const s = el.children[n];
        if (s) el.scrollTo({ left: s.offsetLeft - (el.clientWidth - s.clientWidth) / 2, behavior: 'smooth' });
    };
    const { pause, resume } = useAutoplay(() => go((currentRef.current + 1) % items.length), 4000, [items.length]);

    const onScroll = () => {
        const el = track.current;
        if (!el) return;
        const mid = el.scrollLeft + el.clientWidth / 2;
        let idx = 0, min = Infinity;
        Array.from(el.children).forEach((s, i) => {
            const c = s.offsetLeft + s.clientWidth / 2;
            const d = Math.abs(c - mid);
            if (d < min) { min = d; idx = i; }
        });
        setActive(idx);
    };

    const cur = items[active] || items[0] || {};

    return (
        <div className="milestone rv" onMouseEnter={pause} onMouseLeave={resume}>
            <div className="container">
                <div className="milestone__track" ref={track} onScroll={onScroll}>
                    {items.map((m, i) => (
                        <figure className={'milestone__slide' + (i === active ? ' is-active' : '')} key={i}>
                            <div className="milestone__img" style={m.photo ? { backgroundImage: `url('${m.photo}')` } : {}}></div>
                        </figure>
                    ))}
                </div>
                <div className="milestone__bar"><span className="milestone__bar-fill" style={{ width: `${((active + 1) / items.length) * 100}%` }}></span></div>
                <div className="milestone__foot">
                    <div className="milestone__year display">{cur.year}</div>
                    <div className="milestone__foot-right">
                        <p className="milestone__caption">{cur.caption}</p>
                        <div className="milestone__nav">
                            <button className="arrow-btn arrow-btn--sm" onClick={() => go(active - 1)} aria-label="Previous"><ArrowLeft /></button>
                            <button className="arrow-btn arrow-btn--sm" onClick={() => go(active + 1)} aria-label="Next"><ArrowRight /></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}