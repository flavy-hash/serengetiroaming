<?php

// Business contact details used across the site (navbar, footer, mobile bar, contact page).
// TODO: replace the placeholder WhatsApp number before launch (set SITE_WHATSAPP in .env).
return [
    'whatsapp' => env('SITE_WHATSAPP', '255700000000'),
    'email' => env('SITE_EMAIL', 'info@serengetiroamingafricansafaris.com'),
    'location' => env('SITE_LOCATION', 'Arusha, Tanzania'),
];
