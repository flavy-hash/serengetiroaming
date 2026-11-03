@extends('layouts.app')

@php
    $banner = \App\Models\PageBanner::for('vehicles');
@endphp

@section('title', 'Our Safari Vehicles')
@section('description', 'The vehicles Serengeti Roaming African Safaris uses for safaris and transfers in Tanzania — private 4×4 Land Cruisers with pop-up roofs, driven by our own guides.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs(['About' => route('about'), 'Our Vehicles' => route('vehicles')]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ route('about') }}">About</a>
            <span>/</span>
            <span aria-current="page">Our Vehicles</span>
        </nav>

        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    @if ($vehicles->isEmpty())
        <p class="py-16 text-center text-sm text-charcoal-600">
            Photos and details of our vehicles are coming soon.
            <a href="{{ route('contact') }}" class="font-semibold text-gold-600 hover:text-gold-500">Ask us about our fleet →</a>
        </p>
    @else
        <div class="grid gap-8 md:grid-cols-2 {{ $vehicles->count() > 2 ? 'lg:grid-cols-3' : '' }}">
            @foreach ($vehicles as $vehicle)
                @php($features = $vehicle->featureList())
                <article class="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm" id="{{ $vehicle->anchor() }}">
                    <div class="relative h-60 overflow-hidden bg-gradient-to-br from-forest-700 to-forest-950">
                        @if ($src = $vehicle->imageUrl())
                            <img src="{{ $src }}" alt="{{ $vehicle->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-cream-100/40" aria-hidden="true">
                                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 16V9a2 2 0 0 1 2-2h9l4 4h1a2 2 0 0 1 2 2v3h-2" /><path d="M3 16h2m4 0h6" /><circle cx="7" cy="16.5" r="2" /><circle cx="17" cy="16.5" r="2" /><path d="M8 7V5h5v2" /></svg>
                            </div>
                        @endif
                        @if (filled($vehicle->category))
                            <span class="absolute left-3 top-3 rounded-full bg-forest-900/90 px-3 py-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-cream-50">
                                {{ $vehicle->category }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="font-display text-xl font-semibold text-forest-900">{{ $vehicle->name }}</h2>
                            @if ($vehicle->seats)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-cream-100 px-3 py-1 text-xs font-semibold text-charcoal-700">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5" /><path d="M3 20a6 6 0 0 1 12 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6 6 0 0 1 3 5.5" /></svg>
                                    {{ $vehicle->seats }} {{ \Illuminate\Support\Str::plural('seat', $vehicle->seats) }}
                                </span>
                            @endif
                        </div>

                        @if (filled($vehicle->description))
                            <p class="mt-3 text-sm leading-relaxed text-charcoal-600">{{ $vehicle->description }}</p>
                        @endif

                        @if ($features)
                            <ul class="mt-5 space-y-2.5">
                                @foreach ($features as $feature)
                                    <li class="flex items-start gap-2 text-sm text-charcoal-700">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-gold-600" aria-hidden="true"><path d="m5 12 4.5 4.5L19 7" /></svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto pt-6">
                            <a href="{{ route('contact', ['subject' => 'Vehicle: '.$vehicle->name]) }}#contact-form"
                               class="inline-flex items-center gap-1 rounded-full border border-forest-800 px-4 py-2 text-sm font-semibold text-forest-800 transition hover:bg-forest-800 hover:text-cream-50">
                                Ask about this vehicle
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <p class="mt-10 text-center text-sm text-charcoal-500">
            Photos show the vehicle models we use; the vehicle for your trip may differ in colour and fit-out.
            Tell us your group size and we'll match the right vehicle.
        </p>
    @endif
</div>
@endsection
