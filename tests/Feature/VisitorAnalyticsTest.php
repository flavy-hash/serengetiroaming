<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\PageViews\Pages\ListPageViews;
use App\Filament\Widgets\TopToursViewedChart;
use App\Filament\Widgets\TrafficSourcesChart;
use App\Filament\Widgets\VisitorStatsOverview;
use App\Filament\Widgets\VisitorsTrendChart;
use App\Http\Middleware\LogPageView;
use App\Models\PageView;
use App\Models\TourPage;
use App\Models\User;
use App\Support\GeoCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VisitorAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1';

    private string $geoDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geoDb = storage_path('framework/testing/geo/country.mmdb');
        config(['analytics.geo_db_path' => $this->geoDb, 'analytics.enabled' => true]);
        File::deleteDirectory(dirname($this->geoDb));
        GeoCountry::reset();
    }

    protected function tearDown(): void
    {
        GeoCountry::reset();
        File::deleteDirectory(dirname($this->geoDb));

        parent::tearDown();
    }

    private function visit(string $uri, array $headers = [], array $server = []): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9', ...$server])
            ->withHeaders(['User-Agent' => self::BROWSER, ...$headers])
            ->get($uri)
            ->assertOk();
    }

    private function package(): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar',
            'slug' => 'beach-escape',
            'package_name' => 'Zanzibar Beach Escape',
            'tier' => SafariTier::Classic,
            'is_published' => true,
        ]);
    }

    /**
     * A minimal valid MaxMind DB: every IPv4 address in 0.0.0.0/1 is in {country: {iso_code: DE}}.
     */
    private static function fixtureDatabase(string $type = 'DBIP-Country-Lite'): string
    {
        $str = fn (string $s) => chr(0x40 | strlen($s)).$s;
        $u16 = fn (int $n) => chr(0xA0 | 2).pack('n', $n);
        $u32 = fn (int $n) => chr(0xC0 | 4).pack('N', $n);

        // One node, 24-bit records: left (first bit 0) → data offset 0, right → "not found".
        $nodeCount = 1;
        $tree = substr(pack('N', $nodeCount + 16), 1).substr(pack('N', $nodeCount), 1);
        $data = "\xE1".$str('country')."\xE1".$str('iso_code').$str('DE');

        $metadata = "\xE9" // map, 9 entries
            .$str('node_count').$u32($nodeCount)
            .$str('record_size').$u16(24)
            .$str('ip_version').$u16(4)
            .$str('database_type').$str($type)
            .$str('languages')."\x00\x04"            // empty array
            .$str('binary_format_major_version').$u16(2)
            .$str('binary_format_minor_version').$u16(0)
            .$str('build_epoch')."\x01\x02\x01"      // uint64 = 1
            .$str('description')."\xE0";             // empty map

        return $tree.str_repeat("\0", 16).$data."\xAB\xCD\xEFMaxMind.com".$metadata;
    }

    public function test_a_visit_is_logged_without_storing_the_ip(): void
    {
        $this->visit('/', ['Referer' => 'https://www.google.com/search?q=safari', 'CF-IPCountry' => 'gb']);

        $view = PageView::sole();
        $this->assertSame('/', $view->path);
        $this->assertSame('home', $view->page_type);
        $this->assertSame('desktop', $view->device);
        $this->assertSame('google.com', $view->referrer_host);
        $this->assertSame('GB', $view->country);
        $this->assertSame(LogPageView::visitorHash('203.0.113.9', self::BROWSER), $view->visitor_hash);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $view->visitor_hash);
        $this->assertStringNotContainsString('203.0.113.9', json_encode($view->getAttributes()));
    }

    public function test_package_views_record_the_package_device_and_own_referrer(): void
    {
        $page = $this->package();

        $this->visit('/zanzibar/beach-escape', ['User-Agent' => self::IPHONE, 'Referer' => url('/safaris'), 'CF-IPCountry' => 'XX']);

        $view = PageView::sole();
        $this->assertSame('package', $view->page_type);
        $this->assertSame($page->id, $view->package_id);
        $this->assertSame('mobile', $view->device);
        $this->assertNull($view->referrer_host); // internal navigation
        $this->assertNull($view->country);       // XX = unknown

        $this->assertSame('tablet', LogPageView::device('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X)'));
        $this->assertSame('tablet', LogPageView::device('Mozilla/5.0 (Linux; Android 14; SM-X710) Safari/537.36'));
        $this->assertSame('mobile', LogPageView::device('Mozilla/5.0 (Linux; Android 14; Pixel 8) Mobile Safari/537.36'));

        // Deleting the package keeps the view.
        $page->delete();
        $this->assertNull($view->fresh()->package_id);
    }

    public function test_skipped_requests(): void
    {
        $this->package();

        $this->visit('/', ['DNT' => '1']);
        $this->visit('/', ['Sec-GPC' => '1']);
        $this->visit('/', ['Sec-Purpose' => 'prefetch;prerender']);
        $this->visit('/', ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']);
        $this->visit('/', ['User-Agent' => 'curl/8.4.0']);
        $this->withHeaders(['User-Agent' => ''])->get('/')->assertOk();
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/no-such-page')->assertNotFound();
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/sitemap.xml')->assertOk();
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/zanzibar/beach-escape/itinerary.pdf')->assertOk();

        $this->actingAs(User::factory()->create());
        $this->visit('/');
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/admin')->assertOk();

        $this->assertSame(0, PageView::count());
    }

    public function test_analytics_can_be_switched_off(): void
    {
        config(['analytics.enabled' => false]);

        $this->visit('/');

        $this->assertSame(0, PageView::count());
    }

    public function test_the_visitor_hash_changes_every_day(): void
    {
        $today = LogPageView::visitorHash('203.0.113.9', self::BROWSER);

        $this->assertSame($today, LogPageView::visitorHash('203.0.113.9', self::BROWSER));
        $this->assertNotSame($today, LogPageView::visitorHash('203.0.113.10', self::BROWSER));

        $this->travel(1)->days();
        $this->assertNotSame($today, LogPageView::visitorHash('203.0.113.9', self::BROWSER));
    }

    public function test_old_views_are_pruned(): void
    {
        config(['analytics.retention_days' => 180]);
        PageView::create(['visitor_hash' => str_repeat('a', 16), 'path' => '/old', 'created_at' => now()->subDays(181)]);
        PageView::create(['visitor_hash' => str_repeat('b', 16), 'path' => '/new', 'created_at' => now()->subDays(179)]);

        $this->artisan('model:prune', ['--model' => [PageView::class]])->assertSuccessful();

        $this->assertSame(['/new'], PageView::pluck('path')->all());
    }

    public function test_country_comes_from_the_local_database_when_there_is_no_header(): void
    {
        File::ensureDirectoryExists(dirname($this->geoDb));
        File::put($this->geoDb, self::fixtureDatabase());

        $this->assertSame('DE', GeoCountry::lookup('8.8.8.8'));
        $this->assertNull(GeoCountry::lookup('200.1.1.1'));   // not in the database
        $this->assertNull(GeoCountry::lookup('10.0.0.1'));    // private
        $this->assertNull(GeoCountry::lookup('127.0.0.1'));   // loopback

        $this->visit('/', [], ['REMOTE_ADDR' => '8.8.4.4']);
        $this->assertSame('DE', PageView::sole()->country);

        $this->assertSame('🇩🇪 Germany', PageView::countryLabel('DE'));
    }

    public function test_no_database_means_no_country(): void
    {
        $this->visit('/', [], ['REMOTE_ADDR' => '8.8.4.4']);

        $this->assertNull(PageView::sole()->country);
    }

    public function test_geo_database_command_falls_back_to_last_month_and_validates(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 3));

        Http::fake([
            '*dbip-country-lite-2026-10.mmdb.gz' => Http::response('Not found', 404),
            '*dbip-country-lite-2026-09.mmdb.gz' => Http::response(gzencode(self::fixtureDatabase())),
        ]);

        $this->artisan('analytics:update-geo-db')->assertSuccessful();
        $this->assertSame('DE', GeoCountry::lookup('8.8.8.8'));
        $this->assertSame([], glob(dirname($this->geoDb).'/*.{new,gz}', GLOB_BRACE));

        // A corrupt download never replaces the working database (later months only match this stub).
        $this->travelTo(now()->setDate(2026, 12, 3));
        Http::fake(['*' => Http::response(gzencode('not a database'))]);
        $this->artisan('analytics:update-geo-db')->assertFailed();
        GeoCountry::reset();
        $this->assertSame('DE', GeoCountry::lookup('8.8.8.8'));
    }

    public function test_admin_insights_screen_and_widgets(): void
    {
        $this->actingAs(User::factory()->create());
        $page = $this->package();

        PageView::create(['visitor_hash' => str_repeat('a', 16), 'path' => '/zanzibar/beach-escape', 'page_type' => 'package', 'package_id' => $page->id, 'device' => 'mobile', 'country' => 'DE', 'referrer_host' => 'google.com', 'created_at' => now()]);
        PageView::create(['visitor_hash' => str_repeat('a', 16), 'path' => '/', 'page_type' => 'home', 'device' => 'mobile', 'country' => 'DE', 'created_at' => now()]);
        PageView::create(['visitor_hash' => str_repeat('b', 16), 'path' => '/', 'page_type' => 'home', 'device' => 'desktop', 'country' => 'US', 'created_at' => now()->subDays(2)]);

        $this->get('/admin/insights/visitors')
            ->assertOk()
            ->assertSee('IP geolocation by DB-IP')
            ->assertSee('Not collected:');

        Livewire::test(ListPageViews::class)
            ->assertCanSeeTableRecords(PageView::all())
            ->assertSee('Zanzibar Beach Escape')
            ->assertSee('🇩🇪 Germany')
            ->filterTable('country', 'US')
            ->assertCountTableRecords(1);

        Livewire::test(VisitorStatsOverview::class)->assertSee('Visitors today')->assertSee('3 in the last 7 days', false);
        Livewire::test(VisitorsTrendChart::class)->assertOk();
        Livewire::test(TopToursViewedChart::class)->assertOk();
        Livewire::test(TrafficSourcesChart::class)->assertOk();
    }

    public function test_privacy_policy_mentions_the_statistics_and_strips_html(): void
    {
        $this->get('/privacy')->assertRedirect('/policy');

        $this->get('/policy')
            ->assertOk()
            ->assertSee('Anonymous visitor statistics')
            ->assertSee('without storing your IP address');

        \App\Models\Setting::put('privacy_policy', "## Our policy\n\n<script>alert(1)</script>\n\n[bad](javascript:alert(1))");

        $this->get('/policy')
            ->assertOk()
            ->assertSee('Our policy')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert', false);
    }
}
