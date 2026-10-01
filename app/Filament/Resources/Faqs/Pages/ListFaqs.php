<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View FAQ page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('faq'), shouldOpenInNewTab: true),
            CreateAction::make()->label('Add FAQ'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'unanswered' => Tab::make('Unanswered')
                ->icon(Heroicon::OutlinedInbox)
                ->badge(fn () => Faq::unanswered()->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('answer')),
            'answered' => Tab::make('Answered, not public')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('answer')->where('is_published', false)),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_published', true)),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return Faq::unanswered()->exists() ? 'unanswered' : 'all';
    }
}
