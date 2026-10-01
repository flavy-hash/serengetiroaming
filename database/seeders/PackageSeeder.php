<?php

namespace Database\Seeders;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\TourPage;
use Database\Seeders\Concerns\CopiesSeedImages;
use Illuminate\Database\Seeder;

/**
 * The Machame Route and Ngorongoro Crater Safari packages advertised on the homepage.
 * Only creates them if missing, so re-running never overwrites edits made in the admin.
 */
class PackageSeeder extends Seeder
{
    use CopiesSeedImages;

    public function run(): void
    {
        $this->machame();
        $this->ngorongoro();
    }

    private function machame(): void
    {
        $kili = $this->copyImage('kilimanjaro', 'kilimanjaro-amboseli.jpg');
        $mawenzi = $this->copyImage('kilimanjaro', 'mawenzi.jpg');

        $tents = [
            ['tier' => 'Mountain camp', 'label' => 'Shared mountain tent, sleeping mats provided'],
        ];

        TourPage::firstOrCreate(
            ['category' => TourCategory::Kilimanjaro, 'slug' => 'machame-route'],
            [
                'title' => 'Machame Route',
                'is_published' => true,
                'sort_order' => 1,
                'meta_description' => "Climb Kilimanjaro on the 7-day Machame Route — rainforest to glacier with a strong acclimatisation profile.",
                'banner_image' => $kili,
                'banner_badge' => 'Kilimanjaro Climb',
                'banner_text' => 'The classic "Whiskey Route" to Uhuru Peak, 5,895 m — the roof of Africa.',
                'location' => 'Kilimanjaro National Park, Tanzania',
                'package_name' => 'Machame Route',
                'duration' => '7 Days',
                'card_tagline' => 'The classic whiskey route',
                'card_highlight' => '~85% summit rate',
                'card_summary' => "Kilimanjaro's most popular route — rainforest to glacier with a strong acclimatisation profile.",
                'overview_heading' => 'Package Overview',
                'overview_body' => "Machame is Kilimanjaro's most popular route for good reason: it climbs through rainforest, heath and alpine desert to the glaciers of the summit, with the scenic Barranco Wall along the way.\n\nThe 7-day schedule follows the \"climb high, sleep low\" principle — an extra day on the mountain gives your body time to acclimatise and gives you a much better chance of standing on Uhuru Peak.",
                'price_from' => 1650,
                'price_note' => 'per person sharing, excluding international flights',
                'facts' => [
                    ['label' => 'Duration', 'value' => '7 Days · 6 Nights on the mountain'],
                    ['label' => 'Difficulty', 'value' => 'Moderate'],
                    ['label' => 'Summit', 'value' => 'Uhuru Peak, 5,895 m'],
                    ['label' => 'Best time', 'value' => 'Jan – Mar, Jun – Oct'],
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Machame Gate to Machame Camp', 'open' => true,
                        'hero_image' => $kili, 'hero_caption' => 'Mount Kilimanjaro',
                        'copy' => 'After a morning pick-up from Moshi or Arusha and registration at Machame Gate (1,800 m), the trail climbs steadily through lush montane rainforest to Machame Camp (3,000 m).',
                        'note' => 'Walking time is roughly 5–7 hours.',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Machame Camp, 3,000 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Machame Camp to Shira Camp',
                        'copy' => 'Leave the forest behind and climb a steep, rocky ridge through heath and moorland onto the Shira Plateau, with the first wide views of Kibo peak. Overnight at Shira Camp (3,850 m).',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Shira Camp, 3,850 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Shira Camp to Lava Tower and Barranco Camp',
                        'copy' => 'The key acclimatisation day: climb to the Lava Tower (4,600 m) for lunch, then descend to Barranco Camp (3,950 m) to sleep lower than you climbed.',
                        'activity_title' => 'Climb high, sleep low',
                        'activity_image' => $mawenzi,
                        'activity_copy' => 'Reaching 4,600 m and then dropping back down is what gives the Machame Route its strong acclimatisation profile. Your guides check your oxygen levels and pulse every morning and evening.',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Barranco Camp, 3,950 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 4', 'title' => 'Barranco Wall to Karanga Camp',
                        'copy' => 'Scramble up the famous Barranco Wall — hands-on but not technical — then follow a series of ridges and valleys to Karanga Camp (3,995 m). A shorter day to rest before the summit push.',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Karanga Camp, 3,995 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 5', 'title' => 'Karanga Camp to Barafu Camp',
                        'copy' => 'Climb through alpine desert to Barafu Camp (4,673 m), the base camp for the summit. An early dinner and a few hours of sleep before the midnight start.',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Barafu Camp, 4,673 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 6', 'title' => 'Summit Day: Uhuru Peak',
                        'hero_image' => $mawenzi, 'hero_caption' => 'Mawenzi from Kibo Hut',
                        'copy' => 'Set off around midnight for the long climb to Stella Point on the crater rim, then on to Uhuru Peak (5,895 m) for sunrise over Africa. After photos at the summit, descend to Mweka Camp (3,100 m).',
                        'note' => 'The longest day: around 12–15 hours of walking in total.',
                        'meal_plan' => 'Full board on the mountain',
                        'accommodation' => 'Mweka Camp, 3,100 m',
                        'options' => $tents,
                    ],
                    [
                        'day' => 'Day 7', 'title' => 'Mweka Camp to Mweka Gate',
                        'copy' => 'A final descent through the rainforest to Mweka Gate, where you collect your summit certificate before the transfer back to your hotel in Moshi or Arusha.',
                        'meal_plan' => 'Breakfast and lunch',
                    ],
                ],
                'included' => [
                    'Park fees, camping fees and rescue fees',
                    'Professional mountain guides, cook and porters',
                    'All meals and drinking water on the mountain',
                    'Mountain tents and sleeping mats',
                    'Transfers to and from the gates',
                ],
                'excluded' => [
                    'International flights and visa',
                    'Hotel nights before and after the climb',
                    'Sleeping bag and personal gear (available to rent)',
                    'Tips for the mountain crew',
                    'Travel insurance (must cover high-altitude trekking)',
                ],
                'experiences_heading' => null,
                'experiences' => [],
            ],
        );
    }

    private function ngorongoro(): void
    {
        $crater = $this->copyImage('safaris', 'ngorongoro.jpg');
        $lion = $this->copyImage('safaris', 'lion.jpg');

        TourPage::firstOrCreate(
            ['category' => TourCategory::Safaris, 'slug' => 'ngorongoro-crater-safari'],
            [
                'tier' => SafariTier::Classic,
                'title' => 'Ngorongoro Crater Safari',
                'is_published' => true,
                'sort_order' => 1,
                'meta_description' => '3-day classic safari to the Ngorongoro Crater, Tarangire and Lake Manyara from Arusha.',
                'banner_image' => $crater,
                'banner_text' => 'Game drives on the crater floor — one of the densest concentrations of wildlife in Africa.',
                'location' => 'Ngorongoro Conservation Area, Tanzania',
                'package_name' => 'Ngorongoro Crater Safari',
                'duration' => '3 Days',
                'card_tagline' => 'Wildlife in a caldera',
                'card_highlight' => 'Free cancellation 48h',
                'card_summary' => 'Game drives on the crater floor — one of the densest concentrations of wildlife in Africa.',
                'overview_heading' => 'Package Overview',
                'overview_body' => "Three days on the northern circuit from Arusha: Tarangire's elephant herds and baobabs, a full day on the floor of the Ngorongoro Crater, and the forest and lake shore of Lake Manyara.\n\nThe crater is a collapsed volcano around 600 m deep, and its grasslands, swamps and lake hold lions, buffalo, elephants, hippos and some of the last black rhinos in Tanzania.",
                'price_from' => 980,
                'price_note' => 'per person sharing, excluding international flights',
                'facts' => [
                    ['label' => 'Duration', 'value' => '3 Days · 2 Nights'],
                    ['label' => 'Difficulty', 'value' => 'Easy'],
                    ['label' => 'Starts / ends', 'value' => 'Arusha'],
                    ['label' => 'Best time', 'value' => 'All year'],
                ],
                'itinerary' => [
                    [
                        'day' => 'Day 1', 'title' => 'Arusha to Tarangire National Park', 'open' => true,
                        'hero_image' => $lion, 'hero_caption' => 'Lion on the northern circuit',
                        'copy' => 'Morning pick-up in Arusha and a drive of about two hours to Tarangire National Park for an afternoon game drive among baobab trees, elephant herds and the Tarangire River. Overnight near Karatu.',
                        'meal_plan' => 'Full board',
                        'accommodation' => 'Karatu',
                        'options' => [
                            ['tier' => 'Classic', 'label' => 'Lodge or tented camp near Karatu'],
                        ],
                    ],
                    [
                        'day' => 'Day 2', 'title' => 'Ngorongoro Crater',
                        'hero_image' => $crater, 'hero_caption' => 'Ngorongoro Crater',
                        'copy' => 'An early start to descend 600 m to the crater floor for a full day of game viewing, with a picnic lunch beside the hippo pool before climbing back out to the rim in the late afternoon.',
                        'activity_title' => 'Crater floor game drive',
                        'activity_image' => $crater,
                        'activity_copy' => 'The crater floor is home to lions, buffalo, elephants, zebra, wildebeest, hyenas and flamingos on Lake Magadi — with a chance of seeing the rare black rhino.',
                        'meal_plan' => 'Full board',
                        'accommodation' => 'Karatu',
                        'options' => [
                            ['tier' => 'Classic', 'label' => 'Lodge or tented camp near Karatu'],
                        ],
                    ],
                    [
                        'day' => 'Day 3', 'title' => 'Lake Manyara to Arusha',
                        'copy' => 'A morning game drive in Lake Manyara National Park — groundwater forest, baboon troops and the lake shore — before the drive back to Arusha in the afternoon.',
                        'meal_plan' => 'Breakfast and lunch',
                    ],
                ],
                'included' => [
                    'Private 4x4 safari vehicle with pop-up roof',
                    'Professional English-speaking driver-guide',
                    'Park and crater fees',
                    'Accommodation as per itinerary',
                    'Meals as per itinerary and drinking water',
                ],
                'excluded' => [
                    'International flights and visa',
                    'Drinks other than water',
                    'Tips for your guide',
                    'Travel insurance',
                ],
                'experiences_heading' => null,
                'experiences' => [],
            ],
        );
    }
}
