<?php

namespace Tests\Feature;

use App\Filament\Resources\Vehicles\Pages\ManageVehicles;
use App\Models\NavItem;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\NavigationSeeder;
use Database\Seeders\VehicleSeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VehiclesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_admin_can_add_edit_and_delete_vehicles(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageVehicles::class)
            ->callAction(CreateAction::class, [
                'name' => 'Toyota Hiace',
                'category' => 'Airport transfer',
                'seats' => 9,
                'features' => [['feature' => 'Air conditioning'], ['feature' => 'Luggage space']],
                'is_published' => true,
            ])
            ->assertHasNoActionErrors();

        $vehicle = Vehicle::sole();
        $this->assertSame(9, $vehicle->seats);
        $this->assertSame(['Air conditioning', 'Luggage space'], $vehicle->featureList());

        Livewire::test(ManageVehicles::class)
            ->assertCanSeeTableRecords([$vehicle])
            ->callTableAction(EditAction::class, $vehicle, ['seats' => 12])
            ->assertHasNoTableActionErrors();
        $this->assertSame(12, $vehicle->fresh()->seats);

        Livewire::test(ManageVehicles::class)->callTableAction(DeleteAction::class, $vehicle);
        $this->assertModelMissing($vehicle);
    }

    public function test_name_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageVehicles::class)
            ->callAction(CreateAction::class, ['name' => ''])
            ->assertHasActionErrors(['name' => 'required']);
    }

    public function test_vehicles_page_lists_published_vehicles(): void
    {
        Vehicle::create(['name' => 'Toyota Land Cruiser 4×4', 'category' => 'Safari 4×4', 'seats' => 7, 'features' => ['Pop-up roof', '<b>Fridge</b>'], 'sort_order' => 1]);
        Vehicle::create(['name' => 'Retired Van', 'is_published' => false]);

        $this->get('/vehicles')
            ->assertOk()
            ->assertSee('Our Safari Vehicles')
            ->assertSee('Toyota Land Cruiser 4×4')
            ->assertSee('Safari 4×4')
            ->assertSee('7 seats')
            ->assertSee('Pop-up roof')
            ->assertSee('&lt;b&gt;Fridge&lt;/b&gt;', false)
            ->assertDontSee('Retired Van')
            ->assertSee(e(route('contact', ['subject' => 'Vehicle: Toyota Land Cruiser 4×4'])), false);

        $this->get('/sitemap.xml')->assertSee(route('vehicles'));
    }

    public function test_empty_fleet_shows_a_friendly_message(): void
    {
        $this->get('/vehicles')->assertOk()->assertSee('coming soon');
    }

    public function test_seeder_adds_the_fleet_with_photos_and_keeps_admin_edits(): void
    {
        // A vehicle the admin already has (no photo yet) and has edited.
        Vehicle::create(['name' => 'Toyota Land Cruiser 4×4', 'seats' => 6, 'features' => ['Our own feature']]);

        $this->seed(VehicleSeeder::class);

        $this->assertSame(count(VehicleSeeder::fleet()), Vehicle::count());
        $this->assertGreaterThanOrEqual(8, Vehicle::count());
        Vehicle::all()->each(fn (Vehicle $v) => Storage::disk('public')->assertExists($v->image));

        $cruiser = Vehicle::where('name', 'Toyota Land Cruiser 4×4')->sole();
        $this->assertSame(6, $cruiser->seats);                  // admin edit kept
        $this->assertSame(['Our own feature'], $cruiser->featureList());
        $this->assertSame('vehicles/land-cruiser-extended.jpg', $cruiser->image); // photo filled in

        // Running it again adds nothing.
        $this->seed(VehicleSeeder::class);
        $this->assertSame(count(VehicleSeeder::fleet()), Vehicle::count());

        $this->get('/vehicles')
            ->assertOk()
            ->assertSee('Toyota Coaster')
            ->assertSee('25 seats')
            ->assertSee('/storage/vehicles/coaster.jpg', false);
    }

    public function test_our_vehicles_is_in_the_about_menu(): void
    {
        $this->seed(NavigationSeeder::class);

        $html = $this->get('/vehicles')->assertOk()->getContent();

        // Desktop dropdown (with its hover preview) and the mobile menu both link to it.
        $this->assertMatchesRegularExpression('#<a href="'.preg_quote(url('/vehicles'), '#').'" data-mega-target="preview-about-\d+">Our Vehicles</a>#', $html);
        $this->assertStringContainsString('Our Safari Vehicles', $html);
        $mobile = substr($html, strpos($html, 'id="mobile-sub-about"'));
        $this->assertStringContainsString('<a href="'.url('/vehicles').'" aria-current="page">Our Vehicles</a>', $mobile);
        // About counts as the current section on its dropdown pages.
        $this->assertStringContainsString('class="nav-trigger is-current" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-about"', $html);
        $this->assertStringNotContainsString('class="nav-trigger is-current" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-safaris"', $html);
        $about = collect(NavItem::where('href', '/about')->first()->links)->pluck('label')->all();
        $this->assertSame(['About Us', 'Our Team', 'Our Vehicles', 'Reviews', 'FAQ'], $about);
    }

    public function test_the_menu_migration_adds_the_link_once_after_our_team(): void
    {
        $this->seed(NavigationSeeder::class);
        $about = NavItem::where('href', '/about')->first();
        $about->update(['links' => collect($about->links)->reject(fn ($l) => $l['href'] === '/vehicles')->values()->all()]);

        $migration = require database_path('migrations/2026_10_15_100100_add_vehicles_link_to_about_menu.php');
        $migration->up();
        $migration->up();

        $labels = collect($about->fresh()->links)->pluck('label')->all();
        $this->assertSame(['About Us', 'Our Team', 'Our Vehicles', 'Reviews', 'FAQ'], $labels);

        $migration->down();
        $this->assertNotContains('Our Vehicles', collect($about->fresh()->links)->pluck('label')->all());
    }
}
