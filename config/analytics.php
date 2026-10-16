<?php

/*
| Privacy-friendly visitor statistics (see App\Http\Middleware\LogPageView).
| No cookies, no IP addresses stored: only a daily-changing anonymous visitor ID,
| the page, the device type, the referring website and the country.
*/

return [
    'enabled' => env('ANALYTICS_ENABLED', true),

    // Page views older than this are deleted by the daily `model:prune`.
    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 180),

    // Free DB-IP "IP to Country Lite" database (CC BY 4.0), refreshed monthly by
    // `php artisan analytics:update-geo-db`. Attribution is shown in the admin.
    'geo_db_path' => storage_path('app/geo/dbip-country-lite.mmdb'),
    'geo_db_url' => 'https://download.db-ip.com/free/dbip-country-lite-{month}.mmdb.gz',

    // Route name => page type shown in the admin. Unlisted routes use their name.
    'page_types' => [
        'home' => 'home',
        'tours.index' => 'section',
        'tours.show' => 'package',
        'accommodations' => 'accommodations',
        'vehicles' => 'vehicles',
        'destinations' => 'destinations',
        'destinations.show' => 'destination',
        'about' => 'about',
        'contact' => 'contact',
        'faq' => 'faq',
        'reviews' => 'reviews',
        'policy' => 'policy',
        'terms' => 'terms',
    ],

    // Routes that are never logged (files, forms, admin…).
    'skip_routes' => ['tours.pdf', 'tours.quote', 'quotes.download', 'sitemap', 'robots'],
];
