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
                    ['label' => 'Machame · 7 Days', 'href' => '/kilimanjaro#machame'],
                    ['label' => 'Lemosho · 8 Days', 'href' => '/kilimanjaro#lemosho'],
                    ['label' => 'Marangu · 6 Days', 'href' => '/kilimanjaro#marangu'],
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
                'label' => 'About',
                'href' => '/about',
                'title' => 'We are Serengeti Roaming',
                'description' => 'A Tanzanian-owned operator — the guides, planners and drivers who put every itinerary together.',
                'cta_label' => 'Meet Our Team',
                'image' => $image('about.jpg'),
                'links' => [
                    ['label' => 'About Us', 'href' => '/about'],
                    ['label' => 'Our Team', 'href' => '/about#team'],
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
