<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quote, public string $stage, public ?string $recipientName = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Shipment quotation #'.$this->quote->id.' — '.ucfirst($this->stage).' | West East Freight');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-status');
    }
}
