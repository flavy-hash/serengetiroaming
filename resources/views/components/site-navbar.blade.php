@php
    // Menu items, images and dropdown links are managed in the admin (Website → Navigation).
    $menus = \App\Models\NavItem::forNavbar();


    $whatsappNumber = config('site.whatsapp');
@endphp

<header class="site-header" id="siteHeader">
    <a class="logo notranslate" href="{{ url('/') }}" aria-label="Serengeti Roaming African Safaris, home">
        <img src="{{ asset('images/Logo-2-croped.png') }}" alt="Serengeti Roaming African Safaris">
        <span class="logo__text">
            <span class="logo__text-serengeti">Serengeti</span>
            <span class="logo__text-roaming-row">
                <span class="logo__text-roaming">Roaming</span>
            </span>
            <span class="logo__text-safaris">African Safaris</span>
        </span>
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Main">
        <ul class="main-nav__list">
            @foreach ($menus as $menu)
                @unless ($menu->hasDropdown())
                    <li><a class="nav-link" href="{{ url($menu->href) }}">{{ $menu->label }}</a></li>
                    @continue
                @endunless
                <li class="has-mega">
                    <button class="nav-trigger" type="button" aria-expanded="false" aria-controls="mega-{{ $menu->key() }}">
                        {{ $menu->label }}
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
                    </button>
                    <div class="mega" id="mega-{{ $menu->key() }}" hidden>
                        <div>
                            <div class="mega__heading">{{ $menu->label }}</div>
                            <ul class="mega__links">
                                @foreach ($menu->links as $link)
                                    <li><a href="{{ url($link['href']) }}">{{ $link['label'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="mega__body">
                            <h2 class="mega__title">{{ $menu->title ?: $menu->label }}</h2>
                            <p class="mega__text">{{ $menu->description }}</p>
                            <a class="mega__cta" href="{{ url($menu->href) }}">
                                {{ $menu->cta_label ?: 'Explore '.$menu->label }}
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </a>
                        </div>
                        <div class="mega__img" @if ($menu->imageUrl()) style="background-image: url('{{ $menu->imageUrl() }}')" @endif></div>
                    </div>
                </li>
            @endforeach

        </ul>
    </nav>

    <div class="header-actions">
        <a class="btn-whatsapp" href="https://wa.me/{{ $whatsappNumber }}?text=Hi%20Serengeti%20Roaming%21%20I%20am%20interested%20in%20booking%20a%20safari." aria-label="Chat on WhatsApp">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z" /><path d="M9 9.5c.3 2.3 2.2 4.2 4.5 4.5l1.2-1.1 1.8.8" /></svg>
            <span>WhatsApp</span>
        </a>
        <button class="menu-toggle" id="menuToggle" type="button" aria-expanded="false" aria-controls="mainNav" aria-label="Open menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
    </div>
</header>
