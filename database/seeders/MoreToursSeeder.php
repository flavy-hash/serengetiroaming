<?php

namespace Database\Seeders;

use App\Enums\SafariTier;
use App\Enums\TourCategory;

/**
 * Second batch of itineraries: Southern Circuit safaris, luxury fly-in safaris, chimpanzee
 * trekking, the north-east and coast, Zanzibar / Mafia / Pemba holidays and seven Kilimanjaro
 * climbs. Built with the same builder as SafariCollectionSeeder; day texts are our own.
 *
 * All packages are created as DRAFTS without a price — add the price, check the text and
 * publish in the admin. Only adds packages that don't exist yet:
 *   php artisan db:seed --class=MoreToursSeeder --force
 */
class MoreToursSeeder extends SafariCollectionSeeder
{
    /**
     * Same as the parent, plus packages that list their own inclusions (beach stays, climbs).
     *
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    protected function build(array $spec, string $slug, int $order): array
    {
        $data = parent::build($spec, $slug, $order);

        if (isset($spec['included'])) {
            $data['included'] = $spec['included'];
        }

        return $data;
    }

    protected static function stops(): array
    {
        return array_merge(parent::stops(), self::EXTRA_STOPS, self::CLIMB_STOPS);
    }

    protected static function photos(): array
    {
        return array_merge(parent::photos(), [
            'gombe' => ['tours/gombe.jpg', 'tours/gombe.jpg'],
            'usambara' => ['tours/usambara.jpg', 'tours/usambara.jpg'],
            'saadani' => ['tours/saadani.jpg', 'tours/saadani.jpg'],
            'mkomazi' => ['tours/mkomazi.jpg', 'tours/mkomazi.jpg'],
            'pemba' => ['tours/pemba.jpg', 'tours/pemba.jpg'],
            'mikumi' => ['destinations/mikumi.jpg', 'destinations/mikumi.jpg'],
            'ruaha' => ['destinations/southern-circuit.jpg', 'navigation/southern-circuit.jpg'],
            'nyerere' => ['destinations/nyerere.jpg', 'destinations/nyerere.jpg'],
            'boat' => ['activities/boat-safari.jpg', 'activities/boat-safari.jpg'],
            'mafia' => ['destinations/mafia-island.jpg', 'destinations/mafia-island.jpg'],
            'snorkel' => ['activities/snorkelling.jpg', 'activities/snorkelling.jpg'],
            'kili' => ['tour-pages/kilimanjaro/kilimanjaro-amboseli.jpg', 'kilimanjaro/kilimanjaro-amboseli.jpg'],
            'mawenzi' => ['tour-pages/kilimanjaro/mawenzi.jpg', 'kilimanjaro/mawenzi.jpg'],
            'stone-town' => ['tour-pages/zanzibar/stone-town.jpg', 'zanzibar/stone-town.jpg'],
            'dhow' => ['tour-pages/zanzibar/dhow.jpg', 'zanzibar/dhow.jpg'],
            'spice' => ['tour-pages/zanzibar/spice-tour.jpg', 'zanzibar/spice-tour.jpg'],
        ]);
    }

    private const SERENGETI_FULL = 'A full day of game drives in the Serengeti. Your guide follows the wildlife — the herds of the Great Migration when they are nearby, big cats on the rocky kopjes and hippos in the Seronera River — with a picnic lunch out on the plains.';

    private const EXTRA_STOPS = [
        // Southern Circuit
        'arrival-dar' => [
            'title' => 'Arrival in Dar es Salaam', 'map' => 'dar-es-salaam', 'area' => 'Dar es Salaam', 'meals' => 'Dinner',
            'copy' => 'Karibu Tanzania! You are met at Julius Nyerere International Airport and taken to your hotel in Dar es Salaam to rest after the flight. In the evening your guide briefs you on the safari ahead.',
        ],
        'mikumi' => [
            'title' => 'Mikumi National Park', 'map' => 'mikumi', 'area' => 'Mikumi', 'meals' => 'Full Board', 'park' => 'Mikumi',
            'photo' => 'mikumi', 'caption' => 'Giraffes in Mikumi',
            'copy' => 'Leave the city behind and drive west to Mikumi National Park. In the afternoon, your first game drive on the open Mkata floodplain, home to giraffe, zebra, wildebeest, elephants and lions.',
            'more' => ['A full day in Mikumi. Early and late game drives give the best chances of lions and hunting wild dogs, while hippo pools and birdlife fill the middle of the day.'],
        ],
        'udzungwa' => [
            'title' => 'Udzungwa Mountains National Park', 'map' => 'udzungwa', 'area' => 'Udzungwa', 'meals' => 'Full Board', 'park' => 'Udzungwa Mountains',
            'copy' => 'Swap the vehicle for walking boots in the rainforest of the Udzungwa Mountains. Hike with a ranger to the Sanje waterfalls, looking for rare monkeys such as the Udzungwa red colobus and for colourful forest birds along the way.',
        ],
        'ruaha' => [
            'title' => 'Ruaha National Park', 'map' => 'ruaha', 'area' => 'Ruaha', 'meals' => 'Full Board', 'park' => 'Ruaha',
            'photo' => 'ruaha', 'caption' => 'Elephants in Ruaha',
            'copy' => 'A long, scenic drive brings you to Ruaha, one of Tanzania\'s largest and wildest parks. Game drive along the Great Ruaha River on the way to camp, with elephants, giraffes and big baobabs all around.',
            'more' => ['A full day exploring Ruaha. The park is famous for its large lion prides and is one of the few places to see both greater and lesser kudu — and you will share the sightings with very few other vehicles.'],
        ],
        'ruaha-days' => [
            'title' => 'Ruaha National Park', 'map' => 'ruaha', 'area' => 'Ruaha', 'meals' => 'Full Board', 'park' => 'Ruaha',
            'photo' => 'ruaha', 'caption' => 'Elephants in Ruaha',
            'copy' => 'A full day exploring Ruaha. The park is famous for its large lion prides and is one of the few places to see both greater and lesser kudu — and you will share the sightings with very few other vehicles.',
            'more' => ['Another day in the wild heart of Ruaha: follow the river for elephants and buffalo coming to drink, search the rocky hills for leopards, and enjoy a sundowner in the bush.'],
        ],
        'flight-ruaha' => [
            'title' => 'Flight to Ruaha and afternoon game drive', 'map' => 'ruaha', 'area' => 'Ruaha', 'meals' => 'Full Board', 'park' => 'Ruaha',
            'copy' => 'A light aircraft flies you across the Southern Highlands to Ruaha National Park (included). Your guide meets you at the airstrip and you game drive to camp, arriving in time for an afternoon drive along the river.',
        ],
        'flight-nyerere' => [
            'title' => 'Flight to Nyerere National Park', 'map' => 'nyerere', 'area' => 'Nyerere', 'meals' => 'Full Board', 'park' => 'Nyerere',
            'photo' => 'nyerere', 'caption' => 'The Rufiji River',
            'copy' => 'Fly by light aircraft to Nyerere National Park (included) and land in the middle of the wilderness. After lunch, an afternoon game drive among the lakes and palms along the Rufiji River.',
        ],
        'nyerere' => [
            'title' => 'Nyerere National Park', 'map' => 'nyerere', 'area' => 'Nyerere', 'meals' => 'Full Board', 'park' => 'Nyerere',
            'photo' => 'nyerere', 'caption' => 'The Rufiji River',
            'copy' => 'Travel from Dar es Salaam to Nyerere National Park, the former northern Selous, by road or light aircraft. In the afternoon, your first game drive among the lakes, palms and channels of the Rufiji River.',
            'more' => ['Explore Nyerere by vehicle, on a guided walk with an armed ranger and by boat on the Rufiji River, where hippos, crocodiles and elephants come down to the water — and keep watch for African wild dogs.'],
        ],
        'nyerere-boat' => [
            'title' => 'Full-day boat safari on the Rufiji', 'map' => 'nyerere', 'area' => 'Nyerere', 'meals' => 'Full Board', 'park' => 'Nyerere',
            'photo' => 'boat', 'caption' => 'Hippos in the Rufiji River',
            'copy' => 'Spend the day on the water: glide along the Rufiji River and its lakes past pods of hippos, basking crocodiles, elephants drinking at the bank and a wealth of birds, with a picnic lunch on a sandbank.',
        ],
        'fly-dar-depart' => [
            'title' => 'Flight to Dar es Salaam and departure', 'map' => 'dar-es-salaam', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'A last morning in the bush, then a light-aircraft flight back to Dar es Salaam (included) to connect with your international flight home.',
        ],
        'depart-dar' => [
            'title' => 'Departure from Dar es Salaam', 'map' => 'dar-es-salaam', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'After a final morning game drive, transfer back to Dar es Salaam for your flight home — or continue to Zanzibar for a few days on the beach.',
        ],

        // Western Tanzania (chimpanzees)
        'flight-kigoma' => [
            'title' => 'Flight to Kigoma', 'map' => 'kigoma', 'area' => 'Kigoma', 'meals' => 'Full Board',
            'copy' => 'Fly west across Tanzania to Kigoma on the shore of Lake Tanganyika, the world\'s longest freshwater lake. Settle in at your lakeside hotel and watch the sun set over the mountains of the Congo.',
        ],
        'gombe' => [
            'title' => 'Gombe Stream National Park', 'map' => 'gombe', 'area' => 'Gombe', 'meals' => 'Full Board', 'park' => 'Gombe Stream',
            'photo' => 'gombe', 'caption' => 'Chimpanzees in Gombe',
            'copy' => 'A boat ride up the lake shore takes you to Gombe Stream, where Jane Goodall began her famous chimpanzee research. In the afternoon, trek into the forest with a ranger to spend time with a habituated chimpanzee family.',
            'more' => ['A second morning with the chimpanzees, then time to swim in the lake, walk to a forest waterfall or watch baboons and colobus monkeys around camp.'],
        ],
        'boat-mahale' => [
            'title' => 'Boat journey to the Mahale Mountains', 'map' => 'mahale', 'area' => 'Mahale', 'meals' => 'Full Board', 'park' => 'Mahale Mountains',
            'copy' => 'Travel south from Kigoma by road and boat to the remote Mahale Mountains, where forested peaks rise straight out of Lake Tanganyika. Arrive at camp on a white-sand beach in time for a swim and sunset.',
        ],
        'mahale' => [
            'title' => 'Chimpanzee trekking in Mahale', 'map' => 'mahale', 'area' => 'Mahale', 'meals' => 'Full Board', 'park' => 'Mahale Mountains',
            'photo' => 'gombe', 'caption' => 'Chimpanzees in the forest',
            'copy' => 'Set off with your tracker into the forest to find one of Mahale\'s chimpanzee groups. Watching them groom, play and call through the trees is unforgettable. Afternoon at leisure — perhaps a dhow trip on the lake.',
        ],
        'return-kigoma' => [
            'title' => 'Return to Kigoma', 'map' => 'kigoma', 'area' => 'Kigoma', 'meals' => 'Full Board',
            'copy' => 'A final morning at Mahale, then travel back to Kigoma by boat and road for a relaxed last evening on the shore of Lake Tanganyika.',
        ],

        // North-east and coast
        'mkomazi' => [
            'title' => 'Mkomazi National Park', 'map' => 'mkomazi', 'area' => 'Mkomazi', 'meals' => 'Full Board', 'park' => 'Mkomazi',
            'photo' => 'mkomazi', 'caption' => 'Mkomazi National Park',
            'copy' => 'Drive east to Mkomazi National Park on the Kenyan border, a dry, wild landscape with views of Kilimanjaro. Visit the rhino sanctuary, where black rhinos are protected and bred, and look for gerenuk and lesser kudu on an afternoon game drive.',
        ],
        'usambara-west' => [
            'title' => 'Western Usambara Mountains', 'map' => 'lushoto', 'area' => 'Lushoto', 'meals' => 'Full Board', 'park' => 'Usambara Mountains',
            'photo' => 'usambara', 'caption' => 'The Usambara Mountains',
            'copy' => 'Climb into the cool, green Western Usambara Mountains around Lushoto. Walk with a local guide through hillside farms and villages to a viewpoint where the escarpment drops away to the plains far below.',
        ],
        'usambara-east' => [
            'title' => 'Eastern Usambara forests', 'map' => 'amani', 'area' => 'Eastern Usambara', 'meals' => 'Full Board', 'park' => 'Usambara Mountains',
            'photo' => 'usambara', 'caption' => 'The Usambara Mountains',
            'copy' => 'Continue to the rainforests of the Eastern Usambara, one of Africa\'s richest places for rare plants, chameleons and birds. Settle into your forest lodge and take an afternoon walk among tea gardens and giant trees.',
            'more' => ['A day for forest walks with a naturalist guide — looking for chameleons, butterflies and endemic birds — or simply relax on the veranda with a view over the hills.'],
        ],
        'saadani' => [
            'title' => 'Saadani National Park', 'map' => 'saadani', 'area' => 'Saadani', 'meals' => 'Full Board', 'park' => 'Saadani',
            'photo' => 'saadani', 'caption' => 'Baobab in Saadani National Park',
            'copy' => 'Descend to the coast and Saadani, the only park in Tanzania where the bush meets the Indian Ocean. Settle into your beach camp and enjoy a sunset game drive among giraffes, buffalo and warthogs.',
            'more' => [
                'A boat safari on the Wami River for hippos, crocodiles and mangrove birds, then time on the beach — the beach here is also a nesting site for green turtles.',
                'A last day of game drives, walks and beach time — the perfect, relaxed end to the trip.',
            ],
        ],

        // Luxury and fly-in safaris
        'fly-tarangire' => [
            'title' => 'Fly into Tarangire', 'map' => 'tarangire', 'area' => 'Tarangire', 'meals' => 'Full Board', 'park' => 'Tarangire',
            'photo' => 'tarangire', 'caption' => 'Elephants and baobabs in Tarangire',
            'copy' => 'A short scenic flight takes you from Arusha to Tarangire (included). Your guide meets you at the airstrip for a game drive to your luxury lodge, then an afternoon among the baobabs and elephant herds.',
        ],
        'tarangire-full' => [
            'title' => 'Tarangire National Park', 'map' => 'tarangire', 'area' => 'Tarangire', 'meals' => 'Full Board', 'park' => 'Tarangire',
            'copy' => 'A full day in Tarangire — morning and afternoon game drives along the river, a walking safari with an armed ranger, or a lazy afternoon by the pool before a sundowner overlooking the plains.',
        ],
        'farm-walk' => [
            'title' => 'Farm walk and lunch in the Ngorongoro highlands', 'map' => 'karatu', 'area' => 'Karatu', 'meals' => 'Full Board',
            'photo' => 'coffee', 'caption' => 'Coffee farm in the highlands',
            'copy' => 'A gentle day in the green highlands near Karatu. Walk through coffee plantations and organic vegetable gardens, visit a local village or a forest waterfall, and enjoy a long farm-to-table lunch.',
        ],
        'flight-serengeti' => [
            'title' => 'Fly into the Serengeti', 'map' => 'serengeti', 'area' => 'Central Serengeti', 'meals' => 'Full Board', 'park' => 'Serengeti',
            'photo' => 'serengeti', 'caption' => 'Lion in the Serengeti',
            'copy' => 'Skip the long drive and fly into the Serengeti by light aircraft (included), with the plains, herds and kopjes spread out below. Your guide meets you at the airstrip and you game drive straight to camp.',
        ],
        'serengeti-full' => [
            'title' => 'Serengeti National Park', 'map' => 'serengeti', 'area' => 'Central Serengeti', 'meals' => 'Full Board', 'park' => 'Serengeti',
            'copy' => self::SERENGETI_FULL,
            'more' => ['Another day on the plains, timed around the best light: an early start for hunting cats, a long lunch at camp, and a late-afternoon drive as the sun sets over the acacias.'],
        ],
        'fly-serengeti-zanzibar' => [
            'title' => 'Flight from the Serengeti to Zanzibar', 'map' => 'stone-town', 'area' => 'Zanzibar', 'meals' => 'Bed and Breakfast', 'park' => 'Zanzibar',
            'copy' => 'A final early game drive, then fly from the Serengeti to Zanzibar (included). You are met on arrival and taken to your beach hotel for a well-earned rest by the sea.',
        ],
        'fly-arusha-depart' => [
            'title' => 'Flight to Arusha and departure', 'map' => 'kilimanjaro-airport', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'A last game drive on the way to the airstrip, then fly back to Arusha (included). You are met and transferred to Kilimanjaro International Airport for your flight home.',
        ],
        'fly-arusha-overnight' => [
            'title' => 'Flight to Arusha', 'map' => 'arusha', 'area' => 'Arusha', 'meals' => 'Bed and Breakfast',
            'copy' => 'A final morning game drive, then fly back to Arusha (included) for a relaxed last afternoon and evening at your lodge.',
        ],
        'ngorongoro-photo' => [
            'title' => 'Ngorongoro Crater', 'map' => 'ngorongoro', 'area' => 'Ngorongoro rim', 'meals' => 'Full Board', 'park' => 'Ngorongoro Crater',
            'photo' => 'ngorongoro', 'caption' => 'The Ngorongoro Crater',
            'copy' => 'Descend into the Ngorongoro Crater at first light, when the mist lifts off the crater floor and the animals are most active — perfect conditions for photography. Look for black rhino, lions, elephants and huge herds of buffalo.',
            'more' => ['A second crater day lets you return to the best spots in the best light, and explore the forested crater rim and the highlands beyond.'],
        ],
        'ndutu' => [
            'title' => 'Ndutu and the calving season', 'map' => 'ndutu', 'area' => 'Ndutu', 'meals' => 'Full Board', 'park' => 'Ndutu',
            'photo' => 'serengeti', 'caption' => 'The southern Serengeti plains',
            'note' => 'Ndutu is included from late December to March, when the herds calve here; at other times we replace it with more time in the central Serengeti.',
            'copy' => 'Drive to Ndutu on the southern edge of the Serengeti, where the herds of the Great Migration give birth to hundreds of thousands of calves early in the year — drawing lions, cheetahs and hyenas close.',
            'more' => ['A full day around the lakes and short-grass plains of Ndutu, following the herds and the predators that hunt them.'],
        ],

        // Islands
        'arrival-zanzibar' => [
            'title' => 'Arrival in Zanzibar', 'map' => 'stone-town', 'area' => 'Zanzibar', 'meals' => 'Dinner', 'park' => 'Zanzibar',
            'photo' => 'stone-town', 'caption' => 'Stone Town, Zanzibar',
            'copy' => 'Karibu Zanzibar! You are met at the airport and driven to your beach hotel. Spend the rest of the day settling in, with your toes in the sand and a view of the Indian Ocean.',
        ],
        'zanzibar-stay' => [
            'title' => 'Zanzibar', 'map' => 'nungwi', 'area' => 'Zanzibar', 'meals' => 'Bed and Breakfast', 'park' => 'Zanzibar',
            'photo' => 'zanzibar', 'caption' => 'Nungwi beach, Zanzibar',
            'copy' => 'A free day on the beach. Swim in the warm, turquoise water, relax under the palms or try paddleboarding. Our team can book any excursion for you.',
            'more' => [
                'Optional: a guided walk through historic Stone Town — its carved doors, spice markets, the Old Fort and the former slave market.',
                'Optional: a spice farm tour to see, smell and taste the cloves, nutmeg, vanilla and cinnamon that made Zanzibar famous.',
                'Optional: a boat trip to Mnemba Atoll to snorkel over the coral reefs, with a good chance of seeing dolphins and turtles.',
                'Optional: a full-day sea excursion to sandbanks and lagoons, with snorkelling, a seafood lunch and a sail home on a traditional dhow.',
                'Optional: visit Jozani Forest to see the rare red colobus monkeys, then walk the mangrove boardwalk.',
                'A last lazy day by the sea, finishing with a sunset dhow cruise and dinner on the beach.',
            ],
        ],
        'arrival-mafia' => [
            'title' => 'Arrival on Mafia Island', 'map' => 'mafia-island', 'area' => 'Mafia Island', 'meals' => 'Full Board', 'park' => 'Mafia Island',
            'photo' => 'mafia', 'caption' => 'Mafia Island',
            'copy' => 'A short flight brings you to Mafia Island, south of Zanzibar and far quieter. You are met at the airstrip and taken to your lodge on the edge of the Mafia Island Marine Park.',
        ],
        'mafia' => [
            'title' => 'Mafia Island', 'map' => 'mafia-island', 'area' => 'Mafia Island', 'meals' => 'Full Board', 'park' => 'Mafia Island',
            'photo' => 'snorkel', 'caption' => 'Snorkelling trip',
            'note' => 'Whale sharks visit Mafia from about October to March.',
            'copy' => 'Head out by boat in search of whale sharks — the gentle giants that visit Mafia\'s waters — and slip in to snorkel alongside them (in season).',
            'more' => [
                'Snorkel or dive the coral gardens of the Mafia Island Marine Park, with turtles, reef fish and colourful corals.',
                'Take a dhow to tiny Chole Island to explore its old ruins, village and mangroves, then relax back at the lodge.',
            ],
        ],
        'depart-mafia' => [
            'title' => 'Departure from Mafia Island', 'map' => 'mafia-island', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'A final swim, then fly from Mafia to Dar es Salaam or Zanzibar to connect with your flight home.',
        ],
        'arrival-pemba' => [
            'title' => 'Arrival on Pemba Island', 'map' => 'pemba', 'area' => 'Pemba Island', 'meals' => 'Full Board', 'park' => 'Pemba Island',
            'photo' => 'pemba', 'caption' => 'Vumawimbi Beach, Pemba',
            'copy' => 'Fly to Pemba, the green and little-visited "island of cloves" north of Zanzibar. You are met at the airport and driven to your lodge by a quiet beach.',
        ],
        'pemba' => [
            'title' => 'Pemba Island', 'map' => 'pemba', 'area' => 'Pemba Island', 'meals' => 'Full Board', 'park' => 'Pemba Island',
            'photo' => 'pemba', 'caption' => 'Vumawimbi Beach, Pemba',
            'copy' => 'Snorkel or dive the walls and reefs around Misali Island, some of the clearest water in East Africa.',
            'more' => [
                'Paddle a kayak through the mangroves, walk in the Ngezi forest to look for the Pemba flying fox, or simply enjoy the empty beach.',
                'Visit a clove plantation and village to see how Pemba\'s famous spice is grown and dried, then a final sunset by the sea.',
            ],
        ],
        'depart-pemba' => [
            'title' => 'Departure from Pemba Island', 'map' => 'pemba', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'After breakfast, fly from Pemba to Zanzibar or Dar es Salaam to connect with your international flight home.',
        ],
    ];

    /** Kilimanjaro: one entry per day on the mountain, camp to camp. */
    private const CLIMB_STOPS = [
        'arrival-climb' => [
            'title' => 'Arrival and climb briefing', 'map' => 'moshi', 'area' => 'Moshi', 'meals' => 'Dinner',
            'photo' => 'kili', 'caption' => 'Mount Kilimanjaro',
            'copy' => 'You are met at Kilimanjaro International Airport and driven to your hotel in Moshi. Your lead mountain guide visits in the afternoon to check your gear, answer your questions and brief you on the climb. Anything missing can be hired here.',
        ],
        'k-machame-gate' => [
            'title' => 'Machame Gate (1,800 m) to Machame Camp (2,835 m)', 'map' => 'machame-camp', 'area' => 'Machame Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'After registering at Machame Gate, you climb steadily through lush rainforest, often misty and full of the calls of monkeys and birds. About 5–7 hours of walking bring you to Machame Camp at the edge of the forest.',
        ],
        'k-machame-shira' => [
            'title' => 'Machame Camp (2,835 m) to Shira Camp (3,850 m)', 'map' => 'shira', 'area' => 'Shira Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Leave the forest for giant heather and moorland, climbing a rocky ridge onto the Shira Plateau. The views open up across to Mount Meru, and the summit of Kibo appears above you. About 5–6 hours.',
        ],
        'k-dropoff-shira1' => [
            'title' => 'Drive to the Shira Plateau (3,500 m) and walk to Shira 1 Camp (3,505 m)', 'map' => 'shira-1', 'area' => 'Shira 1 Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'mawenzi', 'caption' => 'Kilimanjaro\'s high slopes',
            'copy' => 'After the park formalities at Londorossi Gate, drive up the western slopes to the edge of the Shira Plateau. A short, easy walk across the high moorland takes you to Shira 1 Camp, with time to rest and acclimatise.',
        ],
        'k-dropoff-shira2' => [
            'title' => 'Drive to the Shira Plateau (3,500 m) and walk to Shira 2 Camp (3,850 m)', 'map' => 'shira', 'area' => 'Shira Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'mawenzi', 'caption' => 'Kilimanjaro\'s high slopes',
            'copy' => 'Drive from Londorossi Gate up to the Shira Plateau, starting the climb high. Walk across the wide plateau of heather and volcanic rock to Shira 2 Camp, with Kibo\'s glaciers in front of you. About 4–5 hours.',
        ],
        'k-shira1-shira2' => [
            'title' => 'Shira 1 Camp (3,505 m) to Shira 2 Camp (3,850 m)', 'map' => 'shira', 'area' => 'Shira Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'A gentle day across the Shira Plateau, an ancient collapsed volcano. The short distance helps your body adjust to the altitude, with time in the afternoon for an acclimatisation walk to Shira Cathedral.',
        ],
        'k-shira-barranco' => [
            'title' => 'Shira Camp (3,850 m) via Lava Tower (4,630 m) to Barranco Camp (3,950 m)', 'map' => 'barranco', 'area' => 'Barranco Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'The key acclimatisation day: climb through alpine desert to the Lava Tower at 4,630 m for lunch, then descend into the Barranco Valley among giant groundsels. "Climb high, sleep low" prepares you for the summit. About 6–7 hours.',
        ],
        'k-barranco-karanga' => [
            'title' => 'Barranco Camp (3,950 m) to Karanga Camp (4,035 m)', 'map' => 'karanga', 'area' => 'Karanga Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Scramble up the famous Barranco Wall — steep but not technical, and great fun — then follow the trail across ridges and valleys beneath the southern glaciers to Karanga Camp. A short day that helps acclimatisation. About 4–5 hours.',
        ],
        'k-barranco-barafu' => [
            'title' => 'Barranco Camp (3,950 m) to Barafu Camp (4,640 m)', 'map' => 'barafu', 'area' => 'Barafu Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Climb the Barranco Wall, pass Karanga Valley and continue up to Barafu Camp on a rocky ridge below the summit. A long day: eat, drink and rest well, as the summit attempt starts around midnight. About 7–8 hours.',
        ],
        'k-karanga-barafu' => [
            'title' => 'Karanga Camp (4,035 m) to Barafu Camp (4,640 m)', 'map' => 'barafu', 'area' => 'Barafu Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'A short climb over barren, rocky ground to Barafu Camp, the base for the summit. Rest in the afternoon, have an early dinner and get your gear ready — the summit attempt begins around midnight. About 3–4 hours.',
        ],
        'k-summit-millennium' => [
            'title' => 'Summit day: Uhuru Peak (5,895 m) and down to Millennium Camp (3,820 m)', 'map' => 'uhuru-peak', 'area' => 'Millennium Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'kili', 'caption' => 'Mount Kilimanjaro',
            'copy' => 'Start by headtorch around midnight and climb slowly through the night to Stella Point on the crater rim, reaching Uhuru Peak — the Roof of Africa — around sunrise. After photos at the summit, descend to Barafu for a rest and continue down to Millennium Camp. About 12–14 hours in total.',
        ],
        'k-millennium-mweka' => [
            'title' => 'Millennium Camp (3,820 m) to Mweka Gate (1,640 m)', 'map' => 'mweka-gate', 'area' => 'Moshi', 'meals' => 'Breakfast and Lunch', 'park' => 'Kilimanjaro',
            'copy' => 'Walk down through moorland and back into the rainforest to Mweka Gate, where you receive your summit certificate. Say goodbye and thank you to your crew, then drive back to your hotel in Moshi for a hot shower. About 4–5 hours.',
        ],
        'k-lemosho-bigtree' => [
            'title' => 'Lemosho Gate (2,100 m) to Big Tree Camp (2,780 m)', 'map' => 'mti-mkubwa', 'area' => 'Big Tree Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Drive to Londorossi Gate to register, then on to the Lemosho trailhead. A beautiful first walk through quiet rainforest — look for black-and-white colobus monkeys — to Big Tree (Mti Mkubwa) Camp. About 3–4 hours.',
        ],
        'k-bigtree-shira1' => [
            'title' => 'Big Tree Camp (2,780 m) to Shira 1 Camp (3,505 m)', 'map' => 'shira-1', 'area' => 'Shira 1 Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Leave the forest and climb into giant heather, crossing the Shira Ridge before dropping onto the Shira Plateau. Kibo comes into full view for the first time. About 5–6 hours.',
        ],
        'k-shira1-moir' => [
            'title' => 'Shira 1 Camp (3,505 m) via Lava Tower (4,630 m) to Moir Hut (4,200 m)', 'map' => 'moir-hut', 'area' => 'Moir Hut Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Cross the Shira Plateau and climb towards the Lava Tower to help your acclimatisation, then turn north to the quiet, little-visited Moir Hut camp beneath the western glaciers. About 6–7 hours.',
        ],
        'k-moir-buffalo' => [
            'title' => 'Moir Hut (4,200 m) to Buffalo Camp (4,020 m)', 'map' => 'buffalo-camp', 'area' => 'Buffalo Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Begin the circuit around the northern slopes — the most remote side of the mountain. The trail rolls over ridges with views across to Kenya\'s Amboseli plains, and you will meet very few other climbers. About 5–7 hours.',
        ],
        'k-buffalo-thirdcave' => [
            'title' => 'Buffalo Camp (4,020 m) to Third Cave Camp (3,870 m)', 'map' => 'third-cave', 'area' => 'Third Cave Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Continue east around the northern slopes through alpine desert, passing Rongai Second Cave, to Third Cave Camp. A steady day that gives your body more time to adapt to the altitude. About 5–6 hours.',
        ],
        'k-thirdcave-school' => [
            'title' => 'Third Cave Camp (3,870 m) to School Hut (4,750 m)', 'map' => 'school-hut', 'area' => 'School Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Climb across the saddle between Mawenzi and Kibo to School Hut, the base camp for the northern summit approach. Rest, eat well and get ready for the night climb. About 4–5 hours.',
        ],
        'k-school-summit' => [
            'title' => 'Summit day: Uhuru Peak (5,895 m) and down to Millennium Camp (3,820 m)', 'map' => 'uhuru-peak', 'area' => 'Millennium Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'kili', 'caption' => 'Mount Kilimanjaro',
            'copy' => 'Leave School Hut around midnight and climb to Gilman\'s Point on the crater rim, then follow the rim past the glaciers to Uhuru Peak at sunrise. Descend via Barafu to Millennium Camp for a well-deserved rest. About 12–14 hours.',
        ],
        'k-marangu-mandara' => [
            'title' => 'Marangu Gate (1,860 m) to Mandara Hut (2,720 m)', 'map' => 'mandara-hut', 'area' => 'Mandara Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Register at Marangu Gate and walk up a good path through the rainforest to Mandara Hut. In the afternoon, a short walk to the Maundi Crater gives your first views towards Kenya. About 3–4 hours.',
        ],
        'k-mandara-horombo' => [
            'title' => 'Mandara Hut (2,720 m) to Horombo Hut (3,720 m)', 'map' => 'horombo-hut', 'area' => 'Horombo Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Climb out of the forest into open moorland dotted with giant lobelias and groundsels, with Kibo and Mawenzi ahead. Horombo Hut sits on a ridge with sweeping views over the plains. About 5–6 hours.',
        ],
        'k-horombo-acclim' => [
            'title' => 'Acclimatisation day at Horombo Hut (3,720 m)', 'map' => 'horombo-hut', 'area' => 'Horombo Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'An extra day at Horombo to help your body adjust — the key to a successful summit. Walk up towards Zebra Rocks or the Mawenzi saddle and come back to sleep at Horombo. About 3–4 hours of walking.',
        ],
        'k-horombo-kibo' => [
            'title' => 'Horombo Hut (3,720 m) to Kibo Hut (4,720 m)', 'map' => 'kibo-hut', 'area' => 'Kibo Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Cross the high, lunar saddle between Mawenzi and Kibo to Kibo Hut at the foot of the summit cone. Rest, eat an early dinner and sleep a few hours before the night climb. About 5–6 hours.',
        ],
        'k-kibo-summit-horombo' => [
            'title' => 'Summit day: Uhuru Peak (5,895 m) and down to Horombo Hut (3,720 m)', 'map' => 'uhuru-peak', 'area' => 'Horombo Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'kili', 'caption' => 'Mount Kilimanjaro',
            'copy' => 'Start around midnight and zig-zag up the steep scree to Gilman\'s Point on the crater rim, then on to Uhuru Peak for sunrise over Africa. Descend to Kibo Hut for a rest and continue down to Horombo. About 12–15 hours.',
        ],
        'k-horombo-marangu' => [
            'title' => 'Horombo Hut (3,720 m) to Marangu Gate (1,860 m)', 'map' => 'marangu-gate', 'area' => 'Moshi', 'meals' => 'Breakfast and Lunch', 'park' => 'Kilimanjaro',
            'copy' => 'A long, easy descent through the moorland and rainforest to Marangu Gate, where you receive your summit certificate. Say thank you to your crew and drive back to Moshi to celebrate. About 5–6 hours.',
        ],
        'k-rongai-firstcave' => [
            'title' => 'Rongai Gate (1,950 m) to First Cave Camp (2,600 m)', 'map' => 'first-cave', 'area' => 'First Cave Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Drive around the mountain to the Rongai Gate near the Kenyan border. The climb starts through pine forest and farmland, then thin forest and heather to First Cave Camp. The northern side is drier and quieter. About 3–4 hours.',
        ],
        'k-firstcave-kikelewa' => [
            'title' => 'First Cave Camp (2,600 m) to Kikelewa Camp (3,600 m)', 'map' => 'kikelewa', 'area' => 'Kikelewa Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Climb steadily through moorland towards the jagged peaks of Mawenzi, with wide views north over the plains of Kenya, to Kikelewa Camp by the caves. About 6–7 hours.',
        ],
        'k-kikelewa-mawenzi' => [
            'title' => 'Kikelewa Camp (3,600 m) to Mawenzi Tarn (4,330 m)', 'map' => 'mawenzi-tarn', 'area' => 'Mawenzi Tarn', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'photo' => 'mawenzi', 'caption' => 'Mawenzi peak',
            'copy' => 'A short, steep climb to Mawenzi Tarn, a small lake beneath the spectacular cliffs of Mawenzi peak. In the afternoon, an acclimatisation walk on the ridges above camp. About 3–4 hours.',
        ],
        'k-mawenzi-kibo' => [
            'title' => 'Mawenzi Tarn (4,330 m) to Kibo Hut (4,720 m)', 'map' => 'kibo-hut', 'area' => 'Kibo Hut', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Cross the wide, desert-like saddle between Mawenzi and Kibo to Kibo Hut at the base of the summit cone. Rest and prepare for the summit attempt at midnight. About 5–6 hours.',
        ],
        'k-umbwe-cave' => [
            'title' => 'Umbwe Gate (1,600 m) to Umbwe Cave Camp (2,900 m)', 'map' => 'umbwe-cave', 'area' => 'Umbwe Cave Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'The Umbwe route is the steepest and most direct way up the mountain. From Umbwe Gate the path climbs sharply along a forested ridge, using tree roots as steps, to Umbwe Cave Camp. About 5–7 hours.',
        ],
        'k-umbwecave-barranco' => [
            'title' => 'Umbwe Cave Camp (2,900 m) to Barranco Camp (3,950 m)', 'map' => 'barranco', 'area' => 'Barranco Camp', 'meals' => 'Full Board', 'park' => 'Kilimanjaro',
            'copy' => 'Continue up the narrow ridge out of the forest into heather and moorland, with dramatic views, before joining the southern circuit at Barranco Camp beneath the Barranco Wall. About 4–5 hours.',
        ],
    ];

