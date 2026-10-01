<?php

namespace App\Filament\Resources\TourPages;

use App\Enums\TourCategory;
use App\Filament\Resources\TourPages\Schemas\TourPageForm;
use App\Filament\Resources\TourPages\Tables\TourPagesTable;
use App\Models\TourPage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Base for the per-section resources (Zanzibar, Kilimanjaro, …). Each subclass
 * only says which category it manages; everything else is shared.
 */
abstract class TourPageResource extends Resource
{
    protected static ?string $model = TourPage::class;

    protected static string|UnitEnum|null $navigationGroup = 'Website Pages';

    protected static ?string $recordTitleAttribute = 'title';

    abstract public static function category(): TourCategory;

    public static function getNavigationLabel(): string
    {
        return static::category()->getLabel();
    }

    public static function getModelLabel(): string
    {
        return static::category()->getLabel().' page';
    }

    public static function getNavigationSort(): ?int
    {
        return array_search(static::category(), TourCategory::cases(), true);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category', static::category());
    }

    public static function form(Schema $schema): Schema
    {
        return TourPageForm::configure($schema, static::category());
    }

    public static function table(Table $table): Table
    {
        return TourPagesTable::configure($table, static::category());
    }
}
