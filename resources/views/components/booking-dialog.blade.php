@props(['selected' => null])

@php
    // Every published tour page is bookable; the page the visitor is on is preselected.
    $trips = \App\Models\TourPage::published()->ordered()->get()
        ->map->bookingLabel()
        ->filter()
        ->unique()
        ->values();

    if ($selected && ! $trips->contains($selected)) {
        $trips->prepend($selected);
    }
@endphp

<dialog class="booking-dialog" id="bookingDialog" aria-label="Booking enquiry">
    <button type="button" class="review-dialog__close" data-booking-close aria-label="Close">✕</button>

    <form id="bookingForm" class="booking-form" action="{{ route('bookings.store') }}" method="POST">
        <div class="booking-form__feedback" data-booking-feedback hidden></div>

        <label class="booking-form__field">
            <span>Which Trip?</span>
            <select name="trip" required>
                @foreach ($trips as $trip)
                    <option @selected($trip === $selected)>{{ $trip }}</option>
                @endforeach
                <option>Something else — a custom trip</option>
            </select>
        </label>

        <div class="booking-form__row">
            <label class="booking-form__field">
                <span>Your Name *</span>
                <input type="text" name="name" required>
            </label>
            <label class="booking-form__field">
                <span>Email *</span>
                <input type="email" name="email" required>
            </label>
        </div>

        <div class="booking-form__row">
            <label class="booking-form__field">
                <span>Phone or WhatsApp</span>
                <input type="tel" name="phone">
            </label>
            <label class="booking-form__field">
                <span>Travellers *</span>
                <input type="number" name="travellers" min="1" value="2" required>
            </label>
        </div>

        <label class="booking-form__field">
            <span>Rough Departure Date</span>
            <input type="date" name="departure_date">
            <small>An approximate month is fine — we can move it.</small>
        </label>

        <label class="booking-form__field">
            <span>Anything we should know?</span>
            <textarea name="notes" rows="4" placeholder="Dietary needs, mobility, special occasions, anything else we should know…"></textarea>
        </label>

        <button type="submit" class="booking-form__submit">
            <span data-booking-submit-label>Send Enquiry</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>

        <p class="booking-form__note">No payment is taken now. We confirm availability before anything is booked.</p>
    </form>
</dialog>
