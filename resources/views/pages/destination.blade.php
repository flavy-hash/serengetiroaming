@extends('layouts.app')

@php
    $highlights = $destination->highlightList();
    $whatsapp = 'https://wa.me/'.config('site.whatsapp').'?text='.rawurlencode('Hi Serengeti Roaming! I would like to plan a trip to '.$destination->name.'.');
@endphp

@section('title', $destination->name.' — Tanzania Safari Destination')
@section('description', $destination->meta_description ?: ($destination->tagline ?: 'Visit '.$destination->name.' with Serengeti Roaming African Safaris.'))
@section('og_image', $destination->imageUrl() ?? '')
@unless ($destination->is_published)
    @section('robots', 'noindex, nofollow')
@endunless

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        array_filter([
            '@type' => 'TouristDestination',
            'name' => $destination->name,
            'description' => $destination->tagline,
            'url' => $destination->url(),
            'image' => $destination->imageUrl(),
        ]),
        \App\Support\Seo::breadcrumbs([
            'Destinations' => route('destinations'),
            $destination->name => $destination->url(),
        ]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($destination->imageUrl()) style="background-image: url('{{ $destination->imageUrl() }}')" @endif>
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ route('destinations') }}">Destinations</a>
            <span>/</span>
            <span aria-current="page">{{ $destination->name }}</span>
        </nav>

        @if (filled($destination->region))
            <a href="{{ route('destinations', ['region' => $destination->region_key]) }}" class="page-banner__badge">{{ $destination->region }}</a>
        @endif
        <h1 class="page-banner__title">{{ $destination->name }}</h1>
        @if (filled($destination->tagline))
            <p class="page-banner__text">{{ $destination->tagline }}</p>
        @endif
    </div>
</section>

<section class="py-16">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 lg:grid-cols-[1fr_20rem] lg:items-start lg:px-8">
        <div>
            <h2 class="font-display text-3xl font-semibold text-forest-900">About {{ $destination->name }}</h2>
            @if ($paragraphs = $destination->paragraphs())
                <div class="mt-6 space-y-4 text-lg leading-relaxed text-charcoal-600">
                    @foreach ($paragraphs as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @endif

            @if ($highlights)
                <h3 class="mt-12 font-display text-2xl font-semibold text-forest-900">Highlights</h3>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($highlights as $highlight)
                        <li class="flex items-start gap-3 rounded-2xl border border-forest-100 bg-white p-4 text-sm text-charcoal-700">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-gold-600" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7" /></svg>
                            {{ $highlight }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <aside class="lg:sticky lg:top-[calc(var(--nav-h)+1.5rem)]">
            <div class="rounded-2xl bg-forest-900 p-7 text-cream-50">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">Plan your visit</p>
                <dl class="mt-5 space-y-4 text-sm">
                    @if (filled($destination->region))
                        <div>
                            <dt class="text-cream-100/70">Region</dt>
                            <dd class="mt-0.5 font-semibold">{{ $destination->region }}</dd>
                        </div>
                    @endif
                    @if (filled($destination->best_time))
                        <div>
                            <dt class="text-cream-100/70">Best time to visit</dt>
                            <dd class="mt-0.5 font-semibold">{{ $destination->best_time }}</dd>
                        </div>
                    @endif
                    @if ($packages->isNotEmpty())
                        <div>
                            <dt class="text-cream-100/70">Our trips here</dt>
                            <dd class="mt-0.5 font-semibold"><a href="#packages" class="underline decoration-gold-400/60 underline-offset-4 hover:text-gold-300">{{ $packages->count() }} {{ \Illuminate\Support\Str::plural('package', $packages->count()) }}</a></dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('contact', ['subject' => 'Trip to '.$destination->name]) }}#contact-form"
                   class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400">
                    Plan a Trip Here
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
            <h2 class="text-center font-display text-3xl font-semibold text-forest-900">Safaris &amp; Tours that Visit {{ $destination->name }}</h2>
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
                <h2 class="font-display text-3xl font-semibold text-forest-900">More Destinations</h2>
                <a href="{{ route('destinations') }}" class="text-sm font-semibold text-gold-600 hover:text-gold-500">See all destinations →</a>
            </div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $other)
                    <x-destination-card :destination="$other" heading="h3" />
                @endforeach
            </div>
        </div>
    </section>
@endif
@endsection
