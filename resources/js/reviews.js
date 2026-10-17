export function initReviewDialogs() {
    const dialog = document.getElementById('reviewDialog');
    if (!dialog) return;

    const body = dialog.querySelector('[data-review-dialog-body]');
    const closeBtn = dialog.querySelector('[data-review-dialog-close]');

    document.querySelectorAll('[data-review-card]').forEach((card) => {
        const trigger = card.querySelector('[data-read-review]');
        const template = card.querySelector('template');
        if (!trigger || !template) return;

        trigger.addEventListener('click', () => {
            body.innerHTML = '';
            body.appendChild(template.content.cloneNode(true));
            dialog.showModal();
        });
    });

    closeBtn.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
}
