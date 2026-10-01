<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(fn (ContactMessage $record) => $record->subject ?: 'Message')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->url(fn (ContactMessage $record) => "mailto:{$record->email}")
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Phone / WhatsApp')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('message')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'whitespace-pre-line']),
                        TextEntry::make('created_at')
                            ->label('Received')
                            ->dateTime()
                            ->since(),
                        TextEntry::make('status')
                            ->state(fn (ContactMessage $record) => $record->isHandled() ? 'Handled' : 'Needs reply')
                            ->badge()
                            ->color(fn (string $state) => $state === 'Handled' ? 'success' : 'warning'),
                        TextEntry::make('handled_at')
                            ->label('Handled on')
                            ->dateTime()
                            ->placeholder('—'),
                    ]),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->hiddenLabel()
                            ->placeholder('No notes yet.')
                            ->extraAttributes(['class' => 'whitespace-pre-line']),
                    ]),
            ]);
    }
}
