// Photo gallery on a stay's page: click a photo to see it large, then browse with the arrows,
// the keyboard (← → Esc) or a swipe.
export function initGallery() {
    const gallery = document.querySelector('[data-gallery]');
    const dialog = document.getElementById('lightbox');
    if (!gallery || !dialog) return;

    const items = [...gallery.querySelectorAll('[data-gallery-item]')];
    const img = dialog.querySelector('[data-lightbox-img]');
    const count = dialog.querySelector('[data-lightbox-count]');
    let index = 0;

    const show = (i) => {
        index = (i + items.length) % items.length;
        img.src = items[index].dataset.src;
        img.alt = items[index].querySelector('img')?.alt || '';
        count.textContent = `${index + 1} / ${items.length}`;
    };

    items.forEach((item, i) => item.addEventListener('click', () => {
        show(i);
        dialog.showModal();
    }));

    dialog.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(index - 1));
    dialog.querySelector('[data-lightbox-next]').addEventListener('click', () => show(index + 1));
    dialog.querySelector('[data-lightbox-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => { img.removeAttribute('src'); });

    dialog.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') show(index - 1);
        if (e.key === 'ArrowRight') show(index + 1);
    });

    let startX = null;
    dialog.addEventListener('touchstart', (e) => { startX = e.changedTouches[0].clientX; }, { passive: true });
    dialog.addEventListener('touchend', (e) => {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        startX = null;
        if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
    });

    if (items.length < 2) dialog.classList.add('lightbox--single');
}
