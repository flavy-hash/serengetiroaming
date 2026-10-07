<?php

namespace App\Filament\Resources\PageBanners;

use App\Filament\Resources\PageBanners\Pages\EditPageBanner;
use App\Filament\Resources\PageBanners\Pages\ListPageBanners;
use App\Filament\Resources\PageBanners\Schemas\PageBannerForm;
use App\Filament\Resources\PageBanners\Tables\PageBannersTable;
use App\Models\PageBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Banners for fixed pages (/safaris, /contact, /faq). The list of pages is fixed
 * in PageBanner::PAGES, so banners can be edited but not added or deleted.
 */
class PageBannerResource extends Resource
{
    protected static ?string $model = PageBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Site Settings';

    protected static ?string $navigationLabel = 'Page Banners';

    protected static ?string $modelLabel = 'page banner';

    protected static ?string $slug = 'page-banners';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('key', array_keys(PageBanner::PAGES));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getRecordTitle(?Model $record): string
    {
        return $record ? $record->label().' banner' : 'Page banner';
    }

    public static function form(Schema $schema): Schema
    {
        return PageBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageBannersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageBanners::route('/'),
            'edit' => EditPageBanner::route('/{record}/edit'),
        ];
    }
}
