@php

    $whatsappNumber = config('site.whatsapp');

    $tabs = [
        [
            'label' => 'Home',
            'href' => url('/'),
            'active' => request()->is('/'),
            'icon' => '<path d="M4 10.5 12 4l8 6.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" /><path d="M6 9v9.5a1 1 0 0 0 1 1h3v-5h4v5h3a1 1 0 0 0 1-1V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />',
        ],
        [
            'label' => 'Safaris',
            'href' => url('/safaris'),
            'active' => request()->is('safaris*'),
            'icon' => '<circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.6" fill="none" /><path d="m14.5 9.5-1.5 5-5 1.5 1.5-5 5-1.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" fill="none" />',
        ],
    ];

    $callTab = [
        'label' => 'Call',
        'href' => 'tel:+' . $whatsappNumber,
        'icon' => '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h1.379a1.5 1.5 0 0 1 1.415 1L9.1 7.86a1.5 1.5 0 0 1-.464 1.749l-.9.72a11 11 0 0 0 5.235 5.235l.72-.9a1.5 1.5 0 0 1 1.75-.464l2.858.807a1.5 1.5 0 0 1 1 1.415V17.9A1.5 1.5 0 0 1 17.8 19.4h-.38A13.42 13.42 0 0 1 4 5.98v-.48Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="none" />',
    ];
@endphp

<nav
    class="bottom-nav fixed inset-x-0 bottom-0 z-50 border-t border-forest-100 bg-white/95 backdrop-blur lg:hidden"
    aria-label="Bottom"
>
    <div class="mx-auto grid max-w-md grid-cols-5 items-end px-1 pb-[env(safe-area-inset-bottom)]">
        <a href="{{ $tabs[0]['href'] }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ $tabs[0]['active'] ? 'text-forest-800' : 'text-charcoal-500' }}">
            <svg viewBox="0 0 24 24" class="h-5.5 w-5.5">{!! $tabs[0]['icon'] !!}</svg>
            {{ $tabs[0]['label'] }}
        </a>

        <a href="{{ $tabs[1]['href'] }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium {{ $tabs[1]['active'] ? 'text-forest-800' : 'text-charcoal-500' }}">
            <svg viewBox="0 0 24 24" class="h-5.5 w-5.5">{!! $tabs[1]['icon'] !!}</svg>
            {{ $tabs[1]['label'] }}
        </a>

        {{-- Elevated WhatsApp CTA --}}
        <div class="flex flex-col items-center">
            <a
                href="https://wa.me/{{ $whatsappNumber }}"
                target="_blank"
                rel="noopener"
                class="-mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-gold-500 text-forest-900 shadow-lg ring-4 ring-white transition hover:bg-gold-400"
            >
                <svg viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.15-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.148.198 2.095 3.2 5.076 4.487.71.306 1.263.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347Z" />
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.004 2.003c-5.514 0-9.997 4.483-9.997 9.997 0 1.762.462 3.416 1.27 4.849L2 21.997l5.29-1.253a9.96 9.96 0 0 0 4.714 1.2h.004c5.513 0 9.996-4.483 9.996-9.997 0-2.672-1.04-5.184-2.928-7.073a9.93 9.93 0 0 0-7.072-2.871Zm5.883 15.876a8.28 8.28 0 0 1-5.883 2.438h-.003a8.29 8.29 0 0 1-4.226-1.156l-.303-.18-3.14.744.751-3.06-.198-.314a8.3 8.3 0 0 1-1.28-4.451c0-4.588 3.734-8.322 8.326-8.322a8.27 8.27 0 0 1 5.884 2.442 8.26 8.26 0 0 1 2.435 5.88 8.29 8.29 0 0 1-2.363 5.98Z" />
                </svg>
            </a>
            <span class="mt-1 text-[11px] font-medium text-gold-600">WhatsApp</span>
        </div>

        <a href="{{ $callTab['href'] }}" class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-charcoal-500">
            <svg viewBox="0 0 24 24" class="h-5.5 w-5.5">{!! $callTab['icon'] !!}</svg>
            {{ $callTab['label'] }}
        </a>

        <button
            type="button"
            data-menu-toggle-alias
            class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium text-charcoal-500"
        >
            <svg viewBox="0 0 24 24" fill="none" class="h-5.5 w-5.5">
                <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
            Menu
        </button>
    </div>
</nav>
