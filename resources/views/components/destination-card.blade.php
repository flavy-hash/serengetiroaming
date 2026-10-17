@props(['destination', 'heading' => 'h2'])

{{-- Photo card with the name and one line over a dark gradient; the whole card links to the destination. --}}
<article class="group relative isolate h-72 overflow-hidden rounded-2xl bg-forest-900 shadow-sm transition hover:-translate-y-1 hover:shadow-lg sm:h-80">
    @if ($src = $destination->imageUrl())
        <img src="{{ $src }}" alt="" loading="lazy" decoding="async"
             class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-500 group-hover:scale-105">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-forest-950/95 via-forest-950/45 to-forest-950/5" aria-hidden="true"></div>

    @if (filled($destination->region))
        <span class="absolute left-4 top-4 rounded-full bg-forest-900/80 px-3 py-1 text-[0.6875rem] font-semibold uppercase tracking-wide text-cream-50 backdrop-blur">
            {{ $destination->region }}
        </span>
    @endif

    <div class="absolute inset-x-0 bottom-0 p-6">
        <{{ $heading }} class="font-display text-2xl font-semibold text-white">
            <a href="{{ $destination->url() }}" class="after:absolute after:inset-0 focus:outline-none">{{ $destination->name }}</a>
        </{{ $heading }}>
        @if (filled($destination->tagline))
            <p class="mt-2 text-sm leading-relaxed text-cream-100/85">{{ $destination->tagline }}</p>
        @endif
        <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-gold-400 transition group-hover:gap-2.5" aria-hidden="true">
            Explore More
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </span>
    </div>
</article>
