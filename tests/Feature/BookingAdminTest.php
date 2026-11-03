<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingAdminTest extends TestCase
{
    use RefreshDatabase;

    private function booking(array $attributes = []): Booking
    {
        return Booking::create([
            'trip' => 'Zanzibar Beach Escape · 4 Days · From $650',
            'name' => 'Jane Traveller',
            'email' => 'jane@example.com',
            'travellers' => 2,
            ...$attributes,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/bookings')->assertRedirect('/admin/login');
    }

    public function test_public_enquiry_is_stored_as_new(): void
    {
        $this->postJson('/bookings', [
            'trip' => 'Machame Route (Kilimanjaro) · 7 Days · From $1,650',
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'travellers' => 3,
        ])->assertOk();

        $this->assertSame(BookingStatus::New, Booking::first()->status);
    }

    public function test_admin_can_list_view_and_update_bookings(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = $this->booking();

        $this->get('/admin/bookings')->assertOk()->assertSee('Jane Traveller');
        $this->get("/admin/bookings/{$booking->id}")->assertOk()->assertSee('jane@example.com');

        Livewire::test(ListBookings::class)
            ->assertCanSeeTableRecords([$booking])
            ->filterTable('status', [BookingStatus::Confirmed->value])
            ->assertCanNotSeeTableRecords([$booking]);

        Livewire::test(EditBooking::class, ['record' => $booking->getRouteKey()])
            ->fillForm(['status' => BookingStatus::Confirmed])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
    }
}
