<?php

namespace App\Filament\Resources\TourPages\Tables;

use App\Enums\SafariTier;
use App\Enums\TourCategory;
use App\Models\TourPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TourPagesTable
{
    public static function configure(Table $table, TourCategory $category): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('banner_image')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(48)
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn (TourPage $record) => $record->isMain() ? 'Main page · /'.$record->category->value : '/'.$record->category->value.'/'.$record->slug),
                TextColumn::make('tier')
                    ->badge()
                    ->sortable()
                    ->label('Style')
                    ->placeholder('Not set'),
                TextColumn::make('package_name')
                    ->label('Package')
                    ->searchable()
                    ->description(fn (TourPage $record) => $record->duration),
                TextColumn::make('price_from')
                    ->label('From')
                    ->money('USD', decimalPlaces: 0)
                    ->placeholder('On request')
                    ->sortable(),
                TextColumn::make('itinerary')
                    ->label('Days')
                    ->state(fn (TourPage $record) => count($record->itinerary ?? [])),
                ToggleColumn::make('is_published')
                    ->label('Published'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tier')
                    ->label('Style')
                    ->options(SafariTier::class)
                    ->multiple(),
            ])
            ->groups([
                Group::make('tier')->label('Style')->collapsible(),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (TourPage $record) => $record->url(), shouldOpenInNewTab: true),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->tooltip('Download the itinerary as a PDF, e.g. to attach to a quote')
                    ->url(fn (TourPage $record) => $record->pdfUrl()),
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['is_published'])
                    ->mutateRecordDataUsing(function (array $data) {
                        $data['title'] .= ' (copy)';
                        $data['slug'] = Str::slug($data['slug'].'-copy-'.Str::random(4));

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No pages yet')
            ->emptyStateDescription('Until a page is published, visitors see a "coming soon" page here.');
    }
}
