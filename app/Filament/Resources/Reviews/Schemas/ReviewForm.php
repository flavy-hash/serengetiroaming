<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Enums\ReviewStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Review')
                    ->columnSpan(2)
                    ->schema([
                        ToggleButtons::make('rating')
                            ->options([1 => '1 ★', 2 => '2 ★', 3 => '3 ★', 4 => '4 ★', 5 => '5 ★'])
                            ->default(5)
                            ->inline()
                            ->required(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('body')
                            ->label('Review')
                            ->required()
                            ->rows(8)
                            ->maxLength(5000)
                            ->helperText('Leave a blank line between paragraphs.'),
                        FileUpload::make('photos')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(4)
                            ->maxSize(5120)
                            ->disk('public')
                            ->directory('reviews')
                            ->panelLayout('grid')
                            ->helperText('Up to 4 photos. The first two show on the review card.'),
                    ]),

                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Visibility')
                            ->schema([
                                Select::make('status')
                                    ->options(ReviewStatus::class)
                                    ->default(ReviewStatus::Published)
                                    ->required()
                                    ->live(),
                                Toggle::make('is_featured')
                                    ->label('Feature on the homepage')
                                    ->helperText('Featured reviews are shown first. The homepage shows 3.')
                                    ->disabled(fn (Get $get) => $get('status') !== ReviewStatus::Published && $get('status') !== ReviewStatus::Published->value),
                            ]),
                        Section::make('Guest')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->maxLength(255)
                                    ->helperText('Private — never shown on the website.'),
                                TextInput::make('country')
                                    ->maxLength(100),
                                TextInput::make('trip_date')
                                    ->label('Trip date')
                                    ->placeholder('August 2026')
                                    ->maxLength(50),
                                Select::make('tour_page_id')
                                    ->label('Trip')
                                    ->relationship('tourPage', 'package_name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Custom / other trip')
                                    ->helperText('The review also shows on that package\'s page.'),
                            ]),
                    ]),
            ]);
    }
}
