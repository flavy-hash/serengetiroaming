// Floating WeChat button: opens a small panel with the WeChat ID and QR code; "Copy ID" copies the ID.
export function initWechat() {
    const root = document.querySelector('[data-wechat-fab]');
    if (!root) return;

    const toggle = root.querySelector('[data-wechat-toggle]');
    const panel = root.querySelector('[data-wechat-panel]');
    const copy = root.querySelector('[data-wechat-copy]');
    const id = root.querySelector('[data-wechat-id]')?.textContent.trim() ?? '';

    const setOpen = (open) => {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        setOpen(panel.hidden);
    });
    document.addEventListener('click', (e) => { if (!root.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

    copy?.addEventListener('click', async () => {
        const label = copy.textContent;
        try {
            await navigator.clipboard.writeText(id);
        } catch {
            // Older browsers / plain http: select the ID so it can be copied by hand.
            const range = document.createRange();
            range.selectNodeContents(root.querySelector('[data-wechat-id]'));
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
        copy.textContent = copy.dataset.copiedLabel;
        setTimeout(() => { copy.textContent = label; }, 1800);
    });
}
