<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Resources\Quotes\QuoteActions;
use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    public function getTitle(): string
    {
        return 'Quote '.$this->getRecord()->reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            QuoteActions::download(),
            QuoteActions::email(),
            DeleteAction::make(),
        ];
    }
}
