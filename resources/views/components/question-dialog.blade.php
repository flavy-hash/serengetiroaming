@props(['tourPage' => null])

{{-- Opened by any button with data-dialog-open="question". Answered in the admin (FAQ). --}}
<dialog class="booking-dialog" data-dialog="question" aria-label="Ask a question">
    <button type="button" class="review-dialog__close" data-dialog-close aria-label="Close">✕</button>

    <form class="booking-form" action="{{ route('faq.store') }}" method="POST" data-ajax-form>
        <div>
            <h2 class="font-display text-2xl font-semibold text-forest-900">Ask a Question</h2>
            <p class="mt-1 text-sm text-charcoal-600">
                @if ($tourPage)
                    About <span class="font-semibold">{{ $tourPage->package_name }}</span> — we'll email you an answer within 24 hours.
                @else
                    We'll email you an answer within 24 hours.
                @endif
            </p>
        </div>

        <div class="booking-form__feedback" data-form-feedback hidden></div>

        <input type="hidden" name="tour_page_id" value="{{ $tourPage?->id }}">

        {{-- Honeypot: hidden from people, bots fill it in and get rejected. --}}
        <div class="hidden" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div class="booking-form__row">
            <label class="booking-form__field">
                <span>Your Name *</span>
                <input type="text" name="name" required maxlength="255" autocomplete="name">
            </label>
            <label class="booking-form__field">
                <span>Email *</span>
                <input type="email" name="email" required maxlength="255" autocomplete="email">
            </label>
        </div>

        <label class="booking-form__field">
            <span>Your Question *</span>
            <textarea name="question" rows="5" required minlength="5" maxlength="2000" placeholder="e.g. Is this trip suitable for children? Can we add an extra night?"></textarea>
        </label>

        <button type="submit" class="booking-form__submit">
            <span data-submit-label>Send Question</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>

        <p class="booking-form__note">Helpful questions and answers may be added to our <a href="{{ route('faq') }}" class="underline">FAQ</a> — without your name or email.</p>
    </form>
</dialog>
