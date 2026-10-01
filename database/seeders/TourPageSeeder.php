<?php

namespace Database\Seeders;

use App\Enums\TourCategory;
use App\Models\TourPage;
use Database\Seeders\Concerns\CopiesSeedImages;
use Illuminate\Database\Seeder;

/**
 * Loads the original hand-built Zanzibar page into the admin-managed tour pages.
 * Safe to re-run: it updates the page in place.
 */
class TourPageSeeder extends Seeder
{
    use CopiesSeedImages;

    public function run(): void
    {
        $img = fn (string $file) => $this->copyImage('zanzibar', $file);

        $stoneTown = $img('stone-town.jpg');
        $nungwi = $img('nungwi.jpg');
        $spice = $img('spice-tour.jpg');
        $dhow = $img('dhow.jpg');

        TourPage::updateOrCreate(
            ['category' => TourCategory::Zanzibar, 'slug' => 'zanzibar-beach-escape'],
            [
                'title' => 'Zanzibar',
                'is_published' => true,
                'sort_order' => 1,
                'meta_description' => 'Beach holidays, Stone Town tours, spice tours and boat trips & snorkeling in Zanzibar, Tanzania.',
                'banner_image' => $img('banner-nungwi.jpg'),
                'banner_badge' => 'Beach Holiday',
                'banner_text' => 'Beach holidays, Stone Town tours, spice tours and boat trips & snorkeling.',
                'location' => 'Zanzibar Archipelago, Tanzania',
                'package_name' => 'Zanzibar Beach Escape',
                'duration' => '4 Days',
                'card_tagline' => 'Stone Town & shores',
                'card_highlight' => 'Free cancellation 48h',
                'card_summary' => "Two days exploring Stone Town's history and spice markets, then two full days on Nungwi's beaches.",
                'overview_heading' => 'Package Overview',
                'overview_body' => "This 4-day escape pairs Zanzibar's UNESCO-listed Stone Town with two full days relaxing on Nungwi's white-sand beaches, plus a guided spice farm tour along the way — the perfect finish to a Tanzania safari, or a standalone island holiday.",
                'price_from' => 650,
                'price_note' => 'per person sharing, excluding international flights',
                'facts' => [
                    ['label' => 'Duration', 'value' => '4 Days · 3 Nights'],
                    ['label' => 'Group size', 'value' => 'Max 8'],
                    ['label' => 'Difficulty', 'value' => 'Easy'],
                    ['label' => 'Best time', 'value' => 'June – October'],
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Arrival', 'open' => true,
                        'hero_image' => $stoneTown, 'hero_caption' => 'Stone Town',
                        'copy' => "You land at Zanzibar Airport (ZNZ) and are met by a Serengeti Roaming representative for the transfer to your hotel in Stone Town. The rest of the day is free to explore the old town on foot — the Sultan's Palace, the old fort and the winding alleys of the UNESCO World Heritage centre.",
                        'note' => 'Check-in starts at 2:00 PM. The hotel rate includes breakfast only.',
                        'meal_plan' => 'Bed and Breakfast',
                        'accommodation' => 'Stone Town',
                        'options' => [
                            ['tier' => 'Comfort', 'label' => '3-star hotel, Stone Town'],
                            ['tier' => 'Premium', 'label' => 'Boutique heritage hotel, Stone Town'],
                        ],
                        'image' => $stoneTown, 'image_caption' => 'Stone Town',
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Stone Town & Spice Tour', 'open' => false,
                        'hero_image' => $stoneTown, 'hero_caption' => 'Stone Town',
                        'copy' => 'After breakfast, transfer out to one of the working spice farms just outside Stone Town before heading north to your beach hotel in Nungwi by early evening.',
                        'activity_title' => 'Spice Farm Tour',
                        'activity_image' => $spice,
                        'activity_copy' => 'Walk through a working spice farm and taste cloves, cinnamon, vanilla and chili straight from the plant — the trade that gave Zanzibar its name. Includes a tasting session and a short cultural introduction from your guide.',
                        'meal_plan' => 'Bed and Breakfast',
                        'accommodation' => 'Nungwi',
                        'options' => [
                            ['tier' => 'Comfort', 'label' => 'Beachfront guesthouse, Nungwi'],
                            ['tier' => 'Premium', 'label' => '4-star beach resort, Nungwi'],
                        ],
                        'image' => $nungwi, 'image_caption' => 'Nungwi Beachfront',
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Nungwi Beach', 'open' => false,
                        'hero_image' => $nungwi, 'hero_caption' => 'Nungwi Beachfront',
                        'copy' => 'A free day on the beach — swim, relax, or join the optional excursion below.',
                        'activity_title' => 'Dhow Sailing & Snorkeling (Optional)',
                        'activity_image' => $dhow,
                        'activity_copy' => 'A traditional dhow sailing trip out to Mnemba Atoll and the sandbanks off Nungwi, with snorkeling over the reef and a seafood lunch on board. Booked locally and paid separately — ask your guide to arrange it.',
                        'meal_plan' => 'Bed and Breakfast',
                        'accommodation' => 'Nungwi',
                        'options' => [
                            ['tier' => 'Comfort', 'label' => 'Beachfront guesthouse, Nungwi'],
                            ['tier' => 'Premium', 'label' => '4-star beach resort, Nungwi'],
                        ],
                        'image' => $nungwi, 'image_caption' => 'Nungwi Beachfront',
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Beach & Departure', 'open' => false,
                        'hero_image' => $nungwi, 'hero_caption' => 'Nungwi Beachfront',
                        'copy' => 'A final morning at leisure on the beach before your transfer back to the airport for your onward flight.',
                        'meal_plan' => 'Breakfast only',
                        'options' => [],
                    ],
                ],
                'included' => [
                    'Airport transfers in Zanzibar',
                    'Accommodation as per itinerary',
                    'Stone Town guided tour',
                    'Spice farm tour with tasting',
                    'Breakfast daily',
                ],
                'excluded' => [
                    'International & domestic flights',
                    'Zanzibar visa / entry fees',
                    'Lunch and dinner (unless noted)',
                    'Optional dhow & snorkeling trip',
                    'Travel insurance',
                ],
                'experiences_heading' => 'Explore Zanzibar',
                'experiences' => [
                    [
                        'anchor' => 'beach-holidays',
                        'title' => 'Beach Holidays',
                        'image' => $nungwi,
                        'copy' => 'White sand and turquoise water along the north coast — Nungwi and Kendwa for sunsets, Paje and Jambiani for kitesurfing.',
                        'price' => 'From $650',
                    ],
                    [
                        'anchor' => 'stone-town',
                        'title' => 'Stone Town Tours',
                        'image' => $stoneTown,
                        'copy' => "A guided walk through Zanzibar's old trading capital — the Sultan's Palace, the old fort, and the winding alleys of the UNESCO World Heritage centre.",
                        'price' => 'From $45',
                    ],
                    [
                        'anchor' => 'spice-tours',
                        'title' => 'Spice Tours',
                        'image' => $spice,
                        'copy' => 'Walk through working spice farms and taste cloves, cinnamon, vanilla and pepper straight from the plant — the trade that gave Zanzibar its name.',
                        'price' => 'From $35',
                    ],
                    [
                        'anchor' => 'boat-trips',
                        'title' => 'Boat Trips & Snorkeling',
                        'image' => $dhow,
                        'copy' => 'Traditional dhow sailing trips to Mnemba Atoll and the sandbanks off Nungwi, with snorkeling over the reef and a seafood lunch on board.',
                        'price' => 'From $60',
                    ],
                ],
            ],
        );
    }
}
