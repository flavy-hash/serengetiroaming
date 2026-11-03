<?php

namespace Tests\Feature;

use App\Filament\Resources\PageBanners\Pages\EditPageBanner;
use App\Filament\Resources\PageBanners\Pages\ListPageBanners;
use App\Models\PageBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PageBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_use_default_banners_until_edited(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Contact Us')->assertSee('/images/banners/contact.jpg', false); // default photo
        $this->get('/faq')->assertOk()->assertSee('Frequently Asked Questions');
        $this->get('/safaris')->assertOk()->assertSee('Tanzania Safari Adventures')->assertSee('020_The_lion_king');
    }

    public function test_admin_can_set_banner_images_and_text_for_contact_and_faq(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->get('/admin/page-banners')->assertOk();
        $this->assertSame(['accommodations', 'contact', 'destinations', 'faq', 'policy', 'reviews', 'safaris', 'terms', 'vehicles'], PageBanner::orderBy('key')->pluck('key')->all());

        Livewire::test(ListPageBanners::class)
            ->assertCanSeeTableRecords(PageBanner::all())
            ->assertActionDoesNotExist('create');

        $contact = PageBanner::firstWhere('key', 'contact');
        $this->get("/admin/page-banners/{$contact->id}/edit")->assertOk();

        Livewire::test(EditPageBanner::class, ['record' => $contact->getRouteKey()])
            ->fillForm([
                'image' => [UploadedFile::fake()->image('kilimanjaro.jpg', 1920, 900)],
                'title' => 'Say Jambo',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $contact->refresh();
        Storage::disk('public')->assertExists($contact->image);

        $this->get('/contact')
            ->assertSee('Say Jambo')
            ->assertSee('Talk to our safari experts') // empty text keeps the default
            ->assertSee('/storage/'.$contact->image);

        // Other pages are unaffected.
        $this->get('/faq')->assertSee('Frequently Asked Questions')->assertDontSee('/storage/banners/', false);
    }
}