    public static function packages(): array
    {
        $north = TourCategory::NorthernCircuit;
        $south = TourCategory::SouthernCircuit;
        $combo = TourCategory::Safaris;
        $zanzibar = TourCategory::Zanzibar;
        $kili = TourCategory::Kilimanjaro;

        $southern = ['location' => 'Southern Tanzania', 'included_extra' => ['Domestic flights as listed in the itinerary']];
        $beach = [
            'vehicle' => 'Airport and hotel transfers',
            'style' => 'Beach holiday',
            'parks_label' => 'Island',
            'excluded' => ['International flights and visas', 'Travel insurance (required)', 'Optional excursions', 'Drinks and personal expenses', 'Tips'],
        ];
        $climbExcluded = ['International flights and visas', 'Travel insurance covering trekking up to 6,000 m (required)', 'Sleeping bag and personal climbing gear (can be hired in Moshi)', 'Tips for your guides, porters and cook', 'Drinks and personal expenses'];
        $climbIncluded = fn (bool $huts) => [
            'Airport transfers and transfers to and from the mountain',
            'Hotel nights in Moshi before and after the climb',
            'Professional mountain guides, assistant guides, porters and a cook',
            'All meals on the mountain and safe drinking water',
            $huts ? 'Hut accommodation on the mountain' : 'Quality mountain tents and sleeping mats',
            'All Kilimanjaro National Park, camping or hut and rescue fees',
            'Daily health checks, including oxygen-level readings',
            'Summit certificate',
        ];
        $climb = fn (string $route, bool $huts = false) => [
            'category' => $kili, 'tier' => SafariTier::Classic, 'badge' => 'Kilimanjaro Climb', 'banner' => 'kili',
            'location' => 'Kilimanjaro National Park, Tanzania', 'vehicle' => 'Private transfers to and from the mountain',
            'style' => $huts ? 'Hut trek' : 'Camping trek', 'parks_label' => 'Route', 'parks_value' => $route,
            'included' => $climbIncluded($huts), 'excluded' => $climbExcluded,
            'overview_more' => 'Your climb is run by our own experienced mountain crew, with a slow pace ("pole pole"), daily health checks and good food to give you the best chance of reaching the summit. The trip includes a night in Moshi before and after the climb. Tell us your dates and fitness and we will help you choose the right route.',
        ];

        return [
            // ---- Southern Circuit
            [...$southern, 'name' => '4-Day Nyerere Boat Safari', 'days' => 4, 'category' => $south, 'tier' => SafariTier::Classic,
                'badge' => 'Boat Safari', 'style' => 'Private safari', 'banner' => 'nyerere',
                'summary' => 'Two days in Nyerere National Park with game drives, a walking safari and a boat trip on the Rufiji River.',
                'overview' => 'A short escape into the wilderness from Dar es Salaam — or a perfect add-on to a Zanzibar holiday. In Nyerere you explore by vehicle, on foot and by boat, with hippos, crocodiles, elephants and wild dogs.',
                'route' => [['arrival-dar', 1, 1], ['nyerere', 2, 1], ['depart-dar', 1, 1]]],
            [...$southern, 'name' => '7-Day Mikumi & Ruaha Safari', 'days' => 7, 'category' => $south, 'tier' => SafariTier::Classic,
                'badge' => 'Southern Circuit', 'style' => 'Private safari', 'banner' => 'ruaha',
                'summary' => 'The open plains of Mikumi and the wild river valleys of Ruaha, two of southern Tanzania\'s great parks.',
                'overview' => 'Discover the quieter south: two days on the Mikumi plains, then the long drive to Ruaha, famous for its lions, elephants and kudu, before flying back to Dar es Salaam.',
                'route' => [['arrival-dar', 1, 1], ['mikumi', 2, 1], ['ruaha', 2, 1], ['fly-dar-depart', 1, 2]]],
            [...$southern, 'name' => '8-Day Mikumi, Udzungwa & Ruaha Safari', 'days' => 8, 'category' => $south, 'tier' => SafariTier::Classic,
                'badge' => 'Southern Circuit', 'style' => 'Private safari', 'banner' => 'ruaha',
                'summary' => 'Mikumi\'s plains, a rainforest hike to the Sanje waterfalls in the Udzungwa Mountains, and two days in Ruaha.',
                'overview' => 'Game drives and a mountain rainforest in one trip. After Mikumi you hike in the Udzungwa Mountains — a paradise for birdwatchers — before two days in remote Ruaha.',
                'route' => [['arrival-dar', 1, 1], ['mikumi', 2, 1], ['udzungwa', 1, 1], ['ruaha', 2, 1], ['fly-dar-depart', 1, 2]]],
            [...$southern, 'name' => '8-Day Ruaha & Nyerere Fly-in Safari', 'days' => 8, 'category' => $south, 'tier' => SafariTier::MidRange,
                'badge' => 'Fly-in Safari', 'style' => 'Private fly-in safari', 'banner' => 'nyerere',
                'vehicle' => 'Light-aircraft flights and safari vehicles in the parks',
                'summary' => 'Fly between Nyerere and Ruaha for boat safaris on the Rufiji River and the big lions of Ruaha.',
                'overview' => 'Short flights instead of long drives: start with a boat safari on the Rufiji River in Nyerere, then fly to Ruaha for three days in one of Africa\'s wildest parks.',
                'route' => [['arrival-dar', 1, 1], ['flight-nyerere', 1, 1], ['nyerere-boat', 1, 1], ['flight-ruaha', 1, 1], ['ruaha-days', 2, 1], ['fly-dar-depart', 1, 2]]],
            [...$southern, 'name' => '9-Day Mikumi, Ruaha & Nyerere Safari', 'days' => 9, 'category' => $south, 'tier' => SafariTier::Classic,
                'badge' => 'Southern Circuit', 'style' => 'Private safari', 'banner' => 'ruaha',
                'summary' => 'Mikumi, Ruaha and Nyerere in one trip, finishing with a full-day boat safari on the Rufiji River.',
                'overview' => 'The south\'s three great parks in nine days: the plains of Mikumi, the lions and kudu of Ruaha, and the lakes and river of Nyerere, explored on a full day by boat.',
                'route' => [['arrival-dar', 1, 1], ['mikumi', 2, 1], ['ruaha', 2, 1], ['flight-nyerere', 1, 1], ['nyerere-boat', 1, 1], ['fly-dar-depart', 1, 2]]],
            [...$southern, 'name' => '10-Day Grand Southern Safari', 'days' => 10, 'category' => $south, 'tier' => SafariTier::Classic,
                'badge' => 'Southern Circuit', 'style' => 'Private safari', 'banner' => 'nyerere',
                'summary' => 'Mikumi, the Udzungwa rainforest, Ruaha and a boat safari in Nyerere — the complete southern circuit.',
                'overview' => 'Everything the south has to offer: Mikumi\'s plains, a waterfall hike in the Udzungwa Mountains, two days in Ruaha and a flight to Nyerere for a boat safari on the Rufiji River.',
                'route' => [['arrival-dar', 1, 1], ['mikumi', 2, 1], ['udzungwa', 1, 1], ['ruaha', 2, 1], ['flight-nyerere', 1, 1], ['nyerere-boat', 1, 1], ['fly-dar-depart', 1, 2]]],

            // ---- Luxury and fly-in (north)
            ['name' => '8-Day Luxury Flying Safari', 'days' => 8, 'category' => $north, 'tier' => SafariTier::Luxury,
                'badge' => 'Luxury Safari', 'style' => 'Private luxury safari', 'banner' => 'ngorongoro',
                'included_extra' => ['Light-aircraft flight to the Serengeti and back to Arusha'],
                'summary' => 'Mto wa Mbu, Lake Manyara, the Ngorongoro Crater and a fly-in Serengeti stay, all in luxury lodges.',
                'overview' => 'A luxury safari with short flights instead of long drives. Explore the village of Mto wa Mbu, Lake Manyara and the Ngorongoro Crater, enjoy a farm lunch in the highlands, then fly into the Serengeti.',
                'route' => [['arrival', 1, 1], ['mto-wa-mbu', 1, 1], ['manyara', 1, 1], ['farm-walk', 1, 1], ['ngorongoro', 1, 1], ['flight-serengeti', 1, 1], ['serengeti-full', 1, 1], ['fly-arusha-depart', 1, 1]]],
            ['name' => '9-Day Luxury Safari: All Major Parks', 'days' => 9, 'category' => $north, 'tier' => SafariTier::Luxury,
                'badge' => 'Luxury Safari', 'style' => 'Private luxury safari', 'banner' => 'serengeti',
                'included_extra' => ['Light-aircraft flights as listed in the itinerary'],
                'summary' => 'Fly into Tarangire, stay in the Ngorongoro highlands and fly to the Serengeti — luxury lodges all the way.',
                'overview' => 'The great northern parks with the comfort of light-aircraft flights: Tarangire, a farm day in the highlands, the Ngorongoro Crater and three days in the Serengeti.',
                'route' => [['arrival', 1, 1], ['fly-tarangire', 1, 1], ['tarangire-full', 1, 1], ['farm-walk', 1, 1], ['ngorongoro', 1, 1], ['flight-serengeti', 1, 1], ['serengeti-full', 2, 1], ['fly-arusha-depart', 1, 1]]],
            ['name' => '11-Day Relaxed & Comfortable Safari', 'days' => 11, 'category' => $north, 'tier' => SafariTier::MidRange,
                'badge' => 'Relaxed Pace', 'style' => 'Private safari at an easy pace', 'banner' => 'tarangire',
                'included_extra' => ['Flights to the Serengeti and back to Arusha'],
                'summary' => 'An easy-paced safari with rest days, two nights in each park and flights instead of long drives — a favourite for travellers 50+.',
                'overview' => 'Designed for comfort: a rest day in Arusha after your flight, two nights in Tarangire, a gentle farm day in the highlands, the Ngorongoro Crater and a flight to the Serengeti to avoid the long, bumpy roads.',
                'route' => [['arrival', 1, 1], ['arusha-rest', 1, 1], ['tarangire', 2, 1], ['farm-walk', 1, 1], ['ngorongoro', 1, 1], ['flight-serengeti', 1, 1], ['serengeti-full', 2, 1], ['fly-arusha-overnight', 1, 1], ['depart-kili', 1, 1]]],
            ['name' => '12-Day Luxury Safari & Zanzibar', 'days' => 12, 'category' => $combo, 'tier' => SafariTier::Luxury,
                'badge' => 'Luxury Safari & Beach', 'style' => 'Private luxury safari + beach', 'banner' => 'zanzibar',
                'included_extra' => ['Flights to the Serengeti and from the Serengeti to Zanzibar'],
                'summary' => 'Luxury lodges in Tarangire, Ngorongoro and the Serengeti, then three days on Zanzibar\'s beaches.',
                'overview' => 'A luxurious bush-and-beach holiday: two days in Tarangire, a cultural day in Mto wa Mbu, the Ngorongoro Crater, a flight into the Serengeti and a flight straight on to Zanzibar for the beach.',
                'route' => [['arrival', 1, 1], ['tarangire', 2, 1], ['mto-wa-mbu', 1, 1], ['ngorongoro', 1, 1], ['flight-serengeti', 1, 1], ['serengeti-full', 1, 1], ['fly-serengeti-zanzibar', 1, 1], ['zanzibar', 3, 1], ['depart-zanzibar', 1, 1]]],
            ['name' => '13-Day Photographic Safari', 'days' => 13, 'category' => $north, 'tier' => SafariTier::MidRange,
                'badge' => 'Photographic Safari', 'style' => 'Private photographic safari', 'banner' => 'serengeti',
                'summary' => 'Long days in the best light: Tarangire, Manyara, Lake Eyasi, two days in the Crater, the Serengeti and Ndutu.',
                'overview' => 'Planned for photographers, with early starts, late finishes and time to wait for the moment. Two days each in Tarangire and the Ngorongoro Crater, three in the Serengeti and — in the calving season — two days at Ndutu.',
                'route' => [['arrival', 1, 1], ['tarangire', 2, 1], ['manyara', 1, 1], ['eyasi', 1, 1], ['ngorongoro-photo', 2, 1], ['serengeti', 3, 1], ['ndutu', 2, 1], ['depart-kili', 1, 1]]],

            // ---- Western Tanzania and north-east
            ['name' => '8-Day Chimpanzee Safari: Gombe & Mahale', 'days' => 8, 'category' => $combo, 'tier' => SafariTier::MidRange,
                'badge' => 'Chimpanzee Trekking', 'style' => 'Private primate safari', 'banner' => 'gombe',
                'location' => 'Western Tanzania', 'vehicle' => 'Domestic flights, boat and road transfers',
                'fees' => 'Gombe and Mahale park fees and chimpanzee trekking',
                'included_extra' => ['Domestic flights and boat transfers as listed in the itinerary'],
                'summary' => 'Trek to see wild chimpanzees in Gombe Stream and the Mahale Mountains on the shores of Lake Tanganyika.',
                'overview' => 'A journey to Tanzania\'s far west, where forested mountains drop into Lake Tanganyika. Spend time with wild chimpanzee families in Gombe — made famous by Jane Goodall — and in the remote Mahale Mountains.',
                'route' => [['arrival-dar', 1, 1], ['flight-kigoma', 1, 1], ['gombe', 2, 1], ['boat-mahale', 1, 1], ['mahale', 1, 1], ['return-kigoma', 1, 1], ['fly-dar-depart', 1, 1]]],
            ['name' => '10-Day Mkomazi, Usambara & Saadani', 'days' => 10, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Off the Beaten Track', 'style' => 'Private safari, mountains and beach', 'banner' => 'saadani',
                'location' => 'North-east Tanzania & the coast',
                'summary' => 'The rhino sanctuary of Mkomazi, hikes in the green Usambara Mountains and Saadani, where the bush meets the beach.',
                'overview' => 'An off-the-beaten-track route from Kilimanjaro to the coast: rhinos in Mkomazi, village walks and rainforest in the Usambara Mountains, and three relaxed days in Saadani with game drives, a river boat trip and the beach.',
                'route' => [['arrival', 1, 1], ['mkomazi', 1, 1], ['usambara-west', 1, 1], ['usambara-east', 2, 1], ['saadani', 3, 1], ['depart-dar', 1, 2]]],

            // ---- Islands
            [...$beach, 'name' => '6-Day Zanzibar Island Holiday', 'days' => 6, 'category' => $zanzibar, 'tier' => SafariTier::Classic,
                'badge' => 'Beach Holiday', 'banner' => 'zanzibar', 'location' => 'Zanzibar', 'parks_value' => 'Zanzibar',
                'included' => ['Beach hotel accommodation with breakfast', 'Airport and hotel transfers', 'Welcome dinner on arrival', 'All hotel taxes'],
                'summary' => 'Four nights on Zanzibar\'s white-sand beaches, with optional Stone Town, spice and snorkelling trips.',
                'overview' => 'A short, easy beach holiday — or the perfect ending to a safari or climb. Stay by the turquoise sea and add the excursions you like: historic Stone Town, a spice farm or a sea trip to sandbanks and reefs.',
                'overview_more' => 'We can book any excursion for you and adjust the hotel to your budget and style — just ask.',
                'route' => [['arrival-zanzibar', 1, 1], ['zanzibar-stay', 3, 1], ['depart-zanzibar', 1, 2]]],
            [...$beach, 'name' => '10-Day Zanzibar Island Holiday', 'days' => 10, 'category' => $zanzibar, 'tier' => SafariTier::Classic,
                'badge' => 'Beach Holiday', 'banner' => 'zanzibar', 'location' => 'Zanzibar', 'parks_value' => 'Zanzibar',
                'included' => ['Beach hotel accommodation with breakfast', 'Airport and hotel transfers', 'Welcome dinner on arrival', 'All hotel taxes'],
                'summary' => 'Eight relaxed nights on Zanzibar, with time for Stone Town, spice farms, reefs and sunset dhow cruises.',
                'overview' => 'Plenty of time to unwind and explore: beach days by the turquoise sea, with optional trips to Stone Town, a spice farm, Mnemba Atoll, Jozani Forest and a sunset dhow cruise.',
                'overview_more' => 'We can book any excursion for you and adjust the hotel to your budget and style — just ask.',
                'route' => [['arrival-zanzibar', 1, 1], ['zanzibar-stay', 7, 1], ['depart-zanzibar', 1, 2]]],
            [...$beach, 'name' => '6-Day Mafia Island Escape', 'days' => 6, 'category' => $zanzibar, 'tier' => SafariTier::Classic,
                'badge' => 'Island Escape', 'banner' => 'mafia', 'location' => 'Mafia Island', 'parks_value' => 'Mafia Island',
                'vehicle' => 'Flights and transfers to and from the island',
                'included' => ['Lodge accommodation with full board', 'Flights to and from Mafia Island', 'Airport transfers', 'Marine park fees', 'One whale shark or snorkelling boat trip'],
                'summary' => 'A quiet island in a marine park — swim with whale sharks in season, snorkel the reefs and visit Chole Island.',
                'overview' => 'Mafia Island is the place for travellers who want peace, nature and the sea. Snorkel or dive in the Mafia Island Marine Park, swim with whale sharks (about October to March) and explore Chole Island by dhow.',
                'overview_more' => 'Ideal as an extension after a safari in Nyerere or the north. Tell us your dates and we will check the whale shark season and flights for you.',
                'route' => [['arrival-mafia', 1, 1], ['mafia', 3, 1], ['depart-mafia', 1, 2]]],
            [...$beach, 'name' => '6-Day Pemba Island Escape', 'days' => 6, 'category' => $zanzibar, 'tier' => SafariTier::Classic,
                'badge' => 'Island Escape', 'banner' => 'pemba', 'location' => 'Pemba Island', 'parks_value' => 'Pemba Island',
                'vehicle' => 'Flights and transfers to and from the island',
                'included' => ['Lodge accommodation with full board', 'Flights to and from Pemba', 'Airport transfers', 'One snorkelling trip to Misali Island'],
                'summary' => 'Green hills, mangroves, empty beaches and some of East Africa\'s clearest water for snorkelling and diving.',
                'overview' => 'Pemba is Zanzibar\'s quiet, green sister island. Snorkel or dive the reefs around Misali Island, paddle through the mangroves and visit clove farms — far from the crowds.',
                'overview_more' => 'Ideal as an extension after a safari or a Kilimanjaro climb. Tell us your dates and we will arrange the flights for you.',
                'route' => [['arrival-pemba', 1, 1], ['pemba', 3, 1], ['depart-pemba', 1, 2]]],

            // ---- Kilimanjaro
            [...$climb('Machame Route (7-day climb)'), 'name' => 'Machame Route – 7-Day Climb', 'days' => 9,
                'summary' => 'The popular "Whiskey Route" with an extra acclimatisation day for a better chance of reaching the summit.',
                'overview' => 'Machame is Kilimanjaro\'s most scenic and popular route, climbing through rainforest, moorland and alpine desert with the Barranco Wall on the way. The seven-day version adds a night at Karanga Camp, giving your body more time to adapt to the altitude.',
                'route' => [['arrival-climb', 1, 1], ['k-machame-gate', 1, 1], ['k-machame-shira', 1, 1], ['k-shira-barranco', 1, 1], ['k-barranco-karanga', 1, 1], ['k-karanga-barafu', 1, 1], ['k-summit-millennium', 1, 1], ['k-millennium-mweka', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Lemosho Route (7-day climb)'), 'name' => 'Lemosho Route – 7-Day Climb', 'days' => 9,
                'summary' => 'Start high on the Shira Plateau and climb the quiet western side, with excellent acclimatisation.',
                'overview' => 'The Lemosho route approaches Kilimanjaro from the west, crossing the beautiful Shira Plateau before joining the southern circuit. It is quieter than Machame, very scenic and has a high summit success rate.',
                'route' => [['arrival-climb', 1, 1], ['k-dropoff-shira1', 1, 1], ['k-shira1-shira2', 1, 1], ['k-shira-barranco', 1, 1], ['k-barranco-karanga', 1, 1], ['k-karanga-barafu', 1, 1], ['k-summit-millennium', 1, 1], ['k-millennium-mweka', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Lemosho–Shira Route (6-day climb)'), 'name' => 'Lemosho–Shira Route – 6-Day Climb', 'days' => 8,
                'summary' => 'Drive up to the Shira Plateau and start the climb at 3,500 m — a shorter way to enjoy the western routes.',
                'overview' => 'This variation of the Lemosho route starts with a drive up to the Shira Plateau, so you begin walking at high altitude among stunning scenery. It suits travellers who have already acclimatised, for example after climbing Mount Meru.',
                'route' => [['arrival-climb', 1, 1], ['k-dropoff-shira2', 1, 1], ['k-shira-barranco', 1, 1], ['k-barranco-karanga', 1, 1], ['k-karanga-barafu', 1, 1], ['k-summit-millennium', 1, 1], ['k-millennium-mweka', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Northern Circuit (8-day climb)'), 'name' => 'Northern Circuit – 8-Day Climb', 'days' => 10,
                'summary' => 'The longest and quietest route, circling the remote northern slopes — the best acclimatisation on the mountain.',
                'overview' => 'The Northern Circuit starts on the Lemosho route and then circles the wild northern slopes of Kilimanjaro, far from the crowds, with views into Kenya. Eight days on the mountain give the best acclimatisation and summit success of any route.',
                'route' => [['arrival-climb', 1, 1], ['k-lemosho-bigtree', 1, 1], ['k-bigtree-shira1', 1, 1], ['k-shira1-moir', 1, 1], ['k-moir-buffalo', 1, 1], ['k-buffalo-thirdcave', 1, 1], ['k-thirdcave-school', 1, 1], ['k-school-summit', 1, 1], ['k-millennium-mweka', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Marangu Route (6-day climb)', true), 'name' => 'Marangu Route – 6-Day Climb', 'days' => 8,
                'summary' => 'Sleep in mountain huts on the classic "Coca-Cola Route", with an extra acclimatisation day at Horombo.',
                'overview' => 'Marangu is the only route with hut accommodation, and its gentle gradient makes it popular with first-time climbers. The six-day version adds an acclimatisation day at Horombo Hut, which greatly improves your chances at the summit.',
                'route' => [['arrival-climb', 1, 1], ['k-marangu-mandara', 1, 1], ['k-mandara-horombo', 1, 1], ['k-horombo-acclim', 1, 1], ['k-horombo-kibo', 1, 1], ['k-kibo-summit-horombo', 1, 1], ['k-horombo-marangu', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Rongai Route (6-day climb)'), 'name' => 'Rongai Route – 6-Day Climb', 'days' => 8,
                'summary' => 'Climb from the dry northern side near Kenya, past Mawenzi Tarn — a good choice in the rainy season.',
                'overview' => 'The Rongai route is the only one that starts from the north, near the Kenyan border. It is quieter, drier and gentler than the southern routes, with a night beneath the cliffs of Mawenzi before the summit, and a descent down the Marangu route.',
                'route' => [['arrival-climb', 1, 1], ['k-rongai-firstcave', 1, 1], ['k-firstcave-kikelewa', 1, 1], ['k-kikelewa-mawenzi', 1, 1], ['k-mawenzi-kibo', 1, 1], ['k-kibo-summit-horombo', 1, 1], ['k-horombo-marangu', 1, 1], ['depart-kili', 1, 1]]],
            [...$climb('Umbwe Route (6-day climb)'), 'name' => 'Umbwe Route – 6-Day Climb', 'days' => 8,
                'summary' => 'The steepest, most direct and least crowded route — for fit, experienced hikers.',
                'overview' => 'Umbwe climbs a narrow, forested ridge straight up the southern slopes before joining the southern circuit at Barranco. It is short and steep, so we recommend it for strong hikers who have already acclimatised.',
                'route' => [['arrival-climb', 1, 1], ['k-umbwe-cave', 1, 1], ['k-umbwecave-barranco', 1, 1], ['k-barranco-karanga', 1, 1], ['k-karanga-barafu', 1, 1], ['k-summit-millennium', 1, 1], ['k-millennium-mweka', 1, 1], ['depart-kili', 1, 1]]],
        ];
    }
}
