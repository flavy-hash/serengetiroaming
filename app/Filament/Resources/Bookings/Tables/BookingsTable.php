<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(['name', 'email'])
                    ->description(fn ($record) => $record->email),
                TextColumn::make('trip')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->trip),
                TextColumn::make('travellers')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('departure_date')
                    ->label('Departure')
                    ->date()
                    ->placeholder('Flexible')
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Phone / WhatsApp')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingStatus::class)
                    ->multiple(),
                Filter::make('departure_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Departing from'),
                        DatePicker::make('until')
                            ->label('Departing until'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('departure_date', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('departure_date', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updateStatus')
                        ->label('Change status')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->schema([
                            Select::make('status')
                                ->options(BookingStatus::class)
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
