<?php

/*
| Places used on the itinerary route maps. A day is pinned automatically when its
| title (or photo caption, or overnight area) mentions one of a place's `match`
| keywords; admins can also pick a place, enter exact coordinates, or hide the day.
|
| Coordinates are approximate (park, town or camp level) — enough for a route map.
*/

return [
    // Towns & airports
    'arusha' => ['name' => 'Arusha', 'lat' => -3.3869, 'lng' => 36.6830, 'match' => ['arusha', 'arusha city']],
    'moshi' => ['name' => 'Moshi', 'lat' => -3.3349, 'lng' => 37.3404, 'match' => ['moshi']],
    'kilimanjaro-airport' => ['name' => 'Kilimanjaro International Airport', 'lat' => -3.4294, 'lng' => 37.0745, 'match' => ['kilimanjaro airport', 'kilimanjaro international airport', 'jro']],
    'karatu' => ['name' => 'Karatu', 'lat' => -3.3420, 'lng' => 35.6730, 'match' => ['karatu']],
    'mto-wa-mbu' => ['name' => 'Mto wa Mbu', 'lat' => -3.3700, 'lng' => 35.8500, 'match' => ['mto wa mbu']],
    'dar-es-salaam' => ['name' => 'Dar es Salaam', 'lat' => -6.7924, 'lng' => 39.2083, 'match' => ['dar es salaam']],

    // Northern Circuit
    'arusha-national-park' => ['name' => 'Arusha National Park', 'lat' => -3.2400, 'lng' => 36.8700, 'match' => ['arusha national park', 'mount meru', 'momella']],
    'tarangire' => ['name' => 'Tarangire National Park', 'lat' => -3.9700, 'lng' => 36.0300, 'match' => ['tarangire']],
    'lake-manyara' => ['name' => 'Lake Manyara National Park', 'lat' => -3.5500, 'lng' => 35.8300, 'match' => ['lake manyara', 'manyara']],
    'ngorongoro' => ['name' => 'Ngorongoro Crater', 'lat' => -3.1750, 'lng' => 35.5870, 'match' => ['ngorongoro', 'ngorongoro crater']],
    'serengeti' => ['name' => 'Central Serengeti', 'lat' => -2.4500, 'lng' => 34.8333, 'match' => ['serengeti', 'central serengeti', 'seronera']],
    'serengeti-north' => ['name' => 'Northern Serengeti (Mara River)', 'lat' => -1.5800, 'lng' => 34.8800, 'match' => ['northern serengeti', 'north serengeti', 'mara river', 'kogatende']],
    'lake-natron' => ['name' => 'Lake Natron', 'lat' => -2.4200, 'lng' => 36.0500, 'match' => ['lake natron', 'natron']],

    // Southern Circuit
    'ruaha' => ['name' => 'Ruaha National Park', 'lat' => -7.7200, 'lng' => 34.9000, 'match' => ['ruaha']],
    'nyerere' => ['name' => 'Nyerere National Park (Selous)', 'lat' => -7.8500, 'lng' => 38.2500, 'match' => ['nyerere', 'selous']],
    'mikumi' => ['name' => 'Mikumi National Park', 'lat' => -7.3000, 'lng' => 37.1000, 'match' => ['mikumi']],
    'udzungwa' => ['name' => 'Udzungwa Mountains', 'lat' => -7.8500, 'lng' => 36.8900, 'match' => ['udzungwa']],

    // Day trips
    'materuni' => ['name' => 'Materuni Waterfalls', 'lat' => -3.2580, 'lng' => 37.3820, 'match' => ['materuni']],

    // Kilimanjaro: gates, camps and huts
    'machame-gate' => ['name' => 'Machame Gate', 'lat' => -3.1700, 'lng' => 37.2420, 'match' => ['machame gate']],
    'machame-camp' => ['name' => 'Machame Camp', 'lat' => -3.1180, 'lng' => 37.2600, 'match' => ['machame camp']],
    'londorossi-gate' => ['name' => 'Londorossi Gate', 'lat' => -2.9850, 'lng' => 37.0970, 'match' => ['londorossi gate', 'londorossi']],
    'mti-mkubwa' => ['name' => 'Mti Mkubwa Camp', 'lat' => -3.0380, 'lng' => 37.1430, 'match' => ['mti mkubwa']],
    'shira-1' => ['name' => 'Shira 1 Camp', 'lat' => -3.0270, 'lng' => 37.2050, 'match' => ['shira 1', 'shira one']],
    'shira' => ['name' => 'Shira Camp', 'lat' => -3.0570, 'lng' => 37.2440, 'match' => ['shira camp', 'shira 2', 'shira two', 'shira cave']],
    'lava-tower' => ['name' => 'Lava Tower', 'lat' => -3.0710, 'lng' => 37.3170, 'match' => ['lava tower']],
    'barranco' => ['name' => 'Barranco Camp', 'lat' => -3.0905, 'lng' => 37.3203, 'match' => ['barranco camp', 'barranco']],
    'karanga' => ['name' => 'Karanga Camp', 'lat' => -3.0960, 'lng' => 37.3480, 'match' => ['karanga camp', 'karanga']],
    'barafu' => ['name' => 'Barafu Camp', 'lat' => -3.0930, 'lng' => 37.3770, 'match' => ['barafu camp', 'barafu']],
    'uhuru-peak' => ['name' => 'Uhuru Peak', 'lat' => -3.0758, 'lng' => 37.3533, 'match' => ['uhuru peak', 'uhuru']],
    'mweka-camp' => ['name' => 'Mweka Camp', 'lat' => -3.1450, 'lng' => 37.3820, 'match' => ['mweka camp']],
    'mweka-gate' => ['name' => 'Mweka Gate', 'lat' => -3.2210, 'lng' => 37.3570, 'match' => ['mweka gate']],
    'marangu-gate' => ['name' => 'Marangu Gate', 'lat' => -3.2410, 'lng' => 37.5160, 'match' => ['marangu gate', 'marangu']],
    'mandara-hut' => ['name' => 'Mandara Hut', 'lat' => -3.1950, 'lng' => 37.5250, 'match' => ['mandara hut', 'mandara']],
    'horombo-hut' => ['name' => 'Horombo Hut', 'lat' => -3.1170, 'lng' => 37.4880, 'match' => ['horombo hut', 'horombo']],
    'kibo-hut' => ['name' => 'Kibo Hut', 'lat' => -3.0950, 'lng' => 37.4170, 'match' => ['kibo hut']],

    // Zanzibar
    'stone-town' => ['name' => 'Stone Town', 'lat' => -6.1630, 'lng' => 39.1880, 'match' => ['stone town', 'zanzibar town']],
    'nungwi' => ['name' => 'Nungwi', 'lat' => -5.7270, 'lng' => 39.2950, 'match' => ['nungwi']],
    'kendwa' => ['name' => 'Kendwa', 'lat' => -5.7500, 'lng' => 39.2900, 'match' => ['kendwa']],
    'paje' => ['name' => 'Paje', 'lat' => -6.2650, 'lng' => 39.5350, 'match' => ['paje']],
    'jambiani' => ['name' => 'Jambiani', 'lat' => -6.3150, 'lng' => 39.5450, 'match' => ['jambiani']],
    'mnemba' => ['name' => 'Mnemba Atoll', 'lat' => -5.8200, 'lng' => 39.3800, 'match' => ['mnemba']],
    'mafia-island' => ['name' => 'Mafia Island', 'lat' => -7.9200, 'lng' => 39.6700, 'match' => ['mafia island', 'mafia']],
    'pemba' => ['name' => 'Pemba Island', 'lat' => -5.2500, 'lng' => 39.7700, 'match' => ['pemba island', 'pemba']],

    // More Kilimanjaro camps (Lemosho, Northern Circuit, Rongai and Umbwe routes)
    'lemosho-gate' => ['name' => 'Lemosho Gate', 'lat' => -3.0050, 'lng' => 37.1200, 'match' => ['lemosho gate', 'lemosho trailhead']],
    'shira-dropoff' => ['name' => 'Shira Plateau drop-off', 'lat' => -3.0100, 'lng' => 37.1900, 'match' => ['morum barrier', 'shira drop-off']],
    'moir-hut' => ['name' => 'Moir Hut', 'lat' => -3.0390, 'lng' => 37.2950, 'match' => ['moir hut', 'moir']],
    'buffalo-camp' => ['name' => 'Buffalo Camp', 'lat' => -3.0050, 'lng' => 37.3830, 'match' => ['buffalo camp']],
    'third-cave' => ['name' => 'Third Cave Camp', 'lat' => -3.0150, 'lng' => 37.4280, 'match' => ['third cave']],
    'school-hut' => ['name' => 'School Hut', 'lat' => -3.0480, 'lng' => 37.4180, 'match' => ['school hut']],
    'millennium-camp' => ['name' => 'Millennium Camp', 'lat' => -3.1290, 'lng' => 37.3780, 'match' => ['millennium camp', 'millenium camp']],
    'rongai-gate' => ['name' => 'Rongai Gate', 'lat' => -2.9950, 'lng' => 37.5280, 'match' => ['rongai gate', 'nale moru']],
    'first-cave' => ['name' => 'First Cave Camp', 'lat' => -3.0150, 'lng' => 37.5000, 'match' => ['first cave']],
    'kikelewa' => ['name' => 'Kikelewa Camp', 'lat' => -3.0420, 'lng' => 37.4900, 'match' => ['kikelewa']],
    'mawenzi-tarn' => ['name' => 'Mawenzi Tarn', 'lat' => -3.0710, 'lng' => 37.4520, 'match' => ['mawenzi tarn']],
    'umbwe-gate' => ['name' => 'Umbwe Gate', 'lat' => -3.2150, 'lng' => 37.2850, 'match' => ['umbwe gate']],
    'umbwe-cave' => ['name' => 'Umbwe Cave Camp', 'lat' => -3.1350, 'lng' => 37.3000, 'match' => ['umbwe cave']],

    // Western Tanzania
    'kigoma' => ['name' => 'Kigoma', 'lat' => -4.8770, 'lng' => 29.6270, 'match' => ['kigoma']],
    'gombe' => ['name' => 'Gombe Stream National Park', 'lat' => -4.6800, 'lng' => 29.6200, 'match' => ['gombe']],
    'mahale' => ['name' => 'Mahale Mountains National Park', 'lat' => -6.1500, 'lng' => 29.9000, 'match' => ['mahale']],

    // North-east and coast
    'mkomazi' => ['name' => 'Mkomazi National Park', 'lat' => -4.0500, 'lng' => 38.0000, 'match' => ['mkomazi']],
    'lushoto' => ['name' => 'Western Usambara Mountains (Lushoto)', 'lat' => -4.7900, 'lng' => 38.2900, 'match' => ['lushoto', 'western usambara']],
    'amani' => ['name' => 'Eastern Usambara forests', 'lat' => -5.1000, 'lng' => 38.6300, 'match' => ['amani', 'eastern usambara', 'magoroto']],
    'saadani' => ['name' => 'Saadani National Park', 'lat' => -6.0400, 'lng' => 38.7700, 'match' => ['saadani']],
    'ndutu' => ['name' => 'Ndutu (southern Serengeti)', 'lat' => -3.0000, 'lng' => 34.9900, 'match' => ['ndutu']],
];
