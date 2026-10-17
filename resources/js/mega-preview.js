// Mega menu previews: pointing at (or tabbing to) a link in a dropdown swaps the
// photo/heading/text/button on the right to that link's preview.
export function initMegaPreviews() {
    document.querySelectorAll('[data-mega-panel]').forEach((panel) => {
        const links = [...panel.querySelectorAll('[data-mega-target]')];
        const previews = [...panel.querySelectorAll('[data-mega-preview]')];
        if (!previews.length) return;

        const fallback = previews.find((p) => p.hasAttribute('data-mega-default')) || previews[0];

        const show = (id, activeLink = null) => {
            const target = previews.find((p) => p.id === id) || fallback;
            previews.forEach((p) => { p.hidden = p !== target; });
            links.forEach((a) => a.classList.toggle('is-active', a === activeLink));
        };

        links.forEach((link) => {
            const select = () => show(link.dataset.megaTarget, link);
            link.addEventListener('mouseenter', select);
            link.addEventListener('focus', select);
        });

        panel.addEventListener('menu:open', () => {
            show(fallback.id);
            // The panel is open now, so fetch every preview photo straight away.
            panel.querySelectorAll('img[loading="lazy"]').forEach((img) => { img.loading = 'eager'; });
        });
    });
}
