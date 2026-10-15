<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Enums\QuoteSource;
use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Booking;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\TourPage;
use Filament\Resources\Pages\CreateRecord;
use Livewire\Attributes\Url;

/**
 * /admin/quotes/create?booking=12 starts the quote from that booking enquiry.
 */
class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;

    #[Url]
    public ?string $booking = null;

    protected function fillForm(): void
    {
        $booking = $this->sourceBooking();

        if (! $booking) {
            parent::fillForm();

            return;
        }

        $this->callHook('beforeFill');
        $this->form->fill(self::prefillFrom($booking));
        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['source'] = QuoteSource::Admin;
        $data['booking_id'] = $this->sourceBooking()?->getKey();

        return $data;
    }

    private function sourceBooking(): ?Booking
    {
        return ctype_digit((string) $this->booking) ? Booking::find($this->booking) : null;
    }

    /**
     * Form state for a quote answering a booking enquiry.
     *
     * @return array<string, mixed>
     */
    public static function prefillFrom(Booking $booking): array
    {
        $package = self::packageFor($booking);
        $adults = max(1, (int) $booking->travellers);
        $days = $package?->days();
        $start = $booking->departure_date;

        return [
            'customer_name' => $booking->name,
            'customer_email' => $booking->email,
            'customer_phone' => $booking->phone,
            'status' => QuoteStatus::Draft->value,
            'valid_until' => now()->addDays(Setting::quoteValidityDays())->toDateString(),
            'show_bank_details' => true,
            'package_id' => $package?->getKey(),
            'adults' => $adults,
            'children' => 0,
            'travel_start' => $start?->toDateString(),
            'travel_end' => $start && $days ? $start->copy()->addDays($days - 1)->toDateString() : null,
            'items' => Quote::itemsForPackage($package, $adults, 0),
            'discount' => 0,
            'notes' => filled($booking->notes) ? 'Your request: '.$booking->notes : null,
        ];
    }

    /**
     * The booking form stores the package's label ("Machame Route · 7 Days · From $1,650").
     */
    private static function packageFor(Booking $booking): ?TourPage
    {
        $packages = TourPage::query()->ordered()->get();

        return $packages->first(fn (TourPage $p) => $p->bookingLabel() === $booking->trip)
            ?? $packages->first(fn (TourPage $p) => filled($p->package_name) && str_starts_with((string) $booking->trip, $p->package_name));
    }
}
