@extends('layouts.app')

@php
    $paragraphs = $stay->paragraphs();
    $cover = $photos[0] ?? null;
    $whatsapp = 'https://wa.me/'.config('site.whatsapp').'?text='.rawurlencode('Hi Serengeti Roaming! I would like to know more about staying at '.$stay->name.'.');
@endphp

@section('title', $stay->name.' — '.$stay->location)
@section('description', \Illuminate\Support\Str::limit($paragraphs[0] ?? ($stay->name.' in '.$stay->location.', hand-picked by Serengeti Roaming African Safaris.'), 155))
@section('og_image', $cover ?? '')
@unless ($stay->is_published)
    @section('robots', 'noindex, nofollow')
@endunless

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        array_filter([
            '@type' => 'LodgingBusiness',
            'name' => $stay->name,
            'description' => $paragraphs[0] ?? null,
            'url' => $stay->url(),
            'image' => $photos ?: null,
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => $stay->location, 'addressCountry' => 'TZ'],
        ]),
        \App\Support\Seo::breadcrumbs([
            'Accommodation' => route('accommodations'),
            $stay->name => $stay->url(),
        ]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($cover) style="background-image: url('{{ $cover }}')" @endif>
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ route('accommodations') }}">Accommodation</a>
            <span>/</span>
            <span aria-current="page">{{ $stay->name }}</span>
        </nav>

        <a href="{{ route('accommodations', ['location' => $stay->location_key]) }}" class="page-banner__badge">{{ $stay->location }}</a>
        <h1 class="page-banner__title">{{ $stay->name }}</h1>
        @if (filled($stay->type) || $stay->level)
            <p class="page-banner__text">{{ collect([$stay->type, $stay->level?->getLabel()])->filter()->implode(' · ') }}</p>
        @endif
    </div>
</section>

<section class="py-16">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-[1fr_20rem] lg:items-start lg:px-8">
        <div>
            <h2 class="font-display text-3xl font-semibold text-forest-900">About {{ $stay->name }}</h2>
            @if ($paragraphs)
                <div class="mt-6 space-y-4 text-lg leading-relaxed text-charcoal-600">
                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @else
                <p class="mt-6 text-lg leading-relaxed text-charcoal-600">Ask us about {{ $stay->name }} and we will share rates, rooms and the best time to stay.</p>
            @endif

            @if (count($photos) > 0)
                <h3 class="mt-12 font-display text-2xl font-semibold text-forest-900">Photo Gallery</h3>
                <div class="stay-gallery mt-6" data-gallery>
                    @foreach ($photos as $i => $src)
                        <button type="button" class="stay-gallery__item {{ $i === 0 && count($photos) > 2 ? 'stay-gallery__item--wide' : '' }}"
                                data-gallery-item data-src="{{ $src }}" aria-label="Open photo {{ $i + 1 }} of {{ count($photos) }}">
                            <img src="{{ $src }}" alt="{{ $stay->name }} — photo {{ $i + 1 }}" loading="{{ $i < 3 ? 'eager' : 'lazy' }}" decoding="async">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="lg:sticky lg:top-[calc(var(--nav-h)+1.5rem)]">
            <div class="rounded-2xl bg-forest-900 p-7 text-cream-50">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">Stay details</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-cream-100/70">Area</dt>
                        <dd class="mt-0.5 font-semibold">{{ $stay->location }}</dd>
                    </div>
                    @if (filled($stay->type))
                        <div>
                            <dt class="text-cream-100/70">Type</dt>
                            <dd class="mt-0.5 font-semibold">{{ $stay->type }}</dd>
                        </div>
                    @endif
                    @if ($stay->level)
                        <div>
                            <dt class="text-cream-100/70">Style</dt>
                            <dd class="mt-0.5 font-semibold">{{ $stay->level->getLabel() }}</dd>
                        </div>
                    @endif
                    @if ($packages->isNotEmpty())
                        <div>
                            <dt class="text-cream-100/70">Included in</dt>
                            <dd class="mt-0.5 font-semibold"><a href="#packages" class="underline decoration-gold-400/60 underline-offset-4 hover:text-gold-300">{{ $packages->count() }} {{ \Illuminate\Support\Str::plural('package', $packages->count()) }}</a></dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('contact', ['subject' => 'Stay: '.$stay->name]) }}#contact-form"
                   class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400">
                    Enquire About This Stay
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                   class="mt-3 flex w-full items-center justify-center gap-2 rounded-full border border-white/25 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-cream-50 transition hover:border-white/50">
                    Ask on WhatsApp
                </a>
            </div>
        </aside>
    </div>
</section>

@if ($packages->isNotEmpty())
    <section id="packages" class="scroll-mt-28 bg-cream-100 py-16">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <h2 class="text-center font-display text-3xl font-semibold text-forest-900">Trips that Stay at {{ $stay->name }}</h2>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $page)
                    <x-package-card :page="$page" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@if ($others->isNotEmpty())
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="font-display text-3xl font-semibold text-forest-900">More Places to Stay</h2>
                <a href="{{ route('accommodations') }}" class="text-sm font-semibold text-gold-600 hover:text-gold-500">See all accommodation →</a>
            </div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $other)
                    <a href="{{ $other->url() }}" class="group block overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:shadow-md">
                        <div class="h-48 overflow-hidden bg-gradient-to-br from-forest-700 to-forest-950">
                            @if ($src = $other->imageUrl())
                                <img src="{{ $src }}" alt="{{ $other->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-display text-lg font-semibold text-forest-900">{{ $other->name }}</h3>
                            <p class="mt-1 text-sm text-charcoal-600">{{ $other->location }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<dialog class="lightbox" id="lightbox" aria-label="Photo gallery">
    <button class="lightbox__close" type="button" data-lightbox-close aria-label="Close gallery">✕</button>
    <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Previous photo">‹</button>
    <img class="lightbox__img" alt="" data-lightbox-img>
    <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Next photo">›</button>
    <p class="lightbox__count" data-lightbox-count></p>
</dialog>
@endsection
