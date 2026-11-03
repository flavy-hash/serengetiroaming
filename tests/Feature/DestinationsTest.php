<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\Destinations\Pages\CreateDestination;
use App\Filament\Resources\Destinations\Pages\EditDestination;
use App\Filament\Resources\Destinations\Pages\ListDestinations;
use App\Models\Destination;
use App\Models\NavItem;
use App\Models\TourPage;
use App\Models\User;
use Database\Seeders\DestinationSeeder;
use Database\Seeders\NavigationSeeder;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DestinationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function destination(array $attributes = []): Destination
    {
        return Destination::create([
            'name' => 'Serengeti National Park',
            'slug' => 'serengeti',
            'region' => 'Northern Circuit',
            'tagline' => 'Home of the Great Migration.',
            'description' => "First paragraph.\n\nSecond <b>paragraph</b>.",
            'highlights' => ['The Great Migration', 'Big cats'],
            'best_time' => 'June – October',
            'places' => ['serengeti', 'serengeti-north'],
            'sort_order' => 1,
            ...$attributes,
        ]);
    }

    private function package(string $slug, array $itinerary, bool $published = true): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Safaris,
            'title' => $slug,
            'slug' => $slug,
            'package_name' => ucfirst($slug).' Safari',
            'tier' => SafariTier::Classic,
            'is_published' => $published,
            'itinerary' => $itinerary,
        ]);
    }

    public function test_listing_shows_published_destinations_with_region_filters(): void
    {
        $this->destination();
        $this->destination(['name' => 'Ruaha National Park', 'slug' => 'ruaha', 'region' => 'Southern Circuit', 'places' => ['ruaha'], 'sort_order' => 2]);
        $this->destination(['name' => 'Secret Park', 'slug' => 'secret', 'is_published' => false]);

        $this->get('/destinations')
            ->assertOk()
            ->assertSee('Tanzania Safari Destinations')
            ->assertSeeInOrder(['Serengeti National Park', 'Ruaha National Park'])
            ->assertSee('Explore More')
            ->assertSee(route('destinations.show', 'serengeti'), false)
            ->assertDontSee('Secret Park')
            ->assertSee('All destinations');

        $this->get('/destinations?region=southern-circuit')
            ->assertOk()
            ->assertSee('Southern Circuit Destinations')
            ->assertSee('Ruaha National Park')
            ->assertDontSee('Serengeti National Park');

        // Unknown values are ignored.
        $this->get('/destinations?region=atlantis')->assertOk()->assertSee('Serengeti National Park')->assertSee('Ruaha National Park');
    }

    public function test_destination_page_shows_details_and_the_packages_that_go_there(): void
    {
        $this->destination();
        $this->destination(['name' => 'Ruaha National Park', 'slug' => 'ruaha', 'region' => 'Southern Circuit', 'places' => ['ruaha'], 'sort_order' => 2]);
        $this->package('migration', [['day' => 'Day 1', 'title' => 'Arusha'], ['day' => 'Day 2', 'title' => 'Northern Serengeti']]);
        $this->package('crater', [['day' => 'Day 1', 'title' => 'Ngorongoro Crater']]);
        $this->package('hidden', [['day' => 'Day 1', 'title' => 'Central Serengeti']], published: false);

        $this->get('/destinations/serengeti')
            ->assertOk()
            ->assertSee('<title>Serengeti National Park — Tanzania Safari Destination', false)
            ->assertSee('Home of the Great Migration.')
            ->assertSee('Second &lt;b&gt;paragraph&lt;/b&gt;.', false)
            ->assertSee('The Great Migration')
            ->assertSee('June – October')
            ->assertSee('Safaris & Tours that Visit Serengeti National Park')
            ->assertSee('Migration Safari')
            ->assertDontSee('Crater Safari')
            ->assertDontSee('Hidden Safari')
            ->assertSee('More Destinations')
            ->assertSee('Ruaha National Park')
            ->assertSee('"@type": "TouristDestination"', false)
            ->assertSee(e(route('contact', ['subject' => 'Trip to Serengeti National Park'])), false);

        // No matching packages: no empty section.
        $this->get('/destinations/ruaha')->assertOk()->assertDontSee('Safaris & Tours that Visit');
    }

    public function test_unpublished_destinations_are_hidden_from_visitors_but_previewable(): void
    {
        $this->destination(['is_published' => false]);

        $this->get('/destinations/serengeti')->assertNotFound();
        $this->get('/destinations/nowhere')->assertNotFound();

        $this->actingAs(User::factory()->create());
        $this->get('/destinations/serengeti')->assertOk()->assertSee('noindex', false);
    }

    public function test_admin_can_create_edit_and_delete_destinations(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateDestination::class)
            ->fillForm([
                'name' => 'Lake Natron',
                'region' => 'Northern Circuit',
                'tagline' => 'A red soda lake beneath a volcano.',
                'highlights' => [['highlight' => 'Flamingos'], ['highlight' => 'Ol Doinyo Lengai']],
                'places' => ['lake-natron'],
                'is_published' => true,
            ])
            ->assertSchemaStateSet(['slug' => 'lake-natron'])
            ->call('create')
            ->assertHasNoFormErrors();

        $natron = Destination::sole();
        $this->assertSame(['Flamingos', 'Ol Doinyo Lengai'], $natron->highlightList());
        $this->assertSame(['lake-natron'], $natron->places);

        // Slugs are unique.
        Livewire::test(CreateDestination::class)
            ->fillForm(['name' => 'Natron again', 'slug' => 'lake-natron', 'tagline' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(EditDestination::class, ['record' => $natron->getRouteKey()])
            ->fillForm(['best_time' => 'June – December'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('June – December', $natron->fresh()->best_time);

        Livewire::test(ListDestinations::class)
            ->assertCanSeeTableRecords([$natron])
            ->callTableAction(DeleteAction::class, $natron);
        $this->assertModelMissing($natron);
    }

    public function test_seeder_adds_twelve_destinations_with_photos_and_keeps_edits(): void
    {
        $this->destination(['tagline' => 'My own words', 'image' => null]);

        $this->seed(DestinationSeeder::class);

        $this->assertSame(12, Destination::count());
        Destination::all()->each(fn (Destination $d) => Storage::disk('public')->assertExists($d->image));
        $this->assertSame('My own words', Destination::where('slug', 'serengeti')->value('tagline'));

        $this->seed(DestinationSeeder::class);
        $this->assertSame(12, Destination::count());

        $this->get('/sitemap.xml')->assertSee(route('destinations'))->assertSee(route('destinations.show', 'mafia-island'));
    }

    public function test_destinations_is_in_the_menu(): void
    {
        $this->seed(NavigationSeeder::class);

        $this->assertSame(
            ['Safaris', 'Northern Circuit', 'Southern Circuit', 'Kilimanjaro', 'Zanzibar', 'Day Trips', 'Destinations', 'About', 'Contact'],
            NavItem::forNavbar()->pluck('label')->all(),
        );

        // A dropdown with the main destinations and Accommodation; the section is underlined on its pages.
        $html = $this->get('/destinations')->assertOk()->getContent();
        $this->assertStringContainsString('class="nav-trigger is-current" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-destinations"', $html);

        $panel = substr($html, strpos($html, 'id="mega-destinations"'));
        $panel = substr($panel, 0, strpos($panel, '</ul>'));
        foreach (['View All Destinations', 'Serengeti National Park', 'Ngorongoro Crater', 'Tarangire National Park', 'Zanzibar Island', 'Accommodation'] as $label) {
            $this->assertStringContainsString('>'.$label.'</a>', $panel);
        }
        $this->assertStringContainsString('href="'.url('/accommodations').'"', $panel);

        // The accommodation page highlights Destinations too (it is one of its links).
        $this->get('/accommodations')->assertOk()
            ->assertSee('class="nav-trigger is-current" type="button" data-menu-trigger aria-expanded="false" aria-controls="mega-destinations"', false);
    }

    public function test_destination_links_in_the_menu_preview_their_photo_and_line(): void
    {
        $this->seed(NavigationSeeder::class);
        Destination::create(['name' => 'Serengeti National Park', 'slug' => 'serengeti', 'tagline' => 'Home of the Great Migration.', 'image' => 'destinations/serengeti.jpg', 'is_published' => true]);

        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('Home of the Great Migration.', $html);
        $this->assertStringContainsString('Explore Serengeti National Park', $html);
        $this->assertStringContainsString('storage/destinations/serengeti.jpg', $html);
        $this->assertStringContainsString(e("Where You'll Stay"), $html); // the Accommodation link's preview
    }

    public function test_the_dropdown_migration_fills_an_empty_destinations_item_only(): void
    {
        $this->seed(NavigationSeeder::class);
        $item = NavItem::where('href', '/destinations')->first();
        $item->update(['links' => [], 'title' => null, 'image' => null]);

        $migration = require database_path('migrations/2026_10_19_100000_add_destinations_dropdown.php');
        $migration->up();

        $item->refresh();
        $this->assertSame('Accommodation', collect($item->links)->last()['label']);
        Storage::disk('public')->assertExists($item->image);

        // An admin's own links are never replaced.
        $item->update(['links' => [['label' => 'Only this', 'href' => '/destinations']]]);
        $migration->up();
        $this->assertSame([['label' => 'Only this', 'href' => '/destinations']], $item->fresh()->links);
    }

    public function test_the_menu_migration_adds_destinations_once_before_about(): void
    {
        $this->seed(NavigationSeeder::class);
        NavItem::where('href', '/destinations')->delete();

        $migration = require database_path('migrations/2026_10_16_100100_add_destinations_nav_item.php');
        $migration->up();
        $migration->up();

        $labels = NavItem::forNavbar()->pluck('label')->all();
        $this->assertSame(1, collect($labels)->filter(fn ($l) => $l === 'Destinations')->count());
        $this->assertSame(array_search('About', $labels) - 1, array_search('Destinations', $labels));
    }
}
