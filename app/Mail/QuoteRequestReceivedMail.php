<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quote, public ?string $recipientName = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Quotation request received #'.$this->quote->id.' | West East Freight');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-request-received');
    }
}
