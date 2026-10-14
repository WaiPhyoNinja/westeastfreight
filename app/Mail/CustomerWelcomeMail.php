<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $language = 'en-US',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->language) {
            'mm' => 'West East Freight မှ ကြိုဆိုပါတယ်',
            'th' => 'ยินดีต้อนรับสู่ West East Freight',
            default => 'Welcome to West East Freight',
        });
    }

    public function content(): Content
    {
        $copy = match ($this->language) {
            'mm' => [
                'greeting' => 'မင်္ဂလာပါ',
                'welcomeText' => 'West East Freight တွင် သင့် customer account ကို ဖွင့်ပြီးပါပြီ။ အကောင့်ဝင်ပြီး quotation တောင်းဆိုနိုင်ပါပြီ။',
                'closing' => 'West East Freight',
            ],
            'th' => [
                'greeting' => 'สวัสดี',
                'welcomeText' => 'บัญชีลูกค้า West East Freight ของคุณพร้อมใช้งานแล้ว คุณสามารถเข้าสู่ระบบและขอใบเสนอราคาได้',
                'closing' => 'West East Freight',
            ],
            default => [
                'greeting' => 'Hello',
                'welcomeText' => 'Your West East Freight customer account is ready. You can sign in and request a quote.',
                'closing' => 'West East Freight',
            ],
        };

        return new Content(view: 'emails.customer-welcome', with: $copy);
    }
}
