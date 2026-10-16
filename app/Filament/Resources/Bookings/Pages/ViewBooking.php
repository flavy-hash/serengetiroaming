<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createQuote')
                ->label('Create quote')
                ->icon(Heroicon::OutlinedDocumentCurrencyDollar)
                ->url(fn () => QuoteResource::getUrl('create', ['booking' => $this->getRecord()->getKey()])),
            EditAction::make(),
        ];
    }
}
