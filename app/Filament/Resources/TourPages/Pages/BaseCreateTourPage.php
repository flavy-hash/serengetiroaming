<?php

namespace App\Filament\Resources\TourPages\Pages;

use App\Models\TourPage;
use Filament\Resources\Pages\CreateRecord;

abstract class BaseCreateTourPage extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $category = static::getResource()::category();

        $data['category'] = $category;
        $data['sort_order'] ??= (int) TourPage::where('category', $category)->max('sort_order') + 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
