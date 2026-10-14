<?php

namespace App\Jobs;

use App\Mail\ContactReceivedMail;
use App\Models\WebsiteSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendContactReceipt implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $submissionId) {
        $this->onConnection('database');
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $submission = WebsiteSubmission::findOrFail($this->submissionId);
        Mail::to($submission->email)->send(new ContactReceivedMail($submission));
        $submission->update(['receipt_mail_status' => 'sent', 'receipt_mail_sent_at' => now()]);
    }

    public function failed(?Throwable $exception): void
    {
        WebsiteSubmission::whereKey($this->submissionId)->update(['receipt_mail_status' => 'failed']);
        Log::error('Contact receipt could not be sent.', ['submission_id' => $this->submissionId, 'exception' => $exception]);
    }
}
