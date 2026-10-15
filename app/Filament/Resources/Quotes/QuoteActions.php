<?php

namespace App\Filament\Resources\Quotes;

use App\Enums\QuoteStatus;
use App\Mail\QuoteMail;
use App\Models\Quote;
use App\Support\QuotePdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Quote actions shared by the table and the edit page.
 */
class QuoteActions
{
    public static function download(): Action
    {
        return Action::make('downloadPdf')
            ->label('Download PDF')
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('gray')
            ->action(fn (Quote $record): StreamedResponse => response()->streamDownload(
                fn () => print(QuotePdf::output($record)),
                $record->pdfFilename(),
                ['Content-Type' => 'application/pdf'],
            ));
    }

    public static function email(): Action
    {
        return Action::make('emailCustomer')
            ->label('Email to customer')
            ->icon(Heroicon::OutlinedEnvelope)
            ->requiresConfirmation()
            ->modalHeading('Email this quote?')
            ->modalDescription(fn (Quote $record) => "The PDF will be sent to {$record->customer_email}.")
            ->modalSubmitActionLabel('Send')
            ->action(function (Quote $record): void {
                try {
                    Mail::to($record->customer_email, $record->customer_name)->send(new QuoteMail($record));
                } catch (\Throwable $e) {
                    report($e);

                    Notification::make()
                        ->title('The email could not be sent')
                        ->body('Check the mail settings, or download the PDF and send it yourself.')
                        ->danger()
                        ->send();

                    return;
                }

                $record->update(['status' => QuoteStatus::Sent]);

                Notification::make()
                    ->title("Quote sent to {$record->customer_email}")
                    ->success()
                    ->send();
            });
    }
}
