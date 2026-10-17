// Site header: mega dropdowns (hover on desktop, click/keyboard everywhere) and a solid
// header once the page is scrolled. The phone/tablet menu is in mobile-menu.js.
export function initNavbar() {
    const header = document.getElementById('siteHeader');
    const nav = document.getElementById('mainNav');

    if (!header || !nav) return;

    const triggers = [...nav.querySelectorAll('[data-menu-trigger]')];
    const desktop = window.matchMedia('(min-width: 1024px) and (hover: hover)');
    const hoverEnabled = nav.hasAttribute('data-menu-hover');
    let closeTimer;

    const panelFor = (trigger) => document.getElementById(trigger.getAttribute('aria-controls'));

    const setPanel = (trigger, open) => {
        const panel = panelFor(trigger);
        const wasOpen = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', String(open));
        if (!panel) return;
        panel.hidden = !open;
        // Let the preview script reset to the default preview and load its photos.
        if (open && !wasOpen) panel.dispatchEvent(new CustomEvent('menu:open'));
    };

    // Only one panel open at a time.
    const closeAll = (except) => {
        triggers.forEach((t) => { if (t !== except) setPanel(t, false); });
        if (desktop.matches) header.classList.toggle('is-open', !!except);
    };

    const open = (trigger) => { closeAll(trigger); setPanel(trigger, true); };

    triggers.forEach((t) => {
        // Click works on every device; Enter/Space on the <button> also fire click.
        t.addEventListener('click', () => {
            const isOpen = t.getAttribute('aria-expanded') === 'true';
            isOpen ? closeAll() : open(t);
        });

        if (!hoverEnabled) return;
        // Hover on desktop, with a short delay so moving into the panel doesn't close it.
        const li = t.parentElement;
        li.addEventListener('mouseenter', () => { if (desktop.matches) { clearTimeout(closeTimer); open(t); } });
        li.addEventListener('mouseleave', () => { if (desktop.matches) closeTimer = setTimeout(() => closeAll(), 150); });
    });

    // Escape closes the open panel and returns focus to its button.
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const openTrigger = triggers.find((t) => t.getAttribute('aria-expanded') === 'true');
        closeAll();
        if (openTrigger) openTrigger.focus();
    });

    // A click outside the header closes any open panel.
    document.addEventListener('click', (e) => { if (!header.contains(e.target)) closeAll(); });

    desktop.addEventListener('change', () => closeAll());

    // Transparent over the hero photo, solid once the page has scrolled.
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 0);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}
