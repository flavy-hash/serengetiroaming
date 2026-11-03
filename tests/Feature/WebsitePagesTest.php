<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Pages\ManageAboutPage;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\CreateKilimanjaroPage;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\ListKilimanjaroPages;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\CreateZanzibarPage;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\EditZanzibarPage;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\ListZanzibarPages;
use App\Models\AboutPage;
use App\Models\NavItem;
use App\Models\TourPage;
use App\Models\User;
use Database\Seeders\ItineraryPackagesSeeder;
use Database\Seeders\NavigationSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\TourPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WebsitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(TourPageSeeder::class);
    }

    private function zanzibar(): TourPage
    {
        return TourPage::where('category', TourCategory::Zanzibar)->firstOrFail();
    }

    public function test_seeded_zanzibar_page_renders_like_the_original(): void
    {
        $this->get('/zanzibar')
            ->assertOk()
            ->assertSee('Zanzibar Beach Escape · 4 Days')
            ->assertSee('Day 3')
            ->assertSee('Dhow Sailing &amp; Snorkeling (Optional)', false)
            ->assertSee('$650')
            ->assertSee('Spice farm tour with tasting')
            ->assertSee('id="stone-town"', false)
            ->assertSee('<option selected>Zanzibar Beach Escape · 4 Days · From $650</option>', false)
            ->assertSee('/storage/tour-pages/zanzibar/banner-nungwi.jpg');

        Storage::disk('public')->assertExists('tour-pages/zanzibar/dhow.jpg');
    }

    public function test_empty_section_shows_coming_soon(): void
    {
        $this->get('/northern-circuit')->assertOk()->assertSee('coming soon')->assertSee('Ngorongoro Crater, Tarangire');

        $this->get('/kilimanjaro')->assertOk()->assertSee('coming soon');
    }

    public function test_drafts_are_hidden_from_visitors_but_previewable_by_admins(): void
    {
        $page = TourPage::create([
            'category' => TourCategory::Kilimanjaro,
            'title' => 'Machame Route',
            'slug' => 'machame',
            'package_name' => 'Machame Route',
            'is_published' => false,
        ]);

        $this->get('/kilimanjaro')->assertSee('coming soon');
        $this->get('/kilimanjaro/machame')->assertNotFound();

        $this->actingAs(User::factory()->create());
        $this->get('/kilimanjaro')->assertOk()->assertSee('Machame Route');

        $this->assertSame(url('/kilimanjaro'), $page->url());
    }

    public function test_secondary_pages_live_under_their_slug_and_are_cross_linked(): void
    {
        TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar Honeymoon',
            'slug' => 'honeymoon',
            'package_name' => 'Zanzibar Honeymoon',
            'duration' => '6 Days',
            'price_from' => 1200,
            'is_published' => true,
            'sort_order' => 5,
        ]);

        $this->get('/zanzibar/honeymoon')->assertOk()->assertSee('Zanzibar Honeymoon')->assertSee('More Zanzibar packages');
        $this->get('/zanzibar')->assertSee(url('/zanzibar/honeymoon'));
        $this->get('/zanzibar/nope')->assertNotFound();
    }

    public function test_safaris_page_lists_every_package_with_filters(): void
    {
        TourPage::create([
            'category' => TourCategory::Kilimanjaro,
            'tier' => SafariTier::Classic,
            'title' => 'Kilimanjaro',
            'slug' => 'machame',
            'package_name' => 'Machame Route',
            'duration' => '7 Days',
            'price_from' => 1650,
            'is_published' => true,
        ]);
        TourPage::create([
            'category' => TourCategory::Safaris,
            'tier' => SafariTier::MidRange,
            'title' => 'Serengeti & Ngorongoro',
            'slug' => 'serengeti-ngorongoro',
            'package_name' => 'Serengeti & Ngorongoro Safari',
            'duration' => '5 Days',
            'is_published' => true,
        ]);
        TourPage::create([
            'category' => TourCategory::DayTrips,
            'title' => 'Materuni',
            'slug' => 'materuni',
            'package_name' => 'Unpublished Materuni Draft',
            'is_published' => false,
        ]);

        $this->get('/safaris')
            ->assertOk()
            // Filters: only destinations that have packages, every style, each duration.
            ->assertSeeInOrder(['data-filter="section" data-value=""', 'data-value="safaris"', 'data-value="kilimanjaro"', 'data-value="zanzibar"'], false)
            ->assertDontSee('data-value="day-trips"', false)
            ->assertSeeInOrder(['data-value="budget"', 'data-value="classic"', 'data-value="mid-range"', 'data-value="luxury"'], false)
            ->assertSeeInOrder(['>4 days<', '>5 days<', '>7 days<'], false)
            // Cards in navbar order, tagged for filtering.
            ->assertSeeInOrder([
                'data-section="safaris" data-tier="mid-range" data-days="5"', 'Serengeti &amp; Ngorongoro Safari',
                'data-section="kilimanjaro" data-tier="classic" data-days="7"', 'Machame Route', 'From $1,650',
                'data-section="zanzibar"', 'Zanzibar Beach Escape',
            ], false)
            ->assertSee('of 3 packages')
            ->assertSee(url('/safaris/serengeti-ngorongoro'))
            ->assertSee(url('/kilimanjaro'))
            ->assertDontSee('Unpublished Materuni Draft')
            ->assertSee('None of these quite right?');

        $this->get('/safaris/serengeti-ngorongoro')
            ->assertOk()
            ->assertSee('Mid-range Safari')
            ->assertSeeInOrder(['Home', 'Safaris', 'Serengeti &amp; Ngorongoro'], false);
    }

    public function test_old_packages_url_redirects_to_safaris(): void
    {
        $this->get('/packages')->assertMovedPermanently()->assertRedirect('/safaris');
    }

    public function test_style_is_required_for_packages_in_every_section(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([CreateZanzibarPage::class, CreateKilimanjaroPage::class] as $page) {
            Livewire::test($page)
                ->fillForm(['title' => 'Test', 'slug' => 'test', 'package_name' => 'Test'])
                ->call('create')
                ->assertHasFormErrors(['tier' => 'required']);
        }

        Livewire::test(CreateKilimanjaroPage::class)
            ->fillForm(['title' => 'Lemosho', 'slug' => 'lemosho', 'package_name' => 'Lemosho Route', 'tier' => SafariTier::Luxury])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(SafariTier::Luxury, TourPage::where('slug', 'lemosho')->first()->tier);

        Livewire::test(ListKilimanjaroPages::class)
            ->assertTableColumnVisible('tier')
            ->filterTable('tier', [SafariTier::Budget->value])
            ->assertCountTableRecords(0);
    }

    public function test_itinerary_packages_are_seeded(): void
    {
        $this->seed([PackageSeeder::class, ItineraryPackagesSeeder::class]);
        $this->seed(ItineraryPackagesSeeder::class); // re-running must not duplicate

        $this->assertSame(7, TourPage::count()); // Zanzibar + Ngorongoro + 3 climbs + 2 Northern Circuit

        // Kilimanjaro: Machame is the section's main page; Lemosho and Marangu have their own.
        $this->get('/kilimanjaro')->assertOk()
            ->assertSee('Machame Route')->assertSee('Stella Point (5,752 m)')->assertSee('$1,800')
            ->assertSee('More Kilimanjaro packages')->assertSee(url('/kilimanjaro/lemosho-route'));
        $this->get('/kilimanjaro/marangu-route')->assertOk()
            ->assertSee("Gillman's Point", false)->assertSee('$1,450')
            ->assertSeeInOrder(['1–2 climbers', '$2,100 pp', '3–5 climbers', '$1,650 pp', '6–10 climbers', '$1,450 pp']);
        $this->get('/kilimanjaro/lemosho-route')->assertOk()->assertSee('Londorossi Gate')->assertSee('Day 8');

        // Northern Circuit: the 5-day safari is the main page, the 8-day has its own.
        $this->get('/northern-circuit')->assertOk()
            ->assertSee('Private &amp; Luxury Tanzania Safari', false)
            ->assertSeeInOrder(['1 traveller', '$4,675 pp', '7 travellers', '$2,761 pp'])
            ->assertSee('Tarangire Elephant Springs')
            ->assertSee('Luxury');
        $this->get('/northern-circuit/great-migration-luxury-safari-8-days')->assertOk()
            ->assertSee('Mara River crossing')->assertSee('Mara Mara Tented Lodge')
            ->assertSee('Serengeti balloon safari with bush breakfast ($520 per person)');

        $this->get('/safaris')->assertSee('of 7 packages');
        $this->get('/zanzibar')
            ->assertSee('>Machame Route · 6 Days · From $1,800</option>', false)
            ->assertSee('>Great Migration Luxury Safari · 8 Days · From $5,722</option>', false);
    }

    public function test_existing_sites_get_the_packages_and_the_machame_placeholder_is_replaced(): void
    {
        $this->seed(NavigationSeeder::class);
        // An existing site: the old placeholder Machame and the old menu anchors.
        TourPage::create(['category' => TourCategory::Kilimanjaro, 'slug' => 'machame-route', 'title' => 'Machame Route', 'package_name' => 'Machame Route', 'duration' => '7 Days', 'price_from' => 1650, 'is_published' => true]);
        $menu = NavItem::where('href', '/kilimanjaro')->first();
        $menu->update(['links' => [['label' => 'Machame · 7 Days', 'href' => '/kilimanjaro#machame'], ['label' => 'Marangu · 6 Days', 'href' => '/kilimanjaro#marangu']]]);

        (require database_path('migrations/2026_10_09_100000_add_itinerary_packages.php'))->up();

        $machame = TourPage::where('slug', 'machame-route')->first();
        $this->assertSame('6 Days', $machame->duration);
        $this->assertSame(1800, $machame->price_from);
        $this->assertSame([
            ['label' => 'Machame · 6 Days', 'href' => '/kilimanjaro'],
            ['label' => 'Marangu · 5 Days', 'href' => '/kilimanjaro/marangu-route'],
        ], $menu->fresh()->links);

        // A Machame the admin has customised is left alone.
        $machame->update(['duration' => '7 Days', 'price_from' => 1999]);
        $this->seed(ItineraryPackagesSeeder::class);
        $this->assertSame(1999, $machame->fresh()->price_from);
    }

    public function test_safaris_page_without_packages(): void
    {
        TourPage::query()->delete();

        $this->get('/safaris')->assertOk()->assertSee('being put together')->assertSee('None of these quite right?');
    }

    public function test_each_admin_resource_only_lists_its_own_section(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListZanzibarPages::class)->assertCanSeeTableRecords([$this->zanzibar()]);
        Livewire::test(ListKilimanjaroPages::class)->assertCanNotSeeTableRecords([$this->zanzibar()]);

        $this->get('/admin/pages/safaris')->assertNotFound(); // removed: /safaris lists every package

        foreach (['northern-circuit', 'southern-circuit', 'kilimanjaro', 'zanzibar', 'day-trips'] as $section) {
            $this->get("/admin/pages/{$section}")->assertOk();
            $this->get("/admin/pages/{$section}/create")->assertOk();
        }
    }

    public function test_admin_can_edit_the_zanzibar_page(): void
    {
        $this->actingAs(User::factory()->create());
        $page = $this->zanzibar();

        $this->get("/admin/pages/zanzibar/{$page->id}/edit")->assertOk();

        Livewire::test(EditZanzibarPage::class, ['record' => $page->getRouteKey()])
            ->assertSchemaStateSet(['package_name' => 'Zanzibar Beach Escape'])
            ->fillForm(['price_from' => 700, 'tier' => SafariTier::Classic])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(700, $page->fresh()->price_from);
        $this->assertCount(4, $page->fresh()->itinerary);
        $this->get('/zanzibar')->assertSee('$700');
    }

    public function test_admin_can_create_a_page_in_a_section(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateKilimanjaroPage::class)
            ->fillForm([
                'title' => 'Kilimanjaro',
                'slug' => 'machame-route',
                'tier' => SafariTier::Classic,
                'package_name' => 'Machame Route',
                'duration' => '7 Days',
                'price_from' => 1650,
                'is_published' => true,
                'itinerary' => [
                    ['day' => 'Day 1', 'title' => 'Machame Gate to Machame Camp'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = TourPage::where('slug', 'machame-route')->firstOrFail();
        $this->assertSame(TourCategory::Kilimanjaro, $page->category);

        $this->get('/kilimanjaro')->assertOk()->assertSee('Machame Gate to Machame Camp');
        $this->get('/zanzibar')->assertSee('Machame Route · 7 Days · From $1,650');
    }

    public function test_about_page_is_edited_in_the_admin(): void
    {
        $this->get('/about')->assertOk()->assertSee('coming soon');

        $this->actingAs(User::factory()->create());
        $this->get('/admin/pages/about')->assertOk();

        Livewire::test(ManageAboutPage::class)
            ->fillForm([
                'is_published' => true,
                'title' => 'About Us',
                'intro_heading' => 'We are Serengeti Roaming',
                'team' => [['name' => 'Asha', 'role' => 'Head Guide']],
                'faqs' => [['question' => 'Do I need a visa?', 'answer' => 'Most visitors do.']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1, AboutPage::count());

        auth()->logout();
        $this->get('/about')
            ->assertOk()
            ->assertSee('We are Serengeti Roaming')
            ->assertSee('Head Guide')
            ->assertSee('Do I need a visa?');
    }
}
