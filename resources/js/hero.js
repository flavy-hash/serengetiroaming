// Homepage hero: a muted background video (public/videos/hero-*.mp4).
// Plays on load, with a pause/play button; it also pauses itself while the hero is
// scrolled out of view or the tab is hidden, so it doesn't burn battery or data.
export function initHero() {
    const video = document.getElementById('heroVideo');
    if (!video) return;

    const button = document.getElementById('heroPause');
    let pausedByVisitor = false;
    let inView = true;

    const setButton = (paused) => {
        if (!button) return;
        button.setAttribute('aria-pressed', String(paused));
        button.setAttribute('aria-label', paused ? 'Play background video' : 'Pause background video');
        button.classList.toggle('is-paused', paused);
    };

    // Autoplay can be refused (e.g. battery saver); the poster image stays as the background.
    const play = () => video.play().then(() => setButton(false)).catch(() => setButton(true));
    const shouldPlay = () => !pausedByVisitor && inView && !document.hidden;
    const sync = () => (shouldPlay() ? play() : video.pause());

    button?.addEventListener('click', () => {
        pausedByVisitor = !video.paused;
        if (pausedByVisitor) {
            video.pause();
            setButton(true);
        } else {
            play();
        }
    });

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            inView = entry.isIntersecting;
            sync();
        }, { threshold: 0.1 }).observe(video);
    }

    document.addEventListener('visibilitychange', sync);
    video.addEventListener('hero:resume', sync); // sent when the film popup closes

    sync();
}

// "Watch the film": the full video with sound, in a popup.
export function initFilmDialog() {
    const dialog = document.getElementById('filmDialog');
    const openBtn = document.getElementById('filmOpen');
    if (!dialog || !openBtn) return;

    const frame = document.getElementById('filmFrame');
    const FILM_SRC = '/videos/The Serengeti 4K - Scenic Wildlife Film With African Music.mp4';
    const backgroundVideo = document.getElementById('heroVideo');

    openBtn.addEventListener('click', () => {
        frame.innerHTML = '';
        const film = document.createElement('video');
        film.src = FILM_SRC;
        film.controls = true;
        film.autoplay = true;
        film.playsInline = true;
        film.poster = backgroundVideo?.getAttribute('poster') || '';
        film.setAttribute('title', 'The Serengeti 4K - Scenic Wildlife Film With African Music');
        frame.appendChild(film);

        backgroundVideo?.pause();
        dialog.showModal();
    });

    const close = () => dialog.close();
    document.getElementById('filmClose').addEventListener('click', close);
    dialog.addEventListener('click', (e) => { if (e.target === dialog) close(); });
    dialog.addEventListener('close', () => {
        frame.innerHTML = ''; // stops the film and frees memory
        backgroundVideo?.dispatchEvent(new Event('hero:resume'));
    });
}
