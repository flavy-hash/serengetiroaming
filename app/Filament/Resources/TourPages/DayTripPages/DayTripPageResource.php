<?php

namespace App\Filament\Resources\TourPages\DayTripPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\TourPageResource;
use App\Filament\Resources\TourPages\DayTripPages\Pages\CreateDayTripPage;
use App\Filament\Resources\TourPages\DayTripPages\Pages\EditDayTripPage;
use App\Filament\Resources\TourPages\DayTripPages\Pages\ListDayTripPages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class DayTripPageResource extends TourPageResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $slug = 'pages/day-trips';

    public static function category(): TourCategory
    {
        return TourCategory::DayTrips;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDayTripPages::route('/'),
            'create' => CreateDayTripPage::route('/create'),
            'edit' => EditDayTripPage::route('/{record}/edit'),
        ];
    }
}
