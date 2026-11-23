<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Database\Seeders\LandCruiserFleetSeeder;
use Database\Seeders\VehicleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** The starter vehicles become Land Cruiser cards; anything edited in the admin is left alone. */
class LandCruiserFleetSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(VehicleSeeder::class);
    }

    public function test_starter_vehicles_become_land_cruisers_and_keep_their_photo_and_order(): void
    {
        $defender = Vehicle::firstWhere('name', 'Land Rover Defender 110');

        $this->seed(LandCruiserFleetSeeder::class);

        $this->assertSame(0, Vehicle::where('name', 'like', '%Defender%')->orWhere('name', 'like', '%HiAce%')->orWhere('name', 'like', '%Coaster%')->count());
        $fleet = Vehicle::find($defender->id);
        $this->assertSame('Our Land Cruiser Fleet', $fleet->name);
        $this->assertSame($defender->image, $fleet->image);
        $this->assertSame($defender->sort_order, $fleet->sort_order);
        $this->assertSame(8, Vehicle::count());

        $this->get('/vehicles')
            ->assertOk()
            ->assertSee('Toyota Land Cruiser 4×4 Safari Vehicle')
            ->assertSee('Pop-Up Roof for Game Viewing')
            ->assertSee('Binoculars for every game drive')
            ->assertDontSee('Toyota Coaster');
    }

    public function test_edited_vehicles_are_left_alone_and_it_is_safe_to_run_twice(): void
    {
        Vehicle::where('name', 'Toyota Coaster')->update(['description' => 'Our own text.']);

        $this->seed(LandCruiserFleetSeeder::class);
        $this->seed(LandCruiserFleetSeeder::class);

        $this->assertSame('Our own text.', Vehicle::firstWhere('name', 'Toyota Coaster')->description);
        $this->assertSame(1, Vehicle::where('name', 'Pop-Up Roof for Game Viewing')->count());
        $this->assertSame(8, Vehicle::count());
    }
}
