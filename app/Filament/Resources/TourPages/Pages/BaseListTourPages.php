<?php

namespace App\Filament\Resources\TourPages\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

abstract class BaseListTourPages extends ListRecords
{
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        // Reordering is a bulk query (no model events) and can change a section's main page.
        \App\Support\NavPreview::flush();
    }

    protected function getHeaderActions(): array
    {
        $category = static::getResource()::category();

        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url($category->value), shouldOpenInNewTab: true),
            CreateAction::make(),
        ];
    }
}
