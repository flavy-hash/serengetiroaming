<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageVehicles extends ManageRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('vehicles'), shouldOpenInNewTab: true),
            CreateAction::make()
                ->label('Add vehicle')
                ->mutateDataUsing(function (array $data): array {
                    $data['sort_order'] = (int) Vehicle::max('sort_order') + 1;

                    return $data;
                }),
        ];
    }
}
