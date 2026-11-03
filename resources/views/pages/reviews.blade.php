@extends('layouts.app')

@section('title', 'Guest Reviews')
@section('description', 'Reviews from travellers who explored Tanzania with Serengeti Roaming African Safaris.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([\App\Support\Seo::breadcrumbs(['Reviews' => route('reviews')])]) !!}
@endpush

@section('content')
@php($banner = \App\Models\PageBanner::for('reviews'))
<section class="page-banner" @if ($banner->imageUrl()) style="background-image: url('{{ $banner->imageUrl() }}')" @endif>
    <x-banner-video :src="$banner->videoUrl()" :poster="$banner->imageUrl()" />
    <div class="page-banner__inner">
        <nav class="page-banner__breadcrumbs" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">Home</a>
            <span>/</span>
            <span aria-current="page">Reviews</span>
        </nav>

        <h1 class="page-banner__title">{{ $banner->heading() }}</h1>
        <p class="page-banner__text">{{ $banner->subheading() }}</p>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-16 lg:px-8">
    <div class="flex flex-col items-center justify-between gap-6 rounded-2xl border border-forest-100 bg-white p-6 sm:flex-row">
        <div class="flex items-center gap-4">
            @if ($average)
                <p class="font-display text-5xl font-semibold text-forest-900">{{ number_format($average, 1) }}</p>
                <div>
                    <x-review-stars :rating="(int) round($average)" :size="18" />
                    <p class="mt-1 text-sm text-charcoal-600">Based on {{ $total }} {{ \Illuminate\Support\Str::plural('review', $total) }}</p>
                </div>
            @else
                <p class="text-sm text-charcoal-600">We're just getting started — no reviews yet. Be the first to share your trip.</p>
            @endif
        </div>
        <button type="button" data-dialog-open="review" class="inline-flex shrink-0 items-center gap-2 rounded-full bg-forest-800 px-6 py-3 text-sm font-semibold text-cream-50 transition hover:bg-forest-700">
            Write a Review
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>
        </button>
    </div>

    @if ($reviews->isNotEmpty())
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($reviews as $review)
                <x-review-card :review="$review" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $reviews->links() }}
        </div>
    @else
        {{-- Same placeholder cards as the homepage until the first review is published. --}}
        <x-review-placeholders />
    @endif

    <x-review-badges class="mt-12" />
</section>

<x-review-dialog />
<x-write-review-dialog />
@endsection
