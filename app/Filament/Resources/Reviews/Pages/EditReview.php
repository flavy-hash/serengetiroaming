<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Keep the form in sync so a later "Save" doesn't undo the approval.
            ...array_map(
                fn (Action $action) => $action->after(fn () => $this->refreshFormData(['status', 'is_featured'])),
                ReviewResource::moderationActions(),
            ),
            DeleteAction::make(),
        ];
    }
}
