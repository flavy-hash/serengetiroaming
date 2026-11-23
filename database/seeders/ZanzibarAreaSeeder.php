<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * The beaches, towns and islands listed on /explore-zanzibar, each with its own page.
 * Safe to re-run: adds only the ones whose slug isn't in the table yet and never changes one you
 * have edited (apart from giving a photo-less one its photo).
 *   php artisan db:seed --class=ZanzibarAreaSeeder --force
 *
 * A few have a starter photo; add the rest in the admin under Website Pages → Destinations.
 */
class ZanzibarAreaSeeder extends Seeder
{
    public function run(): void
    {
        $order = (int) Destination::max('sort_order');
        $added = 0;

        foreach (self::areas() as $area) {
            $image = isset($area['photo']) ? $this->copyPhoto($area['photo']) : null;
            unset($area['photo']);

            $existing = Destination::where('slug', $area['slug'])->first();
            if ($existing) {
                if (blank($existing->image) && $image) {
                    $existing->update(['image' => $image]);
                }

                continue;
            }

            Destination::create([...$area, 'region' => Destination::ZANZIBAR_REGION, 'image' => $image, 'is_published' => true, 'sort_order' => ++$order]);
            $added++;
        }

        $this->command?->info("Zanzibar areas: {$added} added.");
    }

    private function copyPhoto(string $source): string
    {
        $path = 'destinations/'.basename($source);
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/images/{$source}")));
        }

