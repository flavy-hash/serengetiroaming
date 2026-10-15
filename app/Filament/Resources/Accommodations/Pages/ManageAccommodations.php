<?php

namespace App\Filament\Resources\Accommodations\Pages;

use App\Filament\Resources\Accommodations\AccommodationResource;
use App\Models\Accommodation;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageAccommodations extends ManageRecords
{
    protected static string $resource = AccommodationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('accommodations'), shouldOpenInNewTab: true),
            CreateAction::make()
                ->label('Add stay')
                ->mutateDataUsing(function (array $data): array {
                    $data['sort_order'] = (int) Accommodation::max('sort_order') + 1;

                    return $data;
                }),
        ];
    }
}
