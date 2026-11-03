@extends('layouts.app')

@section('seo_title', config('site.name').' – Tanzania Tour Operator')
@section('description', 'Discover Tanzania with Serengeti Roaming African Safaris — Serengeti safaris, Kilimanjaro climbs and Zanzibar beach holidays crafted by local experts.')

@section('content')
<section class="hero" aria-label="Tanzania safaris">
    {{-- Muted background loop (public/videos). The poster shows instantly and on slow connections;
         phones get the lighter 720p file. "Watch the film" plays the full version with sound. --}}
    <video
        class="hero__video"
        id="heroVideo"
        autoplay
        muted
        loop
        playsinline
        preload="auto"
        poster="{{ asset('videos/hero-poster.jpg') }}"
        aria-hidden="true"
        tabindex="-1"
    >
        <source src="{{ asset('videos/The Serengeti 4K - Scenic Wildlife Film With African Music.mp4') }}" type="video/mp4" media="(max-width: 767px)">
        <source src="{{ asset('videos/The Serengeti 4K - Scenic Wildlife Film With African Music.mp4') }}" type="video/mp4">
    </video>

    <div class="hero__content">
        <div class="hero__region">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
            <span>Arusha, Tanzania · Locally owned</span>
        </div>
        <h1 class="hero__title">Discover Wild Tanzania</h1>
        <p class="hero__tagline">Serengeti safaris, Kilimanjaro climbs and Zanzibar beaches guided by the people who live here.</p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="{{ url('/safaris') }}">Plan My Trip</a>
            <a class="btn btn--ghost" href="{{ url('/contact') }}">Check availability</a>
            <button class="btn-film" type="button" id="filmOpen">
                <span class="btn-film__icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l12.5-7.5z" /></svg></span>
                Watch the film
            </button>
        </div>
    </div>

    <button class="hero__pause circle-btn" type="button" id="heroPause" aria-label="Pause background video" aria-pressed="false">
        <svg class="hero__pause-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" /><rect x="14" y="5" width="4" height="14" rx="1" /></svg>
        <svg class="hero__play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l12.5-7.5z" /></svg>
    </button>
</section>

<dialog class="film-dialog" id="filmDialog" aria-label="Safari film">
    <button class="film-dialog__close" type="button" id="filmClose" aria-label="Close video">✕</button>
    <div class="film-dialog__frame" id="filmFrame"></div>
</dialog>

<section class="mx-auto max-w-4xl px-4 py-20 text-center lg:px-8">
    <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Who We Are</p>
    <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">
        A Tanzanian-owned safari company, built by the people who guide it
    </h2>
    <p class="mt-6 text-lg leading-relaxed text-charcoal-600">
        Serengeti Roaming African Safaris is based in Tanzania and run by the same guides, planners and
        drivers who take you into the field. We build every itinerary safari, Kilimanjaro climb or
        Zanzibar escape around where the animals actually are and what you actually want, not a fixed
        package off a shelf. Locally owned, globally trusted, and priced fairly because there's no
        middleman between you and us.
    </p>
    <a
        href="{{ url('/about') }}"
        class="mt-8 inline-flex items-center justify-center gap-2 rounded-full border border-forest-800 px-6 py-3 text-sm font-semibold text-forest-800 transition hover:bg-forest-800 hover:text-cream-50"
    >
        Meet the team
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
    </a>
</section>

