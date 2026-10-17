{{-- Social profile icons. Links come from config/site.php ('social'); empty ones are hidden. --}}
@php
    $icons = [
        'instagram' => ['Instagram', '<rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6" /><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6" /><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" />'],
        'facebook' => ['Facebook', '<path d="M14 8.5h2V5.5h-2c-2.2 0-3.5 1.4-3.5 3.5v2H8.5v3H10.5V19h3v-8h2.2l.3-3H13.5V9c0-.3.1-.5.5-.5Z" fill="currentColor" />'],
        'youtube' => ['YouTube', '<rect x="3" y="6.5" width="18" height="11" rx="3" stroke="currentColor" stroke-width="1.6" /><path d="M10.5 10v4l3.5-2-3.5-2Z" fill="currentColor" />'],
        'tiktok' => ['TikTok', '<path d="M14 4v9.2a2.8 2.8 0 1 1-2.4-2.77" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" /><path d="M14 4c.3 2 1.8 3.5 3.8 3.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />'],
    ];
    $links = collect(config('site.social'))->filter()->intersectByKeys($icons);
@endphp

@if ($links->isNotEmpty())
    <div {{ $attributes }}>
        @foreach ($links as $network => $url)
            <a href="{{ $url }}" aria-label="{{ config('site.short_name') }} on {{ $icons[$network][0] }}" target="_blank" rel="noopener">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icons[$network][1] !!}</svg>
            </a>
        @endforeach
    </div>
@endif
