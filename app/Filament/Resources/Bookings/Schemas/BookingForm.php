<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Trip')
                    ->columns(2)
                    ->schema([
                        TextInput::make('trip')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('travellers')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(1),
                        DatePicker::make('departure_date')
                            ->label('Departure date')
                            ->native(false),
                        Select::make('status')
                            ->options(BookingStatus::class)
                            ->default(BookingStatus::New)
                            ->required(),
                    ]),
                Section::make('Guest')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone / WhatsApp')
                            ->tel()
                            ->maxLength(50),
                        Textarea::make('notes')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
