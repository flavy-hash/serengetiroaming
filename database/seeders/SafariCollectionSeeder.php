<?php

namespace Database\Seeders;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\TourPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fifteen Northern Circuit and safari-plus-Zanzibar itineraries (4 to 18 days), modelled on
 * common Tanzania safari routes. Day texts are our own, written per place; overnight areas
 * are given instead of named lodges.
 *
 * All packages are created as DRAFTS without a price: add the price, check the text, then
 * switch "Published" on in the admin (Website Pages → Northern Circuit / Safaris).
 *
 * Only creates packages that don't exist yet, so admin edits are never overwritten:
 *   php artisan db:seed --class=SafariCollectionSeeder --force
 */
class SafariCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $order = (int) TourPage::max('sort_order');
        $added = 0;

        foreach (static::packages() as $spec) {
            $category = $spec['category'];
            $slug = Str::slug($spec['name']);

            if (TourPage::where('category', $category)->where('slug', $slug)->exists()) {
                continue;
            }

            TourPage::create($this->build($spec, $slug, ++$order));
            $added++;
        }

        $this->command?->info("Safari collection: {$added} draft packages added.");
    }

    /**
     * @param  array<string, mixed>  $spec
     * @return array<string, mixed>
     */
    protected function build(array $spec, string $slug, int $order): array
    {
        $itinerary = $this->itinerary($spec['route']);
        $days = (int) $spec['days'];
        $parks = collect($spec['route'])->map(fn ($leg) => static::stops()[$leg[0]]['park'] ?? null)->filter()->unique()->values();
        $hasZanzibar = $parks->contains('Zanzibar');
        $banner = $this->photo($spec['banner']);

        return [
            'category' => $spec['category'],
            'tier' => $spec['tier'],
            'title' => $spec['name'],
            'slug' => $slug,
            'is_published' => false,
            'sort_order' => $order,
            'banner_image' => $banner,
            'banner_badge' => $spec['badge'],
            'banner_text' => $spec['summary'],
            'location' => $spec['location'] ?? ($hasZanzibar ? 'Northern Tanzania & Zanzibar' : 'Northern Tanzania'),
            'package_name' => $spec['name'],
            'duration' => "{$days} Days",
            'card_tagline' => $spec['tagline'] ?? ($parks->reject(fn ($p) => $p === 'Zanzibar')->take(3)->implode(' · ').($hasZanzibar ? ' + Zanzibar' : '')),
            'card_summary' => $spec['summary'],
            'overview_heading' => 'Package Overview',
            'overview_body' => $spec['overview']."\n\n".($spec['overview_more'] ?? null ?: ($hasZanzibar
                ? 'You travel in a private 4×4 Land Cruiser with a pop-up roof and your own English-speaking driver-guide, then fly to Zanzibar for time on the beach. Every itinerary can be adjusted — tell us your dates, budget and style and we will tailor it for you.'
                : 'You travel in a private 4×4 Land Cruiser with a pop-up roof and your own English-speaking driver-guide, at your own pace. Every itinerary can be adjusted — tell us your dates, budget and style and we will tailor it for you.')),
            'price_from' => null,
            'price_note' => 'Price on request — ask for a quote for your dates and group size.',
            'facts' => [
                ['label' => 'Duration', 'value' => "{$days} Days"],
                ['label' => $spec['parks_label'] ?? 'Parks', 'value' => $spec['parks_value'] ?? $parks->reject(fn ($p) => $p === 'Zanzibar')->implode(', ')],
                ['label' => 'Vehicle', 'value' => $spec['vehicle'] ?? 'Private 4×4 Land Cruiser'],
                ['label' => 'Style', 'value' => $spec['style']],
            ],
            'itinerary' => $itinerary,
            'included' => array_values(array_filter([
                'All accommodation as listed in the itinerary',
                'Meals as listed for each day',
                ($spec['vehicle'] ?? null) ? 'Transport: '.$spec['vehicle'] : 'Private 4×4 Land Cruiser with pop-up roof',
                'Professional English-speaking driver-guide',
                $spec['fees'] ?? 'All national park and Ngorongoro Crater fees',
                'Drinking water during game drives',
                'Airport pick-up and drop-off',
                ...($spec['included_extra'] ?? ($hasZanzibar ? ['Flight from Arusha to Zanzibar and hotel transfers'] : [])),
                'All government taxes',
            ])),
            'excluded' => $spec['excluded'] ?? [
                'International flights and visas',
                'Travel insurance (required)',
                'Optional activities not listed as included',
                'Tips for your guide and lodge staff',
                'Drinks and personal expenses',
            ],
            'experiences_heading' => null,
            'experiences' => [],
        ];
    }

    /**
     * Expands [stop, number of days] legs into one itinerary entry per day.
     *
     * @param  array<int, array{0: string, 1: int}>  $route
     * @return array<int, array<string, mixed>>
     */
    protected function itinerary(array $route): array
    {
        $days = [];
        $n = 1;

        foreach ($route as [$key, $length, $span]) {
            $stop = static::stops()[$key];
            for ($i = 0; $i < $length; $i++) {
                $copy = $i === 0 ? $stop['copy'] : ($stop['more'][min($i - 1, count($stop['more']) - 1)] ?? $stop['copy']);
                $label = ($span ?? 1) > 1 ? 'Day '.$n.' – '.($n + $span - 1) : 'Day '.$n;

                $day = [
                    'day' => $label,
                    'title' => $stop['title'].($length > 1 && $i > 0 ? ' — day '.($i + 1) : ''),
                    'open' => $n === 1,
                    'hero_image' => $i === 0 ? $this->photo($stop['photo'] ?? null) : null,
                    'hero_caption' => $i === 0 ? ($stop['caption'] ?? null) : null,
                    'copy' => $copy,
                    'note' => $i === 0 ? ($stop['note'] ?? null) : null,
                    'accommodation' => $stop['area'],
                    'meal_plan' => $stop['meals'],
                    'options' => [],
                    'map_place' => $stop['map'],
                ];
                if (isset($stop['lat'])) {
                    $day['map_name'] = $stop['map_name'];
                    $day['map_lat'] = $stop['lat'];
                    $day['map_lng'] = $stop['lng'];
                }

                $days[] = array_filter($day, fn ($v) => $v !== null);
                $n += $span ?? 1;
            }
        }

        return $days;
    }

    /**
     * Uses a photo already on the public disk, else copies the bundled seed photo, else none.
     */
    protected function photo(?string $key): ?string
    {
        $photos = static::photos();
        if (! $key || ! isset($photos[$key])) {
            return null;
        }

        [$path, $bundled] = $photos[$key];
        $disk = Storage::disk('public');
        if (! $disk->exists($path) && is_file(database_path("seeders/images/{$bundled}"))) {
            $disk->put($path, file_get_contents(database_path("seeders/images/{$bundled}")));
        }

        return $disk->exists($path) ? $path : null;
    }

    /**
     * Places used in itineraries; subclasses add their own.
     *
     * @return array<string, array<string, mixed>>
     */
    protected static function stops(): array
    {
        return self::STOPS;
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected static function photos(): array
    {
        return self::PHOTOS;
    }

    /** photo key => [public-disk path, bundled file under database/seeders/images] */
    private const PHOTOS = [
        'serengeti' => ['tour-pages/safaris/lion.jpg', 'safaris/lion.jpg'],
        'ngorongoro' => ['tour-pages/safaris/ngorongoro.jpg', 'safaris/ngorongoro.jpg'],
        'tarangire' => ['destinations/tarangire.jpg', 'destinations/tarangire.jpg'],
        'manyara' => ['destinations/lake-manyara.jpg', 'destinations/lake-manyara.jpg'],
        'arusha-np' => ['destinations/arusha-national-park.jpg', 'destinations/arusha-national-park.jpg'],
        'natron' => ['destinations/lake-natron.jpg', 'destinations/lake-natron.jpg'],
        'zanzibar' => ['tour-pages/zanzibar/nungwi.jpg', 'zanzibar/nungwi.jpg'],
        'coffee' => ['activities/coffee-tour.jpg', 'activities/coffee-tour.jpg'],
        'maasai' => ['activities/maasai-culture.jpg', 'activities/maasai-culture.jpg'],
        'walking' => ['activities/walking-safari.jpg', 'activities/walking-safari.jpg'],
    ];

    /**
     * One description per place (our own words); "more" is used for second and later days there.
     */
    private const STOPS = [
        'arrival' => [
            'title' => 'Arrival at Kilimanjaro International Airport', 'map' => 'kilimanjaro-airport', 'area' => 'Arusha', 'meals' => 'Dinner',
            'copy' => 'Karibu Tanzania! Your Serengeti Roaming driver-guide meets you at Kilimanjaro International Airport and drives you to your lodge in Arusha. Depending on your arrival time, relax in the garden or by the pool, then enjoy dinner and a short briefing on the days ahead.',
        ],
        'arusha-rest' => [
            'title' => 'A relaxing day in Arusha', 'map' => 'arusha', 'area' => 'Arusha', 'meals' => 'Bed and Breakfast',
            'copy' => 'Take a day to recover from your journey and the time change. Sleep in, enjoy the lodge grounds and pool, or let us arrange something gentle such as a stroll through a local market.',
        ],
        'arusha-np' => [
            'title' => 'Arusha National Park', 'map' => 'arusha-national-park', 'area' => 'Arusha', 'meals' => 'Full Board', 'park' => 'Arusha National Park',
            'photo' => 'arusha-np', 'caption' => 'Ngurdoto Crater, Arusha National Park',
            'copy' => 'Spend the day in Arusha National Park at the foot of Mount Meru. Look for giraffes, buffalo and black-and-white colobus monkeys, stop at the Ngurdoto Crater viewpoint and the Momella Lakes, and — if you wish — join an armed ranger for a walking safari or paddle a canoe on Small Momella Lake (optional).',
        ],
        'arusha-active' => [
            'title' => 'Free day in Arusha — relax or get active', 'map' => 'arusha', 'area' => 'Arusha', 'meals' => 'Bed and Breakfast',
            'photo' => 'coffee', 'caption' => 'Coffee farm near Arusha',
            'copy' => 'Today is yours. Rest at the lodge, or choose an activity and we will arrange it: a coffee tour on the slopes of Mount Meru, a cultural walk with a local guide, the Maasai market in town, or a hike to a forest waterfall.',
        ],
        'materuni' => [
            'title' => 'Materuni waterfalls and coffee walk', 'map' => 'materuni', 'area' => 'Arusha', 'meals' => 'Full Board',
            'photo' => 'coffee', 'caption' => 'Coffee cherries on the farm',
            'copy' => 'Drive to Materuni village on the green slopes of Kilimanjaro and walk through banana and coffee gardens to a tall waterfall for a refreshing swim. Back in the village, a Chagga family shows you how to roast and grind coffee by hand — and you taste the result.',
        ],
        'tarangire' => [
            'title' => 'Tarangire National Park', 'map' => 'tarangire', 'area' => 'Tarangire', 'meals' => 'Full Board', 'park' => 'Tarangire',
            'photo' => 'tarangire', 'caption' => 'Elephants and baobabs in Tarangire',
            'copy' => 'Drive to Tarangire National Park, known for its ancient baobab trees and big herds of elephants. Spend the day on game drives along the Tarangire River, where zebra, wildebeest, buffalo and lions come to drink in the dry season, with a picnic lunch in the park.',
            'more' => ['A second day in Tarangire lets you go further and slower — the quieter southern swamps, huge elephant families, tree-climbing pythons and more than 500 species of birds.'],
        ],
        'manyara' => [
            'title' => 'Lake Manyara National Park', 'map' => 'lake-manyara', 'area' => 'Lake Manyara', 'meals' => 'Full Board', 'park' => 'Lake Manyara',
            'photo' => 'manyara', 'caption' => 'Wildlife on the shore of Lake Manyara',
            'copy' => 'Drive to Lake Manyara National Park beneath the Rift Valley escarpment. Game drives lead through groundwater forest full of baboons and blue monkeys to the lakeshore, where flamingos, pelicans and hippos gather — and, with luck, you will find the park\'s famous tree-climbing lions.',
        ],
        'manyara-relax' => [
            'title' => 'A relaxing day above Lake Manyara', 'map' => 'lake-manyara', 'area' => 'Lake Manyara', 'meals' => 'Full Board',
            'copy' => 'Slow down after the game drives with a free day at your lodge overlooking Lake Manyara and the Rift Valley. Enjoy the view and the pool, book a massage, or take a sunset walk with a local guide.',
        ],
        'mto-wa-mbu' => [
            'title' => 'Mto wa Mbu: village life by bike', 'map' => 'mto-wa-mbu', 'area' => 'Mto wa Mbu', 'meals' => 'Full Board',
            'photo' => 'maasai', 'caption' => 'A Maasai welcome',
            'copy' => 'Explore the lively village of Mto wa Mbu, where many of Tanzania\'s peoples live side by side. Cycle or walk with a local guide through banana plantations and rice fields, meet woodcarvers and painters, and taste a home-cooked local lunch.',
        ],
        'natron' => [
            'title' => 'Lake Natron', 'map' => 'lake-natron', 'area' => 'Lake Natron', 'meals' => 'Full Board', 'park' => 'Lake Natron',
            'photo' => 'natron', 'caption' => 'Ol Doinyo Lengai from Lake Natron',
            'copy' => 'A scenic drive along the Rift Valley brings you to Lake Natron at the foot of Ol Doinyo Lengai, the Maasai "Mountain of God". In the late afternoon, walk with a Maasai guide to the lakeshore, where flamingos feed in the shallow soda water.',
            'more' => ['Walk up the river gorge to the Ngare Sero waterfalls for a swim in the cool pools. Very fit travellers can ask about a night climb of Ol Doinyo Lengai (optional, at extra cost).'],
        ],
        'serengeti' => [
            'title' => 'Serengeti National Park', 'map' => 'serengeti', 'area' => 'Central Serengeti', 'meals' => 'Full Board', 'park' => 'Serengeti',
            'photo' => 'serengeti', 'caption' => 'Lion in the Serengeti',
            'copy' => 'Climb through the green Ngorongoro highlands and drop down onto the endless plains of the Serengeti. Game drive on the way to your camp in the central Serengeti, where lions, cheetahs and leopards are seen all year round.',
            'more' => [
                'A full day of game drives in the Serengeti. Your guide follows the wildlife — the herds of the Great Migration when they are nearby, big cats on the rocky kopjes and hippos in the Seronera River — with a picnic lunch out on the plains.',
                'Another day on the plains, timed around the best light: an early start for hunting cats, a long lunch at camp, and a late-afternoon drive as the sun sets over the acacias.',
            ],
        ],
        'serengeti-north' => [
            'title' => 'Northern Serengeti & the Mara River', 'map' => 'serengeti-north', 'area' => 'Northern Serengeti', 'meals' => 'Full Board', 'park' => 'Serengeti',
            'photo' => 'serengeti', 'caption' => 'Northern Serengeti',
            'note' => 'River crossings depend on the season and the herds; they are most likely between July and October.',
            'copy' => 'Drive north through the Serengeti to the Mara River. From about July to October the Great Migration is here, and wildebeest and zebra gather in their thousands on the banks before crossing.',
            'more' => ['Spend the day by the Mara River. Crossings can\'t be predicted, so your guide watches the herds and waits patiently at a safe distance — the reward is one of the most dramatic sights in nature.'],
        ],
        'ngorongoro' => [
            'title' => 'Ngorongoro Crater', 'map' => 'ngorongoro', 'area' => 'Karatu', 'meals' => 'Full Board', 'park' => 'Ngorongoro Crater',
            'photo' => 'ngorongoro', 'caption' => 'The Ngorongoro Crater',
            'copy' => 'Descend early into the Ngorongoro Crater, a vast volcanic caldera with an extraordinary density of wildlife. Look for black rhino, lions, elephants, buffalo and hippos, and enjoy a picnic lunch beside a hippo pool before climbing back up to the rim.',
        ],
        'empakaai' => [
            'title' => 'Empakaai Crater hike', 'map' => 'custom', 'map_name' => 'Empakaai Crater', 'lat' => -2.9150, 'lng' => 35.8450,
            'area' => 'Ngorongoro highlands', 'meals' => 'Full Board',
            'photo' => 'walking', 'caption' => 'Walking with a ranger',
            'copy' => 'Drive deeper into the Ngorongoro highlands to the Empakaai Crater. With an armed ranger, hike down the forested crater wall to its soda lake, often pink with flamingos, with views across to Ol Doinyo Lengai.',
        ],
        'eyasi' => [
            'title' => 'Lake Eyasi and the Hadzabe', 'map' => 'custom', 'map_name' => 'Lake Eyasi', 'lat' => -3.6000, 'lng' => 35.0500,
            'area' => 'Karatu', 'meals' => 'Full Board',
            'copy' => 'Leave early for Lake Eyasi to spend the morning with the Hadzabe, one of Africa\'s last hunter-gatherer peoples, and visit the neighbouring Datoga blacksmiths before returning to Karatu.',
        ],
        'flight-zanzibar' => [
            'title' => 'Flight to Zanzibar', 'map' => 'stone-town', 'area' => 'Zanzibar', 'meals' => 'Bed and Breakfast', 'park' => 'Zanzibar',
            'copy' => 'Drive to Arusha Airport for your short flight to Zanzibar (included). You are met on arrival and transferred to your beach hotel — time to swap the safari vehicle for the Indian Ocean.',
        ],
        'zanzibar' => [
            'title' => 'Zanzibar', 'map' => 'nungwi', 'area' => 'Zanzibar', 'meals' => 'Bed and Breakfast', 'park' => 'Zanzibar',
            'photo' => 'zanzibar', 'caption' => 'Nungwi beach, Zanzibar',
            'copy' => 'A free day on Zanzibar\'s white-sand beaches. Swim in the warm, turquoise water, relax under the palms, or book an optional excursion with us.',
            'more' => [
                'Optional excursions we can arrange: a guided walk through historic Stone Town, a spice farm tour, snorkelling at Mnemba Atoll or a sunset cruise on a traditional dhow.',
                'Another lazy island day — or try something new, such as a kitesurfing lesson, a visit to the Jozani forest red colobus monkeys, or a seafood dinner on the beach.',
            ],
        ],
        'depart-kili' => [
            'title' => 'Departure from Kilimanjaro International Airport', 'map' => 'kilimanjaro-airport', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'After a last breakfast, your guide drives you to Kilimanjaro International Airport for your flight home — or ask us to extend your trip with Zanzibar or a Kilimanjaro climb.',
        ],
        'depart-zanzibar' => [
            'title' => 'Departure from Zanzibar', 'map' => 'stone-town', 'area' => null, 'meals' => 'Breakfast',
            'copy' => 'Enjoy a final morning by the sea, then transfer to Zanzibar\'s Abeid Amani Karume International Airport for your flight home.',
        ],
        'drive-arusha' => [
            'title' => 'Drive back to Arusha — end of safari', 'map' => 'arusha', 'area' => null, 'meals' => 'Breakfast and Lunch',
            'copy' => 'After breakfast, drive back to Arusha with a lunch stop on the way. Your guide drops you at your hotel or the airport in the afternoon.',
        ],
    ];

    /**
     * Route legs are [stop, days spent, days covered per entry]; the last value is only used
     * for "Day 6 – 7" style departure entries that include the overnight flight home.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function packages(): array
    {
        $north = TourCategory::NorthernCircuit;
        $combo = TourCategory::Safaris;

        return [
            [
                'name' => '4-Day Tarangire & Ngorongoro Safari', 'days' => 4, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Short Safari', 'style' => 'Private safari', 'banner' => 'ngorongoro',
                'summary' => 'A short, rewarding safari: Tarangire\'s elephants and baobabs and a full day in the Ngorongoro Crater.',
                'overview' => 'Perfect if you have only a few days — before or after a Kilimanjaro climb or a beach holiday. You see two of the north\'s highlights without long drives: the elephant herds of Tarangire and the wildlife-packed Ngorongoro Crater.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 1]],
            ],
            [
                'name' => '5-Day Small-Group Safari', 'days' => 5, 'category' => $north, 'tier' => SafariTier::Budget,
                'badge' => 'Small Group', 'style' => 'Small group (shared vehicle)', 'banner' => 'serengeti',
                'vehicle' => '4×4 Land Cruiser shared with a small group',
                'summary' => 'Tarangire, the Serengeti and the Ngorongoro Crater with a small group — great value without the crowds.',
                'overview' => 'Join a small group of fellow travellers to share the cost of the vehicle and guide while still seeing the very best of the north: Tarangire, two days in the Serengeti and the Ngorongoro Crater.',
                'route' => [['tarangire', 1, 1], ['serengeti', 2, 1], ['ngorongoro', 1, 1], ['drive-arusha', 1, 1]],
            ],
            [
                'name' => '5-Day Northern Parks Safari', 'days' => 5, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Short Safari', 'style' => 'Private safari', 'banner' => 'tarangire',
                'summary' => 'Tarangire, Lake Manyara and the Ngorongoro Crater in five easy days — ideal as an add-on to a climb or beach stay.',
                'overview' => 'Three very different parks with short drives between them: Tarangire\'s baobabs and elephants, the forest and flamingo-lined shores of Lake Manyara, and a day on the floor of the Ngorongoro Crater.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['manyara', 1, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 1]],
            ],
            [
                'name' => '7-Day Classic Tanzania Safari', 'days' => 7, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Family Friendly', 'style' => 'Private safari', 'banner' => 'serengeti',
                'summary' => 'Lake Manyara, two days in the Serengeti and the Ngorongoro Crater — the classic first safari.',
                'overview' => 'The classic introduction to Tanzania. After a night in Arusha you explore Lake Manyara, spend two days on the Serengeti plains — home of the Great Migration — and finish in the Ngorongoro Crater, one of the best places in Africa to see the Big Five.',
                'route' => [['arrival', 1, 1], ['manyara', 1, 1], ['serengeti', 2, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 2]],
            ],
            [
                'name' => '8-Day Serengeti Safari Holiday', 'days' => 8, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Great Migration', 'style' => 'Private safari', 'banner' => 'serengeti',
                'summary' => 'Three full days in the Serengeti between Tarangire and the Ngorongoro Crater — plenty of time to follow the herds.',
                'overview' => 'With three days in the Serengeti there is time to find the herds of the Great Migration wherever they are, and to wait for the big cats. Tarangire at the start and the Ngorongoro Crater at the end complete a very complete safari.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 2]],
            ],
            [
                'name' => '9-Day Big Five Safari', 'days' => 9, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Big Five', 'style' => 'Private safari', 'banner' => 'ngorongoro',
                'summary' => 'Tarangire, Lake Manyara, three days in the Serengeti and the Ngorongoro Crater — the Big Five at an unhurried pace.',
                'overview' => 'A relaxed safari through four of the north\'s best parks. Three days in the Serengeti give you the best chance of seeing the Great Migration and big cats, and the Ngorongoro Crater is one of the few places where you may see all of the Big Five in a day.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['manyara', 1, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 2]],
            ],
            [
                'name' => '9-Day Mara River Migration Safari', 'days' => 9, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'Great Migration', 'style' => 'Private safari', 'banner' => 'serengeti',
                'summary' => 'Two days by the Mara River in the northern Serengeti for a chance to see the famous wildebeest crossings.',
                'overview' => 'Timed for the Great Migration\'s river crossings (best from July to October). You drive through Tarangire and the central Serengeti to the Mara River in the far north, spend two days near the crossing points, then return south to finish in the Ngorongoro Crater.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['serengeti', 1, 1], ['serengeti-north', 2, 1], ['serengeti', 1, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 2]],
            ],
            [
                'name' => '10-Day All Northern Parks Safari', 'days' => 10, 'category' => $north, 'tier' => SafariTier::Classic,
                'badge' => 'All Northern Parks', 'style' => 'Private safari', 'banner' => 'serengeti',
                'summary' => 'Arusha National Park, Tarangire, Lake Manyara, three days in the Serengeti and the Ngorongoro Crater in one trip.',
                'overview' => 'See every park of the Northern Circuit in a single trip: walking and canoeing options in Arusha National Park, Tarangire\'s elephants, Lake Manyara\'s birdlife, three full days in the Serengeti and the Ngorongoro Crater.',
                'route' => [['arrival', 1, 1], ['arusha-np', 1, 1], ['tarangire', 1, 1], ['manyara', 1, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['depart-kili', 1, 2]],
            ],
            [
                'name' => '11-Day Safari & Zanzibar Beach', 'days' => 11, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Safari & Beach', 'style' => 'Private safari + beach', 'banner' => 'zanzibar',
                'summary' => 'Tarangire, the Serengeti and the Ngorongoro Crater, then a flight to Zanzibar for three days on the beach.',
                'overview' => 'The perfect bush-and-beach combination. Five days on safari through Tarangire, the Serengeti and the Ngorongoro Crater, followed by a short flight to Zanzibar to relax on the white sands of the Indian Ocean.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['serengeti', 2, 1], ['ngorongoro', 1, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 3, 1], ['depart-zanzibar', 1, 2]],
            ],
            [
                'name' => '12-Day Honeymoon Safari & Zanzibar', 'days' => 12, 'category' => $combo, 'tier' => SafariTier::Luxury,
                'badge' => 'Honeymoon', 'style' => 'Private honeymoon safari + beach', 'banner' => 'zanzibar',
                'summary' => 'A private safari for two, a lazy day above Lake Manyara and four days on Zanzibar\'s beaches.',
                'overview' => 'Made for couples: a private safari through Tarangire, the Serengeti and the Ngorongoro Crater with a relaxing day at a lodge above Lake Manyara, then four days of sunshine on Zanzibar. Tell us it\'s your honeymoon and we will add a few surprises.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['serengeti', 2, 1], ['ngorongoro', 1, 1], ['manyara-relax', 1, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 4, 1], ['depart-zanzibar', 1, 1]],
            ],
            [
                'name' => '12-Day Mara River Safari & Zanzibar', 'days' => 12, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Great Migration', 'style' => 'Private safari + beach', 'banner' => 'serengeti',
                'summary' => 'The Ngorongoro Crater, the Serengeti and two days by the Mara River, then a flight to Zanzibar.',
                'overview' => 'Follow the Great Migration north to the Mara River for a chance to see the dramatic crossings (best from July to October), then unwind with three days on Zanzibar\'s beaches.',
                'route' => [['arrival', 1, 1], ['tarangire', 1, 1], ['ngorongoro', 1, 1], ['serengeti', 1, 1], ['serengeti-north', 2, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 3, 1], ['depart-zanzibar', 1, 2]],
            ],
            [
                'name' => '13-Day Safari & Zanzibar Holiday', 'days' => 13, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Safari & Beach', 'style' => 'Private safari + beach', 'banner' => 'zanzibar',
                'summary' => 'Time to settle in around Arusha, a full safari through the northern parks and two days on Zanzibar.',
                'overview' => 'An easy-paced holiday: a relaxing start in Arusha with a day in Arusha National Park, then Tarangire, the Serengeti and the Ngorongoro Crater, finishing with two days on Zanzibar.',
                'route' => [['arrival', 1, 1], ['arusha-rest', 1, 1], ['arusha-np', 1, 1], ['arusha-active', 1, 1], ['tarangire', 1, 1], ['serengeti', 2, 1], ['ngorongoro', 1, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 2, 1], ['depart-zanzibar', 1, 2]],
            ],
            [
                'name' => '15-Day Tanzania Safari & Zanzibar', 'days' => 15, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Safari & Beach', 'style' => 'Private safari + beach', 'banner' => 'zanzibar',
                'summary' => 'A slow start in Arusha, three days in the Serengeti, the Ngorongoro Crater and three days on Zanzibar.',
                'overview' => 'Two weeks to see Tanzania properly: settle in around Arusha, explore Arusha National Park, then Tarangire, three days in the Serengeti and the Ngorongoro Crater before flying to Zanzibar for the beach.',
                'route' => [['arrival', 1, 1], ['arusha-rest', 1, 1], ['arusha-np', 1, 1], ['arusha-active', 1, 1], ['tarangire', 1, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 3, 1], ['depart-zanzibar', 1, 2]],
            ],
            [
                'name' => '16-Day Ultimate Northern Circuit Safari', 'days' => 16, 'category' => $north, 'tier' => SafariTier::MidRange,
                'badge' => 'Ultimate Safari', 'style' => 'Private safari', 'banner' => 'natron',
                'summary' => 'Waterfalls, five parks, Lake Natron, the Empakaai Crater and the Hadzabe — the most complete northern safari.',
                'overview' => 'Our longest northern adventure goes well beyond the classic route: a waterfall walk near Kilimanjaro, Arusha National Park, two days in Tarangire, Lake Manyara and Mto wa Mbu village, Lake Natron, three days in the Serengeti, the Ngorongoro and Empakaai craters and a morning with the Hadzabe at Lake Eyasi.',
                'route' => [['arrival', 1, 1], ['materuni', 1, 1], ['arusha-np', 1, 1], ['tarangire', 2, 1], ['manyara', 1, 1], ['mto-wa-mbu', 1, 1], ['natron', 2, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['empakaai', 1, 1], ['eyasi', 1, 1], ['depart-kili', 1, 1]],
            ],
            [
                'name' => '18-Day Grand Tanzania Safari & Zanzibar', 'days' => 18, 'category' => $combo, 'tier' => SafariTier::Classic,
                'badge' => 'Safari & Beach', 'style' => 'Private safari + beach', 'banner' => 'zanzibar',
                'summary' => 'An easy start in Arusha, a full northern safari and six days of island time on Zanzibar.',
                'overview' => 'The grand tour: a relaxed start around Arusha with Arusha National Park, a full safari through Tarangire, three days in the Serengeti and the Ngorongoro Crater, then six days on Zanzibar to explore Stone Town, the reefs and the beaches.',
                'route' => [['arrival', 1, 1], ['arusha-rest', 1, 1], ['arusha-np', 1, 1], ['arusha-active', 1, 1], ['tarangire', 1, 1], ['serengeti', 3, 1], ['ngorongoro', 1, 1], ['flight-zanzibar', 1, 1], ['zanzibar', 6, 1], ['depart-zanzibar', 1, 2]],
            ],
        ];
    }
}
