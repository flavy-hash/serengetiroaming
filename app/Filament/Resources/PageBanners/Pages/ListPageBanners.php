<?php

namespace App\Filament\Resources\PageBanners\Pages;

use App\Filament\Resources\PageBanners\PageBannerResource;
use App\Models\PageBanner;
use Filament\Resources\Pages\ListRecords;

class ListPageBanners extends ListRecords
{
    protected static string $resource = PageBannerResource::class;

    protected ?string $subheading = 'The photo and heading at the top of pages that are not managed elsewhere.';

    public function mount(): void
    {
        PageBanner::ensureDefaults();

        parent::mount();
    }
}
