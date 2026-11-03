<?php

namespace Tests\Feature;

use App\Enums\TourCategory;
use App\Models\Faq;
use App\Models\TourPage;
use Database\Seeders\ItineraryPackagesSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\TourPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([TourPageSeeder::class, PackageSeeder::class, ItineraryPackagesSeeder::class]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function structuredData(TestResponse $response): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $blocks);

        return collect($blocks[1])
            ->flatMap(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR)['@graph'])
            ->all();
    }

    private function ofType(TestResponse $response, string $type): ?array
    {
        return collect($this->structuredData($response))->firstWhere('@type', $type);
    }

    public function test_every_page_has_title_description_canonical_and_social_tags(): void
    {
        $response = $this->get('/safaris?tier=luxury');

        $response->assertOk()
            ->assertSee('<title>Luxury Safaris &amp; Tours | Serengeti Roaming</title>', false) // the filter shows in the title
            ->assertSee('<meta name="description" content="Every Tanzania safari', false)
            ->assertSee('<link rel="canonical" href="'.url('/safaris').'">', false) // filters don't create duplicate pages
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<link rel="icon" href="'.asset('favicon.ico').'"', false);

        $this->get('/')->assertSee('<title>Serengeti Roaming African Safaris – Tanzania Tour Operator</title>', false);
    }

    public function test_business_details_are_on_every_page(): void
    {
        $agency = $this->ofType($this->get('/contact'), 'TravelAgency');

        $this->assertSame('Serengeti Roaming African Safaris', $agency['name']);
        $this->assertSame('+255757127190', $agency['telephone']);
        $this->assertSame('TZ', $agency['address']['addressCountry']);
        $this->assertNotNull($this->ofType($this->get('/'), 'WebSite'));
    }

    public function test_package_pages_describe_the_trip(): void
    {
        $response = $this->get('/kilimanjaro');

        $response->assertSee('<title>Kilimanjaro: Machame Route – 6 Days | Serengeti Roaming</title>', false)
            ->assertSee('<meta property="og:image" content="'.asset('storage/tour-pages/kilimanjaro/kilimanjaro-amboseli.jpg').'">', false);

        $trip = $this->ofType($response, 'TouristTrip');
        $this->assertSame('Machame Route', $trip['name']);
        $this->assertSame(1800, $trip['offers']['price']);
        $this->assertSame('USD', $trip['offers']['priceCurrency']);
        $this->assertCount(6, $trip['itinerary']['itemListElement']);

        $crumbs = $this->ofType($this->get('/safaris/ngorongoro-crater-safari'), 'BreadcrumbList');
        $this->assertSame(['Home', 'Safaris', 'Ngorongoro Crater Safari'], array_column($crumbs['itemListElement'], 'name'));
    }

    public function test_admin_seo_title_overrides_the_default(): void
    {
        TourPage::where('slug', 'zanzibar-beach-escape')->update(['seo_title' => 'Zanzibar Holiday Packages 2026']);

        $this->get('/zanzibar')->assertSee('<title>Zanzibar Holiday Packages 2026 | Serengeti Roaming</title>', false);
    }

    public function test_faq_page_has_faq_structured_data(): void
    {
        Faq::create(['question' => 'Do I need a visa?', 'answer' => 'Most visitors can get one on arrival.', 'is_published' => true]);

        $faq = $this->ofType($this->get('/faq'), 'FAQPage');
        $this->assertSame('Do I need a visa?', $faq['mainEntity'][0]['name']);
    }

    public function test_coming_soon_and_draft_pages_are_kept_out_of_search(): void
    {
        $this->get('/day-trips')->assertSee('<meta name="robots" content="noindex, follow">', false);

        TourPage::create(['category' => TourCategory::DayTrips, 'tier' => 'budget', 'title' => 'Materuni', 'slug' => 'materuni', 'package_name' => 'Materuni Waterfalls', 'is_published' => false]);
        $this->actingAs(\App\Models\User::factory()->create());
        $this->get('/day-trips/materuni')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_sitemap_lists_only_public_pages(): void
    {
        TourPage::create(['category' => TourCategory::DayTrips, 'tier' => 'budget', 'title' => 'Draft', 'slug' => 'secret-draft', 'package_name' => 'Draft', 'is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.url('/safaris').'</loc>', false)
            ->assertSee('<loc>'.url('/kilimanjaro').'</loc>', false)
            ->assertSee('<loc>'.url('/safaris/ngorongoro-crater-safari').'</loc>', false)
            ->assertSee('<loc>'.route('faq').'</loc>', false)
            ->assertDontSee('secret-draft')
            ->assertDontSee('/admin');
    }

    public function test_robots_txt_blocks_everything_except_in_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap:');

        $this->app->detectEnvironment(fn () => 'production');

        $this->get('/robots.txt')
            ->assertSee('Disallow: /admin')
            ->assertSee('Allow: /')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }
}
