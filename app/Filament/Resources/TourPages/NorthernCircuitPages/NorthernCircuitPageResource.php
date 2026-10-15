<?php

namespace App\Filament\Resources\TourPages\NorthernCircuitPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\TourPageResource;
use App\Filament\Resources\TourPages\NorthernCircuitPages\Pages\CreateNorthernCircuitPage;
use App\Filament\Resources\TourPages\NorthernCircuitPages\Pages\EditNorthernCircuitPage;
use App\Filament\Resources\TourPages\NorthernCircuitPages\Pages\ListNorthernCircuitPages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class NorthernCircuitPageResource extends TourPageResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeEuropeAfrica;

    protected static ?string $slug = 'pages/northern-circuit';

    public static function category(): TourCategory
    {
        return TourCategory::NorthernCircuit;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNorthernCircuitPages::route('/'),
            'create' => CreateNorthernCircuitPage::route('/create'),
            'edit' => EditNorthernCircuitPage::route('/{record}/edit'),
        ];
    }
}
