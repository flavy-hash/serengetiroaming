<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\ListKilimanjaroPages;
use App\Models\TourPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Admin photos must load from the address the site is opened on, whatever APP_URL says. */
class AdminImageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_disk_urls_do_not_depend_on_app_url(): void
    {
        config(['app.url' => 'http://localhost']);

        $this->assertSame('/storage/tour-pages/a.jpg', Storage::disk('public')->url('tour-pages/a.jpg'));
    }

    public function test_package_list_thumbnails_use_the_site_address(): void
    {
        $this->actingAs(User::factory()->create());

        $page = TourPage::create([
            'category' => TourCategory::Kilimanjaro, 'title' => 'Machame', 'slug' => 'machame',
            'package_name' => 'Machame Route', 'tier' => SafariTier::Classic, 'is_published' => true,
            'banner_image' => 'tour-pages/kilimanjaro/mawenzi.jpg',
        ]);

        Livewire::test(ListKilimanjaroPages::class)
            ->assertCanSeeTableRecords([$page])
            ->assertTableColumnStateSet('banner_image', asset('storage/tour-pages/kilimanjaro/mawenzi.jpg'), $page);
    }
}