<section class="bg-forest-900 py-20">
    <div class="mx-auto max-w-6xl px-4 text-center lg:px-8">
        <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-300">Why Choose Us</p>
        <h2 class="mt-3 font-display text-3xl font-semibold text-cream-50 lg:text-4xl">
            The details that make a safari go right
        </h2>

        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                [
                    'title' => 'Locally Owned & Operated',
                    'copy' => 'Every guide, driver and planner is Tanzanian — this is our home, not just a route on a map.',
                    'icon' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" />',
                ],
                [
                    'title' => 'Best Price, No Middleman',
                    'copy' => "Book directly with the operator on the ground — no international agency markup added on top.",
                    'icon' => '<path d="M20 12 12 20l-8-8V4h8l8 8Z" /><circle cx="8.5" cy="8.5" r="1.2" fill="currentColor" stroke="none" />',
                ],
                [
                    'title' => 'Tailor-Made Itineraries',
                    'copy' => 'No fixed packages off a shelf — every trip is built around your budget, pace and interests.',
                    'icon' => '<path d="M9 4 4 6v14l5-2 6 2 5-2V4l-5 2-6-2Z" /><path d="M9 4v14M15 6v14" />',
                ],
                [
                    'title' => 'WhatsApp Support, Day or Night',
                    'copy' => "Reach a real person on WhatsApp before, during and after your trip — we reply within 24 hours.",
                    'icon' => '<path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z" />',
                ],
            ] as $reason)
                <div class="flex flex-col items-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-gold-500 text-forest-900">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $reason['icon'] !!}</svg>
                    </span>
                    <h3 class="mt-5 font-display text-lg font-semibold text-cream-50">{{ $reason['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-cream-100/80">{{ $reason['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-20 lg:px-8">
    <div class="text-center">
        <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Featured Packages</p>
        <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">
            Popular packages to start planning
        </h2>
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-3">
        @php
            $wikiImg = fn ($file, $width = 700) => "https://commons.wikimedia.org/wiki/Special:FilePath/{$file}?width={$width}";
        @endphp
        @foreach ([
            [
                'category' => 'Kilimanjaro',
                'image' => $wikiImg('Kilimanjaro_from_Amboseli.jpg'),
                'subtitle' => 'The classic whiskey route',
                'title' => 'Machame Route',
                'tags' => ['6 Days', 'Challenging'],
                'highlight' => '~85% summit rate',
                'copy' => "Kilimanjaro's most popular route — rainforest to glacier with a strong acclimatisation profile.",
                'price' => 'From $1,800',
                'href' => '/kilimanjaro',
            ],
            [
                'category' => 'Zanzibar',
                'image' => $wikiImg('Original_4b713299-08fb-4d32-9299-6844e8a97a78_IMG_20211201_154556.jpg'),
                'subtitle' => 'Stone Town & shores',
                'title' => 'Zanzibar Beach Escape',
                'tags' => ['4 Days', 'Relaxed'],
                'highlight' => 'Free cancellation 48h',
                'copy' => "Two days exploring Stone Town's history and spice markets, then two full days on Nungwi's beaches.",
                'price' => 'From $650',
                'href' => '/zanzibar',
            ],
            [
                'category' => 'Safari',
                'image' => $wikiImg('Ngorongoro-1001-2.jpg'),
                'subtitle' => 'Wildlife in a caldera',
                'title' => 'Ngorongoro Crater Safari',
                'tags' => ['3 Days', 'Classic'],
                'highlight' => 'Free cancellation 48h',
                'copy' => 'Game drives on the crater floor — one of the densest concentrations of wildlife in Africa.',
                'price' => 'From $980',
                'href' => '/safaris/ngorongoro-crater-safari',
            ],
        ] as $package)
            <div class="group overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <div class="relative h-56 overflow-hidden">
                    <img
                        src="{{ $package['image'] }}"
                        alt="{{ $package['title'] }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    >
                    <span class="absolute left-3 top-3 rounded-full bg-forest-900/90 px-3 py-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-cream-50">
                        {{ $package['category'] }}
                    </span>
                </div>
                <div class="p-6">
                    <p class="font-accent text-sm italic text-gold-600">{{ $package['subtitle'] }}</p>
                    <h3 class="mt-1 font-display text-xl font-semibold text-forest-900">{{ $package['title'] }}</h3>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($package['tags'] as $tag)
                            <span class="rounded-full bg-cream-100 px-3 py-1 text-xs font-medium text-charcoal-700">{{ $tag }}</span>
                        @endforeach
                        <span class="rounded-full bg-gold-500/15 px-3 py-1 text-xs font-medium text-gold-600">{{ $package['highlight'] }}</span>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-charcoal-600">{{ $package['copy'] }}</p>

                    <div class="mt-5 flex items-center justify-between border-t border-forest-100 pt-4">
                        <div>
                            <p class="font-display text-lg font-semibold text-gold-500">{{ $package['price'] }}</p>
                            <p class="text-xs text-charcoal-500">per person</p>
                        </div>
                        <a
                            href="{{ url($package['href']) }}"
                            class="inline-flex items-center gap-1 rounded-full border border-forest-800 px-4 py-2 text-sm font-semibold text-forest-800 transition hover:bg-forest-800 hover:text-cream-50"
                        >
                            View
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-12 flex justify-center">
        <a
            href="{{ url('/safaris') }}"
            class="inline-flex items-center gap-2 rounded-full bg-forest-800 px-8 py-3 text-sm font-semibold text-cream-50 transition hover:bg-forest-700"
        >
            Explore More
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </a>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-20 text-center lg:px-8">
    <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Reviews</p>
    <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">
        What travelers are saying
    </h2>

    @php($homepageReviews = \App\Models\Review::forHomepage())

    @if ($homepageReviews->isNotEmpty())
        {{-- Published reviews (featured first), managed in the admin under Reviews. --}}
        <div class="mx-auto mt-10 grid gap-6 text-left {{ $homepageReviews->count() >= 3 ? 'max-w-6xl sm:grid-cols-2 lg:grid-cols-3' : 'max-w-3xl sm:grid-cols-2' }}">
            @foreach ($homepageReviews as $review)
                <x-review-card :review="$review" />
            @endforeach
        </div>
    @else
        <x-review-placeholders />
    @endif

    <div class="mt-8 flex justify-center">
        <a
            href="{{ url('/reviews') }}"
            class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-forest-800 transition hover:text-gold-600"
        >
            Read More Reviews
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </a>
    </div>

    <x-review-badges class="mt-6" />
</section>

<x-review-dialog />

{{-- Top activities: managed in the admin under Website Pages → Activities (hidden while there are none). --}}
@php($activities = \App\Models\Activity::published()->ordered()->get())
@if ($activities->isNotEmpty())
    @php($activityText = \App\Models\Activity::sectionText())
    <section id="activities" class="scroll-mt-24 bg-cream-100/60 py-20" aria-labelledby="activities-heading">
        <div class="mx-auto max-w-6xl px-4 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="flex items-center justify-center gap-4 font-display text-sm uppercase tracking-[0.3em] text-gold-600">
                    <span class="h-px w-10 bg-gold-500/50" aria-hidden="true"></span>
                    {{ $activityText['eyebrow'] }}
                    <span class="h-px w-10 bg-gold-500/50" aria-hidden="true"></span>
                </p>
                <h2 id="activities-heading" class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">{{ $activityText['heading'] }}</h2>
                @if (filled($activityText['intro']))
                    <p class="mt-5 text-lg leading-relaxed text-charcoal-600">{{ $activityText['intro'] }}</p>
                @endif
            </div>

            <div class="mt-14 space-y-14 lg:space-y-20">
                @foreach ($activities as $activity)
                    @php($flip = $loop->index % 2 === 1)
                    <article class="grid items-center gap-7 lg:grid-cols-2 lg:gap-14">
                        <div @class(['lg:order-2' => $flip])>
                            @if (filled($activity->kicker))
                                <p class="font-mono text-xs font-bold uppercase tracking-[0.18em] text-forest-800">{{ $activity->kicker }}</p>
                            @endif
                            <h3 class="mt-2 font-display text-2xl font-semibold text-forest-900 lg:text-3xl">{{ $activity->title }}</h3>
                            @if (filled($activity->description))
                                <p class="mt-4 leading-relaxed text-charcoal-600">{{ $activity->description }}</p>
                            @endif
                            @if ($href = $activity->safeLinkUrl())
                                <a href="{{ $href }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-gold-600 transition hover:gap-3 hover:text-gold-500">
                                    {{ $activity->link_label ?: 'Find out more' }}
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </a>
                            @endif
                        </div>

                        <figure @class(['relative m-0 h-64 overflow-hidden rounded-3xl border border-forest-100 bg-forest-900 shadow-sm sm:h-80', 'lg:order-1' => $flip])>
                            @if ($src = $activity->imageUrl())
                                <img src="{{ $src }}" alt="{{ $activity->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                            @endif
                            @if (filled($activity->image_label))
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-forest-950/85 via-forest-950/40 to-transparent px-5 pb-4 pt-16">
                                    <figcaption class="font-mono text-[0.6875rem] font-bold uppercase tracking-[0.18em] text-cream-50">{{ $activity->image_label }}</figcaption>
                                </div>
                            @endif
                        </figure>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Our fleet: the vehicles from Website Pages → Vehicles that have a photo, drifting slowly from
     left to right. The strip is repeated so there is never a gap, and the second copy is hidden
     from screen readers and the keyboard. Hidden while fewer than two vehicles have photos. --}}
@php($fleet = \App\Models\Vehicle::published()->ordered()->get()->filter(fn ($v) => $v->imageUrl())->values())
@if ($fleet->count() >= 2)
    @php($repeat = max(1, (int) ceil(8 / $fleet->count())))
    <section id="fleet" class="scroll-mt-24 overflow-hidden py-20" aria-labelledby="fleet-heading">
        <div class="mx-auto flex max-w-6xl flex-wrap items-end justify-between gap-4 px-4 lg:px-8">
            <div>
                <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Our Fleet</p>
                <h2 id="fleet-heading" class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">Travel in comfort, wherever you go</h2>
            </div>
            <a href="{{ route('vehicles') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gold-600 transition hover:gap-3 hover:text-gold-500">
                See all vehicles
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </a>
        </div>

        <div class="fleet-marquee mt-10" style="--fleet-seconds: {{ max(36, $fleet->count() * $repeat * 5) }}s" role="region" aria-label="Our vehicles">
            <div class="fleet-marquee__track">
                @foreach ([false, true] as $isCopy)
                    <ul class="fleet-marquee__set"{!! $isCopy ? ' aria-hidden="true"' : '' !!}>
                        @foreach (range(1, $repeat) as $round)
                            @foreach ($fleet as $vehicle)
                                <li>
                                    <a href="{{ route('vehicles') }}#{{ $vehicle->anchor() }}" class="fleet-card"{!! $isCopy || $round > 1 ? ' tabindex="-1"' : '' !!}>
                                        <span class="fleet-card__photo">
                                            <img src="{{ $vehicle->imageUrl() }}" alt="{{ $isCopy || $round > 1 ? '' : $vehicle->name }}" loading="lazy" decoding="async" draggable="false">
                                        </span>
                                        <span class="fleet-card__name">{{ $vehicle->name }}</span>
                                        <span class="fleet-card__meta">
                                            {{ collect([$vehicle->category, $vehicle->seats ? $vehicle->seats.' '.\Illuminate\Support\Str::plural('seat', $vehicle->seats) : null])->filter()->implode(' · ') }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">Explore by destination</h2>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['title' => 'Safaris', 'href' => '/safaris', 'copy' => 'Budget to luxury game drives across the Serengeti.'],
            ['title' => 'Southern Circuit', 'href' => '/southern-circuit', 'copy' => 'Ruaha, Nyerere & Selous, Mikumi and Udzungwa.'],
            ['title' => 'Kilimanjaro', 'href' => '/kilimanjaro', 'copy' => 'Machame, Lemosho, Marangu and day hikes.'],
            ['title' => 'Zanzibar', 'href' => '/zanzibar', 'copy' => 'Beach holidays, Stone Town and spice tours.'],
        ] as $card)
            <a href="{{ url($card['href']) }}" class="group rounded-2xl border border-forest-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                <h3 class="font-display text-lg font-semibold text-forest-900 group-hover:text-gold-600">
                    {{ $card['title'] }}
                </h3>
                <p class="mt-2 text-sm text-charcoal-600">{{ $card['copy'] }}</p>
            </a>
        @endforeach
    </div>
</section>
@endsection
