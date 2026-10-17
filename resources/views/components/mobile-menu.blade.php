@props(['menus'])

@php
    // Same menu items and links as the desktop navigation (admin → Site Settings → Navigation).
    $languages = config('site.languages', []);
@endphp

{{-- Slide-in menu for phones and tablets, opened by the header ☰ button and the bottom bar's "Menu". --}}
<div class="mobile-menu" id="mobileMenu" data-mobile-menu hidden>
    <div class="mobile-menu__backdrop" data-mobile-menu-close aria-hidden="true"></div>

    <div class="mobile-menu__drawer" role="dialog" aria-modal="true" aria-labelledby="mobileMenuTitle">
        <div class="mobile-menu__head">
            <a href="{{ url('/') }}" class="mobile-menu__brand notranslate" id="mobileMenuTitle">
                <img src="{{ asset('images/Logo-2-croped.png') }}" alt="" width="40" height="40">
                Serengeti Roaming
            </a>
            <button type="button" class="mobile-menu__close" data-mobile-menu-close aria-label="Close menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>

        <nav class="mobile-menu__nav" aria-label="Main">
            <ul>
                @foreach ($menus as $menu)
                    @php($current = $menu->isCurrent())
                    @unless ($menu->hasDropdown())
                        <li>
                            <a href="{{ url($menu->href) }}" class="mobile-menu__link{{ $current ? ' is-current' : '' }}"@if ($current) aria-current="page"@endif>{{ $menu->label }}</a>
                        </li>
                        @continue
                    @endunless
                    <li>
                        <button
                            type="button"
                            class="mobile-menu__link{{ $current ? ' is-current' : '' }}"
                            data-mobile-section
                            aria-expanded="false"
                            aria-controls="mobile-sub-{{ $menu->key() }}"
                        >
                            {{ $menu->label }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
                        </button>
                        <ul class="mobile-menu__sub" id="mobile-sub-{{ $menu->key() }}" hidden>
                            @foreach ($menu->links as $link)
                                @php($href = url($link['href'] ?? '#'))
                                <li>
                                    <a href="{{ $href }}"@if (url()->full() === $href) aria-current="page"@endif>{{ $link['label'] ?? '' }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if ($languages)
            <div class="mobile-menu__lang notranslate">
                <button type="button" class="mobile-menu__lang-toggle" data-mobile-lang aria-expanded="false" aria-controls="mobileMenuLang">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3c2.5 2.7 3.8 6 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-6-3.8-9s1.3-6.3 3.8-9Z" /></svg>
                        <span data-lang-current>EN</span>
                    </span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
                </button>
                <div class="mobile-menu__lang-list" id="mobileMenuLang" hidden>
                    @foreach ($languages as $language)
                        <button type="button" data-lang-option="{{ $language['code'] }}" data-lang-short="{{ $language['short'] }}">
                            <span class="mobile-menu__lang-code">{{ $language['short'] }}</span>
                            {{ $language['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('contact') }}#contact-form" class="mobile-menu__cta">Send a Request</a>

        <x-social-links class="mobile-menu__social" />
    </div>
</div>
