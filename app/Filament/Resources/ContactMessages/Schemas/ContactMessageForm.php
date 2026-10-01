<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The visitor's message itself is read-only (see the infolist); admins only add notes.
 */
class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Internal')
                    ->description('Only visible to admins.')
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Notes')
                            ->rows(5)
                            ->placeholder('e.g. Replied on WhatsApp, sent a Zanzibar quote.'),
                        DateTimePicker::make('handled_at')
                            ->label('Handled on')
                            ->helperText('Leave empty while the message still needs a reply.'),
                    ]),
            ]);
    }
}
