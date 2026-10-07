<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Enums\ReviewStatus;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // One compact line per review; hover the review title to read the text.
                TextColumn::make('created_at')
                    ->label('Received')
                    ->date('j M Y')
                    ->description(fn (Review $record) => $record->created_at->diffForHumans())
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Guest')
                    ->weight('semibold')
                    ->searchable(['name', 'email'])
                    ->limit(24)
                    ->description(fn (Review $record) => $record->country),
                TextColumn::make('rating')
                    ->formatStateUsing(fn (int $state) => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Review')
                    ->searchable(['title', 'body'])
                    ->limit(40)
                    ->tooltip(fn (Review $record) => Str::limit($record->body, 300)),
                TextColumn::make('tourPage.package_name')
                    ->label('Trip')
                    ->limit(28)
                    ->placeholder('Custom / other')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Homepage')
                    ->disabled(fn (Review $record) => $record->status !== ReviewStatus::Published),
                ImageColumn::make('photos')
                    ->disk('public')
                    ->imageHeight(28)
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']),
                SelectFilter::make('tour_page_id')
                    ->label('Trip')
                    ->relationship('tourPage', 'package_name'),
            ])
            ->recordActions([
                ...ReviewResource::moderationActions(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon(Heroicon::OutlinedCheck)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => ReviewStatus::Published]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reject')
                        ->label('Reject')
                        ->icon(Heroicon::OutlinedXMark)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ReviewStatus::Rejected, 'is_featured' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No reviews yet')
            ->emptyStateDescription('Reviews guests submit on /reviews appear here for approval. You can also add reviews yourself.');
    }
}
