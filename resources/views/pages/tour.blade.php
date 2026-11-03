@extends('layouts.app')

@section('title', $page->seoTitle())
{{-- Never pass null here: @section with a null value opens a block section. --}}
@section('description', $page->meta_description ?: $page->card_summary ?: $page->banner_text ?: $page->category->getSubtitle())
@section('og_image', \App\Models\TourPage::imageUrl($page->banner_image) ?? '')
@if (! $page->is_published)
    @section('robots', 'noindex, nofollow')
@endif

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::trip($page),
        \App\Support\Seo::breadcrumbs(array_filter([
            $page->category->getLabel() => $page->isMain() ? null : url($page->category->value),
            $page->package_name => $page->url(),
        ])),
    ]) !!}
@endpush

@php
    $img = fn (?string $path) => \App\Models\TourPage::imageUrl($path);
@endphp

@section('content')
<section class="page-banner" @if($page->banner_image) style="background-image: url('{{ $img($page->banner_image) }}')" @endif>
    <x-banner-video :src="$page->bannerVideoUrl()" :poster="$img($page->banner_image)" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            @unless ($page->isMain())
                <a href="{{ url($page->category->value) }}">{{ $page->category->getLabel() }}</a>
                <span>/</span>
            @endunless
            <span aria-current="page">{{ $page->title }}</span>
        </nav>

        @if ($badge = $page->banner_badge ?: ($page->tier && $page->category->hasListingPage() ? $page->tier->getLabel().' Safari' : null))
            <span class="page-banner__badge">{{ $badge }}</span>
        @endif

        <h1 class="page-banner__title">{{ $page->title }}</h1>
        @if ($page->banner_text)
            <p class="page-banner__text">{{ $page->banner_text }}</p>
        @endif

        @if ($page->location)
            <div class="page-banner__meta">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                {{ $page->location }}
            </div>
        @endif
    </div>
</section>

