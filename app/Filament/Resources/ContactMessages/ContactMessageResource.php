<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageForm;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageInfolist;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Enquiries';

    protected static ?string $navigationLabel = 'Contact Messages';

    protected static ?string $modelLabel = 'message';

    protected static ?string $slug = 'contact-messages';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::open()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Not handled yet';
    }

    public static function canCreate(): bool
    {
        return false; // messages only come from the website's contact form
    }

    public static function form(Schema $schema): Schema
    {
        return ContactMessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    /**
     * Shared by the table and the view page.
     */
    public static function toggleHandledAction(): Action
    {
        return Action::make('toggleHandled')
            ->label(fn (ContactMessage $record) => $record->isHandled() ? 'Reopen' : 'Mark handled')
            ->icon(fn (ContactMessage $record) => $record->isHandled() ? Heroicon::OutlinedArrowUturnLeft : Heroicon::OutlinedCheck)
            ->color(fn (ContactMessage $record) => $record->isHandled() ? 'gray' : 'success')
            ->action(fn (ContactMessage $record) => $record->update(['handled_at' => $record->isHandled() ? null : now()]));
    }

    public static function replyAction(): Action
    {
        return Action::make('reply')
            ->label('Reply by email')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('gray')
            ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: '.($record->subject ?: 'Your message to Serengeti Roaming')));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
            'edit' => EditContactMessage::route('/{record}/edit'),
        ];
    }
}
