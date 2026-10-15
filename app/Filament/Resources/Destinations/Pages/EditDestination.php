<?php

namespace App\Filament\Resources\Destinations\Pages;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Models\Destination;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EditDestination extends EditRecord
{
    protected static string $resource = DestinationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (Destination $record) => $record->url(), shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }

    /**
     * FileUpload drops values that aren't files on the disk (e.g. an image URL):
     * keep the existing URL when no new photo was uploaded.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $current = (string) $this->getRecord()->image;
        if (blank($data['image'] ?? null) && Str::startsWith($current, ['http://', 'https://'])) {
            $data['image'] = $current;
        }

        return $data;
    }
}
