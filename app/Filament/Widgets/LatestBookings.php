<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestBookings extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest enquiries';

    public function table(Table $table): Table
    {
        return $table
            ->query(Booking::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->dateTimeTooltip(),
                TextColumn::make('name')
                    ->description(fn (Booking $record) => $record->email),
                TextColumn::make('trip')
                    ->limit(40)
                    ->tooltip(fn (Booking $record) => $record->trip),
                TextColumn::make('travellers')
                    ->numeric(),
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date()
                    ->placeholder('Flexible'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordUrl(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->link()
                    ->url(BookingResource::getUrl('index')),
            ])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('Enquiries from the website booking form will show up here.');
    }
}
