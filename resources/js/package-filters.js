// Instant filtering on /safaris. The page is already filtered by the server for the
// query string (?tier=luxury&section=kilimanjaro&days=7) and every chip is a real link;
// this script just avoids the reload. Navbar links like /safaris#budget also work.
export function initPackageFilters() {
    const root = document.querySelector('[data-package-filters]');
    if (!root) return;

    const chips = [...root.querySelectorAll('[data-filter]')];
    const cards = [...root.querySelectorAll('[data-package]')];
    const count = root.querySelector('[data-filter-count]');
    const empty = root.querySelector('[data-filter-empty]');
    const reset = root.querySelector('[data-filter-reset]');
    const heading = document.querySelector('[data-filter-heading]');
    const crumb = document.querySelector('[data-filter-crumb]');
    const keys = ['section', 'tier', 'days'];

    const state = { section: '', tier: '', days: '' };
    const allowed = (key, value) => chips.some((c) => c.dataset.filter === key && c.dataset.value === value);
    const chipFor = (key) => chips.find((c) => c.dataset.filter === key && c.dataset.value === state[key]);

    // Same wording as the server: "Luxury Kilimanjaro Packages · 8 days".
    const headingText = () => {
        if (keys.every((key) => !state[key])) return heading?.dataset.headingDefault ?? '';
        const tier = state.tier ? `${chipFor('tier')?.dataset.headingPart ?? ''} ` : '';
        const section = state.section ? (chipFor('section')?.dataset.headingPart ?? '') : 'Safaris & Tours';
        const days = state.days ? ` · ${state.days} days` : '';
        return `${tier}${section}${days}`;
    };

    const apply = () => {
        let visible = 0;
        cards.forEach((card) => {
            const show = keys.every((key) => !state[key] || card.dataset[key] === state[key]);
            card.hidden = !show;
            if (show) visible++;
        });

        chips.forEach((c) => c.setAttribute('aria-current', String(state[c.dataset.filter] === c.dataset.value)));

        if (count) count.textContent = String(visible);
        if (empty) empty.classList.toggle('hidden', visible > 0);
        if (reset) reset.classList.toggle('hidden', keys.every((key) => !state[key]));

        const text = headingText();
        if (heading) heading.textContent = text;
        if (crumb) crumb.textContent = text;

        const params = new URLSearchParams();
        keys.forEach((key) => { if (state[key]) params.set(key, state[key]); });
        const query = params.toString();
        history.replaceState(null, '', location.pathname + (query ? `?${query}` : ''));
    };

    const readUrl = () => {
        const params = new URLSearchParams(location.search);
        keys.forEach((key) => {
            const value = params.get(key) ?? '';
            state[key] = allowed(key, value) ? value : '';
        });

        // /safaris#luxury (style) or /safaris#zanzibar (destination) from the navbar.
        const hash = decodeURIComponent(location.hash.slice(1));
        if (hash && allowed('tier', hash)) state.tier = hash;
        else if (hash && allowed('section', hash)) state.section = hash;
    };

    chips.forEach((chip) => chip.addEventListener('click', (e) => {
        e.preventDefault();
        state[chip.dataset.filter] = chip.dataset.value;
        apply();
    }));

    [reset, root.querySelector('[data-filter-reset-link]')].forEach((link) => link?.addEventListener('click', (e) => {
        e.preventDefault();
        keys.forEach((key) => { state[key] = ''; });
        apply();
    }));

    // Clicking a navbar link like "Luxury" while already on /safaris only changes the hash.
    window.addEventListener('hashchange', () => {
        readUrl();
        apply();
        root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    readUrl();
    apply();

    // On small screens the chip rows scroll sideways: bring each active chip into view.
    chips.filter((c) => c.getAttribute('aria-current') === 'true' && c.dataset.value !== '').forEach((c) => {
        c.parentElement.scrollLeft = Math.max(0, c.offsetLeft - c.parentElement.offsetLeft - 16);
    });
}
