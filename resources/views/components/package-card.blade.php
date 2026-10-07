@props(['page'])

{{-- Same design as the "Featured Packages" cards on the homepage (welcome.blade.php). --}}
@php
    $image = \App\Models\TourPage::imageUrl($page->banner_image);
    $difficulty = collect($page->facts)->first(fn ($fact) => strcasecmp($fact['label'] ?? '', 'Difficulty') === 0)['value'] ?? null;
    $tags = collect([$page->duration, $page->tier?->getLabel() ?? $difficulty])->filter();
    $tagline = $page->card_tagline ?: $page->location;
    $summary = $page->card_summary ?: \Illuminate\Support\Str::limit($page->overview_body ?: $page->banner_text, 130);
@endphp

<div class="group overflow-hidden rounded-2xl border border-forest-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    <div class="relative h-56 overflow-hidden bg-forest-900">
        @if ($image)
            <img
                src="{{ $image }}"
                alt="{{ $page->package_name }}"
                loading="lazy"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            >
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-forest-900/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-cream-50">
            {{ $page->category->getLabel() }}
        </span>
    </div>
    <div class="p-6">
        @if ($tagline)
            <p class="font-display text-sm italic text-gold-600">{{ $tagline }}</p>
        @endif
        <h3 class="mt-1 font-display text-xl font-semibold text-forest-900">{{ $page->package_name }}</h3>

        @if ($tags->isNotEmpty() || $page->card_highlight)
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($tags as $tag)
                    <span class="rounded-full bg-cream-100 px-3 py-1 text-xs font-medium text-charcoal-700">{{ $tag }}</span>
                @endforeach
                @if ($page->card_highlight)
                    <span class="rounded-full bg-gold-500/15 px-3 py-1 text-xs font-medium text-gold-600">{{ $page->card_highlight }}</span>
                @endif
            </div>
        @endif

        @if ($summary)
            <p class="mt-4 text-sm leading-relaxed text-charcoal-600">{{ $summary }}</p>
        @endif

        <div class="mt-5 flex items-center justify-between border-t border-forest-100 pt-4">
            <div>
                @if ($page->price_from)
                    <p class="font-display text-lg font-semibold text-forest-900">From ${{ number_format($page->price_from) }}</p>
                    <p class="text-xs text-charcoal-500">per person</p>
                @else
                    <p class="font-display text-lg font-semibold text-forest-900">Price on request</p>
                @endif
            </div>
            <a
                href="{{ $page->url() }}"
                class="inline-flex items-center gap-1 rounded-full border border-forest-800 px-4 py-2 text-sm font-semibold text-forest-800 transition hover:bg-forest-800 hover:text-cream-50"
            >
                View
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </a>
        </div>
    </div>
</div>
