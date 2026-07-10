// Scroll reveal
const revealEls = document.querySelectorAll('.rv');
if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
        });
    }, { threshold: 0.12 });
    revealEls.forEach((el) => io.observe(el));
} else {
    revealEls.forEach((el) => el.classList.add('is-in'));
}

// Mobile menu
const burger = document.getElementById('burger');
const menu = document.getElementById('mobilemenu');
if (burger && menu) {
    const closeBtn = menu.querySelector('[data-close]');
    const open = () => { menu.classList.add('open'); document.body.style.overflow = 'hidden'; burger.setAttribute('aria-expanded', 'true'); };
    const shut = () => { menu.classList.remove('open'); document.body.style.overflow = ''; burger.setAttribute('aria-expanded', 'false'); };
    burger.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', shut);
    menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', shut));
}

// Generic sliders (Our Impact, Milestones) with optional autoplay
document.querySelectorAll('[data-slider]').forEach((slider) => {
    const track = slider.querySelector('[data-track]');
    if (!track) return;
    const prev = slider.querySelector('[data-prev]');
    const next = slider.querySelector('[data-next]');
    const dotsWrap = slider.querySelector('[data-dots]');
    const slides = Array.from(track.children);
    const current = () => {
        let idx = 0, min = Infinity;
        slides.forEach((s, i) => {
            const d = Math.abs(s.offsetLeft - track.offsetLeft - track.scrollLeft);
            if (d < min) { min = d; idx = i; }
        });
        return idx;
    };
    const go = (i) => {
        const s = slides[Math.max(0, Math.min(slides.length - 1, i))];
        if (s) track.scrollTo({ left: s.offsetLeft - track.offsetLeft, behavior: 'smooth' });
    };
    if (prev) prev.addEventListener('click', () => go(current() - 1));
    if (next) next.addEventListener('click', () => go(current() + 1));
    if (dotsWrap) {
        slides.forEach((_, i) => {
            const b = document.createElement('button');
            b.className = 'dot' + (i === 0 ? ' active' : '');
            b.type = 'button';
            b.setAttribute('aria-label', 'Slide ' + (i + 1));
            b.addEventListener('click', () => go(i));
            dotsWrap.appendChild(b);
        });
        track.addEventListener('scroll', () => {
            const c = current();
            dotsWrap.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i === c));
        }, { passive: true });
    }
    // Autoplay
    const delay = parseInt(slider.dataset.autoplay || '0', 10);
    if (delay > 0 && slides.length > 1) {
        let timer = null;
        const start = () => { timer = setInterval(() => go((current() + 1) % slides.length), delay); };
        const stop = () => { if (timer) { clearInterval(timer); timer = null; } };
        start();
        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
    }
});

// Scroll-aware overlay navbar (home)
const navEl = document.getElementById('nav');
if (navEl && navEl.classList.contains('nav--overlay')) {
    const onScroll = () => navEl.classList.toggle('nav--scrolled', window.scrollY > 40);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}
// Hero scroll cue — visible only at the very top of the page
const heroScroll = document.querySelector('.hero__scroll');
if (heroScroll) {
    const toggleCue = () => heroScroll.classList.toggle('is-hidden', window.scrollY > 30);
    toggleCue();
    window.addEventListener('scroll', toggleCue, { passive: true });
}
// Milestone carousel: centred slide active (colour), sides desaturated, bar + caption
document.querySelectorAll('[data-milestone]').forEach((root) => {
    const track = root.querySelector('[data-track]');
    if (!track) return;
    const slides = Array.from(track.children);
    const total = slides.length;
    const yearOut = root.querySelector('[data-year-out]');
    const capOut = root.querySelector('[data-caption-out]');
    const bar = root.querySelector('[data-bar]');
    const prev = root.querySelector('[data-prev]');
    const next = root.querySelector('[data-next]');
    const centerIndex = () => {
        const mid = track.scrollLeft + track.clientWidth / 2;
        let idx = 0, min = Infinity;
        slides.forEach((s, i) => {
            const c = s.offsetLeft + s.clientWidth / 2;
            const d = Math.abs(c - mid);
            if (d < min) { min = d; idx = i; }
        });
        return idx;
    };
    const update = () => {
        const i = centerIndex();
        slides.forEach((s, k) => s.classList.toggle('is-active', k === i));
        const s = slides[i];
        if (s && yearOut) yearOut.textContent = s.dataset.year || '';
        if (s && capOut) capOut.textContent = s.dataset.caption || '';
        if (bar) bar.style.width = (((i + 1) / total) * 100) + '%';
    };
    const go = (i) => {
        const s = slides[Math.max(0, Math.min(total - 1, i))];
        if (s) track.scrollTo({ left: s.offsetLeft - (track.clientWidth - s.clientWidth) / 2, behavior: 'smooth' });
    };
    if (prev) prev.addEventListener('click', () => go(centerIndex() - 1));
    if (next) next.addEventListener('click', () => go(centerIndex() + 1));
    track.addEventListener('scroll', () => window.requestAnimationFrame(update), { passive: true });
    update();
    const delay = parseInt(root.dataset.autoplay || '0', 10);
    if (delay > 0 && total > 1) {
        let timer = setInterval(() => go((centerIndex() + 1) % total), delay);
        root.addEventListener('mouseenter', () => clearInterval(timer));
        root.addEventListener('mouseleave', () => { timer = setInterval(() => go((centerIndex() + 1) % total), delay); });
    }
});
// Product category tabs
document.querySelectorAll('[data-tabs]').forEach((nav) => {
    const btns = nav.querySelectorAll('[data-tab]');
    btns.forEach((b) => b.addEventListener('click', () => {
        btns.forEach((x) => x.classList.toggle('on', x === b));
        const key = b.dataset.tab;
        document.querySelectorAll('[data-panel]').forEach((p) => { p.hidden = p.dataset.panel !== key; });
    }));
});

