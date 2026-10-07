<?php

namespace App\Filament\Resources\Reviews;

use App\Enums\ReviewStatus;
use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Enquiries';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $count = Review::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Waiting for approval';
    }

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    /**
     * Approve / reject, shared by the table and the edit page.
     *
     * @return array<int, Action>
     */
    public static function moderationActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->visible(fn (Review $record) => $record->status !== ReviewStatus::Published)
                ->action(fn (Review $record) => $record->update(['status' => ReviewStatus::Published]))
                ->successNotificationTitle('Review published'),
            Action::make('reject')
                ->label(fn (Review $record) => $record->status === ReviewStatus::Published ? 'Unpublish' : 'Reject')
                ->icon(Heroicon::OutlinedXMark)
                ->color('gray')
                ->visible(fn (Review $record) => $record->status !== ReviewStatus::Rejected)
                ->requiresConfirmation(fn (Review $record) => $record->status === ReviewStatus::Published)
                ->action(fn (Review $record) => $record->update(['status' => ReviewStatus::Rejected, 'is_featured' => false]))
                ->successNotificationTitle('Review hidden from the website'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
