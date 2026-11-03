<?php

namespace Tests\Feature;

use App\Enums\TourCategory;
use App\Models\TourPage;
use App\Models\User;
use Database\Seeders\ItineraryPackagesSeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\TourPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItineraryPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([TourPageSeeder::class, PackageSeeder::class, ItineraryPackagesSeeder::class]);
    }

    public function test_package_page_links_to_its_pdf(): void
    {
        $this->get('/kilimanjaro')
            ->assertOk()
            ->assertSee('Download Itinerary (PDF)')
            ->assertSee(url('/kilimanjaro/machame-route/itinerary.pdf'));
    }

    public function test_visitors_can_download_the_itinerary_pdf(): void
    {
        $response = $this->get('/kilimanjaro/machame-route/itinerary.pdf');

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('machame-route-itinerary.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_pdf_contains_the_itinerary(): void
    {
        $html = view('pdf.itinerary', ['page' => TourPage::where('slug', 'machame-route')->first()])->render();

        $this->assertStringContainsString('Summit Day: Uhuru Peak', $html);
        $this->assertStringContainsString('$1,800', $html);
        $this->assertStringContainsString('1–2 climbers', $html); // group prices
        $this->assertStringContainsString('Stella Point (5,752 m)', $html);
        $this->assertStringContainsString(config('site.email'), $html);
        // Images are embedded from local storage, not fetched over HTTP.
        $this->assertStringContainsString(storage_path('app/public/tour-pages/kilimanjaro/kilimanjaro-amboseli.jpg'), $html);
    }

    public function test_draft_pdfs_are_only_available_to_admins(): void
    {
        TourPage::create([
            'category' => TourCategory::Kilimanjaro,
            'title' => 'Lemosho',
            'slug' => 'lemosho',
            'package_name' => 'Lemosho Route',
            'is_published' => false,
        ]);

        $this->get('/kilimanjaro/lemosho/itinerary.pdf')->assertNotFound();
        $this->get('/kilimanjaro/nope/itinerary.pdf')->assertNotFound();

        $this->actingAs(User::factory()->create());
        $this->get('/kilimanjaro/lemosho/itinerary.pdf')->assertOk()->assertDownload('lemosho-route-itinerary.pdf');
    }
}
