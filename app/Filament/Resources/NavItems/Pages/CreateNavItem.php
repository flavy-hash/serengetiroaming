<?php

namespace App\Filament\Resources\NavItems\Pages;

use App\Filament\Resources\NavItems\NavItemResource;
use App\Models\NavItem;
use Filament\Resources\Pages\CreateRecord;

class CreateNavItem extends CreateRecord
{
    protected static string $resource = NavItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // New items go to the end of the navbar.
        $data['sort_order'] = (int) NavItem::max('sort_order') + 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