        return $path;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function areas(): array
    {
        return [
            [
                'name' => 'Stone Town', 'slug' => 'stone-town', 'photo' => 'zanzibar/stone-town.jpg', 'places' => ['stone-town'],
                'tagline' => 'History, culture and local life in a UNESCO World Heritage Site.',
                'activities' => 'Guided walking tour, the Old Fort, Darajani Market, Forodhani Gardens, the Palace Museum and heritage sites, street food, photography and evening strolls.',
                'description' => "Stone Town is the old heart of Zanzibar. Its narrow lanes, carved wooden doors, bustling markets and coral-stone houses reflect centuries of Swahili, Arab, Indian and European influence.\n\nA guided walk brings the stories behind the streets to life, and in the evening the waterfront gardens fill with food stalls and music.",
                'best_time' => 'All year; June to October is driest',
            ],
            [
                'name' => 'Nungwi', 'slug' => 'nungwi', 'photo' => 'zanzibar/nungwi.jpg', 'places' => ['nungwi'],
                'tagline' => 'Beach life, sunsets and ocean adventures on the northern tip.',
                'activities' => 'Swimming, snorkelling, scuba diving, traditional dhow and sunset cruises, fishing-village visits, dhow-building demonstrations and beach relaxation.',
                'description' => "Nungwi, at the northern tip of the island, has a lively beach, calm turquoise water at almost any tide and some of Zanzibar's best sunsets.\n\nIt is also home to a long dhow-building tradition, so a visit to the boat yards and fishing village is a good way to meet local life.",
                'best_time' => 'June to October and December to February',
            ],
            [
                'name' => 'Kendwa', 'slug' => 'kendwa', 'places' => ['kendwa'],
                'tagline' => 'White sand, calm swimming and golden sunsets.',
                'activities' => 'Swimming, snorkelling, sunset dhow cruises, beach walks, diving, water sports, beachfront dining and pure beach relaxation.',
                'description' => "Kendwa is known for its wide white beach and water that stays swimmable all day, whatever the tide. It is a little quieter than neighbouring Nungwi, with relaxed beach restaurants and a famous sunset.",
                'best_time' => 'June to October and December to February',
            ],
            [
                'name' => 'Paje', 'slug' => 'paje', 'places' => ['paje'],
                'tagline' => 'Kitesurfing, ocean views and east-coast life.',
                'activities' => 'Kitesurfing lessons, snorkelling, swimming, beach walks, seaweed-farming visits, cycling, village visits and sunset relaxation.',
                'description' => "Paje, on the east coast, is Zanzibar's kitesurfing capital. Steady winds, a shallow lagoon and a laid-back village atmosphere make it popular with active travellers and anyone who likes an easy-going beach.\n\nAt low tide the lagoon empties and local women tend their seaweed farms on the sand flats.",
                'best_time' => 'June to October (windy) and December to March',
            ],
            [
                'name' => 'Jambiani', 'slug' => 'jambiani', 'places' => ['jambiani'],
                'tagline' => 'Village life and a slower pace beside the lagoon.',
                'activities' => 'Beach walks, swimming, seaweed-farming visits, village visits, cycling, snorkelling, local cooking experiences and quiet time by the ocean.',
                'description' => "Jambiani is a long, quiet stretch of the east coast where traditional village life carries on beside the sea. It suits travellers who want slow days, friendly locals and a more authentic side of Zanzibar.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Matemwe', 'slug' => 'matemwe',
                'tagline' => 'Mnemba views, snorkelling and quiet beaches.',
                'activities' => 'Snorkelling and diving at Mnemba, dhow excursions, swimming, beach walks, fishing experiences and coastal village visits.',
                'description' => "Matemwe sits on the north-east coast facing Mnemba Atoll. The beach is peaceful, the village is small and the reef just offshore is among the best places on the island to snorkel or dive.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Kizimkazi', 'slug' => 'kizimkazi',
                'tagline' => 'Dolphins and the quiet south of the island.',
                'activities' => 'Dolphin excursions, boat trips, snorkelling, swimming, fishing-community visits and coastal sightseeing.',
                'description' => "Kizimkazi is a fishing community on the south coast and the starting point for dolphin trips. Early morning boats head out to look for bottlenose and humpback dolphins in the open water.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Jozani Forest', 'slug' => 'jozani-forest',
                'tagline' => 'Zanzibar’s nature and the rare red colobus monkey.',
                'activities' => 'Guided forest walks, red colobus monkey viewing, mangrove boardwalk, birdwatching, nature photography and learning about the island’s native ecosystem.',
                'description' => "Jozani–Chwaka Bay National Park is Zanzibar's only national park and the home of the red colobus monkey, found nowhere else on earth. A guide leads you along shaded forest paths and over a mangrove boardwalk.",
                'best_time' => 'All year; the dry months are easiest for walking',
            ],
            [
                'name' => 'Spice Farms', 'slug' => 'spice-farms', 'photo' => 'zanzibar/spice-tour.jpg',
                'tagline' => 'The flavours that earned Zanzibar its name as the Spice Island.',
                'activities' => 'Guided spice tour, tasting fresh spices and tropical fruit, learning their traditional uses, farm walks, a local lunch and cultural exchange.',
                'description' => "A spice tour takes you through working farms where cloves, cinnamon, vanilla, nutmeg and pepper grow. Guides let you smell, taste and touch each plant, and the tour usually ends with fresh fruit or a Swahili lunch.",
                'best_time' => 'All year',
            ],
            [
                'name' => 'Prison Island', 'slug' => 'prison-island',
                'tagline' => 'History, giant tortoises and clear water just off Stone Town.',
                'activities' => 'Boat excursion, giant tortoise viewing, island walk, swimming, snorkelling, photography and historical sightseeing.',
                'description' => "A short boat ride from Stone Town, Changuu (Prison Island) is home to a colony of giant Aldabra tortoises. The old buildings tell a layered story, and the beaches around the island are good for a swim or snorkel.",
                'best_time' => 'All year',
            ],
            [
                'name' => 'Mnemba Atoll', 'slug' => 'mnemba-atoll', 'places' => ['mnemba'],
                'tagline' => 'Snorkelling and marine life in a protected reef.',
                'activities' => 'Snorkelling, scuba diving, boat trips, swimming and marine-life viewing.',
                'description' => "Mnemba Atoll is a protected marine area off the north-east coast. Its clear water and healthy coral are home to colourful reef fish, turtles and, with luck, dolphins.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Nakupenda Sandbank', 'slug' => 'nakupenda-sandbank',
                'tagline' => 'A day on the Indian Ocean.',
                'activities' => 'Sandbank visit, swimming, snorkelling, sunbathing, seafood lunch, photography and boat excursions.',
                'description' => "Nakupenda means “I love you” in Swahili. At low tide a bright white sandbank rises from the sea off Stone Town, and day trips spend a few hours there swimming, snorkelling and eating grilled seafood.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Safari Blue', 'slug' => 'safari-blue', 'photo' => 'zanzibar/dhow.jpg',
                'tagline' => 'Sailing, sandbanks and seafood on a traditional dhow.',
                'activities' => 'Traditional dhow sailing, snorkelling, sandbank visits, swimming, seafood lunch, coastal exploration and, on some trips, sunset sailing.',
                'description' => "A full-day dhow trip in the southern lagoons: sail, snorkel, walk on a sandbank, eat a seafood lunch and relax in the shade. It is one of the most popular days out on the island.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Sunset Dhow Cruise', 'slug' => 'sunset-dhow-cruise', 'photo' => 'zanzibar/dhow.jpg',
                'tagline' => 'Zanzibar at golden hour, under a traditional sail.',
                'activities' => 'Sunset sailing on a traditional dhow, swimming, drinks and snacks on board, live music on some cruises, and photography as the sky changes colour.',
                'description' => "As the heat fades, a wooden dhow glides along the coast under its triangular sail while the sky turns orange and pink. It is a relaxed, romantic way to end a beach day, and most cruises leave from Nungwi, Kendwa or Stone Town.",
                'best_time' => 'All year; clearest skies June to October',
            ],
            [
                'name' => 'Diving & Snorkeling', 'slug' => 'diving-snorkeling', 'places' => ['mnemba'],
                'tagline' => 'Discover Zanzibar underwater.',
                'activities' => 'Snorkelling trips, introductory and certified scuba dives, reef and wall dives, turtle and dolphin sightings, and underwater photography.',
                'description' => "Warm, clear water and healthy reefs make Zanzibar a good place to learn to dive or simply to snorkel. Popular sites include Mnemba Atoll, the reefs off Nungwi and Kendwa and the small islands around Stone Town, with trips for beginners and experienced divers.",
                'best_time' => 'June to October and December to March',
            ],
            [
                'name' => 'Forodhani Gardens', 'slug' => 'forodhani-gardens', 'places' => ['stone-town'],
                'tagline' => 'Sunset, street food and the Stone Town waterfront.',
                'activities' => 'Evening walks along the sea wall, the Forodhani night food market, seafood and Zanzibar pizza, sunset photography and watching the dhows come in.',
                'description' => "The waterfront gardens in Stone Town are the island's favourite evening meeting place. As the sun sets, stalls open along the sea wall serving grilled seafood, sugar-cane juice and Zanzibar pizza, and families and visitors gather to watch the harbour.",
                'best_time' => 'All year; evenings',
            ],
        ];
    }
}
