<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Our fleet is Toyota Land Cruisers: the photos uploaded on /vehicles all show them, while the starter
 * vehicles (VehicleSeeder) were a mix of models. This turns each starter vehicle into a Land Cruiser
 * card that fits its photo (the photo, order and "Show on website" stay as they are).
 *
 * Only vehicles that still have their starter name and starter text are changed, so anything edited in
 * the admin is left alone. Safe to run more than once:
 *   php artisan db:seed --class=LandCruiserFleetSeeder --force
 *
 * Don't run VehicleSeeder afterwards: it would add the old starter vehicles back.
 */
class LandCruiserFleetSeeder extends Seeder
{
    public function run(): void
    {
        $starter = collect(VehicleSeeder::fleet())->keyBy('name');
        $changed = 0;

        foreach (self::landCruisers() as $oldName => $card) {
            $vehicle = Vehicle::where('name', $oldName)->first();

            if (! $vehicle || $vehicle->description !== ($starter[$oldName]['description'] ?? null)) {
                continue; // already changed, renamed or edited in the admin
            }

            $vehicle->update($card);
            $changed++;
        }

        $this->command?->info("Land Cruiser fleet: {$changed} vehicles updated.");
    }

    /**
     * Starter vehicle name => its Land Cruiser card (in the order the photos were uploaded).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function landCruisers(): array
    {
        return [
            'Toyota Land Cruiser 4×4' => [
                'name' => 'Toyota Land Cruiser 4×4 Safari Vehicle',
                'category' => 'Safari 4×4',
                'seats' => 7,
                'description' => 'Every safari with us is in a private Toyota Land Cruiser, the vehicle trusted across Tanzania\'s national parks. It is strong, reliable and built for the rough, dusty and muddy park roads of the Serengeti, Ngorongoro and beyond, so you can travel in comfort from the first game drive to the last.',
                'features' => ['Private use, never shared with other groups', 'Up to 7 guests, each with a window seat', 'Full-time 4×4 for every season', 'Driven by your own English-speaking guide'],
            ],
            'Open-Sided Land Cruiser Game Viewer' => [
                'name' => 'Pop-Up Roof for Game Viewing',
                'category' => 'Game viewing',
                'seats' => 7,
                'description' => 'The roof lifts up so everyone can stand, look out over the plains and take photos in the open air, then drops back down for the drive between parks. It is the best way to watch lions, elephants and the Great Migration up close, with shade over your head.',
                'features' => ['Stand-up viewing for every guest', 'Shade from the sun while you watch', 'Clear, all-round view for photographers'],
            ],
            'Land Rover Defender 110' => [
                'name' => 'Our Land Cruiser Fleet',
                'category' => 'Private safaris',
                'seats' => null,
                'description' => 'Our Land Cruisers all carry the Serengeti Roaming colours. With several vehicles of the same kind, we can take families and larger groups in two or three cars that travel together, and there is always a spare vehicle close by.',
                'features' => ['Same vehicle type for every group', 'Families and groups in vehicles that travel together', 'A back-up vehicle never far away'],
            ],
            'Toyota HiAce Safari Minivan' => [
                'name' => 'A Window Seat for Everyone',
                'category' => 'Comfort',
                'seats' => 7,
                'description' => 'We never fill every seat. Each guest has a window seat, there is room for camera bags at your feet, and the wide doors make it easy to get in and out at lodges and viewpoints.',
                'features' => ['Window seat guaranteed', 'Space for cameras and day bags', 'Wide doors for easy boarding'],
            ],
            'Toyota Land Cruiser Prado' => [
                'name' => 'Comfortable on Long Drives',
                'category' => 'Comfort',
                'seats' => 7,
                'description' => 'Safari days can mean long drives between parks. Padded seats, good suspension and room to stretch make the hours between sightings comfortable, and your guide stops whenever you need a break.',
                'features' => ['Padded seats with room to stretch', 'Built for rough roads', 'Stops whenever you need them'],
            ],
            'Toyota Land Cruiser V8' => [
                'name' => 'On Board: Binoculars & Charging',
                'category' => 'Inside the vehicle',
                'seats' => null,
                'description' => 'Everything you need for a day in the park is in the car: binoculars to share, charging points for phones and cameras, and cold drinking water, so you never miss a moment.',
                'features' => ['Binoculars for every game drive', 'Charging points for phones and cameras', 'Cold drinking water on board'],
            ],
            'Toyota HiAce' => [
                'name' => 'Driven by Your Own Guide',
                'category' => 'Driver-guide',
                'seats' => null,
                'description' => 'Your driver is also your guide: a Tanzanian professional who knows the parks, the animals and the best places to find them. He stays with you for the whole safari, from airport pickup to the last day.',
                'features' => ['Experienced, licensed driver-guides', 'English-speaking and friendly', 'The same guide for your whole trip'],
            ],
            'Toyota Coaster' => [
                'name' => 'Checked Before Every Safari',
                'category' => 'Safety',
                'seats' => null,
                'description' => 'Our Land Cruisers are serviced regularly and checked before every trip. Each one carries spare wheels, a first-aid kit and tools, so you can relax and enjoy Tanzania knowing you are in safe hands.',
                'features' => ['Regular servicing and pre-trip checks', 'Spare wheels and tools on board', 'First-aid kit in every vehicle'],
            ],
        ];
    }
}
