@props(['tourPage'])

@php
    $errorBag = $errors->getBag(\App\Http\Controllers\InstantQuoteController::ERROR_BAG);
    $result = session('instant_quote');
    $childRate = \App\Models\Setting::childRate() * 100;
@endphp

{{-- Opened by data-dialog-open="instant-quote"; reopens itself after the form posts back. --}}
<dialog
    id="instant-quote"
    class="booking-dialog"
    data-dialog="instant-quote"
    aria-label="Get an instant quote"
    @if ($result || $errorBag->any()) data-open-on-load @endif
>
    <button type="button" class="review-dialog__close" data-dialog-close aria-label="Close">✕</button>

    @if ($result)
        <div class="booking-form">
            <div>
                <h2 class="font-display text-2xl font-semibold text-forest-900">Your quote is ready</h2>
                <p class="mt-1 text-sm text-charcoal-600">
                    Quote {{ $result['reference'] }} for {{ $tourPage->package_name }}: {{ $result['total'] }}.
                    We've also received a copy and will be in touch to confirm availability.
                </p>
            </div>
            <a href="{{ $result['download_url'] }}" class="booking-form__submit" download>
                Download Quote (PDF)
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></svg>
            </a>
            <p class="booking-form__note">The download link works for 7 days. No payment is taken online.</p>
        </div>
    @else
        <form class="booking-form" action="{{ route('tours.quote', ['category' => $tourPage->category->value, 'slug' => $tourPage->slug]) }}" method="POST">
            @csrf
            <div>
                <h2 class="font-display text-2xl font-semibold text-forest-900">Get an Instant Quote</h2>
                <p class="mt-1 text-sm text-charcoal-600">
                    {{ $tourPage->package_name }} from ${{ number_format($tourPage->price_from) }} per adult{{ $childRate > 0 && $childRate < 100 ? ', children under 16 at '.round($childRate).'%' : '' }}. Your PDF quote is ready straight away.
                </p>
            </div>

            @if ($errorBag->any())
                <div class="booking-form__feedback booking-form__feedback--error" role="alert">
                    {{ $errorBag->first() }}
                </div>
            @endif

            {{-- Honeypot: hidden from people, bots fill it in and get rejected. --}}
            <div class="hidden" aria-hidden="true">
                <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="booking-form__row">
                <label class="booking-form__field">
                    <span>Your Name *</span>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" @if($errorBag->has('name')) aria-invalid="true" @endif>
                </label>
                <label class="booking-form__field">
                    <span>Email *</span>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" @if($errorBag->has('email')) aria-invalid="true" @endif>
                </label>
            </div>

            <div class="booking-form__row">
                <label class="booking-form__field">
                    <span>Phone / WhatsApp</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="50" autocomplete="tel">
                </label>
                <label class="booking-form__field">
                    <span>Country</span>
                    <input type="text" name="country" value="{{ old('country') }}" maxlength="100" autocomplete="country-name">
                </label>
            </div>

            <div class="booking-form__row">
                <label class="booking-form__field">
                    <span>Travel Date *</span>
                    <input type="date" name="travel_date" value="{{ old('travel_date') }}" min="{{ now()->toDateString() }}" required @if($errorBag->has('travel_date')) aria-invalid="true" @endif>
                </label>
                <div class="grid grid-cols-2 gap-[1.125rem]">
                    <label class="booking-form__field">
                        <span>Adults *</span>
                        <input type="number" name="adults" value="{{ old('adults', 2) }}" min="1" max="30" required>
                    </label>
                    <label class="booking-form__field">
                        <span>Children</span>
                        <input type="number" name="children" value="{{ old('children', 0) }}" min="0" max="30">
                    </label>
                </div>
            </div>

            <button type="submit" class="booking-form__submit">
                Get My Quote
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </button>

            <p class="booking-form__note">An estimate from the package price — we confirm availability before any payment.</p>
        </form>
    @endif
</dialog>
