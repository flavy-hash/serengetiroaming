<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ContactMessagesTable
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
                    ->weight(fn (ContactMessage $record) => $record->isHandled() ? null : 'bold')
                    ->description(fn (ContactMessage $record) => $record->email),
                TextColumn::make('message')
                    ->searchable(['subject', 'message'])
                    ->limit(50)
                    ->tooltip(fn (ContactMessage $record) => Str::limit($record->message, 300))
                    ->description(fn (ContactMessage $record) => $record->subject, position: 'above'),
                TextColumn::make('status')
                    ->state(fn (ContactMessage $record) => $record->isHandled() ? 'Handled' : 'Needs reply')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Handled' ? 'success' : 'warning'),
            ])
            ->filters([
                TernaryFilter::make('handled')
                    ->label('Status')
                    ->placeholder('All messages')
                    ->trueLabel('Handled')
                    ->falseLabel('Needs reply')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('handled_at'),
                        false: fn (Builder $query) => $query->whereNull('handled_at'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                ContactMessageResource::toggleHandledAction(),
                ActionGroup::make([
                    ContactMessageResource::replyAction(),
                    EditAction::make()->label('Notes'),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markHandled')
                        ->label('Mark handled')
                        ->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => $records->each->update(['handled_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No messages yet')
            ->emptyStateDescription('Messages sent from the contact page appear here.');
    }
}
