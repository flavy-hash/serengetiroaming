@extends('layouts.app')

@section('title', $title)
@section('description', $subtitle)
@section('robots', 'noindex, follow')

@section('content')
<section class="page-banner">
    <div class="page-banner__inner">
        <p class="page-banner__eyebrow">Serengeti Roaming African Safaris</p>
        <h1 class="page-banner__title">{{ $title }}</h1>
        <p class="page-banner__text">{{ $subtitle }}</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-24 text-center lg:px-8">
    <p class="text-sm text-charcoal-500">This page is coming soon — check back shortly.</p>
    <a href="{{ url('/') }}" class="mt-8 inline-flex items-center justify-center rounded-full bg-forest-800 px-6 py-3 text-sm font-semibold text-cream-50 transition hover:bg-forest-700">
        Back to Home
    </a>
</section>
@endsection
