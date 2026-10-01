<?php

namespace App\Mail;

use App\Models\Faq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FaqAnswered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Faq $faq) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your question to Serengeti Roaming',
            replyTo: [config('site.email')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.faq-answered');
    }
}
