export function initLanguageSwitcher() {
    const root = document.querySelector('[data-lang-fab]');
    if (!root) return;

    const toggleBtn = root.querySelector('[data-lang-toggle]');
    const panel = root.querySelector('[data-lang-panel]');
    const options = [...root.querySelectorAll('[data-lang-option]')];

    function currentGoogTransLang() {
        const m = document.cookie.match(/googtrans=\/en\/([a-zA-Z-]+)/);
        return m ? m[1] : 'en';
    }

    function highlightActive() {
        const active = currentGoogTransLang();
        options.forEach((opt) => {
            opt.classList.toggle('is-active', opt.dataset.langOption === active);
        });
    }

    function setOpen(open) {
        panel.hidden = !open;
        toggleBtn.setAttribute('aria-expanded', String(open));
        if (open) highlightActive();
    }

    function setSiteLanguage(lang) {
        if (lang === 'en') {
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;domain=' + location.hostname + ';';
            location.reload();
            return;
        }

        document.cookie = 'googtrans=/en/' + lang + '; path=/';

        const combo = document.querySelector('select.goog-te-combo');
        if (combo) {
            combo.value = lang;
            combo.dispatchEvent(new Event('change'));
            setOpen(false);
        } else {
            location.reload();
        }
    }

    toggleBtn.addEventListener('click', () => setOpen(panel.hidden));

    options.forEach((opt) => {
        opt.addEventListener('click', () => setSiteLanguage(opt.dataset.langOption));
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });

    highlightActive();
}
