<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Pages\ManageAboutPage;
use App\Filament\Resources\Destinations\Pages\EditDestination;
use App\Filament\Resources\Vehicles\Pages\ManageVehicles;
use App\Filament\Resources\PageBanners\Pages\EditPageBanner;
use App\Filament\Resources\TourPages\NorthernCircuitPages\Pages\EditNorthernCircuitPage;
use App\Models\Destination;
use App\Models\PageBanner;
use App\Models\TourPage;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HeroImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        PageBanner::ensureDefaults();
        $this->actingAs(User::factory()->create());
    }

    /** Every banner photo field accepts up to 30 MB and shrinks very wide photos while uploading. */
    private function isHeroImageField(): \Closure
    {
        return fn (FileUpload $field) => $field->getMaxSize() === 30720
            && $field->getImageResizeMode() === 'contain'
            && $field->getImageResizeTargetWidth() === '2560'
            && $field->getImageResizeUpscale() === false
            && in_array('image/*', (array) $field->getAcceptedFileTypes(), true);
    }

    public function test_page_banner_image_allows_30_mb(): void
    {
        $banner = PageBanner::firstWhere('key', 'contact');

        Livewire::test(EditPageBanner::class, ['record' => $banner->getRouteKey()])
            ->assertFormFieldExists('image', $this->isHeroImageField());

        // A 20 MB photo (over the old 5 MB limit) is saved.
        Livewire::test(EditPageBanner::class, ['record' => $banner->getRouteKey()])
            ->fillForm(['image' => [UploadedFile::fake()->image('big.jpg', 4000, 2000)->size(20 * 1024)]])
            ->call('save')
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertExists($banner->fresh()->image);
    }

    public function test_package_banner_image_allows_30_mb(): void
    {
        $page = TourPage::create([
            'category' => TourCategory::NorthernCircuit, 'title' => 'Big Cats', 'slug' => 'big-cats',
            'package_name' => 'Big Cats Safari', 'tier' => SafariTier::Classic, 'is_published' => true,
        ]);

        Livewire::test(EditNorthernCircuitPage::class, ['record' => $page->getRouteKey()])
            ->assertFormFieldExists('banner_image', $this->isHeroImageField())
            // Other package photos (itinerary days, stays…) keep their normal 5 MB limit.
            ->fillForm(['banner_image' => [UploadedFile::fake()->image('banner.jpg', 4000, 2000)->size(25 * 1024)]])
            ->call('save')
            ->assertHasNoFormErrors();

        Storage::disk('public')->assertExists($page->fresh()->banner_image);
    }

    public function test_about_and_destination_banner_photos_allow_30_mb(): void
    {
        Livewire::test(ManageAboutPage::class)->assertFormFieldExists('banner_image', $this->isHeroImageField());

        $destination = Destination::create(['name' => 'Serengeti', 'slug' => 'serengeti', 'tagline' => 'Plains']);
        Livewire::test(EditDestination::class, ['record' => $destination->getRouteKey()])
            ->assertFormFieldExists('image', $this->isHeroImageField());
    }

    public function test_our_story_image_and_team_photos_allow_30_mb(): void
    {
        Livewire::test(ManageAboutPage::class)->assertFormFieldExists('intro_image', $this->isHeroImageField());

        Livewire::test(ManageAboutPage::class)
            ->fillForm([
                'title' => 'About Us',
                'intro_image' => [UploadedFile::fake()->image('story.jpg', 4000, 2500)->size(20 * 1024)],
                'team' => [['name' => 'Asha', 'role' => 'Head Guide', 'photo' => [UploadedFile::fake()->image('asha.jpg', 3000, 3000)->size(20 * 1024)]]],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $about = \App\Models\AboutPage::first();
        Storage::disk('public')->assertExists($about->intro_image);
        Storage::disk('public')->assertExists($about->team[0]['photo']);
    }

    public function test_vehicle_photos_allow_30_mb(): void
    {
        // A 20 MB photo (over the old 5 MB limit) is accepted when adding a vehicle.
        Livewire::test(ManageVehicles::class)
            ->callAction(CreateAction::class, [
                'name' => 'Toyota Land Cruiser',
                'image' => [UploadedFile::fake()->image('cruiser.jpg', 4000, 2500)->size(20 * 1024)],
            ])
            ->assertHasNoActionErrors();

        $vehicle = Vehicle::sole();
        $this->assertStringStartsWith('vehicles/', $vehicle->image);
        Storage::disk('public')->assertExists($vehicle->image);
    }
}
