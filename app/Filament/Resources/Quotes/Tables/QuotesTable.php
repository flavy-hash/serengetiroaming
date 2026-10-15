<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteSource;
use App\Enums\QuoteStatus;
use App\Filament\Resources\Quotes\QuoteActions;
use App\Models\Quote;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_email'])
                    ->description(fn (Quote $record) => $record->customer_email),
                TextColumn::make('package.package_name')
                    ->label('Package')
                    ->placeholder('—')
                    ->limit(36)
                    ->toggleable(),
                TextColumn::make('total')
                    ->state(fn (Quote $record) => Quote::money($record->total))
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(QuoteStatus::class)->multiple(),
                SelectFilter::make('source')->options(QuoteSource::class),
            ])
            ->recordActions([
                QuoteActions::download()->iconButton()->tooltip('Download PDF'),
                ActionGroup::make([
                    EditAction::make(),
                    QuoteActions::email(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
