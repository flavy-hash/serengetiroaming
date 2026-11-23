// Sideways card carousel (home page reviews): drag/swipe or use the arrows; the arrows hide when
// there is nothing more to scroll to in that direction.
export function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach((root) => {
        const track = root.querySelector('[data-carousel-track]');
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');
        if (!track) return;

        const step = () => {
            const slide = track.querySelector('[data-carousel-slide]');
            return slide ? slide.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth;
        };

        const update = () => {
            const max = track.scrollWidth - track.clientWidth - 2;
            const atStart = track.scrollLeft <= 8;
            const atEnd = track.scrollLeft >= max - 6;
            if (prev) prev.hidden = atStart;
            if (next) next.hidden = atEnd;
            root.classList.toggle('has-prev', !atStart);
            root.classList.toggle('has-next', !atEnd);
        };

        const move = (dir) => track.scrollBy({ left: dir * step(), behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });

        prev?.addEventListener('click', () => move(-1));
        next?.addEventListener('click', () => move(1));
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        track.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') { e.preventDefault(); move(1); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); move(-1); }
        });
        update();
    });
}
