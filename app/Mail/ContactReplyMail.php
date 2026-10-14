<?php

namespace App\Mail;

use App\Models\WebsiteSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WebsiteSubmission $submission, public string $messageBody) {}

    public function envelope(): Envelope
    {
        $subject = trim(preg_replace('/[\r\n]+/', ' ', (string) ($this->submission->details['subject'] ?? '')));

        return new Envelope(subject: 'Re: '.($subject ?: 'Your message to West East Freight'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-reply');
    }
}
