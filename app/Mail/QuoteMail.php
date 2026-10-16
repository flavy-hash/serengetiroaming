<?php

namespace App\Mail;

use App\Models\Quote;
use App\Support\QuotePdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A quotation with its PDF attached: to the customer (from the admin), or to the
 * office when a visitor requests an instant quote on the website.
 */
class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote, public bool $forOffice = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forOffice
                ? "New instant quote {$this->quote->reference} — {$this->quote->customer_name}"
                : "Your Serengeti Roaming quotation {$this->quote->reference}",
            replyTo: [$this->forOffice
                ? new Address($this->quote->customer_email, $this->quote->customer_name)
                : new Address(config('site.email'), config('site.name'))],
        );
    }

    public function content(): Content
    {
        return new Content(text: $this->forOffice ? 'mail.quote-office' : 'mail.quote');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => QuotePdf::output($this->quote), $this->quote->pdfFilename())
                ->withMime('application/pdf'),
        ];
    }
}
