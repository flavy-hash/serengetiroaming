<?php

namespace App\Filament\Resources\TourPages\KilimanjaroPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\TourPageResource;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\CreateKilimanjaroPage;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\EditKilimanjaroPage;
use App\Filament\Resources\TourPages\KilimanjaroPages\Pages\ListKilimanjaroPages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class KilimanjaroPageResource extends TourPageResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?string $slug = 'pages/kilimanjaro';

    public static function category(): TourCategory
    {
        return TourCategory::Kilimanjaro;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKilimanjaroPages::route('/'),
            'create' => CreateKilimanjaroPage::route('/create'),
            'edit' => EditKilimanjaroPage::route('/{record}/edit'),
        ];
    }
}
