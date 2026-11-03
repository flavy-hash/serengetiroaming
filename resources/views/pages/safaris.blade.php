@extends('layouts.app')

@php
    // Banner image and text are editable in the admin (Site Settings → Page Banners).
    $banner = \App\Models\PageBanner::for('safaris');
    $visible = $pages->filter($matches);
    $chip = 'shrink-0 rounded-full border border-forest-100 bg-white px-4 py-2 text-sm font-semibold text-forest-900 transition hover:border-forest-600 aria-[current=true]:border-forest-800 aria-[current=true]:bg-forest-800 aria-[current=true]:text-cream-50';
    $groups = [
        ['param' => 'section', 'label' => 'Destination', 'min' => 2],
        ['param' => 'tier', 'label' => 'Style', 'min' => 1],
        ['param' => 'days', 'label' => 'Duration', 'min' => 2],
    ];
@endphp

@section('title', $heading ?? 'Tanzania Safaris & Tours')
@section('description', 'Every Tanzania safari, Kilimanjaro climb, Zanzibar escape and day trip we offer — filter by destination, style and length.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        [
            '@type' => 'ItemList',
            'name' => 'Tanzania safaris and tours',
            'numberOfItems' => $pages->count(),
            'itemListElement' => $pages->values()->map(fn ($p, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $p->url(), 'name' => $p->package_name])->all(),
        ],
        \App\Support\Seo::breadcrumbs(['Safaris' => url('/safaris')]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            @if ($heading)
                <a href="{{ url('/safaris') }}">Safaris</a>
                <span>/</span>
                <span aria-current="page" data-filter-crumb>{{ $heading }}</span>
            @else
                <span aria-current="page">Safaris</span>
            @endif
        </nav>

        <h1 class="page-banner__title" data-filter-heading data-heading-default="{{ $banner->heading() }}">{{ $heading ?? $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-16 lg:px-8" data-package-filters>
    @if ($pages->isEmpty())
        <p class="py-12 text-center text-sm text-charcoal-500">Our packages are being put together — check back shortly, or tell us what you have in mind.</p>
    @else
        {{-- Each chip is a real link (works without JavaScript); the script filters instantly instead. --}}
        <div class="space-y-4 rounded-2xl border border-forest-100 bg-white p-5 lg:p-6">
            @foreach ($groups as $group)
                @continue (count($options[$group['param']]) < $group['min'])
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:gap-4">
                    <span class="w-28 shrink-0 text-xs font-semibold uppercase tracking-[0.2em] text-charcoal-500">{{ $group['label'] }}</span>
                    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1" role="group" aria-label="{{ $group['label'] }}">
                        @foreach ($chips[$group['param']] as $link)
                            <a href="{{ $link['url'] }}" class="{{ $chip }}" data-filter="{{ $group['param'] }}" data-value="{{ $link['value'] }}"
                               @if ($group['param'] === 'tier' && $link['value'] !== '') data-heading-part="{{ $link['label'] }}" @endif
                               @if ($group['param'] === 'section' && $link['value'] !== '') data-heading-part="{{ \App\Enums\TourCategory::from($link['value'])->getPackagesHeading() }}" @endif
                               aria-current="{{ $link['active'] ? 'true' : 'false' }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex items-center justify-between gap-4">
            <p class="text-sm text-charcoal-600" aria-live="polite">
                Showing <span class="font-semibold text-forest-900" data-filter-count>{{ $visible->count() }}</span>
                of {{ $pages->count() }} {{ \Illuminate\Support\Str::plural('package', $pages->count()) }}
            </p>
            <a href="{{ url('/safaris') }}" class="{{ $heading ? '' : 'hidden' }} text-sm font-semibold text-gold-600 hover:text-gold-500" data-filter-reset>Clear filters</a>
        </div>

        <div class="mt-6 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pages as $page)
                <div data-package data-section="{{ $page->category->value }}" data-tier="{{ $page->tier?->value }}" data-days="{{ $page->days() }}" @unless ($matches($page)) hidden @endunless>
                    <x-package-card :page="$page" />
                </div>
            @endforeach
        </div>

        <div class="{{ $visible->isEmpty() ? '' : 'hidden' }} py-12 text-center" data-filter-empty>
            <p class="text-sm text-charcoal-600">No packages match those filters yet — try another style or duration, or ask us to build one.</p>
            <a href="{{ url('/safaris') }}" class="mt-4 inline-block text-sm font-semibold text-gold-600 hover:text-gold-500" data-filter-reset-link>See all packages →</a>
        </div>
    @endif

    <section class="mt-16 rounded-2xl bg-forest-900 px-6 py-12 text-center text-cream-50">
        <h2 class="font-display text-2xl font-semibold lg:text-3xl">None of these quite right?</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm text-cream-100/80">
            Every trip can be tailored — tell us your dates, budget and what you'd love to see, and we'll build the itinerary around you.
        </p>
        <button type="button" data-booking-open class="mt-6 inline-flex items-center gap-2 rounded-full bg-gold-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-forest-900 transition hover:bg-gold-400">
            Plan a custom trip
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </section>
</div>

<x-booking-dialog />
@endsection
