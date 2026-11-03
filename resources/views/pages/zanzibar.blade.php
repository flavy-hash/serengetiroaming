@extends('layouts.app')

@section('title', 'Zanzibar')
@section('description', 'Beach holidays, Stone Town tours, spice tours and boat trips & snorkeling in Zanzibar, Tanzania.')

@php
    $experiences = [
        [
            'id' => 'beach-holidays',
            'title' => 'Beach Holidays',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg/960px-Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg',
            'copy' => 'White sand and turquoise water along the north coast — Nungwi and Kendwa for sunsets, Paje and Jambiani for kitesurfing.',
            'price' => 'From $650',
        ],
        [
            'id' => 'stone-town',
            'title' => 'Stone Town Tours',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Zanzibar_sultan_palace.jpg/960px-Zanzibar_sultan_palace.jpg',
            'copy' => "A guided walk through Zanzibar's old trading capital — the Sultan's Palace, the old fort, and the winding alleys of the UNESCO World Heritage centre.",
            'price' => 'From $45',
        ],
        [
            'id' => 'spice-tours',
            'title' => 'Spice Tours',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Spice_tour_2_%2814701047191%29.jpg/960px-Spice_tour_2_%2814701047191%29.jpg',
            'copy' => "Walk through working spice farms and taste cloves, cinnamon, vanilla and pepper straight from the plant — the trade that gave Zanzibar its name.",
            'price' => 'From $35',
        ],
        [
            'id' => 'boat-trips',
            'title' => 'Boat Trips & Snorkeling',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/Dhow_znz.jpg/960px-Dhow_znz.jpg',
            'copy' => 'Traditional dhow sailing trips to Mnemba Atoll and the sandbanks off Nungwi, with snorkeling over the reef and a seafood lunch on board.',
            'price' => 'From $60',
        ],
    ];

    $stoneTownImg = 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/Zanzibar_sultan_palace.jpg/960px-Zanzibar_sultan_palace.jpg';
    $nungwiImg = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg/960px-Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg';
    $spiceImg = 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Spice_tour_2_%2814701047191%29.jpg/960px-Spice_tour_2_%2814701047191%29.jpg';
    $dhowImg = 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/Dhow_znz.jpg/960px-Dhow_znz.jpg';

    $itinerary = [
        [
            'day' => 'Day 1', 'title' => 'Arrival', 'open' => true,
            'heroImage' => $stoneTownImg, 'heroCaption' => 'Stone Town',
            'copy' => 'You land at Zanzibar Airport (ZNZ) and are met by a Serengeti Roaming representative for the transfer to your hotel in Stone Town. The rest of the day is free to explore the old town on foot — the Sultan\'s Palace, the old fort and the winding alleys of the UNESCO World Heritage centre.',
            'note' => 'Check-in starts at 2:00 PM. The hotel rate includes breakfast only.',
            'mealPlan' => 'Bed and Breakfast',
            'accommodation' => 'Stone Town',
            'options' => [
                ['tier' => 'Comfort', 'label' => '3-star hotel, Stone Town'],
                ['tier' => 'Premium', 'label' => 'Boutique heritage hotel, Stone Town'],
            ],
            'image' => $stoneTownImg, 'imageCaption' => 'Stone Town',
        ],
        [
            'day' => 'Day 2', 'title' => 'Stone Town & Spice Tour',
            'heroImage' => $stoneTownImg, 'heroCaption' => 'Stone Town',
            'copy' => 'After breakfast, transfer out to one of the working spice farms just outside Stone Town before heading north to your beach hotel in Nungwi by early evening.',
            'activity' => [
                'title' => 'Spice Farm Tour',
                'image' => $spiceImg,
                'copy' => 'Walk through a working spice farm and taste cloves, cinnamon, vanilla and chili straight from the plant — the trade that gave Zanzibar its name. Includes a tasting session and a short cultural introduction from your guide.',
            ],
            'mealPlan' => 'Bed and Breakfast',
            'accommodation' => 'Nungwi',
            'options' => [
                ['tier' => 'Comfort', 'label' => 'Beachfront guesthouse, Nungwi'],
                ['tier' => 'Premium', 'label' => '4-star beach resort, Nungwi'],
            ],
            'image' => $nungwiImg, 'imageCaption' => 'Nungwi Beachfront',
        ],
        [
            'day' => 'Day 3', 'title' => 'Nungwi Beach',
            'heroImage' => $nungwiImg, 'heroCaption' => 'Nungwi Beachfront',
            'copy' => 'A free day on the beach — swim, relax, or join the optional excursion below.',
            'activity' => [
                'title' => 'Dhow Sailing & Snorkeling (Optional)',
                'image' => $dhowImg,
                'copy' => 'A traditional dhow sailing trip out to Mnemba Atoll and the sandbanks off Nungwi, with snorkeling over the reef and a seafood lunch on board. Booked locally and paid separately — ask your guide to arrange it.',
            ],
            'mealPlan' => 'Bed and Breakfast',
            'accommodation' => 'Nungwi',
            'options' => [
                ['tier' => 'Comfort', 'label' => 'Beachfront guesthouse, Nungwi'],
                ['tier' => 'Premium', 'label' => '4-star beach resort, Nungwi'],
            ],
            'image' => $nungwiImg, 'imageCaption' => 'Nungwi Beachfront',
        ],
        [
            'day' => 'Day 4', 'title' => 'Beach & Departure',
            'heroImage' => $nungwiImg, 'heroCaption' => 'Nungwi Beachfront',
            'copy' => 'A final morning at leisure on the beach before your transfer back to the airport for your onward flight.',
            'mealPlan' => 'Breakfast only',
        ],
    ];
