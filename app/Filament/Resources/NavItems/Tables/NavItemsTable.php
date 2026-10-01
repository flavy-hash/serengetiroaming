<?php

namespace App\Filament\Resources\NavItems\Tables;

use App\Models\NavItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NavItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(40)
                    ->square(),
                TextColumn::make('label')
                    ->weight('semibold')
                    ->description(fn (NavItem $record) => $record->href),
                TextColumn::make('type')
                    ->state(fn (NavItem $record) => $record->hasDropdown() ? 'Dropdown' : 'Link')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Dropdown' ? 'primary' : 'gray'),
                TextColumn::make('links')
                    ->label('Dropdown links')
                    ->state(fn (NavItem $record) => collect($record->links)->pluck('label')->implode(', '))
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap(),
                ToggleColumn::make('is_visible')
                    ->label('Visible'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No menu items')
            ->emptyStateDescription('The navbar is empty until you add items here.');
    }
}
