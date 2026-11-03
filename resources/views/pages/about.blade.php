@extends('layouts.app')

@section('title', $page->title)
{{-- Never pass null here: @section with a null value opens a block section. --}}
@section('description', $page->meta_description ?: $page->banner_text ?: 'Locally owned, globally trusted, best prices.')

@push('structured-data')
    {!! \App\Support\Seo::jsonLd([
        ['@type' => 'AboutPage', 'url' => route('about'), 'about' => ['@id' => url('/').'#organization']],
        \App\Support\Seo::breadcrumbs([$page->title => route('about')]),
    ]) !!}
@endpush

@php
    $img = fn (?string $path) => \App\Models\TourPage::imageUrl($path);

    // The hero: the last word of the heading is set in italic gold; photos fall back to the site's own.
    $words = preg_split('/\s+/', trim($page->title)) ?: [];
    $lastWord = count($words) > 1 ? array_pop($words) : null;
    $heroPhoto = $img($page->banner_image) ?: asset('images/banners/about.jpg');
    $heroFrame = $img($page->intro_image) ?: $img(collect($page->team)->pluck('photo')->filter()->first()) ?: asset('images/banners/about-frame.jpg');
    $hasTeam = ! empty($page->team);
@endphp

@section('content')
<section class="about-hero" aria-labelledby="aboutHeroTitle">
    <div class="about-hero__inner">
        <div class="about-hero__copy">
            <p class="about-hero__eyebrow">{{ str_replace(', ', ' · ', config('site.location')) }}</p>

            <h1 class="about-hero__title" id="aboutHeroTitle">
                @if ($lastWord)
                    {{ implode(' ', $words) }} <em>{{ $lastWord }}</em>
                @else
                    {{ $page->title }}
                @endif
            </h1>

            @if ($page->banner_text)
                <p class="about-hero__lead">{{ $page->banner_text }}</p>
            @endif

            <div class="about-hero__actions">
                <a class="btn btn--primary" href="{{ $hasTeam ? '#team' : url('/contact') }}">
                    {{ $hasTeam ? 'Meet the team' : 'Plan my trip' }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </a>
                <a class="about-hero__link" href="{{ $hasTeam ? url('/contact') : url('/safaris') }}">{{ $hasTeam ? 'Plan my trip' : 'See our safaris' }}</a>
            </div>
        </div>

        <div class="about-hero__visual">
            <div class="about-hero__arch">
                <img src="{{ $heroPhoto }}" alt="{{ $page->title }}" fetchpriority="high" decoding="async">
            </div>

            <div class="about-hero__card">
                <strong>Karibu</strong>
                <span>Welcome to {{ Illuminate\Support\Str::before(config('site.location'), ',') }}</span>
            </div>

            <div class="about-hero__frame">
                <img src="{{ $heroFrame }}" alt="" loading="lazy" decoding="async">
            </div>
        </div>
    </div>
</section>

@if ($page->intro_heading || $page->intro_body)
<section class="py-20">
    <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 lg:px-8 {{ $page->intro_image ? 'lg:grid-cols-2' : '' }}">
        <div class="text-center lg:text-left">
            <p class="font-display text-sm uppercase tracking-[0.3em] text-gold-600">Serengeti Roaming African Safaris</p>
            @if ($page->intro_heading)
                <h2 class="mt-3 font-display text-3xl font-semibold text-forest-900 lg:text-4xl">{{ $page->intro_heading }}</h2>
            @endif
            @if ($page->intro_body)
                <div class="mt-6 space-y-4 text-lg leading-relaxed text-charcoal-600">
                    @foreach (preg_split('/\R{2,}/', trim($page->intro_body)) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @endif
        </div>
        @if ($page->intro_image)
            <div class="h-72 overflow-hidden rounded-2xl sm:h-96">
                <img src="{{ $img($page->intro_image) }}" alt="{{ $page->intro_heading }}" loading="lazy" class="h-full w-full object-cover">
            </div>
        @endif
    </div>
</section>
@endif

@if (! empty($page->highlights))
<section class="mx-auto max-w-6xl px-4 pb-20 lg:px-8">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($page->highlights as $highlight)
            <div class="rounded-2xl border border-forest-100 bg-white p-6">
                <h3 class="font-display text-lg font-semibold text-forest-900">{{ $highlight['title'] ?? '' }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-charcoal-600">{{ $highlight['copy'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

@if (! empty($page->team))
<section id="team" class="scroll-mt-24 bg-cream-100 py-20">
    <div class="mx-auto max-w-6xl px-4 lg:px-8">
        <h2 class="text-center font-display text-2xl font-semibold text-forest-900 lg:text-3xl">Our Team</h2>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($page->team as $member)
                <div class="overflow-hidden rounded-2xl border border-forest-100 bg-white">
                    @if (!empty($member['photo']))
                        <img src="{{ $img($member['photo']) }}" alt="{{ $member['name'] ?? '' }}" loading="lazy" class="h-56 w-full object-cover">
                    @endif
                    <div class="p-5">
                        <h3 class="font-display text-lg font-semibold text-forest-900">{{ $member['name'] ?? '' }}</h3>
                        @if (!empty($member['role']))
                            <p class="text-sm font-semibold text-gold-600">{{ $member['role'] }}</p>
                        @endif
                        @if (!empty($member['bio']))
                            <p class="mt-2 text-sm leading-relaxed text-charcoal-600">{{ $member['bio'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if (! empty($page->reviews))
<section id="reviews" class="scroll-mt-24 py-20">
    <div class="mx-auto max-w-6xl px-4 lg:px-8">
        <h2 class="text-center font-display text-2xl font-semibold text-forest-900 lg:text-3xl">What Our Guests Say</h2>
        <div class="mt-10 grid gap-6 lg:grid-cols-3">
            @foreach ($page->reviews as $review)
                <figure class="rounded-2xl border border-forest-100 bg-white p-6">
                    @php($rating = (int) ($review['rating'] ?? 5))
                    <div class="text-gold-500" aria-label="{{ $rating }} out of 5 stars">{{ str_repeat('★', $rating) }}<span class="text-forest-100">{{ str_repeat('★', 5 - $rating) }}</span></div>
                    <blockquote class="mt-3 text-sm leading-relaxed text-charcoal-600">“{{ $review['quote'] ?? '' }}”</blockquote>
                    <figcaption class="mt-4 text-sm font-semibold text-forest-900">
                        {{ $review['name'] ?? '' }}
                        @if (!empty($review['country']))
                            <span class="font-normal text-charcoal-500">· {{ $review['country'] }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

@if (! empty($page->faqs))
<section id="faq" class="scroll-mt-24 mx-auto max-w-3xl px-4 pb-20 lg:px-8 {{ empty($page->reviews) ? 'pt-20' : '' }}">
    <h2 class="text-center font-display text-2xl font-semibold text-forest-900 lg:text-3xl">Frequently Asked Questions</h2>
    <div class="mt-10 divide-y divide-forest-100 overflow-hidden rounded-2xl border border-forest-100 bg-white">
        @foreach ($page->faqs as $faq)
            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 marker:content-none">
                    <span class="font-display text-lg font-semibold text-forest-900">{{ $faq['question'] ?? '' }}</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-charcoal-500 transition duration-200 group-open:rotate-180" aria-hidden="true"><path d="m18 15-6-6-6 6" /></svg>
                </summary>
                <p class="px-6 pb-6 text-sm leading-relaxed text-charcoal-600">{{ $faq['answer'] ?? '' }}</p>
            </details>
        @endforeach
    </div>
</section>
@endif
@endsection
