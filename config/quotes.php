<?php

/*
| Defaults for quotes. Admins can change all of these under Site Settings → Site settings;
| these values are only used until they do.
*/

return [
    // Quotes are valid for this many days unless the admin picks another date.
    'validity_days' => 14,

    // Children (under 16) pay this percentage of the adult package price.
    'child_rate' => 70,

    'currency' => 'USD',

    'terms' => "Prices are per the itemised costs above, in US dollars.\n"
        ."A 30% deposit confirms the booking; the balance is due 60 days before travel.\n"
        ."Rates are subject to park fee and accommodation availability at the time of booking.\n"
        .'This quote is not a booking — we confirm availability before taking any payment.',

    'instant_note' => 'Instant estimate based on the package price from the website. We will confirm availability and the final price with you before any payment is taken.',
];
