@extends('layouts.app')

@php
    $banner = \App\Models\PageBanner::for('explore-zanzibar');
@endphp

@section('title', 'Explore Zanzibar — Beaches, Stone Town & Ocean Adventures')
@section('description', 'Stone Town, Nungwi, Paje, Jozani Forest, spice farms and more — explore every corner of Zanzibar with Serengeti Roaming African Safaris.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        \App\Support\Seo::breadcrumbs(['Zanzibar' => url('/zanzibar'), 'Explore Zanzibar' => route('zanzibar.explore')]),
    ]) !!}
@endpush

@section('content')
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <a href="{{ url('/zanzibar') }}">Zanzibar</a>
            <span>/</span>
            <span aria-current="page">Explore Zanzibar</span>
        </nav>

        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<section class="about-page about-page--team">
    <h2 class="about-heading">Discover the Island, Its Beaches, Culture &amp; Ocean Adventures</h2>
    <div class="about-prose">
        <p>Zanzibar is far more than a beach destination. Every part of the island has its own character: the historic lanes of Stone Town, spice farms in the green interior, the lively northern beaches, the tidal villages of the east coast and the clear Indian Ocean all around. Our Zanzibar holidays bring these sides together, so you can mix beach time, culture, nature and ocean adventures in one well-planned trip.</p>
    </div>

    <h2 class="about-heading">Explore Zanzibar Islands</h2>
</section>

<section class="zan-wrap" aria-label="Zanzibar areas and islands">
    @if ($areas->isEmpty())
        <p class="text-center text-charcoal-600">Our Zanzibar guides are coming soon.</p>
    @else
        <div class="zan-grid">
            @foreach ($areas as $area)
                <x-explore-card :area="$area" />
            @endforeach
        </div>
    @endif
</section>

<section class="about-page about-page--after">
    <div class="about-actions">
        <a class="btn btn--primary" href="{{ url('/zanzibar') }}">See Zanzibar holidays</a>
        <a class="btn btn--dark" href="{{ url('/contact') }}#contact-form">Plan Your Safari</a>
    </div>
</section>
@endsection
