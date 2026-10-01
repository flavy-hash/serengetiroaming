<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Mail\FaqAnswered;
use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('question')
                    ->searchable()
                    ->wrap()
                    ->limit(120)
                    ->description(fn (Faq $record) => collect([
                        $record->name ? 'Asked by '.$record->name : 'Added by admin',
                        $record->tourPage?->package_name,
                    ])->filter()->implode(' · ')),
                TextColumn::make('status')
                    ->state(fn (Faq $record) => match (true) {
                        ! $record->isAnswered() => 'Unanswered',
                        $record->is_published => 'Published',
                        default => 'Answered',
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Unanswered' => 'warning',
                        'Published' => 'success',
                        default => 'info',
                    }),
                ToggleColumn::make('is_published')
                    ->label('On FAQ page')
                    ->disabled(fn (Faq $record) => ! $record->isAnswered()),
                TextColumn::make('created_at')
                    ->label('Asked')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tour_page_id')
                    ->label('Package')
                    ->relationship('tourPage', 'package_name'),
            ])
            ->recordActions([
                self::answerAction(),
                Action::make('emailReply')
                    ->label('Email')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->color('gray')
                    ->visible(fn (Faq $record) => filled($record->email))
                    ->url(fn (Faq $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Your question to Serengeti Roaming')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No questions yet')
            ->emptyStateDescription('Questions from the "Ask a Question" form appear here. You can also add your own FAQs.');
    }

    /**
     * Answer a question without leaving the list.
     */
    public static function answerAction(): Action
    {
        return Action::make('answer')
            ->label(fn (Faq $record) => $record->isAnswered() ? 'Edit answer' : 'Answer')
            ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
            ->color(fn (Faq $record) => $record->isAnswered() ? 'gray' : 'primary')
            ->modalHeading(fn (Faq $record) => $record->question)
            ->modalDescription(fn (Faq $record) => collect([
                $record->name ? "From {$record->name}" : null,
                $record->email,
                $record->tourPage?->package_name,
            ])->filter()->implode(' · '))
            ->fillForm(fn (Faq $record) => [
                'answer' => $record->answer,
                'is_published' => $record->is_published || ! $record->isAnswered(),
                'send_email' => filled($record->email) && ! $record->isAnswered(),
            ])
            ->schema([
                Textarea::make('answer')
                    ->required()
                    ->rows(6),
                Toggle::make('is_published')
                    ->label('Show on the FAQ page')
                    ->helperText('Names and emails are never shown publicly.'),
                Checkbox::make('send_email')
                    ->label(fn (Faq $record) => "Email this answer to {$record->email}")
                    ->visible(fn (Faq $record) => filled($record->email)),
            ])
            ->action(function (Faq $record, array $data) {
                $record->update([
                    'answer' => $data['answer'],
                    'is_published' => $data['is_published'],
                ]);

                if (($data['send_email'] ?? false) && filled($record->email)) {
                    Mail::to($record->email)->send(new FaqAnswered($record));

                    Notification::make()->title("Answer emailed to {$record->email}")->success()->send();
                }
            })
            ->successNotificationTitle('Answer saved');
    }
}
