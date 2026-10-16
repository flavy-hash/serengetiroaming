<?php

namespace Database\Seeders;

use App\Enums\AccommodationLevel;
use App\Models\Accommodation;
use Illuminate\Database\Seeder;

/**
 * Starter stays: the lodges used in the company's Northern Circuit itineraries.
 * Only runs on an empty table, so it never overwrites admin edits. Safe on production:
 *   php artisan db:seed --class=AccommodationSeeder --force
 *
 * No photos are included (we have no licensed photos of these lodges) — upload them in
 * the admin under Website Pages → Accommodation.
 */
class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        if (Accommodation::exists()) {
            $this->command?->info('Accommodation already has entries — nothing seeded.');

            return;
        }

        $stays = [
            ['Arusha Serena Hotel Resort & Spa', 'Arusha', 'Hotel', 'A peaceful retreat on the slopes of Mount Meru, surrounded by gardens and forest, with comfortable rooms and a tranquil setting overlooking nearby Lake Duluti.'],
            ['Tarangire Elephant Springs', 'Tarangire', 'Lodge', 'A comfortable safari retreat overlooking the wildlife-rich landscapes of Tarangire, with stylish accommodation and beautiful views across the surrounding wilderness.'],
            ['Serengeti Serena Safari Lodge', 'Serengeti', 'Lodge', 'A comfortable safari retreat in the heart of the Serengeti, with sweeping views across the wildlife-rich plains of Tanzania\'s most famous national park.'],
            ['Mara Mara Tented Lodge', 'Serengeti', 'Tented camp', 'Stylish tented accommodation in the Northern Serengeti, with excellent access to the plains and the Mara River crossings.'],
            ["Ngorongoro Lion's Paw Camp", 'Ngorongoro', 'Tented camp', 'A comfortable safari retreat on the edge of the Ngorongoro Crater, with spectacular views from one of Tanzania\'s most remarkable locations.'],
        ];

        foreach ($stays as $i => [$name, $location, $type, $description]) {
            Accommodation::create([
                'name' => $name,
                'location' => $location,
                'type' => $type,
                'level' => AccommodationLevel::Luxury,
                'description' => $description,
                'is_published' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
