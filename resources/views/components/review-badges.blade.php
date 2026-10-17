{{-- Links to our reviews on Tripadvisor and Google (URLs in config/site.php). --}}
@php
    $badge = 'group inline-flex flex-col items-center gap-2 rounded-2xl px-6 py-4 text-center transition hover:bg-white';
    $label = 'inline-flex items-center gap-1 text-sm font-semibold text-forest-800 group-hover:text-gold-600';
    $arrow = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" /></svg>';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-center gap-4 sm:gap-10']) }}>
    @if ($tripadvisor = config('site.tripadvisor_url'))
        <a href="{{ $tripadvisor }}" target="_blank" rel="noopener" class="{{ $badge }}">
            <img
                src="{{ asset('images/tripadvisor.png') }}"
                alt="Serengeti Roaming African Safaris on Tripadvisor"
                width="112"
                height="96"
                loading="lazy"
                class="h-24 w-auto transition group-hover:scale-105"
            >
            <span class="{{ $label }}">Read our reviews on Tripadvisor {!! $arrow !!}</span>
        </a>
    @endif

    @if ($google = config('site.google_reviews_url'))
        <a href="{{ $google }}" target="_blank" rel="noopener" class="{{ $badge }}">
            {{-- Drawn here (no image file): Google "G" + "Reviews" wordmark, same height as the Tripadvisor badge. --}}
            <span class="flex h-24 flex-col items-center justify-center gap-1.5 transition group-hover:scale-105" role="img" aria-label="Serengeti Roaming African Safaris on Google Reviews">
                <svg width="52" height="52" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                </svg>
                <span class="text-xl font-semibold tracking-tight">
                    <span class="text-[#4285F4]">G</span><span class="text-[#EA4335]">o</span><span class="text-[#FBBC05]">o</span><span class="text-[#4285F4]">g</span><span class="text-[#34A853]">l</span><span class="text-[#EA4335]">e</span>
                    <span class="text-charcoal-600">Reviews</span>
                </span>
            </span>
            <span class="{{ $label }}">Read our reviews on Google {!! $arrow !!}</span>
        </a>
    @endif
</div>
