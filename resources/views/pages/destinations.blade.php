@extends('layouts.app')

@php
    $banner = \App\Models\PageBanner::for('destinations');
    $heading = $activeRegion ? $activeRegion.' Destinations' : $banner->heading();
    $chip = 'shrink-0 rounded-full border border-forest-100 bg-white px-4 py-2 text-sm font-semibold text-forest-900 transition hover:border-forest-600 aria-[current=true]:border-forest-800 aria-[current=true]:bg-forest-800 aria-[current=true]:text-cream-50';
@endphp

@section('title', $activeRegion ? $activeRegion.' Destinations' : 'Tanzania Safari Destinations')
@section('description', $activeRegion
    ? 'Parks and places to visit in Tanzania\'s '.$activeRegion.' — safaris and tours by Serengeti Roaming African Safaris.'
    : 'Explore Tanzania\'s safari destinations — the Serengeti, Ngorongoro Crater, Tarangire, Kilimanjaro, Zanzibar and more.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs(array_filter([
            'Destinations' => route('destinations'),
            $heading => $activeRegion ? request()->fullUrl() : null,
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
            @if ($activeRegion)
                <a href="{{ route('destinations') }}">Destinations</a>
                <span>/</span>
                <span aria-current="page">{{ $activeRegion }}</span>
            @else
                <span aria-current="page">Destinations</span>
            @endif
        </nav>

        <h1 class="page-banner__title">{{ $heading }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    @if (count($chips) > 2)
        <nav class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="Regions">
            @foreach ($chips as $link)
                <a href="{{ $link['url'] }}" class="{{ $chip }}" aria-current="{{ $link['active'] ? 'true' : 'false' }}">{{ $link['label'] }}</a>
            @endforeach
        </nav>
    @endif

    @if ($destinations->isEmpty())
        <p class="py-16 text-center text-sm text-charcoal-600">
            {{ $activeRegion ? 'No destinations listed in the '.$activeRegion.' yet.' : 'Our destination guides are coming soon.' }}
            <a href="{{ route('destinations') }}" class="font-semibold text-gold-600 hover:text-gold-500">See all destinations →</a>
        </p>
    @else
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($destinations as $destination)
                <x-destination-card :destination="$destination" />
            @endforeach
        </div>
    @endif
</div>
@endsection
