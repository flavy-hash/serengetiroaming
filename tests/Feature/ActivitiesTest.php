<?php

namespace Tests\Feature;

use App\Filament\Resources\Activities\Pages\ManageActivities;
use App\Models\Activity;
use App\Models\User;
use Database\Seeders\ActivitySeeder;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_homepage_shows_activities_below_the_reviews(): void
    {
        $this->seed(ActivitySeeder::class);
        Activity::create(['title' => 'Hidden Activity', 'is_published' => false, 'sort_order' => 99]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['What travelers are saying', 'Top activities on a Tanzania safari', 'Walking safaris', 'Boat safaris on the Rufiji', 'Maasai cultural visits', 'Coffee farm &amp; waterfall tours', 'Snorkelling in the Indian Ocean', 'Explore by destination'], false)
            ->assertSee('Rufiji River · Nyerere')
            ->assertSee('/storage/activities/boat-safari.jpg', false)
            ->assertSee('href="'.url('/destinations/nyerere').'"', false)
            ->assertDontSee('Hidden Activity');
    }

    public function test_section_is_hidden_without_activities_and_text_is_editable(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="activities"', false);

        $this->actingAs(User::factory()->create());
        Activity::create(['title' => 'Bird walks', 'description' => '<b>Early</b> start', 'link_url' => 'javascript:alert(1)']);

        Livewire::test(ManageActivities::class)
            ->callAction('sectionText', ['activities_eyebrow' => 'Extras', 'activities_heading' => 'Things to do', 'activities_intro' => 'Pick your favourites.'])
            ->assertHasNoActionErrors();

        $this->get('/')
            ->assertSee('Things to do')
            ->assertSee('Pick your favourites.')
            ->assertSee('&lt;b&gt;Early&lt;/b&gt; start', false)
            ->assertDontSee('javascript:alert', false); // unsafe links are never output
    }

    public function test_admin_can_add_edit_and_delete_activities(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageActivities::class)
            ->callAction(CreateAction::class, ['title' => 'Night game drives', 'kicker' => '20:00 EAT', 'link_url' => '/safaris', 'is_published' => true])
            ->assertHasNoActionErrors();

        $activity = Activity::sole();
        $this->assertSame('20:00 EAT', $activity->kicker);

        Livewire::test(ManageActivities::class)
            ->callAction(CreateAction::class, ['title' => 'Bad link', 'link_url' => 'javascript:alert(1)'])
            ->assertHasActionErrors(['link_url']);

        Livewire::test(ManageActivities::class)
            ->callTableAction(EditAction::class, $activity, ['title' => 'Night drives'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('Night drives', $activity->fresh()->title);

        Livewire::test(ManageActivities::class)->callTableAction(DeleteAction::class, $activity);
        $this->assertModelMissing($activity);
    }

    public function test_seeder_adds_activities_with_photos_once(): void
    {
        $this->seed(ActivitySeeder::class);
        $this->seed(ActivitySeeder::class);

        $this->assertSame(5, Activity::count());
        Activity::all()->each(fn (Activity $a) => Storage::disk('public')->assertExists($a->image));
    }
}
