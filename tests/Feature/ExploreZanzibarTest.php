<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\NavItem;
use Database\Seeders\ZanzibarAreaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** /explore-zanzibar: a card for every Zanzibar area, each with its own page and "Activities". */
class ExploreZanzibarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_the_page_lists_every_zanzibar_area_and_links_to_it(): void
    {
        $this->seed(ZanzibarAreaSeeder::class);
        Destination::create(['name' => 'Lake Plains Park', 'slug' => 'lake-plains', 'region' => 'Northern Circuit', 'tagline' => 'Plains.', 'is_published' => true]);
        Destination::create(['name' => 'Hidden Beach', 'slug' => 'hidden-beach', 'region' => Destination::ZANZIBAR_REGION, 'tagline' => 'Secret.', 'is_published' => false]);

        $this->get('/explore-zanzibar')
            ->assertOk()
            ->assertSee('Explore Zanzibar')
            ->assertSee('Discover the Island, Its Beaches, Culture &amp; Ocean Adventures', false)
            ->assertSee('Explore Zanzibar Islands')
            ->assertSee('Stone Town')->assertSee('Nungwi')->assertSee('Jozani Forest')->assertSee('Safari Blue')->assertSee('Sunset Dhow Cruise')->assertSee('Diving &amp; Snorkeling', false)->assertSee('Forodhani Gardens')
            ->assertSee('href="'.url('/destinations/stone-town').'"', false)
            ->assertSee('class="zan-card"', false)
            ->assertSee('Activities:')
            ->assertSee('Guided walking tour, the Old Fort')
            ->assertDontSee('Lake Plains Park')
            ->assertDontSee('Hidden Beach');
    }

    public function test_an_area_page_shows_its_activities(): void
    {
        $this->seed(ZanzibarAreaSeeder::class);

        $this->get('/destinations/nungwi')
            ->assertOk()
            ->assertSee('Activities')
            ->assertSee('traditional dhow and sunset cruises');
    }

    public function test_a_destination_without_activities_has_no_activities_block(): void
    {
        $destination = Destination::create(['name' => 'Ruaha', 'slug' => 'ruaha', 'region' => 'Southern Circuit', 'tagline' => 'Wild.', 'is_published' => true]);

        $this->get($destination->url())->assertOk()->assertDontSee('>Activities<', false);
    }

    public function test_the_seeder_is_safe_to_run_again_and_keeps_admin_edits(): void
    {
        $this->seed(ZanzibarAreaSeeder::class);
        $this->assertSame(16, Destination::where('region', Destination::ZANZIBAR_REGION)->count());

        Destination::where('slug', 'paje')->update(['tagline' => 'My own line.']);
        $this->seed(ZanzibarAreaSeeder::class);

        $this->assertSame(16, Destination::where('region', Destination::ZANZIBAR_REGION)->count());
        $this->assertSame('My own line.', Destination::firstWhere('slug', 'paje')->tagline);
        Storage::disk('public')->assertExists('destinations/stone-town.jpg');
    }

    public function test_the_zanzibar_menu_gets_the_link_once_and_the_page_is_in_the_sitemap(): void
    {
        NavItem::create(['label' => 'Zanzibar', 'href' => '/zanzibar', 'links' => [['label' => 'Beach Holidays', 'href' => '/zanzibar']]]);

        $migration = require database_path('migrations/2026_11_09_120000_add_activities_to_destinations_and_zanzibar_menu_link.php');
        $migration->down();
        $migration->up();
        $migration->down();
        $migration->up();

        $this->assertSame(['Beach Holidays', 'Explore Zanzibar'], collect(NavItem::first()->links)->pluck('label')->all());
        $this->get('/sitemap.xml')->assertSee(route('zanzibar.explore'));
    }
}
