<?php

namespace App\Filament\Resources\NavItems\Pages;

use App\Filament\Resources\NavItems\NavItemResource;
use App\Models\NavItem;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListNavItems extends ListRecords
{
    protected static string $resource = NavItemResource::class;

    protected ?string $subheading = 'The menu at the top of every page. Drag rows to change the order.';

    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        // Reordering is a bulk query, so the model's cache-clearing events don't fire.
        NavItem::flushCache();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View on site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url('/'), shouldOpenInNewTab: true),
            CreateAction::make(),
        ];
    }
}
