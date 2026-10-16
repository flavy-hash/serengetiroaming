<?php

namespace App\Support;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Mail\InquiryReceived;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as AdminNotification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

use function Illuminate\Support\defer;

/**
 * Tells the team about a new enquiry the moment it arrives: an alert in the admin's
 * notification bell and an email to the addresses under Site settings → Enquiry emails.
 *
 * Both are sent right after the visitor's response (defer), so a slow mail server never
 * makes the form feel stuck, and synchronously (no queue worker needed on shared hosting).
 * Failures are logged and never shown to the visitor — the enquiry is already saved.
 */
class InquiryNotifier
{
    public static function notify(Booking|ContactMessage|Faq $record): void
    {
        $inquiry = self::describe($record);

        defer(fn () => self::deliver($inquiry));
    }

    /**
     * @param  array<string, mixed>  $inquiry  from describe()
     */
    public static function deliver(array $inquiry): void
    {
        try {
            $alert = AdminNotification::make()
                ->title($inquiry['title'])
                ->body($inquiry['summary'])
                ->icon($inquiry['icon'])
                ->iconColor('success')
                ->actions([
                    Action::make('open')
                        ->label('Open')
                        ->url($inquiry['admin_url'])
                        ->markAsRead(),
                ]);

            Notification::sendNow(User::all(), $alert->toDatabase());
        } catch (\Throwable $e) {
            report($e);
        }

        if (! Setting::inquiryEmailsEnabled() || ! ($to = Setting::inquiryRecipients())) {
            return;
        }

        try {
            Mail::to($to)->send(new InquiryReceived($inquiry));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Everything the alert and the email need, as plain values (no models).
     *
     * @return array{kind: string, title: string, summary: string, fields: array<string, string>, message_label: ?string, message: ?string, reply_to: array{email: string, name: string}, admin_url: string, icon: \BackedEnum|string}
     */
    public static function describe(Booking|ContactMessage|Faq $record): array
    {
        $field = fn ($value) => filled($value) ? (string) $value : '—';

        return match (true) {
            $record instanceof Booking => [
                'kind' => 'Booking enquiry',
                'title' => "New booking enquiry from {$record->name}",
                'summary' => Str::limit($record->trip, 80).' · '.$record->travellers.' '.Str::plural('traveller', (int) $record->travellers),
                'fields' => [
                    'Trip' => $field($record->trip),
                    'Name' => $field($record->name),
                    'Email' => $field($record->email),
                    'Phone / WhatsApp' => $field($record->phone),
                    'Travellers' => $field($record->travellers),
                    'Departure date' => $record->departure_date ? $record->departure_date->format('j F Y') : 'Flexible',
                ],
                'message_label' => 'Notes',
                'message' => $record->notes,
                'reply_to' => ['email' => $record->email, 'name' => $record->name],
                'admin_url' => BookingResource::getUrl('view', ['record' => $record]),
                'icon' => Heroicon::OutlinedInboxStack,
            ],
            $record instanceof ContactMessage => [
                'kind' => 'Contact message',
                'title' => "New message from {$record->name}",
                'summary' => Str::limit($record->subject ?: $record->message, 90),
                'fields' => [
                    'Name' => $field($record->name),
                    'Email' => $field($record->email),
                    'Phone / WhatsApp' => $field($record->phone),
                    'Subject' => $field($record->subject),
                ],
                'message_label' => 'Message',
                'message' => $record->message,
                'reply_to' => ['email' => $record->email, 'name' => $record->name],
                'admin_url' => ContactMessageResource::getUrl('view', ['record' => $record]),
                'icon' => Heroicon::OutlinedEnvelope,
            ],
            $record instanceof Faq => [
                'kind' => 'Question',
                'title' => "New question from {$record->name}",
                'summary' => Str::limit($record->question, 90),
                'fields' => [
                    'Name' => $field($record->name),
                    'Email' => $field($record->email),
                    'About' => $field($record->tourPage?->package_name ?? 'General question'),
                ],
                'message_label' => 'Question',
                'message' => $record->question,
                'reply_to' => ['email' => $record->email, 'name' => $record->name],
                'admin_url' => FaqResource::getUrl('edit', ['record' => $record]),
                'icon' => Heroicon::OutlinedQuestionMarkCircle,
            ],
        };
    }
}
