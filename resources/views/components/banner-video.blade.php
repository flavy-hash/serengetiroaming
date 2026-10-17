@props(['src' => null, 'poster' => null])

{{-- Looping background video for a banner (page-banner). Muted, so browsers allow autoplay.
     The banner photo is the poster; visitors who ask for reduced motion see only the photo (CSS). --}}
@if (filled($src))
    <video
        class="page-banner__video"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        @if (filled($poster)) poster="{{ $poster }}" @endif
        aria-hidden="true"
        tabindex="-1"
    >
        <source src="{{ $src }}" type="{{ str_ends_with(strtolower((string) parse_url($src, PHP_URL_PATH)), '.webm') ? 'video/webm' : 'video/mp4' }}">
    </video>
@endif
