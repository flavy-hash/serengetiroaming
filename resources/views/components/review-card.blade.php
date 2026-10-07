@props(['review'])

{{-- Same design as the homepage review placeholders. "Read Review" opens the full text
     (the <template> below) in the #reviewDialog popup — see <x-review-dialog> and reviews.js. --}}
@php
    $photos = array_slice($review->photoUrls(), 0, 2);
    $byline = collect([$review->country, $review->trip_date])->filter()->implode(' · ');
    $trip = $review->tourPage?->is_published ? $review->tourPage : null;
@endphp

<div class="flex flex-col overflow-hidden rounded-2xl border border-forest-100 bg-white p-5 text-left shadow-sm" data-review-card>
    @if ($photos)
        <div class="grid {{ count($photos) > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
            @foreach ($photos as $photo)
                <img src="{{ $photo }}" alt="Photo from {{ $review->name }}'s trip" loading="lazy" class="h-28 w-full rounded-xl object-cover">
            @endforeach
        </div>
    @endif

    <div class="{{ $photos ? 'mt-4' : '' }} flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cream-100 text-sm font-semibold text-forest-800">{{ $review->initials() }}</span>
        <div>
            <x-review-stars :rating="$review->rating" />
            <p class="text-sm text-charcoal-900">
                <span class="font-semibold">{{ $review->name }}</span>
                @if ($byline)
                    <span class="text-charcoal-500">/ {{ $byline }}</span>
                @endif
            </p>
        </div>
    </div>

    <h3 class="mt-4 font-display text-lg font-semibold text-forest-900">{{ $review->title }}</h3>
    <p class="mt-2 text-sm leading-relaxed text-charcoal-600">{{ \Illuminate\Support\Str::limit($review->body, 180) }}</p>

    <div class="mt-auto pt-5">
        <div class="flex items-center justify-between gap-3 border-t border-forest-100 pt-4">
            <button type="button" data-read-review class="text-sm font-semibold text-charcoal-900 hover:text-gold-600">
                Read Review
            </button>
            @if ($trip)
                <a href="{{ $trip->url() }}" class="truncate text-xs font-medium text-gold-600 hover:text-gold-500">{{ $trip->package_name }}</a>
            @endif
        </div>
    </div>

    <template>
        @if ($review->photoUrls())
            <div class="grid grid-cols-2 gap-2">
                @foreach ($review->photoUrls() as $photo)
                    <img src="{{ $photo }}" alt="Photo from {{ $review->name }}'s trip" class="h-40 w-full rounded-xl object-cover">
                @endforeach
            </div>
        @endif
        <div class="mt-4 flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cream-100 text-sm font-semibold text-forest-800">{{ $review->initials() }}</span>
            <div>
                <x-review-stars :rating="$review->rating" />
                <p class="text-sm text-charcoal-900">
                    <span class="font-semibold">{{ $review->name }}</span>
                    @if ($byline)
                        <span class="text-charcoal-500">/ {{ $byline }}</span>
                    @endif
                </p>
            </div>
        </div>
        <h3 class="mt-4 font-display text-xl font-semibold text-forest-900">{{ $review->title }}</h3>
        <div class="mt-3 space-y-3 text-sm leading-relaxed text-charcoal-600">
            @foreach (preg_split('/\R{2,}/', trim($review->body)) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
        @if ($trip)
            <a href="{{ $trip->url() }}" class="mt-4 inline-block text-sm font-semibold text-gold-600 hover:text-gold-500">{{ $trip->package_name }} →</a>
        @endif
    </template>
</div>
