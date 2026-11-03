<?php

namespace App\Filament\Resources\PageBanners\Schemas;

use App\Filament\Support\HeroImage;
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
                        HeroImage::apply(FileUpload::make('image')
                            ->label('Banner image')
                            ->imageEditor()
                            ->disk('public')
                            ->directory('banners'))
                            ->helperText(fn (?PageBanner $record) => $default($record, 'image')
                                ? HeroImage::help('Leave empty to keep the current default photo.')
                                : HeroImage::help('Leave empty for a plain green banner.')),
                        FileUpload::make('video')
                            ->label('Banner video (optional)')
                            ->disk('public')
                            ->directory('banners/videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(30720)
                            ->helperText('A short looping clip (MP4 or WebM, up to 30 MB — under 10 MB loads fastest, no sound needed). It plays in place of the photo; the banner image above is shown while it loads and to visitors who prefer reduced motion, so add both.'),
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
