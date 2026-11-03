<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Filament\Widgets\BookingsByStatusChart;
use App\Filament\Widgets\BookingStatsOverview;
use App\Filament\Widgets\EnquiriesChart;
use App\Filament\Widgets\LatestBookings;
use App\Filament\Widgets\PopularTripsChart;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function seedBookings(): void
    {
        foreach ([
            ['Zanzibar Beach Escape · 4 Days · From $650', BookingStatus::New, now()],
            ['Zanzibar Beach Escape · 4 Days · From $650', BookingStatus::Confirmed, now()->subDays(3)],
            ['Machame Route (Kilimanjaro) · 7 Days · From $1,650', BookingStatus::Contacted, now()->subMonths(2)],
        ] as [$trip, $status, $createdAt]) {
            $booking = Booking::create([
                'trip' => $trip,
                'name' => 'Guest',
                'email' => 'guest@example.com',
                'travellers' => 2,
                'status' => $status,
            ]);
            $booking->forceFill(['created_at' => $createdAt])->save();
        }
    }

    public function test_dashboard_renders_with_and_without_data(): void
    {
        $widgets = [BookingStatsOverview::class, EnquiriesChart::class, BookingsByStatusChart::class, PopularTripsChart::class, LatestBookings::class];

        $response = $this->get('/admin')->assertOk();
        foreach ($widgets as $widget) {
            $response->assertSeeLivewire($widget);
            Livewire::test($widget)->assertOk();
        }

        $this->seedBookings();

        $this->get('/admin')->assertOk();
    }

    public function test_stats_overview(): void
    {
        $this->seedBookings();

        Livewire::test(BookingStatsOverview::class)
            ->assertSee('New enquiries')
            ->assertSee('Waiting for a reply')
            ->assertSee('33%')
            ->assertSee('1 of 3 enquiries confirmed');
    }

    public function test_enquiries_chart_supports_every_filter(): void
    {
        $this->seedBookings();

        foreach (['7', '30', '90', '365'] as $filter) {
            Livewire::test(EnquiriesChart::class)->set('filter', $filter)->assertOk();
        }
    }

    public function test_bar_charts_and_latest_table(): void
    {
        $this->seedBookings();

        Livewire::test(BookingsByStatusChart::class)->assertOk();
        Livewire::test(PopularTripsChart::class)->assertOk();
        Livewire::test(LatestBookings::class)->assertCanSeeTableRecords(Booking::all());
    }
}
