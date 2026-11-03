<?php

namespace Tests\Feature;

use App\Enums\TourCategory;
use App\Models\TourPage;
use App\Models\User;
use App\Support\RouteMap;
use Database\Seeders\MoreToursSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MoreToursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_it_adds_twenty_four_drafts_across_the_sections(): void
    {
        $this->seed(MoreToursSeeder::class);

        $this->assertSame(24, TourPage::count());
        $this->assertTrue(TourPage::all()->every(fn (TourPage $p) => ! $p->is_published && $p->price_from === null));

        $bySection = TourPage::all()->countBy(fn (TourPage $p) => $p->category->value);
        $this->assertSame(6, $bySection[TourCategory::SouthernCircuit->value]);
        $this->assertSame(4, $bySection[TourCategory::NorthernCircuit->value]);
        $this->assertSame(3, $bySection[TourCategory::Safaris->value]);
        $this->assertSame(4, $bySection[TourCategory::Zanzibar->value]);
        $this->assertSame(7, $bySection[TourCategory::Kilimanjaro->value]);
    }

    public function test_every_itinerary_is_complete_and_mapped(): void
    {
        $this->seed(MoreToursSeeder::class);

        foreach (MoreToursSeeder::packages() as $spec) {
            $page = TourPage::where('package_name', $spec['name'])->sole();

            $this->assertSame($spec['days'].' Days', $page->duration);
            $this->assertMatchesRegularExpression('/'.$spec['days'].'$/', collect($page->itinerary)->last()['day'], $spec['name']);
            // Single-island stays have one stop, so (by design) no route map; everything else has one.
            $singleIsland = in_array($spec['name'], ['6-Day Mafia Island Escape', '6-Day Pemba Island Escape'], true);
            $this->assertGreaterThanOrEqual($singleIsland ? 1 : 2, count(RouteMap::stops($page->itinerary)), $spec['name'].' route map');
            $this->assertNotEmpty($page->included);
        }
    }

    public function test_climbs_have_mountain_inclusions_and_camp_to_camp_days(): void
    {
        $this->seed(MoreToursSeeder::class);
        $this->actingAs(User::factory()->create());

        $page = TourPage::where('package_name', 'Rongai Route – 6-Day Climb')->sole();

        $this->assertContains('Professional mountain guides, assistant guides, porters and a cook', $page->included);
        $this->assertNotContains('Drinking water during game drives', $page->included);
        $this->assertSame('Route', $page->facts[1]['label']);

        $this->get($page->url())
            ->assertOk()
            ->assertSee('Mawenzi Tarn (4,330 m) to Kibo Hut (4,720 m)')
            ->assertSee('Summit day: Uhuru Peak (5,895 m)')
            ->assertSee('data-route-map', false);
    }

    public function test_beach_holidays_do_not_list_safari_inclusions(): void
    {
        $this->seed(MoreToursSeeder::class);

        $page = TourPage::where('package_name', '10-Day Zanzibar Island Holiday')->sole();

        $this->assertNotContains('Professional English-speaking driver-guide', $page->included);
        $this->assertSame('Beach holiday', $page->facts[3]['value']);

        $labels = collect($page->itinerary)->pluck('day')->all();
        $this->assertSame('Day 1', $labels[0]);
        $this->assertSame('Day 9 – 10', end($labels));
        $this->assertCount(9, $labels); // 8 days on the island + the 2-day departure entry
    }

    public function test_running_it_again_adds_nothing(): void
    {
        $this->seed(MoreToursSeeder::class);
        $this->seed(MoreToursSeeder::class);

        $this->assertSame(24, TourPage::count());
    }
}
