<?php

namespace Tests\Feature;

use App\Models\TourPage;
use App\Models\User;
use App\Support\RouteMap;
use Database\Seeders\SafariCollectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SafariCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_it_adds_fifteen_draft_packages_without_prices(): void
    {
        $this->seed(SafariCollectionSeeder::class);

        $packages = TourPage::all();
        $this->assertCount(15, $packages);
        $this->assertTrue($packages->every(fn (TourPage $p) => ! $p->is_published && $p->price_from === null));

        // Drafts are hidden from visitors…
        foreach ($packages as $page) {
            $this->get($page->url())->assertDontSee($page->package_name); // 404, or the section's "coming soon"
        }
        $this->get('/safaris')->assertDontSee('7-Day Classic Tanzania Safari');
    }

    public function test_every_package_has_a_full_itinerary_and_a_route_map(): void
    {
        $this->seed(SafariCollectionSeeder::class);

        foreach (SafariCollectionSeeder::packages() as $spec) {
            $page = TourPage::where('package_name', $spec['name'])->sole();

            $this->assertSame($spec['days'].' Days', $page->duration);
            $lastLabel = collect($page->itinerary)->last()['day'];
            $this->assertMatchesRegularExpression('/'.$spec['days'].'$/', $lastLabel, $spec['name'].' ends on its last day');
            $this->assertGreaterThanOrEqual(3, count(RouteMap::stops($page->itinerary)), $spec['name'].' has a route map');
            $this->assertNotEmpty($page->included);
            $this->assertNotEmpty($page->overview_body);
        }
    }

    public function test_admins_can_preview_the_drafts(): void
    {
        $this->seed(SafariCollectionSeeder::class);
        $this->actingAs(User::factory()->create());

        $page = TourPage::where('package_name', '16-Day Ultimate Northern Circuit Safari')->sole();

        $this->get($page->url())
            ->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('Price on request')
            ->assertSee('Lake Natron')
            ->assertSee('Empakaai Crater hike')
            ->assertSee('Lake Eyasi and the Hadzabe')
            ->assertSee('data-route-map', false);
    }

    public function test_running_it_again_adds_nothing_and_keeps_edits(): void
    {
        $this->seed(SafariCollectionSeeder::class);
        $page = TourPage::where('package_name', '7-Day Classic Tanzania Safari')->sole();
        $page->update(['price_from' => 1850, 'is_published' => true]);

        $this->seed(SafariCollectionSeeder::class);

        $this->assertSame(15, TourPage::count());
        $this->assertSame(1850, $page->fresh()->price_from);
        $this->get($page->fresh()->url())->assertOk()->assertSee('$1,850');
    }
}
