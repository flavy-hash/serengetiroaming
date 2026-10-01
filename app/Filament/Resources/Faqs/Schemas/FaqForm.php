<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Question')
                    ->columns(2)
                    ->schema([
                        Textarea::make('question')
                            ->required()
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Select::make('tour_page_id')
                            ->label('About package')
                            ->relationship('tourPage', 'package_name')
                            ->searchable()
                            ->preload()
                            ->placeholder('General question')
                            ->helperText('Published answers also show on that package\'s page.')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Asked by')
                            ->maxLength(255)
                            ->placeholder('Added by admin'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('Answer')
                    ->columns(2)
                    ->schema([
                        Textarea::make('answer')
                            ->rows(6)
                            ->live(onBlur: true)
                            ->helperText('Leave a blank line between paragraphs.')
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label('Show on the FAQ page')
                            ->disabled(fn (Get $get) => blank($get('answer')))
                            ->helperText('Needs an answer first. Names and emails are never shown publicly.'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first on the FAQ page.'),
                    ]),
            ]);
    }
}
