import { postForm } from './csrf';

// Shared behaviour for the "Ask a Question" pop-up and the contact form:
//  - <dialog data-dialog="id"> opens from any [data-dialog-open="id"] button
//  - <form data-ajax-form> posts as JSON and shows the result in [data-form-feedback]
// (The booking pop-up has its own script in booking.js.)
export function initAjaxForms() {
    document.querySelectorAll('dialog[data-dialog]').forEach((dialog) => {
        const id = dialog.dataset.dialog;

        document.querySelectorAll(`[data-dialog-open="${id}"]`).forEach((btn) => btn.addEventListener('click', () => {
            const form = dialog.querySelector('form');
            if (form) setFeedback(form, null);

            // Buttons can prefill fields: data-prefill-tour-page-id="3" sets the "tour_page_id" field.
            Object.entries(btn.dataset).forEach(([key, value]) => {
                if (!key.startsWith('prefill')) return;
                const name = key.slice(7).replace(/^./, (c) => c.toLowerCase()).replace(/[A-Z]/g, (c) => `_${c.toLowerCase()}`);
                const field = form?.elements[name];
                if (field) field.value = value;
            });

            dialog.showModal();
        }));

        dialog.querySelectorAll('[data-dialog-close]').forEach((btn) => btn.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
    });

    document.querySelectorAll('form[data-ajax-form]').forEach(bindForm);
}

function setFeedback(form, message, isError = false) {
    const feedback = form.querySelector('[data-form-feedback]');
    if (!feedback) return;

    feedback.hidden = !message;
    feedback.textContent = message || '';
    feedback.classList.toggle('booking-form__feedback--error', !!message && isError);
    feedback.classList.toggle('booking-form__feedback--success', !!message && !isError);
}

function bindForm(form) {
    const submit = form.querySelector('[type="submit"]');
    const label = form.querySelector('[data-submit-label]');
    const idleLabel = label?.textContent;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setFeedback(form, null);
        if (submit) submit.disabled = true;
        if (label) label.textContent = 'Sending…';

        try {
            const response = await postForm(form);
            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                const firstError = Object.values(data.errors || {})[0]?.[0];
                setFeedback(form, firstError || 'Please check the form and try again.', true);
                return;
            }

            if (response.status === 419) {
                setFeedback(form, "Your session expired — please refresh the page and try again.", true);
                return;
            }

            if (response.status === 429) {
                setFeedback(form, "You've sent a few messages in a row — please wait a minute and try again.", true);
                return;
            }

            if (!response.ok) {
                setFeedback(form, 'Something went wrong — please try again, or message us on WhatsApp.', true);
                return;
            }

            setFeedback(form, data.message, false);
            form.reset();

            const dialog = form.closest('dialog');
            if (dialog) setTimeout(() => dialog.close(), 2500);
        } catch (error) {
            setFeedback(form, "Couldn't reach the server — check your connection and try again.", true);
        } finally {
            if (submit) submit.disabled = false;
            if (label) label.textContent = idleLabel;
        }
    });
}
