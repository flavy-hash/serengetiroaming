<?php

namespace App\Filament\Resources\TourPages\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

abstract class BaseEditTourPage extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label(fn () => $this->getRecord()->is_published ? 'View on site' : 'Preview draft')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn () => $this->getRecord()->url(), shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
