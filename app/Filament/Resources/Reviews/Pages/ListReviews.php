<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Enums\ReviewStatus;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewOnSite')
                ->label('View reviews page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(route('reviews'), shouldOpenInNewTab: true),
            CreateAction::make()->label('Add review'),
        ];
    }

    public function getTabs(): array
    {
        $tab = fn (ReviewStatus $status) => Tab::make($status === ReviewStatus::Pending ? 'Waiting for approval' : $status->getLabel())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));

        return [
            'pending' => $tab(ReviewStatus::Pending)
                ->icon(Heroicon::OutlinedInbox)
                ->badge(fn () => Review::pending()->count() ?: null)
                ->badgeColor('warning'),
            'published' => $tab(ReviewStatus::Published),
            'rejected' => $tab(ReviewStatus::Rejected),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return Review::pending()->exists() ? 'pending' : 'published';
    }
}
