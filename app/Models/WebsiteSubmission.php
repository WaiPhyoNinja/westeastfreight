<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSubmission extends Model
{
    protected $fillable = ['type', 'email', 'details', 'status', 'reply_message', 'replied_at', 'receipt_mail_status', 'receipt_mail_sent_at', 'reply_mail_status'];

    protected function casts(): array
    {
        return ['details' => 'array', 'replied_at' => 'datetime', 'receipt_mail_sent_at' => 'datetime'];
    }
}
