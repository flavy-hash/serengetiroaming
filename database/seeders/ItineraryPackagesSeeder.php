<?php

namespace Database\Seeders;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\TourPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Packages from the company's own itinerary documents (Oct 2026):
 * Marangu 5 days, Machame 6 days, Lemosho 8 days (Kilimanjaro) and two luxury
 * Northern Circuit safaris. Group prices are listed in each package's price box.
 *
 * Only creates packages that don't exist yet, so admin edits are never overwritten.
 * The one exception: the earlier placeholder "Machame Route – 7 Days, $1,650" is
 * replaced by the real 6-day Machame itinerary.
 */
class ItineraryPackagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->packages() as $package) {
            $existing = TourPage::where('category', $package['category'])->where('slug', $package['slug'])->first();

            if (! $existing) {
                TourPage::create($package);
            } elseif ($package['slug'] === 'machame-route' && $existing->duration === '7 Days' && (int) $existing->price_from === 1650) {
                $existing->update(collect($package)->except(['category', 'slug', 'sort_order', 'is_published'])->all());
            }
        }
    }

    /**
     * Uses the photo already in storage, copies the bundled one, or leaves it empty.
     */
    private function image(string $folder, string $file): ?string
    {
        $path = "tour-pages/{$folder}/{$file}";
        $bundled = database_path("seeders/images/{$folder}/{$file}");
        $disk = Storage::disk('public');

        if (! $disk->exists($path) && is_file($bundled)) {
            $disk->put($path, file_get_contents($bundled));
        }

        return $disk->exists($path) ? $path : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function packages(): array
    {
        $kili = $this->image('kilimanjaro', 'kilimanjaro-amboseli.jpg');
        $mawenzi = $this->image('kilimanjaro', 'mawenzi.jpg');
        $crater = $this->image('safaris', 'ngorongoro.jpg');
        $lion = $this->image('safaris', 'lion.jpg');

        $climb = [
            'category' => TourCategory::Kilimanjaro,
            'tier' => SafariTier::Classic,
            'is_published' => true,
            'banner_badge' => 'Kilimanjaro Climb',
            'location' => 'Kilimanjaro National Park, Tanzania',
            'overview_heading' => 'Package Overview',
            'included' => [],
            'excluded' => [],
            'experiences_heading' => null,
            'experiences' => [],
        ];

        $safari = [
            'category' => TourCategory::NorthernCircuit,
            'tier' => SafariTier::Luxury,
            'is_published' => true,
            'banner_badge' => 'Luxury Safari',
            'location' => 'Northern Tanzania',
            'overview_heading' => 'Package Overview',
            'experiences_heading' => null,
            'experiences' => [],
            'excluded' => [
                'International and domestic flights, and visa',
                'Mandatory medical and travel insurance',
                'Laundry (available at an additional cost)',
                'Drinks and alcoholic beverages at the lodges',
            ],
        ];

        $safariIncluded = fn (int $nights) => [
            'Safari vehicle: 4×4 Land Cruiser with charging ports, fridge & binoculars',
            'English-speaking driver-guide with expert wildlife knowledge',
            'Soft drinks, tea, coffee and water during game drives',
            'One arrival and one departure airport transfer',
            "Accommodation: {$nights} nights in comfortable lodges and camps",
            'Guided game drives in Tarangire, the Serengeti and Ngorongoro',
            'All national park and Ngorongoro Crater entrance fees',
            'All government taxes and 18% VAT',
        ];

        // ['1–2' => 2100, …] => "1–2 climbers: $2,100 pp" rows for the price box ("1 traveller", singular).
        $groupPrices = fn (array $prices, string $unit) => collect($prices)
            ->map(fn (int $price, string|int $group) => [
                'label' => $group.' '.((string) $group === '1' ? rtrim($unit, 's') : $unit),
                'value' => '$'.number_format($price).' pp',
            ])
            ->values()->all();

        return [
            // ------------------------------------------------------------------ Machame
            [
                ...$climb,
                'slug' => 'machame-route',
                'sort_order' => 1,
                'title' => 'Machame Route',
                'package_name' => 'Machame Route',
                'duration' => '6 Days',
                'meta_description' => 'Climb Kilimanjaro on the 6-day Machame "Whiskey" Route — Shira Plateau, Lava Tower and the Barranco Wall to Uhuru Peak (5,895 m). Group prices from $1,800 pp.',
                'banner_image' => $kili,
                'banner_text' => 'The Whiskey Route to Uhuru Peak (5,895 m / 19,341 ft) — 6 days from Machame Gate, descending via Mweka.',
                'card_tagline' => 'The Whiskey Route',
                'card_highlight' => '~85% summit rate',
                'card_summary' => 'Six days through rainforest, the Shira Plateau, Lava Tower and the Barranco Wall to a sunrise summit, descending via Mweka.',
                'overview_body' => "The Machame Route — the \"Whiskey Route\" — climbs from Machame Gate through montane rainforest, heath and moorland, across the Shira Plateau and over the Barranco Wall to Uhuru Peak (5,895 m / 19,341 ft), then descends via the Mweka Route.\n\nThis 6-day version follows the \"climb high, sleep low\" principle with a day via Lava Tower (about 4,600 m). It is challenging and best for fit, experienced trekkers with limited time. You sleep in mountain tents set up by your crew.",
                'price_from' => 1800,
                'price_note' => 'per person for 6–10 climbers — prices for smaller groups below',
                'facts' => [
                    ['label' => 'Duration', 'value' => '6 Days / 5 Nights on the mountain'],
                    ['label' => 'Start', 'value' => 'Machame Gate'],
                    ['label' => 'Summit', 'value' => 'Uhuru Peak – 5,895 m'],
                    ['label' => 'Descent', 'value' => 'Mweka Route'],
                    ['label' => 'Accommodation', 'value' => 'Mountain tents'],
                    ['label' => 'Difficulty', 'value' => 'Challenging'],
                    ['label' => 'Best for', 'value' => 'Fit, experienced trekkers with limited time'],
                    ...$groupPrices(['1–2' => 2200, '3–5' => 2000, '6–10' => 1800], 'climbers'),
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Machame Gate to Machame Camp', 'open' => true,
                        'hero_image' => $kili, 'hero_caption' => 'Mount Kilimanjaro',
                        'copy' => 'After breakfast, drive from your hotel to Machame Gate for registration and park formalities. The trek begins through beautiful montane rainforest, climbing gradually along a winding path among lush vegetation and ancient trees. After several hours you arrive at Machame Camp, where your mountain crew will have prepared your tents. Dinner and overnight.',
                        'note' => 'Elevation 1,800 m → 3,000 m · Distance approx. 11 km · Hiking time 5–7 hours · Habitat: montane rainforest',
                        'meal_plan' => 'Breakfast, Lunch & Dinner',
                        'accommodation' => 'Machame Camp',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Machame Camp to Shira Camp',
                        'copy' => 'Leave the rainforest behind and climb through the heath and moorland zone. The trail becomes steeper towards the Shira Plateau, with increasingly impressive views of Mount Meru and the surrounding landscape. Arrive at Shira Camp in the afternoon for lunch and time to relax while your body adapts to the altitude. Dinner and overnight.',
                        'note' => 'Elevation 3,000 m → 3,840 m · Distance approx. 5 km · Hiking time 4–6 hours · Habitat: moorland / heath',
                        'meal_plan' => 'Breakfast, Lunch & Dinner',
                        'accommodation' => 'Shira Camp',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Shira Camp – Lava Tower – Barranco Camp',
                        'copy' => 'An important acclimatisation day following the principle of "climb high, sleep low". Cross the spectacular Shira Plateau and climb gradually to Lava Tower (about 4,600 m), then descend into the beautiful Barranco Valley. You arrive at Barranco Camp in the late afternoon, with spectacular views of the Barranco Wall and the southern slopes of Kibo. Dinner and overnight.',
                        'note' => 'Elevation 3,840 m → 4,600 m → 3,960 m · Distance approx. 10–11 km · Hiking time 6–8 hours · Habitat: semi-desert',
                        'meal_plan' => 'Breakfast, Lunch & Dinner',
                        'accommodation' => 'Barranco Camp',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Barranco Wall – Karanga – Barafu Camp',
                        'copy' => 'After an early breakfast, begin the challenging ascent of the famous Barranco Wall. With your guide\'s help you scramble carefully up — no technical climbing equipment is needed, but some sections need your hands for balance — and are rewarded with magnificent views at the top. After a short break at Karanga Camp, continue the demanding ascent to Barafu Camp, the base camp for the summit. Early dinner, a summit briefing from your guide, and as much rest as possible before the midnight start.',
                        'note' => 'Elevation 3,960 m → 4,600–4,670 m · Distance approx. 8–9 km · Hiking time 8–10 hours · Habitat: alpine desert',
                        'meal_plan' => 'Breakfast, Lunch & Dinner',
                        'accommodation' => 'Barafu Camp',
                    ],
                    [
                        'day' => 'Day 5', 'title' => 'Summit Day: Uhuru Peak, down to Mweka Camp',
                        'hero_image' => $mawenzi, 'hero_caption' => 'Above the clouds on Kilimanjaro',
                        'copy' => 'Wake around midnight for the final ascent. The climb starts slowly through steep volcanic scree to Stella Point (5,752 m) on the crater rim — normally the most physically and mentally demanding section of the climb. After a short rest, continue along the crater rim to Uhuru Peak (5,895 m), the highest point in Africa, to celebrate, take photographs and enjoy the views, weather permitting. Then begin the long descent, stopping at Barafu for rest and lunch, down to Mweka Camp for dinner and a well-deserved night\'s rest.',
                        'note' => 'Ascent approx. 5 km · Descent approx. 12 km · Hiking time 12–16 hours · Habitat: alpine desert → arctic → rainforest',
                        'meal_plan' => 'Breakfast, Lunch & Dinner',
                        'accommodation' => 'Mweka Camp, approx. 3,100 m',
                    ],
                    [
                        'day' => 'Day 6', 'title' => 'Mweka Camp – Mweka Gate – Moshi',
                        'copy' => 'After breakfast, make your final descent through the rainforest to Mweka Gate. Complete the park procedures and receive your Kilimanjaro summit certificate if you reached the summit, then transfer back to your hotel in Moshi or Arusha. Congratulations on reaching the Roof of Africa!',
                        'note' => 'Elevation 3,100 m → 1,640–1,800 m · Distance approx. 10 km · Hiking time 3–4 hours · Habitat: montane rainforest',
                        'meal_plan' => 'Breakfast & Lunch',
                    ],
                ],
            ],

            // ------------------------------------------------------------------ Lemosho
            [
                ...$climb,
                'slug' => 'lemosho-route',
                'sort_order' => 2,
                'title' => 'Lemosho Route',
                'package_name' => 'Lemosho Route',
                'duration' => '8 Days',
                'meta_description' => 'Climb Kilimanjaro on the 8-day Lemosho Route from Londorossi Gate, across the Shira Plateau to Uhuru Peak (5,895 m) at sunrise. Group prices from $2,150 pp.',
                'banner_image' => $kili,
                'banner_text' => 'Eight days from Londorossi Gate across the Shira Plateau to a sunrise summit on Uhuru Peak (5,895 m).',
                'card_tagline' => 'Across the Shira Plateau',
                'card_highlight' => 'Extra acclimatisation days',
                'card_summary' => 'Eight days from Londorossi Gate through rainforest and across the Shira Plateau, via Lava Tower and the Barranco Wall to a sunrise summit.',
                'overview_body' => "The Lemosho Route starts at Londorossi Gate and climbs through dense rainforest and moorland onto the Shira Plateau, with spectacular views of Mount Meru and Kibo Peak.\n\nWith 8 days on the mountain — including an easy acclimatisation walk across the plateau and a \"climb high, sleep low\" day via Lava Tower — your body has more time to adjust before the midnight summit push to Uhuru Peak (5,895 m) at sunrise.",
                'price_from' => 2150,
                'price_note' => 'per person for 6–10 climbers — prices for smaller groups below',
                'facts' => [
                    ['label' => 'Duration', 'value' => '8 Days / 7 Nights on the mountain'],
                    ['label' => 'Start', 'value' => 'Londorossi Gate'],
                    ['label' => 'Summit', 'value' => 'Uhuru Peak – 5,895 m'],
                    ['label' => 'Descent', 'value' => 'Mweka Gate'],
                    ...$groupPrices(['1–2' => 2800, '3–5' => 2380, '6–10' => 2150], 'climbers'),
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Londorossi Gate to Mti Mkubwa Camp', 'open' => true,
                        'hero_image' => $kili, 'hero_caption' => 'Mount Kilimanjaro',
                        'copy' => 'After breakfast, drive from Arusha or Moshi to Londorossi Gate for registration, then a short drive to the trailhead. Begin the trek through dense rainforest. Dinner and overnight at Mti Mkubwa Camp.',
                        'note' => 'Hiking time 3–4 hours · Altitude 2,100 m → 2,650 m',
                        'accommodation' => 'Mti Mkubwa Camp',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Mti Mkubwa Camp to Shira 1 Camp',
                        'copy' => 'Trek through forest and moorland, crossing streams and enjoying scenic views. Dinner and overnight at Shira 1 Camp.',
                        'note' => 'Hiking time 5–6 hours · Altitude 2,650 m → 3,610 m',
                        'accommodation' => 'Shira 1 Camp',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Shira 1 Camp to Shira 2 Camp',
                        'copy' => 'An easy acclimatisation walk across the Shira Plateau, with spectacular views of Mount Meru and Kibo Peak. Dinner and overnight at Shira 2 Camp.',
                        'note' => 'Hiking time 3–4 hours · Altitude 3,610 m → 3,850 m',
                        'accommodation' => 'Shira 2 Camp',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Shira 2 Camp – Lava Tower – Barranco Camp',
                        'copy' => 'A very important acclimatisation day: hike up to Lava Tower, have lunch there, then descend to Barranco Camp for the night.',
                        'note' => 'Hiking time 6–8 hours · Altitude 3,850 m → 4,630 m → 3,950 m',
                        'accommodation' => 'Barranco Camp',
                    ],
                    [
                        'day' => 'Day 5', 'title' => 'Barranco Camp to Karanga Camp',
                        'copy' => 'Climb the Barranco Wall, then continue trekking to Karanga Camp. Lunch, dinner and overnight at Karanga Camp.',
                        'note' => 'Hiking time 4–5 hours · Altitude 3,950 m → 4,035 m',
                        'accommodation' => 'Karanga Camp',
                    ],
                    [
                        'day' => 'Day 6', 'title' => 'Karanga Camp to Barafu Camp',
                        'copy' => 'A short hike to Barafu Camp, then lunch and rest. Summit briefing and an early dinner before the night at Barafu Camp.',
                        'note' => 'Hiking time 3–4 hours · Altitude 4,035 m → 4,673 m',
                        'accommodation' => 'Barafu Camp',
                    ],
                    [
                        'day' => 'Day 7', 'title' => 'Summit Day: Uhuru Peak, down to Mweka Camp',
                        'hero_image' => $mawenzi, 'hero_caption' => 'Above the clouds on Kilimanjaro',
                        'copy' => 'Wake around midnight for the summit attempt and reach Uhuru Peak (5,895 m) at sunrise. Then descend to Mweka Camp for dinner and overnight.',
                        'note' => 'Hiking time 10–14 hours · Altitude 4,673 m → 5,895 m → 3,100 m',
                        'accommodation' => 'Mweka Camp',
                    ],
                    [
                        'day' => 'Day 8', 'title' => 'Mweka Camp to Mweka Gate',
                        'copy' => 'Descend through the rainforest to Mweka Gate, receive your summit certificates and transfer back to Arusha or Moshi.',
                    ],
                ],
            ],

            // ------------------------------------------------------------------ Marangu
            [
                ...$climb,
                'slug' => 'marangu-route',
                'sort_order' => 3,
                'title' => 'Marangu Route',
                'package_name' => 'Marangu Route',
                'duration' => '5 Days',
                'meta_description' => 'Climb Kilimanjaro in 5 days on the Marangu Route, sleeping in mountain huts all the way to Uhuru Peak (5,895 m). Group prices from $1,450 pp.',
                'banner_image' => $mawenzi,
                'banner_text' => 'Climb Kilimanjaro in 5 days, sleeping in mountain huts all the way to Uhuru Peak (5,895 m).',
                'card_tagline' => 'Hut to hut to the Roof of Africa',
                'card_highlight' => 'Mountain huts, no tents',
                'card_summary' => 'Five days from Marangu Gate through rainforest and moorland to Uhuru Peak via Gillman\'s Point, sleeping at Mandara, Horombo and Kibo huts.',
                'overview_body' => "The Marangu Route climbs Kilimanjaro from Marangu Gate (1,860 m) through rainforest, moorland and alpine desert, with nights in the mountain huts at Mandara, Horombo and Kibo.\n\nSummit night starts around midnight from Kibo Hut, reaching Gillman's Point (5,685 m) and then Uhuru Peak (5,895 m) — the highest point in Africa — with sunrise over Mawenzi and the glaciers.",
                'price_from' => 1450,
                'price_note' => 'per person for 6–10 climbers — prices for smaller groups below',
                'facts' => [
                    ['label' => 'Duration', 'value' => '5 Days / 4 Nights on the mountain'],
                    ['label' => 'Start', 'value' => 'Marangu Gate'],
                    ['label' => 'Summit', 'value' => 'Uhuru Peak – 5,895 m'],
                    ['label' => 'Accommodation', 'value' => 'Mountain huts'],
                    ...$groupPrices(['1–2' => 2100, '3–5' => 1650, '6–10' => 1450], 'climbers'),
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Marangu Gate to Mandara Hut', 'open' => true,
                        'copy' => 'Registration at Marangu Gate (1,860 m), then trek through the lush rainforest — you might see blue monkeys and colobus monkeys. Dinner and overnight at Mandara Hut.',
                        'note' => 'Distance 8 km · Hiking time 4–5 hours · Habitat: rainforest',
                        'accommodation' => 'Mandara Hut, 2,700 m',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Mandara Hut to Horombo Hut',
                        'copy' => 'A gradual ascent through moorland terrain with views of Mawenzi Peak. Dinner and overnight at Horombo Hut.',
                        'note' => 'Distance 12 km · Hiking time 5–6 hours · Habitat: moorland',
                        'accommodation' => 'Horombo Hut, 3,720 m',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Horombo Hut to Kibo Hut',
                        'copy' => 'Trek past the "Last Water Point" and cross the saddle between Mawenzi and Kibo. Rest early at Kibo Hut for the midnight summit attempt.',
                        'note' => 'Distance 10 km · Hiking time 5–6 hours · Habitat: alpine desert',
                        'accommodation' => 'Kibo Hut, 4,703 m',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Summit Day: Uhuru Peak, down to Horombo Hut',
                        'hero_image' => $kili, 'hero_caption' => 'Mount Kilimanjaro',
                        'copy' => 'A midnight start for the summit (around 12:00 am). Reach Gillman\'s Point (5,685 m), then continue to Uhuru Peak (5,895 m), the highest point in Africa, and enjoy sunrise over Mawenzi and the glaciers. Descend to Horombo Hut for a well-deserved rest.',
                        'note' => 'Distance 21 km (ascent + descent) · Hiking time 10–14 hours · Habitat: arctic zone (summit) → moorland',
                        'accommodation' => 'Horombo Hut, 3,720 m',
                    ],
                    [
                        'day' => 'Day 5', 'title' => 'Horombo Hut to Marangu Gate',
                        'copy' => 'Descend through the forest to the gate, collect your summit certificates and transfer back to your hotel in Moshi or Arusha.',
                        'note' => 'Distance 18 km · Hiking time 5–6 hours · Habitat: moorland → rainforest',
                    ],
                ],
            ],

            // ------------------------------------------------------------------ Northern Circuit, 5 days
            [
                ...$safari,
                'slug' => 'private-luxury-safari-5-days',
                'sort_order' => 1,
                'title' => 'Private & Luxury Tanzania Safari',
                'package_name' => 'Private & Luxury Tanzania Safari',
                'duration' => '5 Days',
                'meta_description' => '5-day private luxury safari through Tarangire, the Serengeti and the Ngorongoro Crater, staying at Serena lodges and Karibu Camps.',
                'banner_image' => $crater,
                'banner_text' => 'Tarangire, the Serengeti and the Ngorongoro Crater on a private luxury safari.',
                'card_tagline' => 'Tarangire · Serengeti · Ngorongoro',
                'card_highlight' => 'Private safari',
                'card_summary' => 'Elephant herds in Tarangire, lions on the Serengeti plains and the wildlife of the Ngorongoro Crater, staying in comfortable lodges and camps.',
                'overview_body' => "Experience three of Tanzania's most iconic wildlife destinations on this 5-day private and luxury safari through Tarangire, the Serengeti and the Ngorongoro Crater.\n\nEncounter elephant herds, search for lions across endless plains, and explore the world's largest intact volcanic caldera, home to an extraordinary concentration of wildlife.",
                'price_from' => 2761,
                'price_note' => 'per person for a group of 7 — prices for smaller groups below',
                'facts' => [
                    ['label' => 'Duration', 'value' => '5 Days'],
                    ['label' => 'Parks', 'value' => 'Tarangire, Serengeti, Ngorongoro'],
                    ['label' => 'Vehicle', 'value' => 'Private 4×4 Land Cruiser'],
                    ...$groupPrices(['1' => 4675, '2' => 3559, '3' => 3187, '4' => 2996, '5' => 2889, '6' => 2815, '7' => 2761], 'travellers'),
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Kilimanjaro International Airport – Arusha', 'open' => true,
                        'copy' => 'On arrival at Kilimanjaro International Airport, our driver will meet you and transfer you to Arusha, a drive of about 45 to 60 minutes, with views of the landscape and glimpses of local life along the way. On arrival there is time to relax after your journey before dinner and overnight at Arusha Serena Hotel Resort & Spa — a peaceful retreat on the slopes of Mount Meru, surrounded by gardens and forest and overlooking nearby Lake Duluti.',
                        'accommodation' => 'Arusha Serena Hotel Resort & Spa',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Arusha – Tarangire National Park',
                        'copy' => 'After breakfast and a short briefing, we set off from Arusha and reach Tarangire in about two hours. As we enter the park, the baobabs and open landscape immediately stand out. We stop for a packed lunch inside, often with elephants not too far away. The afternoon is spent exploring, with good chances of spotting buffalo, giraffe and more, before dinner and overnight at Tarangire Elephant Springs, overlooking the wildlife-rich landscapes of Tarangire.',
                        'accommodation' => 'Tarangire Elephant Springs',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Tarangire – Central Serengeti National Park',
                        'hero_image' => $lion, 'hero_caption' => 'Lion in the Serengeti',
                        'copy' => 'After breakfast in Tarangire, we set off towards the Serengeti, a drive of about four hours that always feels longer in a good way as the landscape keeps changing. There is a real sense of arrival as we enter the Serengeti. We stop for a packed lunch, then head out on our first game drive, with good chances of seeing hyenas, lions, elephants and herds moving across the plains, before dinner and overnight at Serengeti Serena Safari Lodge, with sweeping views across the plains.',
                        'accommodation' => 'Serengeti Serena Safari Lodge',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Central Serengeti – Ngorongoro Crater',
                        'hero_image' => $crater, 'hero_caption' => 'Ngorongoro Crater',
                        'copy' => 'A highlight of your safari: after an early breakfast we head down into the Ngorongoro Crater. The drive itself is part of the experience, winding through forest before opening onto the crater floor below — full of life, with lions, elephants and, if we\'re lucky, even rhino. We spend the morning exploring and enjoy a packed lunch in this incredible setting. Later, we begin the five-hour drive to Kilimanjaro International Airport, with plenty of memories to take home.',
                    ],
                ],
                'included' => $safariIncluded(4),
                'excluded' => [
                    'Tips: suggested driver-guide tip of $25 per day, per couple',
                    'Serengeti balloon safari with bush breakfast ($450 per person)',
                    ...$safari['excluded'],
                ],
            ],

            // ------------------------------------------------------------------ Northern Circuit, 8 days
            [
                ...$safari,
                'slug' => 'great-migration-luxury-safari-8-days',
                'sort_order' => 2,
                'title' => 'Great Migration Luxury Safari',
                'package_name' => 'Great Migration Luxury Safari',
                'duration' => '8 Days',
                'meta_description' => '8-day luxury Great Migration safari: Tarangire, the Central and Northern Serengeti for the Mara River crossings, and the Ngorongoro Crater.',
                'banner_image' => $lion,
                'banner_text' => 'Follow the Great Migration to the Mara River crossings, with Tarangire and the Ngorongoro Crater.',
                'card_tagline' => 'Mara River crossings',
                'card_highlight' => 'Great Migration',
                'card_summary' => 'Tarangire, the Central and Northern Serengeti for the Mara River crossings, and the Ngorongoro Crater — eight days with luxury lodges and camps.',
                'overview_body' => "Experience the drama of the Great Migration on this 8-day safari through Tarangire, the Central and Northern Serengeti, and the Ngorongoro Crater.\n\nWitness the legendary Mara River crossing, search for predators across endless plains, encounter elephant herds, and explore some of Tanzania's most iconic wildlife destinations.",
                'price_from' => 5722,
                'price_note' => 'per person for a group of 7 — prices for smaller groups below',
                'facts' => [
                    ['label' => 'Duration', 'value' => '8 Days / 7 Nights'],
                    ['label' => 'Parks', 'value' => 'Tarangire, Serengeti, Ngorongoro'],
                    ['label' => 'Vehicle', 'value' => 'Private 4×4 Land Cruiser'],
                    ...$groupPrices(['1' => 8991, '2' => 7078, '3' => 6450, '4' => 6122, '5' => 5940, '6' => 5800, '7' => 5722], 'travellers'),
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Kilimanjaro International Airport – Arusha', 'open' => true,
                        'copy' => 'On arrival at Kilimanjaro International Airport, our driver will meet you and transfer you to Arusha, a drive of about 45 to 60 minutes, with views of the landscape and glimpses of local life along the way. On arrival there is time to relax after your journey before dinner and overnight at Arusha Serena Hotel Resort & Spa — a peaceful retreat on the slopes of Mount Meru, surrounded by gardens and forest and overlooking nearby Lake Duluti.',
                        'accommodation' => 'Arusha Serena Hotel Resort & Spa',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Arusha – Tarangire National Park',
                        'copy' => 'After breakfast and a short briefing, we set off from Arusha and reach Tarangire in about two hours. As we enter the park, the baobabs and open landscape immediately stand out. We stop for a packed lunch inside, often with elephants not too far away. The afternoon is spent exploring, with good chances of spotting buffalo, giraffe and more, before dinner and overnight at Tarangire Elephant Springs, overlooking the wildlife-rich landscapes of Tarangire.',
                        'accommodation' => 'Tarangire Elephant Springs',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Tarangire – Central Serengeti National Park',
                        'hero_image' => $lion, 'hero_caption' => 'Lion in the Serengeti',
                        'copy' => 'After breakfast in Tarangire, we set off towards the Serengeti, a drive of about six hours that always feels longer in a good way as the landscape keeps changing. There is a real sense of arrival as we enter the Serengeti. We stop for a packed lunch, then head out on our first game drive, with good chances of seeing hyenas, lions, elephants and herds moving across the plains, before dinner and overnight at Serengeti Serena Safari Lodge, with sweeping views across the plains.',
                        'accommodation' => 'Serengeti Serena Safari Lodge',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Central Serengeti – Northern Serengeti',
                        'copy' => 'After breakfast we set off towards the Northern Serengeti, a drive of about five hours following wildlife across the plains, with chances of seeing lions, cheetahs and elephants. Near the Mara River your guide takes you straight to the river. Game drives here are all about timing and patience — positioning the vehicle at a safe distance, often under a tree, and as the wildebeest gather, slowly moving closer to watch the crossing unfold. Dinner and overnight at Mara Mara Tented Lodge, with excellent access to the Mara River region.',
                        'activity_title' => 'Mara River crossing',
                        'activity_copy' => 'When a crossing begins, it is fast and full of energy, with crocodiles in the water and predators nearby.',
                        'accommodation' => 'Mara Mara Tented Lodge',
                    ],
                    [
                        'day' => 'Day 5', 'title' => 'Northern Serengeti game drives',
                        'copy' => 'Today is spent around the Mara River, where patience often leads to unforgettable moments. We head out after breakfast and position ourselves carefully, watching as herds gather along the riverbanks — there is always a sense that something could happen at any time. We enjoy a packed lunch in the area before continuing through the afternoon. As the light softens, the plains take on a warm golden glow before we return to Mara Mara Tented Lodge for dinner and overnight.',
                        'accommodation' => 'Mara Mara Tented Lodge',
                    ],
                    [
                        'day' => 'Day 6', 'title' => 'Northern Serengeti – Central Serengeti',
                        'copy' => 'After breakfast, we set off from the Northern Serengeti towards the Central Serengeti, a drive of about five hours through different parts of the park. The landscape gradually changes and wildlife remains part of the journey, with chances of seeing lions, cheetahs, elephants and herds spread across the plains. A packed lunch, then on through the afternoon to arrive at Serengeti Serena Safari Lodge by early evening for dinner and a relaxed evening.',
                        'accommodation' => 'Serengeti Serena Safari Lodge',
                    ],
                    [
                        'day' => 'Day 7', 'title' => 'Central Serengeti – Ngorongoro Crater',
                        'hero_image' => $crater, 'hero_caption' => 'Ngorongoro Crater',
                        'copy' => 'After breakfast, a final morning game drive across the endless plains — a last chance to take in the wildlife before leaving. We head towards Ngorongoro with a packed lunch along the way; the drive takes about 3.5 hours, and as we reach the crater rim the vast Ngorongoro Crater suddenly opens up below. By late afternoon we arrive at Ngorongoro Lion\'s Paw Camp, on the edge of the crater, for dinner and overnight.',
                        'accommodation' => "Ngorongoro Lion's Paw Camp",
                    ],
                    [
                        'day' => 'Day 8', 'title' => 'Ngorongoro Crater – Kilimanjaro Airport',
                        'copy' => 'A highlight of your safari: after an early breakfast we head down into the Ngorongoro Crater. The drive winds through forest before opening onto the crater floor below — full of life, with lions, elephants and, if we\'re lucky, even rhino. We spend the morning exploring and enjoy a packed lunch in this incredible setting, then begin the five-hour drive to Kilimanjaro International Airport, with plenty of memories to take home.',
                    ],
                ],
                'included' => $safariIncluded(7),
                'excluded' => [
                    'Tips: suggested driver-guide tip of $20 per day, per couple',
                    'Serengeti balloon safari with bush breakfast ($520 per person)',
                    ...$safari['excluded'],
                ],
            ],
        ];
    }
}
