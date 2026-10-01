<?php

namespace App\Filament\Resources\TourPages\SouthernCircuitPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\TourPageResource;
use App\Filament\Resources\TourPages\SouthernCircuitPages\Pages\CreateSouthernCircuitPage;
use App\Filament\Resources\TourPages\SouthernCircuitPages\Pages\EditSouthernCircuitPage;
use App\Filament\Resources\TourPages\SouthernCircuitPages\Pages\ListSouthernCircuitPages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class SouthernCircuitPageResource extends TourPageResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $slug = 'pages/southern-circuit';

    public static function category(): TourCategory
    {
        return TourCategory::SouthernCircuit;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSouthernCircuitPages::route('/'),
            'create' => CreateSouthernCircuitPage::route('/create'),
            'edit' => EditSouthernCircuitPage::route('/{record}/edit'),
        ];
    }
}
