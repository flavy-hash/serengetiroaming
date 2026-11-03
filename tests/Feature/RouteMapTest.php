<?php

namespace Tests\Feature;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\EditZanzibarPage;
use App\Models\TourPage;
use App\Models\User;
use App\Support\RouteMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RouteMapTest extends TestCase
{
    use RefreshDatabase;

    private function package(array $itinerary): TourPage
    {
        return TourPage::create([
            'category' => TourCategory::Zanzibar,
            'title' => 'Zanzibar',
            'slug' => 'island-escape',
            'package_name' => 'Island Escape',
            'tier' => SafariTier::Classic,
            'is_published' => true,
            'itinerary' => $itinerary,
        ]);
    }

    public function test_places_are_guessed_from_the_day_text(): void
    {
        $this->assertSame('arusha', RouteMap::guess('Arrival in Arusha'));
        // The place mentioned last wins.
        $this->assertSame('ngorongoro', RouteMap::guess('Serengeti to Ngorongoro Crater'));
        // A longer, more specific phrase beats a shorter one ending at the same spot.
        $this->assertSame('serengeti-north', RouteMap::guess('Game drive in Northern Serengeti'));
        // Whole words only.
        $this->assertNull(RouteMap::guess('Departure lounge'));
        $this->assertNull(RouteMap::guess(null));

        // Title first, then photo caption, then overnight area.
        $this->assertSame('tarangire', RouteMap::guessForDay(['title' => 'Game drive', 'hero_caption' => 'Elephants in Tarangire', 'accommodation' => 'Karatu']));
        $this->assertSame('karatu', RouteMap::guessForDay(['title' => 'Game drive', 'accommodation' => 'Karatu']));
    }

    public function test_consecutive_days_at_one_place_are_merged(): void
    {
        $stops = RouteMap::stops([
            ['title' => 'Arrival in Stone Town'],
            ['title' => 'Spice farm tour'],          // nothing recognised: skipped
            ['title' => 'Transfer to Nungwi'],
            ['title' => 'Beach day', 'map_place' => 'nungwi'],
            ['title' => 'Departure', 'map_place' => 'stone-town'],
        ]);

        $this->assertSame(['Day 1', 'Day 3-4', 'Day 5'], array_column($stops, 'label'));
        $this->assertSame(['Stone Town', 'Nungwi', 'Stone Town'], array_column($stops, 'name'));
        $this->assertSame('Day 1 Stone Town → Day 3-4 Nungwi → Day 5 Stone Town', RouteMap::describe($stops));
    }

    public function test_custom_coordinates_and_hidden_days(): void
    {
        $custom = RouteMap::resolve(['title' => 'Lake Eyasi', 'map_place' => 'custom', 'map_name' => 'Lake Eyasi', 'map_lat' => '-3.6123456', 'map_lng' => '35.0987654']);
        $this->assertSame(['key' => 'custom:-3.61235,35.09877', 'name' => 'Lake Eyasi', 'lat' => -3.61235, 'lng' => 35.09877], $custom);

        // Invalid coordinates are ignored rather than breaking the map.
        $this->assertNull(RouteMap::resolve(['map_place' => 'custom', 'map_lat' => '200', 'map_lng' => '35']));
        $this->assertNull(RouteMap::resolve(['map_place' => 'custom', 'map_lat' => 'abc', 'map_lng' => '35']));

        // "Don't show on the map" wins over a recognisable title.
        $this->assertNull(RouteMap::resolve(['title' => 'Arusha', 'map_place' => 'none']));

        // An unknown key falls back to guessing.
        $this->assertSame('arusha', RouteMap::resolve(['title' => 'Arusha', 'map_place' => 'atlantis'])['key']);
    }

    public function test_map_is_shown_only_with_at_least_two_stops(): void
    {
        $page = $this->package([
            ['day' => 'Day 1', 'title' => 'Arrival in Stone Town'],
            ['day' => 'Day 2', 'title' => 'Transfer to <Nungwi>'],
        ]);

        $this->get('/zanzibar')
            ->assertOk()
            ->assertSee('data-route-map', false)
            ->assertSee('aria-label="Route map: Day 1 Stone Town → Day 2 Nungwi"', false)
            ->assertSee('tile.openstreetmap.org', false)
            // Escaped in the page source.
            ->assertDontSee('<Nungwi>', false)
            ->assertSee('Transfer to &lt;Nungwi&gt;', false);

        $page->update(['itinerary' => [
            ['day' => 'Day 1', 'title' => 'Arrival in Stone Town'],
            ['day' => 'Day 2', 'title' => 'Free day', 'map_place' => 'none'],
        ]]);

        $this->get('/zanzibar')->assertOk()->assertDontSee('data-route-map', false);
    }

    public function test_mapbox_tiles_are_used_when_a_token_is_set(): void
    {
        config(['services.mapbox.token' => 'pk.test']);

        $this->assertStringContainsString('api.mapbox.com', RouteMap::tiles()['url']);
        $this->assertStringContainsString('access_token=pk.test', RouteMap::tiles()['url']);
    }

    public function test_first_day_opens_and_empty_optional_fields_are_hidden(): void
    {
        $this->package([
            ['day' => 'Day 1', 'title' => 'Arrival in Stone Town'],
            ['day' => 'Day 2', 'title' => 'Nungwi', 'meal_plan' => 'Half Board'],
        ]);

        $html = $this->get('/zanzibar')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<details class="group"  open >') + substr_count($html, '<details class="group" open>'), 'Only the first day is open');
        $this->assertStringNotContainsString('| Accommodation,', $html);
        $this->assertStringContainsString('Half Board', $html);
        $this->assertStringContainsString('Ask on WhatsApp', $html);
    }

    public function test_admin_can_pin_a_day_at_custom_coordinates(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $page = $this->package([['day' => 'Day 1', 'title' => 'Arrival in Stone Town']]);

        $component = Livewire::test(EditZanzibarPage::class, ['record' => $page->getRouteKey()])
            ->assertSee('Pinned at: Stone Town');
        $key = array_key_first($component->get('data.itinerary'));

        // Custom requires coordinates.
        $component->set("data.itinerary.{$key}.map_place", 'custom')
            ->call('save')
            ->assertHasFormErrors(["itinerary.{$key}.map_lat" => 'required']);

        $component->set("data.itinerary.{$key}.map_name", 'Forodhani Gardens')
            ->set("data.itinerary.{$key}.map_lat", -6.15912)
            ->set("data.itinerary.{$key}.map_lng", 39.18766)
            ->call('save')
            ->assertHasNoFormErrors();

        $day = $page->fresh()->itinerary[0];
        $this->assertSame('custom', $day['map_place']);
        $this->assertEquals(-6.15912, $day['map_lat']);
        $this->assertArrayNotHasKey('map_picker', $day);
        $this->assertSame('Forodhani Gardens', RouteMap::resolve($day)['name']);
    }
}