@endphp

@section('content')
<section class="page-banner" style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/c/c7/Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg/1920px-Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg')">
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <span aria-current="page">Zanzibar</span>
        </nav>

        <span class="page-banner__badge">Beach Holiday</span>

        <h1 class="page-banner__title">Zanzibar</h1>
        <p class="page-banner__text">Beach holidays, Stone Town tours, spice tours and boat trips & snorkeling.</p>

        <div class="page-banner__meta">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
            Zanzibar Archipelago, Tanzania
        </div>
    </div>
</section>

<section class="py-20">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-[1fr_20rem] lg:items-start lg:px-8">
    <div>
        <div class="text-center lg:text-left">
            <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Zanzibar Beach Escape · 4 Days</p>
            <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">
                Package Overview
            </h2>
            <p class="mt-6 text-lg leading-relaxed text-charcoal-600">
                This 4-day escape pairs Zanzibar's UNESCO-listed Stone Town with two full days relaxing on
                Nungwi's white-sand beaches, plus a guided spice farm tour along the way — the perfect finish
                to a Tanzania safari, or a standalone island holiday.
            </p>
        </div>

        <h3 class="mt-16 text-center font-display text-2xl font-semibold text-forest-900 lg:text-left">Day-by-Day Itinerary</h3>

        <div class="mt-10 divide-y divide-forest-100 overflow-hidden rounded-2xl border border-forest-100 bg-white">
            @foreach ($itinerary as $day)
                <details class="group" @if(!empty($day['open'])) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 marker:content-none">
                        <span class="font-display text-lg font-semibold text-forest-900">
                            {{ $day['day'] }} <span class="text-charcoal-400">&bull;</span> {{ $day['title'] }}
                        </span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-charcoal-500 transition duration-200 group-open:rotate-180" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
                    </summary>

                    <div class="px-6 pb-8">
                        @if (!empty($day['heroImage']))
                            <div class="relative mb-6 h-56 overflow-hidden rounded-2xl sm:h-72">
                                <img src="{{ $day['heroImage'] }}" alt="{{ $day['heroCaption'] }}" loading="lazy" class="h-full w-full object-cover">
                                <span class="absolute bottom-3 left-3 rounded-full bg-forest-900/80 px-3 py-1 text-xs font-semibold text-cream-50">
                                    {{ $day['heroCaption'] }}
                                </span>
                            </div>
                        @endif

                        <p class="text-sm leading-relaxed text-charcoal-600">{{ $day['copy'] }}</p>

                        @if (!empty($day['note']))
                            <p class="mt-4 text-sm text-charcoal-600">
                                <span class="font-semibold text-forest-900">Note:</span> {{ $day['note'] }}
                            </p>
                        @endif

                        @if (!empty($day['activity']))
                            <div class="mt-6 rounded-2xl border border-gold-500/30 bg-gold-500/5 p-5">
                                <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gold-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z" /></svg>
                                    Activity &bull; {{ $day['activity']['title'] }}
                                </div>
                                <div class="mt-4 h-44 overflow-hidden rounded-xl sm:h-56">
                                    <img src="{{ $day['activity']['image'] }}" alt="{{ $day['activity']['title'] }}" loading="lazy" class="h-full w-full object-cover">
                                </div>
                                <p class="mt-4 text-sm leading-relaxed text-charcoal-600">{{ $day['activity']['copy'] }}</p>
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-forest-100 pt-5">
                            <div class="flex items-center gap-2 text-sm font-semibold text-forest-900">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="text-gold-600" aria-hidden="true"><path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 18h18M3 18v2M21 18v2M7 9V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" /></svg>
                                {{ $day['day'] }}{{ !empty($day['accommodation']) ? ' | Accommodation, '.$day['accommodation'] : '' }}
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-cream-100 px-4 py-2 text-sm text-charcoal-700">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3v18M6 3a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3M18 3v18M14 3v7a3 3 0 0 0 3 3h1" /></svg>
                                Meal Plan: <span class="font-semibold">{{ $day['mealPlan'] }}</span>
                            </div>
                        </div>

                        @if (!empty($day['options']))
                            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_18.75rem]">
                                <div>
                                    <p class="text-sm font-semibold text-forest-900">Options based on your package:</p>
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($day['options'] as $option)
                                            <li class="flex items-start gap-2">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 shrink-0 text-charcoal-500" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="m9 12 2 2 4-4" /></svg>
                                                <div>
                                                    <p class="text-sm font-medium text-charcoal-700">{{ $option['tier'] }}</p>
                                                    <p class="text-sm text-gold-600">{{ $option['label'] }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="relative h-48 overflow-hidden rounded-2xl lg:h-auto">
                                    <img src="{{ $day['image'] }}" alt="{{ $day['imageCaption'] }}" loading="lazy" class="h-full w-full object-cover">
                                    <span class="absolute bottom-3 left-3 rounded-full bg-forest-900/80 px-3 py-1 text-xs font-semibold text-cream-50">
                                        {{ $day['imageCaption'] }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>

    </div>

    <aside class="lg:sticky lg:top-[calc(var(--nav-h)+1.5rem)]">
        <div class="rounded-2xl bg-forest-900 p-7 text-cream-50">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">From</p>
            <p class="mt-2 font-display text-4xl font-semibold">$650</p>
            <p class="mt-2 text-sm text-cream-100/70">per person sharing, excluding international flights</p>

            <dl class="mt-6 space-y-3 border-t border-white/15 pt-6 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-cream-100/70">Duration</dt>
                    <dd class="font-semibold">4 Days · 3 Nights</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-cream-100/70">Group size</dt>
                    <dd class="font-semibold">Max 8</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-cream-100/70">Difficulty</dt>
                    <dd class="font-semibold">Easy</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-cream-100/70">Best time</dt>
                    <dd class="font-semibold">June – October</dd>
                </div>
            </dl>

            <button
                type="button"
                data-booking-open
                class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400"
            >
                Book This Adventure
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </button>
            <a
                href="{{ url('/contact') }}"
                class="mt-3 flex items-center justify-center gap-2 rounded-full border border-white/25 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-cream-50 transition hover:border-white/50"
            >
                Ask a Question
            </a>

            <p class="mt-5 text-center text-xs text-cream-100/60">
                No payment taken online — we confirm availability first.
            </p>
        </div>
    </aside>
    </div>
</section>

<dialog class="booking-dialog" id="bookingDialog" aria-label="Booking enquiry">
    <button type="button" class="review-dialog__close" data-booking-close aria-label="Close">✕</button>

    <form id="bookingForm" class="booking-form" action="{{ route('bookings.store') }}" method="POST">
        <div class="booking-form__feedback" data-booking-feedback hidden></div>

        <label class="booking-form__field">
            <span>Which Trip?</span>
            <select name="trip" required>
                <option>Zanzibar Beach Escape · 4 Days · From $650</option>
                <option>Machame Route (Kilimanjaro) · 7 Days · From $1,650</option>
                <option>Ngorongoro Crater Safari · 3 Days · From $980</option>
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

<section class="mx-auto max-w-3xl px-4 py-16 text-center lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">What's included</h2>
    <div class="mt-8 grid gap-8 text-left sm:grid-cols-2">
        <div>
            <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-gold-600">Included</h3>
            <ul class="mt-3 space-y-2 text-sm text-charcoal-600">
                <li>Airport transfers in Zanzibar</li>
                <li>Accommodation as per itinerary</li>
                <li>Stone Town guided tour</li>
                <li>Spice farm tour with tasting</li>
                <li>Breakfast daily</li>
            </ul>
        </div>
        <div>
            <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Not Included</h3>
            <ul class="mt-3 space-y-2 text-sm text-charcoal-600">
                <li>International & domestic flights</li>
                <li>Zanzibar visa / entry fees</li>
                <li>Lunch and dinner (unless noted)</li>
                <li>Optional dhow & snorkeling trip</li>
                <li>Travel insurance</li>
            </ul>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-16 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">Explore Zanzibar</h2>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($experiences as $experience)
            <div id="{{ $experience['id'] }}" class="scroll-mt-24 overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <img
                    src="{{ $experience['image'] }}"
                    alt="{{ $experience['title'] }}"
                    loading="lazy"
                    class="h-44 w-full object-cover"
                >
                <div class="p-5">
                    <h3 class="font-display text-lg font-semibold text-forest-900">{{ $experience['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-charcoal-600">{{ $experience['copy'] }}</p>
                    <p class="mt-4 text-sm font-semibold text-gold-600">{{ $experience['price'] }} per person</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
