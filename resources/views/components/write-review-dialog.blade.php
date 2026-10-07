@props(['tourPage' => null])

@php
    $trips = \App\Models\TourPage::published()->ordered()->get(['id', 'package_name', 'category', 'sort_order']);
@endphp

{{-- Opened by any button with data-dialog-open="review". New reviews wait for approval in the admin. --}}
<dialog class="booking-dialog" data-dialog="review" aria-label="Write a review">
    <button type="button" class="review-dialog__close" data-dialog-close aria-label="Close">✕</button>

    <form class="booking-form" action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" data-ajax-form>
        <div>
            <h2 class="font-display text-2xl font-semibold text-forest-900">Share your trip</h2>
            <p class="mt-1 text-sm text-charcoal-600">Travelled with us? Tell future guests how it went — your review appears once our team has checked it.</p>
        </div>

        <div class="booking-form__feedback" data-form-feedback hidden></div>

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
                <small>Never shown publicly.</small>
            </label>
        </div>

        <div class="booking-form__row">
            <label class="booking-form__field">
                <span>Country</span>
                <input type="text" name="country" maxlength="100" autocomplete="country-name" placeholder="e.g. Germany">
            </label>
            <label class="booking-form__field">
                <span>When did you travel?</span>
                <input type="text" name="trip_date" maxlength="50" placeholder="e.g. August 2026">
            </label>
        </div>

        <div class="booking-form__row">
            <label class="booking-form__field">
                <span>Which trip?</span>
                <select name="tour_page_id">
                    <option value="">A custom or other trip</option>
                    @foreach ($trips as $trip)
                        <option value="{{ $trip->id }}" @selected($tourPage?->is($trip))>{{ $trip->package_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="booking-form__field">
                <span>Your Rating *</span>
                <select name="rating" required>
                    <option value="5">★★★★★ Excellent</option>
                    <option value="4">★★★★ Very good</option>
                    <option value="3">★★★ Good</option>
                    <option value="2">★★ Fair</option>
                    <option value="1">★ Poor</option>
                </select>
            </label>
        </div>

        <label class="booking-form__field">
            <span>Title *</span>
            <input type="text" name="title" required maxlength="120" placeholder="e.g. The migration was unforgettable">
        </label>

        <label class="booking-form__field">
            <span>Your Review *</span>
            <textarea name="body" rows="5" required minlength="20" maxlength="5000" placeholder="What stood out? How were your guide, the camps and the food?"></textarea>
        </label>

        <label class="booking-form__field">
            <span>Photos (optional)</span>
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
            <small>Up to 3 photos, 5 MB each.</small>
        </label>

        <button type="submit" class="booking-form__submit">
            <span data-submit-label>Submit Review</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </form>
</dialog>
