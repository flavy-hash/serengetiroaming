<?php

namespace App\Filament\Resources\PageBanners\Schemas;

use App\Models\PageBanner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        $default = fn (?PageBanner $record, string $field) => PageBanner::PAGES[$record?->key][$field] ?? null;

        return $schema
            ->columns(1)
            ->components([
                Section::make('Banner')
                    ->description('The large photo and heading at the top of the page. Leave text empty to keep the default.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Banner image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('banners')
                            ->maxSize(5120)
                            ->helperText(fn (?PageBanner $record) => $default($record, 'image')
                                ? 'Wide landscape photo, ideally 1920px wide. Leave empty to keep the current default photo.'
                                : 'Wide landscape photo, ideally 1920px wide. Leave empty for a plain green banner.'),
                        TextInput::make('title')
                            ->label('Heading')
                            ->maxLength(255)
                            ->placeholder(fn (?PageBanner $record) => $default($record, 'title')),
                        TextInput::make('text')
                            ->label('Text')
                            ->maxLength(255)
                            ->placeholder(fn (?PageBanner $record) => $default($record, 'text')),
                    ]),
            ]);
    }
}
