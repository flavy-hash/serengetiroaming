<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Email to the office about a new enquiry (see InquiryNotifier). Replying goes straight to the visitor.
 */
class InquiryReceived extends Mailable
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $inquiry  from InquiryNotifier::describe()
     */
    public function __construct(public array $inquiry) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->inquiry['reply_to'] ?? null;

        return new Envelope(
            subject: $this->inquiry['title'],
            replyTo: $replyTo && filter_var($replyTo['email'], FILTER_VALIDATE_EMAIL)
                ? [new Address($replyTo['email'], (string) $replyTo['name'])]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.inquiry',
            text: 'mail.inquiry-text',
            with: ['inquiry' => $this->inquiry],
        );
    }
}
