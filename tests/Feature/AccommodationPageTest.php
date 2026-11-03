<?php

namespace Tests\Feature;

use App\Enums\AccommodationLevel;
use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\Accommodations\Pages\ManageAccommodations;
use App\Models\Accommodation;
use App\Models\TourPage;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Each stay has its own page (description + photo gallery), reached from the itinerary. */
class AccommodationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function stay(array $attributes = []): Accommodation
    {
        return Accommodation::create($attributes + [
            'name' => 'Serena Mountain Lodge',
            'location' => 'Stone Town',
            'type' => 'Hotel',
            'level' => AccommodationLevel::Luxury,
            'description' => "First paragraph about the lodge.\n\nSecond paragraph about the rooms.",
            'image' => 'accommodations/main.jpg',
            'gallery' => ['accommodations/gallery/pool.jpg', 'accommodations/gallery/room.jpg'],
            'is_published' => true,
        ]);
    }

    private function package(array $day): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Zanzibar, 'title' => 'Beach Escape', 'slug' => 'beach-escape',
            'package_name' => 'Beach Escape', 'tier' => SafariTier::Classic, 'is_published' => true,
            'itinerary' => [$day + ['day' => 'Day 1', 'title' => 'Arrive']],
        ]);
    }

    public function test_a_stay_gets_a_unique_web_address_from_its_name(): void
    {
        $first = $this->stay();
        $second = $this->stay();

        $this->assertSame('serena-mountain-lodge', $first->slug);
        $this->assertSame('serena-mountain-lodge-2', $second->slug);
        $this->assertSame(url('/accommodations/serena-mountain-lodge'), $first->url());
    }

    public function test_the_stay_page_shows_the_description_and_every_photo(): void
    {
        $stay = $this->stay();

        $this->get($stay->url())
            ->assertOk()
            ->assertSee('Serena Mountain Lodge')
            ->assertSee('First paragraph about the lodge.')
            ->assertSee('Second paragraph about the rooms.')
            ->assertSee('Photo Gallery')
            ->assertSee('/storage/accommodations/main.jpg', false)
            ->assertSee('/storage/accommodations/gallery/pool.jpg', false)
            ->assertSee('/storage/accommodations/gallery/room.jpg', false)
            ->assertSee('data-gallery-item', false);
    }

    public function test_a_stay_without_photos_still_has_a_page_and_no_gallery(): void
    {
        $stay = $this->stay(['image' => null, 'gallery' => null]);

        $this->get($stay->url())->assertOk()->assertDontSee('Photo Gallery');
    }

    public function test_hidden_stays_are_not_public_and_unknown_ones_are_404(): void
    {
        $hidden = $this->stay(['is_published' => false]);

        $this->get($hidden->url())->assertNotFound();
        $this->get('/accommodations/nothing-here')->assertNotFound();
    }

    public function test_the_stay_page_lists_the_packages_that_stay_there(): void
    {
        $stay = $this->stay();
        $this->package(['accommodation_id' => $stay->id]);

        $this->get($stay->url())->assertSee('Trips that Stay at Serena Mountain Lodge')->assertSee('Beach Escape');
    }

    public function test_the_itinerary_accommodation_links_to_the_stay_page(): void
    {
        $stay = $this->stay();
        $page = $this->package(['accommodation_id' => $stay->id, 'accommodation' => 'Stone Town', 'image' => 'a.jpg']);

        $this->get($page->url())
            ->assertOk()
            ->assertSee('href="'.$stay->url().'"', false)
            ->assertSee('Accommodation, Serena Mountain Lodge');
    }

    public function test_without_a_picked_stay_the_area_links_to_the_list_for_that_area(): void
    {
        $this->stay();
        $page = $this->package(['accommodation' => 'Stone Town']);

        $this->get($page->url())->assertSee('href="'.route('accommodations', ['location' => 'stone-town']).'"', false);
    }

    public function test_a_stay_named_in_the_day_is_linked_without_picking_it(): void
    {
        $stay = $this->stay(['name' => 'Tembo House Hotel', 'location' => 'Zanzibar']);
        $page = $this->package([
            'accommodation' => 'Stone Town',
            'image' => 'a.jpg',
            'image_caption' => 'Tembo House Hotel',
            'options' => [['tier' => 'Premium', 'label' => 'Tembo House Hotel, Stone Town'], ['tier' => 'Comfort', 'label' => '3-star hotel, Stone Town']],
        ]);

        $html = $this->get($page->url())->assertOk()->getContent();

        // The overnight line, the photo and the option that names the stay all go to its page...
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.$stay->url().'"'));
        $this->assertStringContainsString('Accommodation, Tembo House Hotel', $html);
        // ...while an option that names no stay stays plain text.
        $this->assertStringContainsString('3-star hotel, Stone Town', $html);
        $this->assertStringNotContainsString('>3-star hotel, Stone Town</a>', $html);
    }

    public function test_an_area_with_no_stays_is_plain_text(): void
    {
        $page = $this->package(['accommodation' => 'Nowhereland']);

        $this->get($page->url())
            ->assertSee('Accommodation, Nowhereland')
            ->assertDontSee('/accommodations?location=nowhereland', false);
    }

    public function test_stay_pages_are_in_the_sitemap(): void
    {
        $stay = $this->stay();
        $this->stay(['name' => 'Hidden Camp', 'is_published' => false]);

        $this->get('/sitemap.xml')->assertSee($stay->url())->assertDontSee('hidden-camp');
    }

    public function test_admin_can_add_gallery_photos_up_to_30_mb(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageAccommodations::class)
            ->callAction(CreateAction::class, [
                'name' => 'Beach House',
                'location' => 'Nungwi',
                'image' => [UploadedFile::fake()->image('main.jpg', 3000, 2000)->size(20 * 1024)],
                'gallery' => [
                    UploadedFile::fake()->image('one.jpg', 3000, 2000)->size(20 * 1024),
                    UploadedFile::fake()->image('two.jpg', 3000, 2000)->size(1024),
                ],
            ])
            ->assertHasNoActionErrors();

        $stay = Accommodation::firstWhere('name', 'Beach House');
        $this->assertSame('beach-house', $stay->slug);
        $this->assertCount(2, $stay->gallery);
        foreach ($stay->gallery as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }
}
