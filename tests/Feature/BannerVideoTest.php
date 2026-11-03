<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\PageBanners\Pages\EditPageBanner;
use App\Filament\Resources\TourPages\NorthernCircuitPages\Pages\EditNorthernCircuitPage;
use App\Models\PageBanner;
use App\Models\TourPage;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BannerVideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        PageBanner::ensureDefaults();
    }

    public function test_admin_attaches_a_video_to_a_page_banner(): void
    {
        $this->actingAs(User::factory()->create());
        $banner = PageBanner::firstWhere('key', 'vehicles');

        Livewire::test(EditPageBanner::class, ['record' => $banner->getRouteKey()])
            ->fillForm([
                'image' => [UploadedFile::fake()->image('poster.jpg', 1920, 900)],
                'video' => [UploadedFile::fake()->create('hero.mp4', 4096, 'video/mp4')],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $banner->refresh();
        Storage::disk('public')->assertExists($banner->video);
        $this->assertStringStartsWith('banners/videos/', $banner->video);

        $html = $this->get('/vehicles')->assertOk()->getContent();
        $this->assertStringContainsString('<video', $html);
        $this->assertStringContainsString('class="page-banner__video"', $html);
        $this->assertStringContainsString('/storage/'.$banner->video, $html);
        $this->assertStringContainsString('poster="'.asset('storage/'.$banner->image).'"', $html);
        $this->assertStringContainsString('type="video/mp4"', $html);
        $this->assertStringContainsString('muted', $html);
    }

    public function test_without_a_video_there_is_no_video_element(): void
    {
        foreach (['/vehicles', '/contact', '/faq', '/reviews', '/safaris', '/accommodations', '/destinations'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('page-banner__video', false);
        }
    }

    public function test_every_banner_page_shows_the_video_when_one_is_attached(): void
    {
        PageBanner::query()->update(['video' => 'banners/videos/loop.webm']);

        foreach (['/vehicles', '/contact', '/faq', '/reviews', '/safaris', '/accommodations', '/destinations', '/policy', '/terms-and-conditions'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('class="page-banner__video"', false)
                ->assertSee('type="video/webm"', false);
        }
    }

    public function test_only_short_mp4_or_webm_videos_are_accepted(): void
    {
        $this->actingAs(User::factory()->create());
        $banner = PageBanner::firstWhere('key', 'faq');

        // Oversized files are turned away at upload time (Livewire's limit), so check the field's own rules.
        Livewire::test(EditPageBanner::class, ['record' => $banner->getRouteKey()])
            ->assertFormFieldExists('video', fn (FileUpload $field) => $field->getMaxSize() === 30720
                && $field->getAcceptedFileTypes() === ['video/mp4', 'video/webm']
                && $field->getDirectory() === 'banners/videos');

        $this->assertSame(['required', 'file', 'max:30720'], config('livewire.temporary_file_upload.rules'));

        // A wrong file type is rejected when saving.
        Livewire::test(EditPageBanner::class, ['record' => $banner->getRouteKey()])
            ->fillForm(['video' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')]])
            ->call('save')
            ->assertHasFormErrors(['video']);

        $this->assertNull($banner->fresh()->video);
    }

    public function test_a_package_can_have_a_banner_video(): void
    {
        $this->actingAs(User::factory()->create());
        Storage::disk('public')->put('tour-pages/northern-circuit/banner.jpg', UploadedFile::fake()->image('banner.jpg')->getContent());
        $page = TourPage::create([
            'category' => TourCategory::NorthernCircuit,
            'title' => 'Big Cats',
            'slug' => 'big-cats',
            'package_name' => 'Big Cats Safari',
            'tier' => SafariTier::Classic,
            'is_published' => true,
            'banner_image' => 'tour-pages/northern-circuit/banner.jpg',
        ]);

        $this->get($page->url())->assertOk()->assertDontSee('page-banner__video', false);

        Livewire::test(EditNorthernCircuitPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['banner_video' => [UploadedFile::fake()->create('cats.mp4', 3000, 'video/mp4')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $page->refresh();
        $this->assertStringStartsWith('tour-pages/videos/', $page->banner_video);

        $this->get($page->url())
            ->assertOk()
            ->assertSee('class="page-banner__video"', false)
            ->assertSee('/storage/'.$page->banner_video, false)
            ->assertSee('poster="'.asset('storage/tour-pages/northern-circuit/banner.jpg').'"', false);
    }
}
