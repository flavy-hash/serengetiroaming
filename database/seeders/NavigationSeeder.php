<?php

namespace Database\Seeders;

use App\Models\NavItem;
use Database\Seeders\Concerns\CopiesSeedImages;
use Illuminate\Database\Seeder;

/**
 * Loads the original hard-coded navbar into the admin-managed navigation.
 * Only runs when there are no items yet, so it never overwrites admin edits.
 */
class NavigationSeeder extends Seeder
{
    use CopiesSeedImages;

    public function run(): void
    {
        if (NavItem::exists()) {
            return;
        }

        $image = fn (string $file) => $this->copyImage('navigation', $file, prefix: 'menus');

        $items = [
            [
                'label' => 'Safaris',
                'href' => '/safaris',
                'title' => 'Tanzania Safari Adventures',
                'description' => 'Serengeti, Ngorongoro, Tarangire and Lake Manyara with Tanzanian guides who build each route around where the animals actually are this week.',
                'cta_label' => 'Explore Safaris',
                'image' => $image('safaris.jpg'),
                'links' => [
                    ['label' => 'View All Safaris', 'href' => '/safaris'],
                    ['label' => 'Budget', 'href' => '/safaris#budget'],
                    ['label' => 'Classic', 'href' => '/safaris#classic'],
                    ['label' => 'Mid-range', 'href' => '/safaris#mid-range'],
                    ['label' => 'Luxury', 'href' => '/safaris#luxury'],
                ],
            ],
            [
                'label' => 'Northern Circuit',
                'href' => '/northern-circuit',
                'title' => 'Tanzania Northern Circuit',
                'description' => 'The classic safari route — the Serengeti plains and the Great Migration, the Ngorongoro Crater, Tarangire\'s elephants and Lake Manyara.',
                'cta_label' => 'Explore the North',
                'image' => $image('northern-circuit.jpg'),
                'links' => [
                    ['label' => 'View All Northern', 'href' => '/northern-circuit'],
                    ['label' => 'Serengeti National Park', 'href' => '/northern-circuit#serengeti'],
                    ['label' => 'Ngorongoro Crater', 'href' => '/northern-circuit#ngorongoro'],
                    ['label' => 'Tarangire National Park', 'href' => '/northern-circuit#tarangire'],
                    ['label' => 'Lake Manyara', 'href' => '/northern-circuit#lake-manyara'],
                ],
            ],
            [
                'label' => 'Southern Circuit',
                'href' => '/southern-circuit',
                'title' => 'Tanzania Southern Circuit',
                'description' => 'The uncrowded south — Ruaha, Nyerere (Selous) and Mikumi. Big herds, big cats and boat safaris, well away from the busier northern parks.',
                'cta_label' => 'Explore the South',
                'image' => $image('southern-circuit.jpg'),
                'links' => [
                    ['label' => 'View All Southern', 'href' => '/southern-circuit'],
                    ['label' => 'Ruaha National Park', 'href' => '/southern-circuit#ruaha'],
                    ['label' => 'Nyerere · Selous', 'href' => '/southern-circuit#nyerere-selous'],
                    ['label' => 'Mikumi National Park', 'href' => '/southern-circuit#mikumi'],
                    ['label' => 'Udzungwa Mountains', 'href' => '/southern-circuit#udzungwa'],
                ],
            ],
            [
                'label' => 'Kilimanjaro',
                'href' => '/kilimanjaro',
                'title' => 'Climbing the Roof of Africa',
                'description' => 'Rainforest to glacier at 5,895 m — guided climbs with proper gear, honest acclimatisation schedules and crews who are paid properly.',
                'cta_label' => 'Climb Kilimanjaro',
                'image' => $image('kilimanjaro.jpg'),
                'links' => [
                    ['label' => 'Overview', 'href' => '/kilimanjaro'],
                    ['label' => 'Machame · 6 Days', 'href' => '/kilimanjaro'],
                    ['label' => 'Lemosho · 8 Days', 'href' => '/kilimanjaro/lemosho-route'],
                    ['label' => 'Marangu · 5 Days', 'href' => '/kilimanjaro/marangu-route'],
                    ['label' => 'Day Hikes on the Slopes', 'href' => '/kilimanjaro#day-hikes'],
                ],
            ],
            [
                'label' => 'Zanzibar',
                'href' => '/zanzibar',
                'title' => 'Zanzibar Island Paradise',
                'description' => 'Finish the safari barefoot — dhow cruises, Stone Town alleyways, spice farms and the turquoise water off Mnemba Atoll.',
                'cta_label' => 'Explore Zanzibar',
                'image' => $image('zanzibar.jpg'),
                'links' => [
                    ['label' => 'Beach Holidays', 'href' => '/zanzibar'],
                    ['label' => 'Stone Town Tours', 'href' => '/zanzibar#stone-town'],
                    ['label' => 'Spice Tours', 'href' => '/zanzibar#spice-tours'],
                    ['label' => 'Boat Trips & Snorkeling', 'href' => '/zanzibar#boat-trips'],
                    ['label' => 'Explore Zanzibar', 'href' => '/explore-zanzibar'],
                ],
            ],
            [
                'label' => 'Day Trips',
                'href' => '/day-trips',
                'title' => 'Tanzania Day Trips',
                'description' => 'Short on time? Arusha National Park, Materuni waterfalls and coffee, or a cultural village visit — a real Tanzania experience in a single day.',
                'cta_label' => 'Explore Day Trips',
                'image' => $image('day-trips.jpg'),
                'links' => [
                    ['label' => 'View All Day Trips', 'href' => '/day-trips'],
                    ['label' => 'Arusha National Park', 'href' => '/day-trips#arusha-national-park'],
                    ['label' => 'Materuni Waterfalls & Coffee', 'href' => '/day-trips#materuni'],
                    ['label' => 'Lake Manyara Day Trip', 'href' => '/day-trips#lake-manyara'],
                    ['label' => 'Cultural Village Tour', 'href' => '/day-trips#cultural-village'],
                ],
            ],
            [
                'label' => 'Destinations',
                'href' => '/destinations',
                'title' => 'Tanzania Safari Destinations',
                'description' => 'The parks, mountains and islands we visit — and the lodges and camps where you will stay.',
                'cta_label' => 'See All Destinations',
                'image' => $image('destinations.jpg'),
                'links' => [
                    ['label' => 'View All Destinations', 'href' => '/destinations'],
                    ['label' => 'Serengeti National Park', 'href' => '/destinations/serengeti'],
                    ['label' => 'Ngorongoro Crater', 'href' => '/destinations/ngorongoro'],
                    ['label' => 'Tarangire National Park', 'href' => '/destinations/tarangire'],
                    ['label' => 'Zanzibar Island', 'href' => '/destinations/zanzibar'],
                    ['label' => 'Accommodation', 'href' => '/accommodations'],
                ],
            ],
            [
                'label' => 'About',
                'href' => '/about',
                'title' => 'We are Serengeti Roaming',
                'description' => 'A Tanzanian-owned operator — the guides, planners and drivers who put every itinerary together.',
                'cta_label' => 'Meet Our Team',
                'image' => $image('about.jpg'),
                'links' => [
                    ['label' => 'About Us', 'href' => '/about'],
                    ['label' => 'Our Team', 'href' => '/our-team'],
                    [
                        'label' => 'Our Vehicles',
                        'href' => '/vehicles',
                        'preview_title' => 'Our Safari Vehicles',
                        'preview_text' => 'Private 4×4 Land Cruisers with pop-up roofs, driven by our own guides.',
                        'preview_cta' => 'See our vehicles',
                    ],
                    ['label' => 'Reviews', 'href' => '/reviews'],
                    ['label' => 'FAQ', 'href' => '/faq'],
                ],
            ],
            [
                'label' => 'Contact',
                'href' => '/contact',
                'links' => [],
            ],
        ];

        foreach ($items as $index => $item) {
            NavItem::create([...$item, 'sort_order' => $index + 1]);
        }
    }
}
