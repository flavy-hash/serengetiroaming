<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Starter fleet for /vehicles: the safari 4×4s and transfer vehicles a Tanzanian operator
 * typically runs, each with a photo. Safe on production and safe to re-run:
 *   php artisan db:seed --class=VehicleSeeder --force
 *
 * - Adds only vehicles whose name isn't in the table yet (renamed or deleted ones come
 *   back if you run it again, so run it once).
 * - Never changes a vehicle you have edited, except to give a photo-less one its photo.
 *
 * The photos are openly licensed placeholders from Wikimedia Commons (credited on
 * /photo-credits; other operators' logos removed). Seats and features are typical for
 * each model: check them, and swap in photos of your own vehicles, in the admin under
 * Website Pages → Vehicles.
 */
class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $added = 0;

        foreach (self::fleet() as $i => $vehicle) {
            $image = $this->copyPhoto($vehicle['photo']);
            unset($vehicle['photo']);

            $existing = Vehicle::where('name', $vehicle['name'])->first();
            if ($existing) {
                if (blank($existing->image)) {
                    $existing->update(['image' => $image]);
                }

                continue;
            }

            Vehicle::create([...$vehicle, 'image' => $image, 'is_published' => true, 'sort_order' => $i + 1]);
            $added++;
        }

        $this->command?->info("Vehicles: {$added} added.");
    }

    private function copyPhoto(string $file): string
    {
        $path = "vehicles/{$file}";
        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(database_path("seeders/images/vehicles/{$file}")));
        }

        return $path;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function fleet(): array
    {
        return [
            [
                'name' => 'Toyota Land Cruiser 4×4',
                'category' => 'Safari 4×4',
                'seats' => 7,
                'photo' => 'land-cruiser-extended.jpg',
                'description' => 'Our main safari vehicle: a rugged, extended 4×4 Land Cruiser built for Tanzania\'s park roads, with a pop-up roof so everyone can stand up for photos on game drives.',
                'features' => [
                    'Pop-up roof for game viewing',
                    'Window seat for every guest',
                    'Private use — never shared with other groups',
                    'Charging ports for phones and cameras',
                    'Fridge with soft drinks and water',
                    'Binoculars on board',
                ],
            ],
            [
                'name' => 'Open-Sided Land Cruiser Game Viewer',
                'category' => 'Open-sided game viewer',
                'seats' => 9,
                'photo' => 'open-game-viewer.jpg',
                'description' => 'No glass between you and the wildlife: raised, tiered seats and open sides give photographers clear, low-angle shots on game drives.',
                'features' => [
                    'Open sides for unobstructed photos',
                    'Raised, tiered seating',
                    'Canvas roof for shade',
                    'Best for photographers',
                ],
            ],
            [
                'name' => 'Land Rover Defender 110',
                'category' => 'Safari 4×4',
                'seats' => 5,
                'photo' => 'land-rover-defender.jpg',
                'description' => 'A classic go-anywhere 4×4 for couples and small groups heading to remote tracks and fly-in safaris.',
                'features' => [
                    'High clearance for remote tracks',
                    'Roof rack for luggage and gear',
                    'Ideal for couples and small groups',
                ],
            ],
            [
                'name' => 'Toyota HiAce Safari Minivan',
                'category' => 'Safari minivan',
                'seats' => 7,
                'photo' => 'hiace-safari-van.jpg',
                'description' => 'Our budget safari option: a sturdy minivan with a pop-up roof, comfortable on the main park circuits at a lower price.',
                'features' => [
                    'Pop-up roof for game viewing',
                    'Sliding side door for easy access',
                    'Great value for budget safaris',
                ],
            ],
            [
                'name' => 'Toyota Land Cruiser Prado',
                'category' => 'Private transfer',
                'seats' => 4,
                'photo' => 'prado.jpg',
                'description' => 'Comfortable private transfers between Arusha, Moshi and the airports, and for day trips on paved roads.',
                'features' => [
                    'Air conditioning',
                    'Room for luggage',
                    'Ideal for couples and families',
                ],
            ],
            [
                'name' => 'Toyota Land Cruiser V8',
                'category' => 'VIP transfer',
                'seats' => 4,
                'photo' => 'land-cruiser-v8.jpg',
                'description' => 'Premium comfort for VIP and executive transfers, honeymooners and guests who want extra space on the road.',
                'features' => [
                    'Air conditioning',
                    'Spacious, quiet cabin',
                    'Smooth ride on long transfers',
                ],
            ],
            [
                'name' => 'Toyota HiAce',
                'category' => 'Airport transfer',
                'seats' => 10,
                'photo' => 'hiace.jpg',
                'description' => 'Airport transfers from Kilimanjaro International Airport (JRO) and Arusha for families and groups, with plenty of room for bags.',
                'features' => [
                    'Air conditioning',
                    'Plenty of luggage space',
                    'Door-to-door pickup and drop-off',
                ],
            ],
            [
                'name' => 'Toyota Coaster',
                'category' => 'Group transfer',
                'seats' => 25,
                'photo' => 'coaster.jpg',
                'description' => 'For large groups and Kilimanjaro climbing teams: transfers to the trailheads, between towns and to the airport.',
                'features' => [
                    'Room for large groups',
                    'Space for luggage and climbing gear',
                    'Ideal for school, church and company groups',
                ],
            ],
        ];
    }
}
