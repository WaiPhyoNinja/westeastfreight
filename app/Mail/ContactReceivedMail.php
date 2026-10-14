<?php

namespace App\Mail;

use App\Models\WebsiteSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WebsiteSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your message was received | West East Freight');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-received');
    }
}
