<?php

namespace App\Filament\Resources\TourPages\ZanzibarPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\TourPageResource;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\CreateZanzibarPage;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\EditZanzibarPage;
use App\Filament\Resources\TourPages\ZanzibarPages\Pages\ListZanzibarPages;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class ZanzibarPageResource extends TourPageResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static ?string $slug = 'pages/zanzibar';

    public static function category(): TourCategory
    {
        return TourCategory::Zanzibar;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListZanzibarPages::route('/'),
            'create' => CreateZanzibarPage::route('/create'),
            'edit' => EditZanzibarPage::route('/{record}/edit'),
        ];
    }
}
