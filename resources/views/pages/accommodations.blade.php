@extends('layouts.app')

@php
    $banner = \App\Models\PageBanner::for('accommodations');
    $heading = $activeLocation ? 'Stays in '.$activeLocation : $banner->heading();
    $chip = 'shrink-0 rounded-full border border-forest-100 bg-white px-4 py-2 text-sm font-semibold text-forest-900 transition hover:border-forest-600 aria-[current=true]:border-forest-800 aria-[current=true]:bg-forest-800 aria-[current=true]:text-cream-50';
@endphp

@section('title', $activeLocation ? 'Stays in '.$activeLocation : 'Accommodation')
@section('description', $activeLocation
    ? 'Lodges, tented camps and hotels in '.$activeLocation.', hand-picked by Serengeti Roaming African Safaris.'
    : 'Hand-picked lodges, tented camps and hotels across Tanzania — from Arusha and the Serengeti to Zanzibar.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs(array_filter([
            'Accommodation' => route('accommodations'),
            $heading => $activeLocation ? request()->fullUrl() : null,
        ])),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            @if ($activeLocation)
                <a href="{{ route('accommodations') }}">Accommodation</a>
                <span>/</span>
                <span aria-current="page">{{ $activeLocation }}</span>
            @else
                <span aria-current="page">Accommodation</span>
            @endif
        </nav>

        <h1 class="page-banner__title">{{ $heading }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    @if (count($chips) > 2)
        <nav class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="Areas">
            @foreach ($chips as $link)
                <a href="{{ $link['url'] }}" class="{{ $chip }}" aria-current="{{ $link['active'] ? 'true' : 'false' }}">{{ $link['label'] }}</a>
            @endforeach
        </nav>
    @endif

    @if ($stays->isEmpty())
        <div class="py-16 text-center">
            <p class="text-sm text-charcoal-600">
                {{ $activeLocation ? 'No stays listed in '.$activeLocation.' yet.' : 'Our list of lodges and camps is being put together.' }}
                Tell us what you are looking for and we'll suggest the right place.
            </p>
            @if ($activeLocation)
                <a href="{{ route('accommodations') }}" class="mt-4 inline-block text-sm font-semibold text-gold-600 hover:text-gold-500">See all stays →</a>
            @endif
        </div>
    @else
        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($stays as $stay)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm" id="stay-{{ $stay->id }}">
                    <div class="relative h-56 overflow-hidden bg-gradient-to-br from-forest-700 to-forest-950">
                        @if ($src = $stay->imageUrl())
                            <img src="{{ $src }}" alt="{{ $stay->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-cream-100/40" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V10l7-5 7 5v11M9 21v-6h6v6" /></svg>
                            </div>
                        @endif
                        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-forest-900/90 px-3 py-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-cream-50">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                            {{ $stay->location }}
                        </span>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <h2 class="font-display text-xl font-semibold text-forest-900">{{ $stay->name }}</h2>
                        @if (filled($stay->type) || $stay->level)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if (filled($stay->type))
                                    <span class="rounded-full bg-cream-100 px-3 py-1 text-xs font-medium text-charcoal-700">{{ $stay->type }}</span>
                                @endif
                                @if ($stay->level)
                                    <span class="rounded-full bg-gold-500/15 px-3 py-1 text-xs font-medium text-gold-600">{{ $stay->level->getLabel() }}</span>
                                @endif
                            </div>
                        @endif
                        @if (filled($stay->description))
                            <p class="mt-4 text-sm leading-relaxed text-charcoal-600">{{ $stay->description }}</p>
                        @endif
                        <div class="mt-auto pt-5">
                            <a href="{{ route('contact', ['subject' => 'Stay: '.$stay->name]) }}#contact-form"
                               class="inline-flex items-center gap-1 rounded-full border border-forest-800 px-4 py-2 text-sm font-semibold text-forest-800 transition hover:bg-forest-800 hover:text-cream-50">
                                Enquire
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
