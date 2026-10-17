import { postForm } from './csrf';

export function initBookingDialog() {
    const dialog = document.getElementById('bookingDialog');
    const openBtns = document.querySelectorAll('[data-booking-open]');
    if (!dialog || !openBtns.length) return;

    const closeBtn = dialog.querySelector('[data-booking-close]');
    const form = document.getElementById('bookingForm');
    const feedback = form.querySelector('[data-booking-feedback]');
    const submitBtn = form.querySelector('.booking-form__submit');
    const submitLabel = form.querySelector('[data-booking-submit-label]');

    const setFeedback = (message, isError) => {
        if (!message) {
            feedback.hidden = true;
            feedback.textContent = '';
            return;
        }
        feedback.hidden = false;
        feedback.textContent = message;
        feedback.classList.toggle('booking-form__feedback--error', !!isError);
        feedback.classList.toggle('booking-form__feedback--success', !isError);
    };

    openBtns.forEach((btn) => btn.addEventListener('click', () => {
        setFeedback(null);
        dialog.showModal();
    }));
    closeBtn.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        setFeedback(null);
        submitBtn.disabled = true;
        submitLabel.textContent = 'Sending…';

        try {
            const response = await postForm(form);
            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                const firstError = Object.values(data.errors || {})[0]?.[0];
                setFeedback(firstError || 'Please check the form and try again.', true);
                return;
            }

            if (!response.ok) {
                setFeedback("Something went wrong — please try again, or message us on WhatsApp.", true);
                return;
            }

            setFeedback(data.message, false);
            form.reset();
            setTimeout(() => dialog.close(), 2000);
        } catch (error) {
            setFeedback("Couldn't reach the server — check your connection and try again.", true);
        } finally {
            submitBtn.disabled = false;
            submitLabel.textContent = 'Send Enquiry';
        }
    });
}
