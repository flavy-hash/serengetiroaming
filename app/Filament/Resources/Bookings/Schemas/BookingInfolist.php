<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Trip')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('trip')
                            ->columnSpanFull(),
                        TextEntry::make('travellers'),
                        TextEntry::make('departure_date')
                            ->label('Departure date')
                            ->date()
                            ->placeholder('Flexible'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Received')
                            ->since()
                            ->dateTimeTooltip(),
                    ]),
                Section::make('Guest')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->url(fn ($record) => "mailto:{$record->email}")
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Phone / WhatsApp')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
