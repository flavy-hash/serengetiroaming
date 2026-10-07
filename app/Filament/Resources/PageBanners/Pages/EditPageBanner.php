<?php

namespace App\Filament\Resources\PageBanners\Pages;

use App\Filament\Resources\PageBanners\PageBannerResource;
use App\Models\PageBanner;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPageBanner extends EditRecord
{
    protected static string $resource = PageBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (PageBanner $record) => url($record->path()), shouldOpenInNewTab: true),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
