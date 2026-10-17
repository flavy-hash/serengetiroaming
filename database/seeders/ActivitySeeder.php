<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter "Top activities" for the homepage, each with a photo. Safe on production and safe to re-run:
 *   php artisan db:seed --class=ActivitySeeder --force
 *
 * - Adds only activities whose title isn't in the table yet (deleted ones come back if you run
 *   it again, so run it once).
 * - Never changes an activity you have edited, except to give a photo-less one its photo.
 *
 * Photos are openly licensed (Wikimedia Commons), credited on /photo-credits.
 * Edit everything in the admin under Website Pages → Activities.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $added = 0;

        foreach (self::activities() as $i => $activity) {
            $image = $this->copyPhoto($activity['photo']);
            unset($activity['photo']);

            $existing = Activity::where('title', $activity['title'])->first();
            if ($existing) {
                if (blank($existing->image)) {
                    $existing->update(['image' => $image]);
                }

                continue;
            }

            Activity::create([...$activity, 'image' => $image, 'is_published' => true, 'sort_order' => $i + 1]);
            $added++;
        }

        $this->command?->info("Activities: {$added} added.");
    }

    private function copyPhoto(string $file): string
    {
        $path = "activities/{$file}";
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/images/activities/{$file}")));
        }

        return $path;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function activities(): array
    {
        return [
            [
                'kicker' => '06:45 EAT',
                'title' => 'Walking safaris',
                'photo' => 'walking-safari.jpg',
                'image_label' => 'On foot · with an armed ranger',
                'description' => 'Leave the vehicle behind and follow an armed park ranger on foot — reading tracks, learning the plants and feeling the bush up close in Arusha National Park, Tarangire or Nyerere.',
                'link_url' => '/destinations/arusha-national-park',
                'link_label' => 'Arusha National Park',
            ],
            [
                'kicker' => '09:30 EAT',
                'title' => 'Boat safaris on the Rufiji',
                'photo' => 'boat-safari.jpg',
                'image_label' => 'Rufiji River · Nyerere',
                'description' => 'Glide along the Rufiji River in Nyerere National Park past pods of hippos, basking crocodiles and elephants coming down to drink — a quiet, low-level view you never get from a vehicle.',
                'link_url' => '/destinations/nyerere',
                'link_label' => 'Nyerere National Park',
            ],
            [
                'kicker' => 'Afternoon',
                'title' => 'Maasai cultural visits',
                'photo' => 'maasai-culture.jpg',
                'image_label' => 'Maasai boma · Ngorongoro',
                'description' => 'Visit a Maasai family near the Ngorongoro Crater, join the songs and the famous jumping dance, and learn how the Maasai have lived alongside the wildlife for generations.',
                'link_url' => '/destinations/ngorongoro',
                'link_label' => 'Ngorongoro Crater',
            ],
            [
                'kicker' => 'On the slopes',
                'title' => 'Coffee farm & waterfall tours',
                'photo' => 'coffee-tour.jpg',
                'image_label' => 'Coffee farm · Northern Tanzania',
                'description' => 'Pick, roast and grind coffee by hand with a local farming family on the green foothills of Kilimanjaro, then walk to the Materuni waterfalls for a swim in the cool pool.',
                'link_url' => '/day-trips',
                'link_label' => 'Day trips',
            ],
            [
                'kicker' => 'After the safari',
                'title' => 'Snorkelling in the Indian Ocean',
                'photo' => 'snorkelling.jpg',
                'image_label' => 'Snorkel trip · Indian Ocean',
                'description' => 'Wash off the dust in Zanzibar: snorkel over the coral reefs around Mnemba Atoll, look out for turtles and dolphins, and sail back at sunset on a traditional dhow.',
                'link_url' => '/zanzibar',
                'link_label' => 'Zanzibar',
            ],
        ];
    }
}
