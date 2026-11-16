/* KAGE 影 SUPPLY: interface motion. Plain JS, no libraries. */
(() => {
    const root = document.documentElement;
    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = matchMedia('(pointer: fine)').matches;
    const store = {
        get: (k) => { try { return sessionStorage.getItem(k); } catch (e) { return null; } },
        set: (k, v) => { try { sessionStorage.setItem(k, v); } catch (e) { /* storage blocked */ } },
    };

    /* ---- First-visit intro ---- */
    if (root.classList.contains('show-intro')) {
        store.set('kage-intro', '1');
        setTimeout(() => root.classList.add('intro-done'), reduceMotion ? 0 : 1500);
        setTimeout(() => root.classList.remove('show-intro'), reduceMotion ? 0 : 2500);
    }

    /* ---- Scroll reveal ---- */
    const revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && !reduceMotion) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        // Wait for the intro to lift before revealing what is on screen.
        const delay = root.classList.contains('show-intro') ? 1300 : 0;
        setTimeout(() => revealEls.forEach((el) => io.observe(el)), delay);
    } else {
        revealEls.forEach((el) => el.classList.add('is-in'));
    }

    /* ---- Page transition wipe on internal links ---- */
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        const url = new URL(a.href, location.href);
        if (url.origin !== location.origin || a.target === '_blank' || a.hasAttribute('download')) return;
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
        if (reduceMotion) return;
        e.preventDefault();
        store.set('kage-wipe', '1');
        root.classList.add('wipe-leave');
        setTimeout(() => { location.href = url.href; }, 420);
    });
    // Coming back with the browser's back button: clear the covering panel.
    addEventListener('pageshow', (e) => { if (e.persisted) root.classList.remove('wipe-leave'); });

    /* ---- Toasts auto-dismiss ---- */
    document.querySelectorAll('.toast').forEach((toast, i) => {
        const dismiss = () => {
            toast.classList.add('is-leaving');
            toast.addEventListener('animationend', () => toast.remove(), { once: true });
        };
        toast.addEventListener('click', dismiss);
        setTimeout(dismiss, 4200 + i * 400);
    });

    /* ---- Mobile menu ---- */
    const toggle = document.querySelector('.menu-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const open = root.classList.toggle('menu-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    /* ---- Quantity stepper ---- */
    document.querySelectorAll('[data-step]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input[type=number]');
            const min = Number(input.min || 1);
            const max = Number(input.max || 999);
            input.value = Math.min(max, Math.max(min, Number(input.value || 1) + Number(btn.dataset.step)));
            input.animate([{ transform: 'scale(1.35)', color: '#ffe14d' }, { transform: 'scale(1)' }], { duration: 300, easing: 'ease-out' });
        });
    });

    if (reduceMotion || !finePointer) return;

    /* ---- Custom cursor ---- */
    const dot = document.createElement('div');
    const ring = document.createElement('div');
    dot.className = 'cursor-dot';
    ring.className = 'cursor-ring';
    document.body.append(dot, ring);
    root.classList.add('has-cursor');

    let mx = innerWidth / 2, my = innerHeight / 2, rx = mx, ry = my;
    addEventListener('mousemove', (e) => {
        mx = e.clientX; my = e.clientY;
        dot.style.transform = `translate(${mx}px, ${my}px)`;
    });
    addEventListener('mousedown', () => ring.classList.add('is-down'));
    addEventListener('mouseup', () => ring.classList.remove('is-down'));
    document.addEventListener('mouseover', (e) => {
        ring.classList.toggle('is-hover', !!e.target.closest('a, button, [data-tilt], .chip, .cat'));
    });
    (function follow() {
        rx += (mx - rx) * 0.18;
        ry += (my - ry) * 0.18;
        ring.style.transform = `translate(${rx}px, ${ry}px)`;
        requestAnimationFrame(follow);
    })();

    /* ---- 3D tilt + glare on product images ---- */
    document.querySelectorAll('[data-tilt]').forEach((el) => {
        const target = el.matches('.card') ? el.querySelector('.card-media') : el;
        el.addEventListener('mousemove', (e) => {
            const r = target.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width;
            const py = (e.clientY - r.top) / r.height;
            target.style.setProperty('--ry', `${(px - 0.5) * 14}deg`);
            target.style.setProperty('--rx', `${(0.5 - py) * 12}deg`);
            target.style.setProperty('--gx', `${px * 100}%`);
            target.style.setProperty('--gy', `${py * 100}%`);
        });
        el.addEventListener('mouseleave', () => {
            target.style.setProperty('--rx', '0deg');
            target.style.setProperty('--ry', '0deg');
        });
    });

    /* ---- Magnetic buttons ---- */
    document.querySelectorAll('[data-magnetic]').forEach((el) => {
        el.addEventListener('mousemove', (e) => {
            const r = el.getBoundingClientRect();
            const x = e.clientX - (r.left + r.width / 2);
            const y = e.clientY - (r.top + r.height / 2);
            el.style.transform = `translate(${x * 0.25}px, ${y * 0.35}px)`;
        });
        el.addEventListener('mouseleave', () => { el.style.transform = ''; });
    });

    /* ---- Hero parallax ---- */
    const hero = document.querySelector('[data-parallax]');
    if (hero) {
        const layers = hero.querySelectorAll('[data-depth]');
        hero.addEventListener('mousemove', (e) => {
            const x = e.clientX / innerWidth - 0.5;
            const y = e.clientY / innerHeight - 0.5;
            layers.forEach((layer) => {
                const d = Number(layer.dataset.depth) * 60;
                layer.style.transform = `translate(${x * d}px, ${y * d}px)`;
            });
        });
        addEventListener('scroll', () => {
            const s = scrollY;
            if (s > innerHeight) return;
            hero.style.setProperty('--scroll', s);
            const kanji = hero.querySelector('.hero-kanji');
            if (kanji) kanji.style.rotate = `${s * 0.02}deg`;
        }, { passive: true });
    }
})();
