<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetStripTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(string $name, array $attributes = []): Vehicle
    {
        return Vehicle::create([
            'name' => $name,
            'category' => 'Safari 4×4',
            'seats' => 7,
            'image' => 'vehicles/'.str($name)->slug().'.jpg',
            'is_published' => true,
            'sort_order' => Vehicle::count() + 1,
            ...$attributes,
        ]);
    }

    public function test_the_homepage_shows_the_vehicles_that_have_photos(): void
    {
        $cruiser = $this->vehicle('Toyota Land Cruiser');
        $this->vehicle('Toyota Coaster', ['seats' => 25, 'category' => 'Group transfer']);
        $this->vehicle('Hidden Van', ['is_published' => false]);
        $this->vehicle('Photoless Van', ['image' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['What travelers are saying', 'Our Fleet', 'Explore by destination']) // below the reviews and activities
            ->assertSee('class="fleet-marquee', false)
            ->assertSee('Toyota Land Cruiser')
            ->assertSee('Toyota Coaster')
            ->assertSee('25 seats')
            ->assertSee('/storage/vehicles/toyota-land-cruiser.jpg', false)
            ->assertSee(route('vehicles').'#'.$cruiser->anchor(), false)
            ->assertSee(route('vehicles'), false)
            ->assertDontSee('Hidden Van')
            ->assertDontSee('Photoless Van');
    }

    public function test_the_repeated_copy_is_hidden_from_assistive_technology(): void
    {
        $this->vehicle('Toyota Land Cruiser');
        $this->vehicle('Toyota Coaster');

        $html = $this->get('/')->assertOk()->getContent();

        // Two identical strips so the loop has no gap; only the first is exposed to screen readers and the keyboard.
        $this->assertSame(2, substr_count($html, 'class="fleet-marquee__set"'));
        $this->assertSame(1, substr_count($html, 'class="fleet-marquee__set" aria-hidden="true"'));
        $this->assertStringContainsString('tabindex="-1"', $html);
        // 2 vehicles are repeated until each strip holds at least 8 cards: 8 cards x 2 strips.
        $this->assertSame(16, substr_count($html, 'class="fleet-card"'));
    }

    public function test_the_strip_is_hidden_until_two_vehicles_have_photos(): void
    {
        $this->get('/')->assertOk()->assertDontSee('fleet-marquee', false);

        $this->vehicle('Toyota Land Cruiser');
        $this->get('/')->assertOk()->assertDontSee('fleet-marquee', false);

        $this->vehicle('Toyota Coaster');
        $this->get('/')->assertOk()->assertSee('fleet-marquee', false);
    }
}