// Product detail modal
const productModal = document.getElementById('product-modal');
if (productModal) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || ''; };
    const openModal = (d) => {
        set('pm-name', d.name);
        set('pm-cat', d.cat);
        set('pm-desc', d.desc);
        const im = document.getElementById('pm-img');
        if (im) im.style.backgroundImage = d.img ? "url('" + d.img + "')" : '';
        productModal.classList.add('open');
        productModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    const closeModal = () => {
        productModal.classList.remove('open');
        productModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };
    document.querySelectorAll('[data-product]').forEach((c) => c.addEventListener('click', () => openModal(c.dataset)));
    productModal.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', closeModal));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
}
// Product search + sort (per category panel)
document.querySelectorAll('[data-panel]').forEach((panel) => {
    const grid = panel.querySelector('[data-grid]');
    if (!grid) return;
    const search = panel.querySelector('[data-search]');
    const sort = panel.querySelector('[data-sort]');
    const empty = panel.querySelector('[data-empty]');
    const cards = Array.from(grid.querySelectorAll('.pcard'));
    const apply = () => {
        const q = (search && search.value ? search.value : '').toLowerCase().trim();
        let visible = cards.filter((c) => (c.dataset.name || '').toLowerCase().includes(q));
        const s = sort ? sort.value : 'az';
        visible.sort((a, b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''));
        if (s === 'za') visible.reverse();
        cards.forEach((c) => { c.style.display = 'none'; });
        visible.forEach((c) => { c.style.display = ''; grid.appendChild(c); });
        if (empty) empty.hidden = visible.length !== 0;
    };
    if (search) search.addEventListener('input', apply);
    if (sort) sort.addEventListener('change', apply);
});

// Board member bio modal
const boardModal = document.getElementById('board-modal');
if (boardModal) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || ''; };
    const openBoard = (d) => {
        set('bm-name', d.name);
        set('bm-role', d.role);
        set('bm-bio', d.bio || '');
        const im = document.getElementById('bm-img');
        if (im) im.style.backgroundImage = d.img ? "url('" + d.img + "')" : '';
        boardModal.classList.add('open');
        boardModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    const closeBoard = () => {
        boardModal.classList.remove('open');
        boardModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };
    document.querySelectorAll('[data-board]').forEach((c) => c.addEventListener('click', () => openBoard(c.dataset)));
    boardModal.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', closeBoard));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeBoard(); });
}
// Facilities modal + office filter (About)
const facModal = document.getElementById('facilities-modal');
if (facModal) {
    const openFac = () => { facModal.classList.add('open'); facModal.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; };
    const closeFac = () => { facModal.classList.remove('open'); facModal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };
    document.querySelectorAll('[data-facilities]').forEach((b) => b.addEventListener('click', openFac));
    facModal.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', closeFac));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeFac(); });
}

const officeGrid = document.querySelector('[data-office-grid]');
if (officeGrid) {
    const cityS = document.querySelector('[data-office-city]');
    const catS = document.querySelector('[data-office-cat]');
    const empty = document.querySelector('[data-office-empty]');
    const cards = Array.from(officeGrid.querySelectorAll('[data-office]'));
    const apply = () => {
        const city = cityS ? cityS.value : '';
        const cat = catS ? catS.value : '';
        let n = 0;
        cards.forEach((c) => {
            const ok = (!city || c.dataset.city === city) && (!cat || c.dataset.cat === cat);
            c.style.display = ok ? '' : 'none';
            if (ok) n++;
        });
        if (empty) empty.hidden = n !== 0;
    };
    if (cityS) cityS.addEventListener('change', apply);
    if (catS) catS.addEventListener('change', apply);
}
// Vacancy detail modal (Careers)
const vacModal = document.getElementById('vac-modal');
if (vacModal) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || ''; };
    const openVac = (d) => {
        set('vm-title', d.title);
        set('vm-meta', d.meta);
        set('vm-desc', d.desc);
        vacModal.classList.add('open');
        vacModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    const closeVac = () => { vacModal.classList.remove('open'); vacModal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; };
    document.querySelectorAll('[data-vacancy]').forEach((r) => r.addEventListener('click', () => openVac(r.dataset)));
    vacModal.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', closeVac));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeVac(); });
}