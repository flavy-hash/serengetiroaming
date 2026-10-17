// Phone/tablet menu: a drawer that slides in from the right over a blurred page.
// Opened by the header ☰ button and the bottom bar's "Menu"; closed by ✕, the backdrop,
// Escape, following a link, or growing the window to desktop size.
export function initMobileMenu() {
    const menu = document.querySelector('[data-mobile-menu]');
    if (!menu) return;

    const drawer = menu.querySelector('.mobile-menu__drawer');
    const openers = [...document.querySelectorAll('[data-mobile-menu-open]')];
    const sections = [...menu.querySelectorAll('[data-mobile-section]')];
    const langToggle = menu.querySelector('[data-mobile-lang]');
    const desktop = window.matchMedia('(min-width: 1024px)');
    let lastOpener = null;
    let closeTimer;

    const isOpen = () => menu.classList.contains('is-open');

    const setSection = (button, open) => {
        button.setAttribute('aria-expanded', String(open));
        const list = document.getElementById(button.getAttribute('aria-controls'));
        if (list) list.hidden = !open;
    };

    const setLang = (open) => {
        if (!langToggle) return;
        langToggle.setAttribute('aria-expanded', String(open));
        document.getElementById(langToggle.getAttribute('aria-controls')).hidden = !open;
    };

    const open = (opener) => {
        if (isOpen()) return;
        clearTimeout(closeTimer);
        lastOpener = opener || null;

        // Start with only the section the visitor is in expanded.
        sections.forEach((s) => setSection(s, s.classList.contains('is-current')));
        setLang(false);

        menu.hidden = false;
        // Next frame, so the slide-in transition runs from the hidden state.
        requestAnimationFrame(() => requestAnimationFrame(() => menu.classList.add('is-open')));

        openers.forEach((o) => o.setAttribute('aria-expanded', 'true'));
        document.documentElement.classList.add('mobile-menu-open');
        menu.querySelector('.mobile-menu__close')?.focus({ preventScroll: true });
    };

    const close = ({ restoreFocus = true } = {}) => {
        if (!isOpen()) return;
        menu.classList.remove('is-open');
        openers.forEach((o) => o.setAttribute('aria-expanded', 'false'));
        document.documentElement.classList.remove('mobile-menu-open');

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        closeTimer = setTimeout(() => { menu.hidden = true; }, reduced ? 0 : 320);

        if (restoreFocus && lastOpener) lastOpener.focus({ preventScroll: true });
    };

    openers.forEach((opener) => opener.addEventListener('click', () => (isOpen() ? close() : open(opener))));
    menu.querySelectorAll('[data-mobile-menu-close]').forEach((el) => el.addEventListener('click', () => close()));

    // One section open at a time.
    sections.forEach((button) => button.addEventListener('click', () => {
        const opening = button.getAttribute('aria-expanded') !== 'true';
        sections.forEach((s) => setSection(s, false));
        setSection(button, opening);
        if (opening) button.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }));

    langToggle?.addEventListener('click', () => setLang(langToggle.getAttribute('aria-expanded') !== 'true'));
    menu.addEventListener('language:changed', () => setLang(false));

    // Following a link (including #anchors on the same page) closes the menu.
    menu.querySelectorAll('a[href]').forEach((a) => a.addEventListener('click', () => close({ restoreFocus: false })));

    document.addEventListener('keydown', (e) => {
        if (!isOpen()) return;

        if (e.key === 'Escape') {
            close();
            return;
        }

        // Keep Tab inside the drawer while it is open.
        if (e.key === 'Tab') {
            const focusable = [...drawer.querySelectorAll('a[href], button:not([disabled])')].filter((el) => el.offsetParent !== null);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    desktop.addEventListener('change', () => { if (desktop.matches) close({ restoreFocus: false }); });
}
