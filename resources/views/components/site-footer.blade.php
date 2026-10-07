@php
    $exploreLinks = [
        ['label' => 'Safaris', 'href' => '/safaris'],
        ['label' => 'Southern Circuit', 'href' => '/southern-circuit'],
        ['label' => 'Kilimanjaro', 'href' => '/kilimanjaro'],
        ['label' => 'Zanzibar', 'href' => '/zanzibar'],
        ['label' => 'Day Trips', 'href' => '/day-trips'],
    ];

    $companyLinks = [
        ['label' => 'About Us', 'href' => '/about'],
        ['label' => 'Our Team', 'href' => '/about#team'],
        ['label' => 'Reviews', 'href' => '/reviews'],
        ['label' => 'FAQ', 'href' => '/faq'],
        ['label' => 'Contact', 'href' => '/contact'],
    ];

    // Contact details live in config/site.php.
    $whatsappNumber = config('site.whatsapp');
    $email = config('site.email');

    $socials = [
        ['label' => 'Instagram', 'href' => '#', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.6" /><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6" /><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" />'],
        ['label' => 'Facebook', 'href' => '#', 'icon' => '<path d="M14 8.5h2V5.5h-2c-2.2 0-3.5 1.4-3.5 3.5v2H8.5v3H10.5V19h3v-8h2.2l.3-3H13.5V9c0-.3.1-.5.5-.5Z" fill="currentColor" />'],
        ['label' => 'YouTube', 'href' => '#', 'icon' => '<rect x="3" y="6.5" width="18" height="11" rx="3" stroke="currentColor" stroke-width="1.6" /><path d="M10.5 10v4l3.5-2-3.5-2Z" fill="currentColor" />'],
        ['label' => 'TikTok', 'href' => '#', 'icon' => '<path d="M14 4v9.2a2.8 2.8 0 1 1-2.4-2.77" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" /><path d="M14 4c.3 2 1.8 3.5 3.8 3.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />'],
    ];
@endphp

<footer class="site-footer">
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
            <div class="footer__socials">
                @foreach ($socials as $social)
                    <a href="{{ $social['href'] }}" aria-label="{{ $social['label'] }}" target="_blank" rel="noopener">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $social['icon'] !!}</svg>
                    </a>
                @endforeach
            </div>
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
                        WhatsApp us
                    </a>
                </li>
                <li class="footer__reply-note">We reply within 24 hours</li>
            </ul>
        </div>
    </div>

    <div class="footer__bottom">
        <p>
            &copy; {{ date('Y') }} Serengeti Roaming African Safaris. All rights reserved.
            <a href="{{ url('/photo-credits') }}" class="footer__bottom-link">Photo Credits</a>
        </p>
    </div>
</footer>
