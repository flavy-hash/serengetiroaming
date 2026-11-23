@php
    $exploreLinks = [
        ['label' => 'Safaris', 'href' => '/safaris'],
        ['label' => 'Northern Circuit', 'href' => '/northern-circuit'],
        ['label' => 'Southern Circuit', 'href' => '/southern-circuit'],
        ['label' => 'Kilimanjaro', 'href' => '/kilimanjaro'],
        ['label' => 'Zanzibar', 'href' => '/zanzibar'],
        ['label' => 'Day Trips', 'href' => '/day-trips'],
    ];

    $companyLinks = [
        ['label' => 'About Us', 'href' => '/about'],
        ['label' => 'Our Team', 'href' => '/our-team'],
        ['label' => 'Reviews', 'href' => '/reviews'],
        ['label' => 'FAQ', 'href' => '/faq'],
        ['label' => 'Contact', 'href' => '/contact'],
    ];

    // Contact details live in config/site.php.
    $whatsappNumber = config('site.whatsapp');
    $email = config('site.email');
    // Social icons: links in config/site.php ('social'), drawn by <x-social-links>.
@endphp

<footer class="site-footer">
    {{-- Full-width layout (like the reference footer): one container with small side margins. --}}
    <div class="footer__container">
    <div class="footer__top">
        <div class="footer__brand">
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
            <p class="footer__tagline">
                A Tanzanian-owned safari operator based in Arusha — locally owned, globally trusted, best prices.
            </p>
            <x-social-links class="footer__socials" />
        </div>

        <div class="footer__col">
            <h3 class="footer__heading">Explore</h3>
            <ul class="footer__links">
                @foreach ($exploreLinks as $link)
                    <li><a href="{{ url($link['href']) }}">{{ $link['label'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__heading">Company</h3>
            <ul class="footer__links">
                @foreach ($companyLinks as $link)
                    <li><a href="{{ url($link['href']) }}">{{ $link['label'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__heading">Get In Touch</h3>
            <ul class="footer__links footer__contact">
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                    {{ config('site.location') }}
                </li>
                <li>
                    <a href="mailto:{{ $email }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m4 7 8 6 8-6" /></svg>
                        {{ $email }}
                    </a>
                </li>
                <li>
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.4L3.5 20.5l1.5-4.3a8.5 8.5 0 1 1 15.5-4.6z" /></svg>
                        WhatsApp {{ config('site.whatsapp_display') }}
                    </a>
                </li>
                <li class="footer__reply-note">We reply within 24 hours</li>
            </ul>
        </div>
    </div>

    @if ($partners = config('site.partners'))
        <div class="footer__partners">
            <h3 class="footer__partners-heading">Proudly Associated With</h3>
            <ul>
                @foreach ($partners as $partner)
                    <li>
                        <img src="{{ asset($partner['image']) }}" alt="{{ $partner['name'] }}" title="{{ $partner['name'] }}" width="{{ $partner['width'] }}" height="{{ $partner['height'] }}" loading="lazy" decoding="async" draggable="false">
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (filled($cards = config('site.payment_cards')) && filled($cards['image'] ?? null))
        <div class="footer__partners footer__payments">
            <h3 class="footer__partners-heading">We Accept</h3>
            <img src="{{ asset($cards['image']) }}" alt="{{ $cards['alt'] }}" title="{{ $cards['alt'] }}" width="{{ $cards['width'] }}" height="{{ $cards['height'] }}" loading="lazy" decoding="async" draggable="false">
        </div>
    @endif

    <div class="footer__bottom">
        <p class="footer__copyright">&copy; {{ date('Y') }} Serengeti Roaming African Safaris. All rights reserved.</p>
        <nav class="footer__legal" aria-label="Legal">
            <a href="{{ route('policy') }}" class="footer__bottom-link">Privacy Policy</a>
            <a href="{{ route('policy') }}#booking" class="footer__bottom-link">Booking Terms</a>
            <a href="{{ route('terms') }}" class="footer__bottom-link">Terms &amp; Conditions</a>
            <a href="{{ url('/photo-credits') }}" class="footer__bottom-link">Photo Credits</a>
        </nav>
    </div>
    </div>
</footer>
