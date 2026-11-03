<?php

namespace Tests\Feature;

use App\Filament\Resources\NavItems\Pages\CreateNavItem;
use App\Filament\Resources\NavItems\Pages\EditNavItem;
use App\Filament\Resources\NavItems\Pages\ListNavItems;
use App\Models\NavItem;
use App\Models\User;
use Database\Seeders\NavigationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(NavigationSeeder::class);
    }

    public function test_seeded_navbar_matches_the_original_menu(): void
    {
        $this->seed(NavigationSeeder::class); // re-running must not duplicate
        $this->assertSame(9, NavItem::count());

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['aria-controls="mega-safaris"', 'aria-controls="mega-northern-circuit"', 'aria-controls="mega-southern-circuit"', 'aria-controls="mega-kilimanjaro"', 'aria-controls="mega-zanzibar"', 'aria-controls="mega-day-trips"', 'aria-controls="mega-destinations"', 'aria-controls="mega-about"', 'class="nav-link" href="'.url('/contact').'"'], false)
            ->assertSee(url('/safaris#mid-range'))
            ->assertSee('Climbing the Roof of Africa')
            ->assertSee('/storage/menus/navigation/kilimanjaro.jpg');

        Storage::disk('public')->assertExists('menus/navigation/zanzibar.jpg');
    }

    public function test_admin_changes_show_up_on_the_site_immediately(): void
    {
        $this->get('/')->assertSee('Tanzania Safari Adventures'); // warms the cache
        $this->actingAs(User::factory()->create());

        $safaris = NavItem::where('label', 'Safaris')->firstOrFail();

        Livewire::test(EditNavItem::class, ['record' => $safaris->getRouteKey()])
            ->fillForm(['title' => 'Big Five Safaris', 'links' => [['label' => 'Great Migration', 'href' => '/safaris#migration']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')
            ->assertSee('Big Five Safaris')
            ->assertSee('Great Migration')
            ->assertDontSee('Tanzania Safari Adventures');

        // Hiding an item removes it from the navbar.
        Livewire::test(ListNavItems::class)->call('updateTableColumnState', 'is_visible', $safaris->getKey(), false);
        $this->get('/')->assertDontSee('aria-controls="mega-safaris"', false);
    }

    public function test_reordering_updates_the_navbar(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/'); // warm the cache

        $ids = NavItem::ordered()->pluck('id')->all();
        $contact = array_pop($ids);

        Livewire::test(ListNavItems::class)->call('reorderTable', [$contact, ...$ids]);

        $this->get('/')->assertSeeInOrder(['class="nav-link" href="'.url('/contact').'"', 'aria-controls="mega-safaris"'], false);
    }

    public function test_migration_adds_northern_circuit_to_an_existing_menu(): void
    {
        NavItem::where('href', '/northern-circuit')->delete(); // a menu set up before the new section
        $migration = require database_path('migrations/2026_10_08_100000_add_northern_circuit_nav_item.php');

        $migration->up();
        $migration->up(); // running twice must not duplicate

        $this->assertSame(1, NavItem::where('href', '/northern-circuit')->count());
        $this->assertSame(
            ['Safaris', 'Northern Circuit', 'Southern Circuit'],
            NavItem::ordered()->take(3)->pluck('label')->all(),
        );
        Storage::disk('public')->assertExists('menus/navigation/northern-circuit.jpg');

        $this->get('/')->assertSee('Tanzania Northern Circuit')->assertSee(url('/northern-circuit#serengeti'));
    }

    public function test_admin_can_add_a_plain_link(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/navigation')->assertOk()->assertSee('Southern Circuit');
        $this->get('/admin/navigation/create')->assertOk();

        Livewire::test(CreateNavItem::class)
            ->fillForm(['label' => 'Photo Credits', 'href' => '/photo-credits', 'is_visible' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(10, NavItem::where('label', 'Photo Credits')->value('sort_order'));
        $this->get('/')->assertSee('class="nav-link" href="'.url('/photo-credits').'">Photo Credits<', false);
    }
}