<section class="py-20">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-[1fr_20rem] lg:items-start lg:px-8">
    <div>
        <div class="text-center lg:text-left">
            <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">
                {{ collect([$page->package_name, $page->duration])->filter()->implode(' · ') }}
            </p>
            <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">
                {{ $page->overview_heading }}
            </h2>
            @if ($page->overview_body)
                <div class="mt-6 space-y-4 text-lg leading-relaxed sm:text-xl text-charcoal-600">
                    @foreach (preg_split('/\R{2,}/', trim($page->overview_body)) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($routeStops)
            @php($routeText = \App\Support\RouteMap::describe($routeStops))
            <section class="mt-16" aria-labelledby="route-map-heading">
                <h3 id="route-map-heading" class="text-center font-display text-2xl font-semibold text-forest-900 lg:text-left">Route Map</h3>
                <div
                    class="route-map mt-6"
                    data-route-map
                    data-stops="{{ json_encode(array_map(fn ($s) => ['label' => $s['label'], 'name' => $s['name'], 'lat' => $s['lat'], 'lng' => $s['lng']], $routeStops)) }}"
                    data-tiles="{{ json_encode(\App\Support\RouteMap::tiles()) }}"
                    role="region"
                    aria-label="Route map: {{ $routeText }}"
                ></div>
                <ol class="route-map__stops" aria-label="Route">
                    @foreach ($routeStops as $stop)
                        <li><span class="route-map__day">{{ $stop['label'] }}</span> {{ $stop['name'] }}</li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if (! empty($page->itinerary))
        @php($noneOpen = collect($page->itinerary)->every(fn ($d) => empty($d['open'])))
        {{-- Where a day's accommodation links to: the stay picked for that day (its own page with
             description and gallery), or else the list of stays in the day's overnight area. --}}
        <?php
            $dayStays = \App\Models\Accommodation::published()
                ->whereIn('id', collect($page->itinerary)->pluck('accommodation_id')->filter()->unique()->all())
                ->get()->keyBy('id');
            $areaStays = \App\Models\Accommodation::locationOptions();
            // Longest names first, so "Serengeti Serena Safari Lodge" wins over "Serena".
            $namedStays = \App\Models\Accommodation::published()->get()->sortByDesc(fn ($s) => mb_strlen($s->name))->values();
            $stayNamedIn = fn (?string $text) => filled($text)
                ? $namedStays->first(fn ($s) => mb_stripos($text, $s->name) !== false)
                : null;
            $stayLink = function (array $day) use ($dayStays, $areaStays, $stayNamedIn): ?array {
                if ($stay = $dayStays->get($day['accommodation_id'] ?? null)) {
                    return ['url' => $stay->url(), 'label' => $stay->name];
                }
                $texts = [$day['image_caption'] ?? null, $day['accommodation'] ?? null, ...array_column($day['options'] ?? [], 'label')];
                foreach ($texts as $text) {
                    if ($stay = $stayNamedIn($text)) {
                        return ['url' => $stay->url(), 'label' => $stay->name];
                    }
                }
                $area = \Illuminate\Support\Str::slug((string) ($day['accommodation'] ?? ''));
                if ($area !== '' && isset($areaStays[$area])) {
                    return ['url' => route('accommodations', ['location' => $area]), 'label' => $day['accommodation']];
                }

                return null;
            };
        ?>
        <h3 class="mt-16 text-center font-display text-2xl font-semibold text-forest-900 lg:text-left">Day-by-Day Itinerary</h3>

        <div class="mt-10 divide-y divide-forest-100 overflow-hidden rounded-2xl border border-forest-100 bg-white">
            @foreach ($page->itinerary as $day)
                <details class="group" @if(!empty($day['open']) || ($noneOpen && $loop->first)) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 marker:content-none">
                        <span class="font-display text-lg font-semibold text-forest-900">
                            {{ $day['day'] ?? '' }} <span class="text-charcoal-400">&bull;</span> {{ $day['title'] ?? '' }}
                        </span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-charcoal-500 transition duration-200 group-open:rotate-180" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
                    </summary>

                    <div class="px-6 pb-8">
                        @if (!empty($day['hero_image']))
                            <div class="relative mb-6 h-56 overflow-hidden rounded-2xl sm:h-72">
                                <img src="{{ $img($day['hero_image']) }}" alt="{{ $day['hero_caption'] ?? $day['title'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                                @if (!empty($day['hero_caption']))
                                    <span class="absolute bottom-3 left-3 rounded-full bg-forest-900/80 px-3 py-1 text-xs font-semibold text-cream-50">
                                        {{ $day['hero_caption'] }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if (!empty($day['copy']))
                            <p class="text-base leading-relaxed sm:text-lg text-charcoal-600">{{ $day['copy'] }}</p>
                        @endif

                        @if (!empty($day['note']))
                            <p class="mt-4 text-base text-charcoal-600">
                                <span class="font-semibold text-forest-900">Note:</span> {{ $day['note'] }}
                            </p>
                        @endif

                        @if (!empty($day['activity_title']))
                            <div class="mt-6 rounded-2xl border border-gold-500/30 bg-gold-500/5 p-5">
                                <div class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-gold-600">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.8l2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9z" /></svg>
                                    Activity &bull; {{ $day['activity_title'] }}
                                </div>
                                @if (!empty($day['activity_image']))
                                    <div class="mt-4 h-44 overflow-hidden rounded-xl sm:h-56">
                                        <img src="{{ $img($day['activity_image']) }}" alt="{{ $day['activity_title'] }}" loading="lazy" class="h-full w-full object-cover">
                                    </div>
                                @endif
                                @if (!empty($day['activity_copy']))
                                    <p class="mt-4 text-base leading-relaxed text-charcoal-600">{{ $day['activity_copy'] }}</p>
                                @endif
                            </div>
                        @endif

                        @if (filled($day['accommodation'] ?? null) || filled($day['meal_plan'] ?? null))
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-forest-100 pt-5">
                            @if (filled($day['accommodation'] ?? null))
                            <div class="flex items-center gap-2 text-sm font-semibold text-forest-900">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="text-gold-600" aria-hidden="true"><path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 18h18M3 18v2M21 18v2M7 9V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" /></svg>
                                @php($stayTo = $stayLink($day))
                                @if ($stayTo)
                                    <a href="{{ $stayTo['url'] }}" class="inline-flex flex-wrap items-center gap-x-1.5 text-forest-900 underline decoration-gold-500/50 underline-offset-4 transition hover:text-gold-600 hover:decoration-gold-500" title="See photos and details">
                                        <span>{{ $day['day'] ?? '' }} | Accommodation, {{ $stayTo['label'] }}</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gold-600" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </a>
                                @else
                                    {{ $day['day'] ?? '' }} | Accommodation, {{ $day['accommodation'] }}
                                @endif
                            </div>
                            @endif
                            @if (filled($day['meal_plan'] ?? null))
                                <div class="inline-flex items-center gap-2 rounded-full bg-cream-100 px-4 py-2 text-sm text-charcoal-700">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3v18M6 3a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3M18 3v18M14 3v7a3 3 0 0 0 3 3h1" /></svg>
                                    Meal Plan: <span class="font-semibold">{{ $day['meal_plan'] }}</span>
                                </div>
                            @endif
                        </div>
                        @endif

                        @if (!empty($day['options']) || filled($day['image'] ?? null))
                            <div class="mt-6 grid gap-6 {{ !empty($day['options']) && !empty($day['image']) ? 'lg:grid-cols-[1fr_18.75rem]' : '' }}">
                                @if (!empty($day['options']))
                                <div>
                                    <p class="text-sm font-semibold text-forest-900">Options based on your package:</p>
                                    <ul class="mt-3 space-y-3">
                                        @foreach ($day['options'] as $option)
                                            <li class="flex items-start gap-2">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="mt-0.5 shrink-0 text-charcoal-500" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="m9 12 2 2 4-4" /></svg>
                                                <div>
                                                    <p class="text-sm font-medium text-charcoal-700">{{ $option['tier'] ?? '' }}</p>
                                                    @php($optionStay = $stayNamedIn($option['label'] ?? null))
                                                    @if ($optionStay)
                                                        <a href="{{ $optionStay->url() }}" class="text-sm text-gold-600 underline decoration-gold-500/50 underline-offset-4 transition hover:text-gold-500">{{ $option['label'] }}</a>
                                                    @else
                                                        <p class="text-sm text-gold-600">{{ $option['label'] ?? '' }}</p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                @if (!empty($day['image']))
                                    <div class="relative overflow-hidden rounded-2xl {{ !empty($day['options']) ? 'h-48 lg:h-auto' : 'h-56 sm:h-72' }}">
                                        <img src="{{ $img($day['image']) }}" alt="{{ $day['image_caption'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                                        @if (!empty($day['image_caption']))
                                            <span class="absolute bottom-3 left-3 rounded-full bg-forest-900/80 px-3 py-1 text-xs font-semibold text-cream-50">
                                                {{ $day['image_caption'] }}
                                            </span>
                                        @endif
                                        @if ($stayLink($day))
                                            <a href="{{ $stayLink($day)['url'] }}" class="absolute inset-0" aria-label="See photos and details of {{ $stayLink($day)['label'] }}"></a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach
        </div>
        @endif

    </div>

    <aside class="lg:sticky lg:top-[calc(var(--nav-h)+1.5rem)]">
        <div class="rounded-2xl bg-forest-900 p-7 text-cream-50">
            @if ($page->price_from)
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">From</p>
                <p class="mt-2 font-display text-4xl font-semibold">${{ number_format($page->price_from) }}</p>
            @else
                <p class="font-display text-2xl font-semibold">Price on request</p>
            @endif
            @if ($page->price_note)
                <p class="mt-2 text-sm text-cream-100/70">{{ $page->price_note }}</p>
            @endif

            @php($facts = collect($page->facts)->filter(fn ($fact) => filled($fact['value'] ?? null)))
            @if ($facts->isNotEmpty())
                <dl class="mt-6 space-y-3 border-t border-white/15 pt-6 text-sm">
                    @foreach ($facts as $fact)
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-cream-100/70">{{ $fact['label'] ?? '' }}</dt>
                            <dd class="text-right font-semibold">{{ $fact['value'] ?? '' }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            <button
                type="button"
                data-booking-open
                class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400"
            >
                Book This Adventure
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </button>
            @if ($page->price_from && $page->is_published)
                <button
                    type="button"
                    data-dialog-open="instant-quote"
                    class="mt-3 flex w-full items-center justify-center gap-2 rounded-full bg-cream-50 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-white"
                >
                    Get an Instant Quote
                </button>
            @endif
            <button
                type="button"
                data-dialog-open="question"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-full border border-white/25 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-cream-50 transition hover:border-white/50"
            >
                Ask a Question
            </button>
            <a
                href="https://wa.me/{{ config('site.whatsapp') }}?text={{ rawurlencode('Hi Serengeti Roaming! I have a question about the '.$page->package_name.' ('.$page->url().').') }}"
                target="_blank"
                rel="noopener"
                class="mt-3 flex w-full items-center justify-center gap-2 rounded-full border border-white/25 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-cream-50 transition hover:border-white/50"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4a.5.5 0 0 0 0-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7a2.8 2.8 0 0 0 1.8-1.3 2.3 2.3 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>
                Ask on WhatsApp
            </a>

            <a
                href="{{ $page->pdfUrl() }}"
                class="mt-3 flex items-center justify-center gap-2 rounded-full px-6 py-2 text-sm font-semibold text-gold-300 transition hover:text-gold-400"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></svg>
                Download Itinerary (PDF)
            </a>

            <p class="mt-4 text-center text-xs text-cream-100/60">
                No payment taken online — we confirm availability first.
            </p>
        </div>
    </aside>
    </div>
</section>

<x-booking-dialog :selected="$page->bookingLabel()" />
<x-question-dialog :tour-page="$page" />
@if ($page->price_from && $page->is_published)
    <x-instant-quote-dialog :tour-page="$page" />
@endif
<x-review-dialog />
<x-write-review-dialog :tour-page="$page" />

@if (! empty($page->included) || ! empty($page->excluded))
<section class="mx-auto max-w-3xl px-4 py-16 text-center lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">What's included</h2>
    <div class="mt-8 grid gap-8 text-left sm:grid-cols-2">
        <div>
            <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-gold-600">Included</h3>
            <ul class="mt-3 space-y-2 text-sm text-charcoal-600">
                @foreach ($page->included ?? [] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Not Included</h3>
            <ul class="mt-3 space-y-2 text-sm text-charcoal-600">
                @foreach ($page->excluded ?? [] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
@endif

@if ($reviews->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 pb-16 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="font-display text-2xl font-semibold text-forest-900">Reviews of this trip</h2>
            <div class="mt-2 flex items-center gap-2 text-sm text-charcoal-600">
                <x-review-stars :rating="(int) round($reviewStats->average)" />
                {{ number_format($reviewStats->average, 1) }} · {{ $reviewStats->total }} {{ \Illuminate\Support\Str::plural('review', (int) $reviewStats->total) }}
            </div>
        </div>
        <div class="flex gap-4 text-sm font-semibold">
            <button type="button" data-dialog-open="review" class="text-forest-800 hover:text-forest-600">Write a review</button>
            <a href="{{ route('reviews') }}" class="text-gold-600 hover:text-gold-500">All reviews →</a>
        </div>
    </div>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reviews as $review)
            <x-review-card :review="$review" />
        @endforeach
    </div>
</section>
@endif

@if ($faqs->isNotEmpty())
<section class="mx-auto max-w-3xl px-4 pb-16 lg:px-8">
    <h2 class="text-center font-display text-2xl font-semibold text-forest-900">Questions about this trip</h2>
    <div class="mt-8 divide-y divide-forest-100 overflow-hidden rounded-2xl border border-forest-100 bg-white">
        @foreach ($faqs as $faq)
            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 marker:content-none">
                    <span class="font-display text-base font-semibold text-forest-900">{{ $faq->question }}</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-charcoal-500 transition duration-200 group-open:rotate-180" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
                </summary>
                <p class="whitespace-pre-line px-6 pb-6 text-base leading-relaxed text-charcoal-600">{{ $faq->answer }}</p>
            </details>
        @endforeach
    </div>
    <p class="mt-4 text-center text-sm text-charcoal-600">
        Something else? <button type="button" data-dialog-open="question" class="font-semibold text-gold-600 hover:text-gold-500">Ask a question</button>
    </p>
</section>
@endif

@if (! empty($page->experiences))
<section class="mx-auto max-w-7xl px-4 pb-16 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">{{ $page->experiences_heading ?: 'Explore '.$page->category->getLabel() }}</h2>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($page->experiences as $experience)
            <div @if(!empty($experience['anchor'])) id="{{ $experience['anchor'] }}" @endif class="scroll-mt-24 overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                @if (!empty($experience['image']))
                    <img
                        src="{{ $img($experience['image']) }}"
                        alt="{{ $experience['title'] ?? '' }}"
                        loading="lazy"
                        class="h-44 w-full object-cover"
                    >
                @endif
                <div class="p-5">
                    <h3 class="font-display text-lg font-semibold text-forest-900">{{ $experience['title'] ?? '' }}</h3>
                    @if (!empty($experience['copy']))
                        <p class="mt-2 text-base leading-relaxed text-charcoal-600">{{ $experience['copy'] }}</p>
                    @endif
                    @if (!empty($experience['price']))
                        <p class="mt-4 text-sm font-semibold text-gold-600">{{ $experience['price'] }} per person</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

@if ($others->isNotEmpty())
<section class="mx-auto max-w-7xl px-4 pb-20 lg:px-8">
    <h2 class="font-display text-2xl font-semibold text-forest-900">More {{ $page->category->getLabel() }} packages</h2>
    <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($others as $other)
            <x-package-card :page="$other" />
        @endforeach
    </div>
</section>
@endif
@endsection
