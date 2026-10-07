<?php

namespace App\Filament\Resources\PageBanners\Tables;

use App\Models\PageBanner;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageBannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->state(fn (PageBanner $record) => $record->imageUrl())
                    ->imageHeight(48)
                    ->imageWidth(80)
                    ->defaultImageUrl(null),
                TextColumn::make('page')
                    ->state(fn (PageBanner $record) => $record->label())
                    ->weight('semibold')
                    ->description(fn (PageBanner $record) => $record->path()),
                TextColumn::make('heading')
                    ->state(fn (PageBanner $record) => $record->heading())
                    ->description(fn (PageBanner $record) => $record->subheading())
                    ->limit(60),
                TextColumn::make('image_status')
                    ->label('Image')
                    ->state(fn (PageBanner $record) => match (true) {
                        filled($record->image) => 'Uploaded',
                        filled(PageBanner::PAGES[$record->key]['image'] ?? null) => 'Default photo',
                        default => 'None',
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Uploaded' => 'success',
                        'Default photo' => 'info',
                        default => 'gray',
                    }),
            ])
            ->recordActions([
                Action::make('viewOnSite')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (PageBanner $record) => url($record->path()), shouldOpenInNewTab: true),
                EditAction::make(),
            ]);
    }
}
