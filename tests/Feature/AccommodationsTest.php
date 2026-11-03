<?php

namespace Tests\Feature;

use App\Enums\AccommodationLevel;
use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\Accommodations\Pages\ManageAccommodations;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\EditZanzibarPage;
use App\Models\Accommodation;
use App\Models\TourPage;
use App\Models\User;
use Database\Seeders\AccommodationSeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccommodationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function stay(array $attributes = []): Accommodation
    {
        return Accommodation::create([
            'name' => 'Serengeti Serena Safari Lodge',
            'location' => 'Serengeti',
            'type' => 'Lodge',
            'level' => AccommodationLevel::Luxury,
            'description' => 'Views across the plains.',
            'is_published' => true,
            'sort_order' => 1,
            ...$attributes,
        ]);
    }

    public function test_admin_can_create_edit_and_delete_a_stay(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageAccommodations::class)
            ->callAction(CreateAction::class, [
                'name' => 'Tarangire Elephant Springs',
                'location' => 'Tarangire',
                'type' => 'Lodge',
                'level' => AccommodationLevel::MidRange->value,
                'is_published' => true,
            ])
            ->assertHasNoActionErrors();

        $stay = Accommodation::firstOrFail();
        $this->assertSame('Tarangire', $stay->location);
        $this->assertSame('tarangire', $stay->location_key);

        Livewire::test(ManageAccommodations::class)
            ->assertCanSeeTableRecords([$stay])
            ->callTableAction(EditAction::class, $stay, ['name' => 'Elephant Springs'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('Elephant Springs', $stay->fresh()->name);

        Livewire::test(ManageAccommodations::class)->callTableAction(DeleteAction::class, $stay);
        $this->assertModelMissing($stay);
    }

    public function test_name_and_location_are_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageAccommodations::class)
            ->callAction(CreateAction::class, ['name' => '', 'location' => ''])
            ->assertHasActionErrors(['name' => 'required', 'location' => 'required']);
    }

    public function test_public_page_lists_published_stays_with_location_chips(): void
    {
        $this->stay();
        $this->stay(['name' => 'Tarangire Elephant Springs', 'location' => 'Tarangire', 'sort_order' => 2]);
        $this->stay(['name' => 'Secret Draft Camp', 'location' => 'Ruaha', 'is_published' => false]);

        $this->get('/accommodations')
            ->assertOk()
            ->assertSee('Serengeti Serena Safari Lodge')
            ->assertSee('Tarangire Elephant Springs')
            ->assertDontSee('Secret Draft Camp')
            ->assertSee('All areas')
            ->assertDontSee('?location=ruaha', false) // no chip for an area with only hidden stays
            ->assertSee(e(route('contact', ['subject' => 'Stay: Tarangire Elephant Springs'])), false);
    }

    public function test_location_filter_and_unknown_location(): void
    {
        $this->stay();
        $this->stay(['name' => 'Tarangire Elephant Springs', 'location' => 'Tarangire', 'sort_order' => 2]);

        $this->get('/accommodations?location=tarangire')
            ->assertOk()
            ->assertSee('Stays in Tarangire')
            ->assertSee('Tarangire Elephant Springs')
            ->assertDontSee('Serengeti Serena Safari Lodge');

        // Unknown values are ignored: everything is shown, "All areas" is active.
        $this->get('/accommodations?location=atlantis')
            ->assertOk()
            ->assertSee('Tarangire Elephant Springs')
            ->assertSee('Serengeti Serena Safari Lodge')
            ->assertDontSee('Stays in');
    }

    public function test_contact_form_is_prefilled_from_the_enquire_link(): void
    {
        $this->get('/contact?subject='.urlencode('Stay: Tarangire <b>Springs</b>'))
            ->assertOk()
            ->assertSee('value="Stay: Tarangire &lt;b&gt;Springs&lt;/b&gt;"', false);
    }

    public function test_seeder_only_runs_on_an_empty_table(): void
    {
        $this->seed(AccommodationSeeder::class);
        $this->assertSame(5, Accommodation::count());

        Accommodation::first()->update(['name' => 'Renamed by admin']);
        $this->seed(AccommodationSeeder::class);

        $this->assertSame(5, Accommodation::count());
        $this->assertTrue(Accommodation::where('name', 'Renamed by admin')->exists());
    }

    public function test_picking_a_stay_copies_its_photo_name_and_area_into_the_itinerary_day(): void
    {
        $this->actingAs(User::factory()->create());

        Storage::disk('public')->put('accommodations/serena.jpg', UploadedFile::fake()->image('serena.jpg')->getContent());
        $stay = $this->stay(['image' => 'accommodations/serena.jpg']);

        $page = TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar',
            'slug' => 'zanzibar-escape',
            'package_name' => 'Zanzibar Escape',
            'tier' => SafariTier::Classic,
            'is_published' => true,
            'itinerary' => [['day' => 'Day 1', 'title' => 'Game drive']],
        ]);

        $component = Livewire::test(EditZanzibarPage::class, ['record' => $page->getRouteKey()]);
        $key = array_key_first($component->get('data.itinerary'));

        $component->set("data.itinerary.{$key}.accommodation_id", $stay->id)
            ->assertSet("data.itinerary.{$key}.image_caption", 'Serengeti Serena Safari Lodge')
            ->assertSet("data.itinerary.{$key}.accommodation", 'Serengeti');
        $this->assertContains('accommodations/serena.jpg', (array) $component->get("data.itinerary.{$key}.image"));

        // An area the admin already typed is kept.
        $component->set("data.itinerary.{$key}.accommodation", 'Seronera')
            ->set("data.itinerary.{$key}.accommodation_id", null)
            ->set("data.itinerary.{$key}.accommodation_id", $stay->id)
            ->assertSet("data.itinerary.{$key}.accommodation", 'Seronera')
            ->call('save')
            ->assertHasNoFormErrors();

        $day = $page->fresh()->itinerary[0];
        $this->assertSame('accommodations/serena.jpg', $day['image']);
        $this->assertSame('Serengeti Serena Safari Lodge', $day['image_caption']);
        $this->assertEquals($stay->id, $day['accommodation_id']);
    }
}
