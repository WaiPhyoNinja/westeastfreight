<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $customerName, public string $activity, public string $occurredAt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->activity === 'login'
            ? 'New sign-in to your West East Freight account'
            : 'Your West East Freight password has been changed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-activity');
    }
}
