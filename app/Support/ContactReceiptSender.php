<?php

namespace App\Support;

use App\Jobs\SendContactReceipt;
use App\Models\WebsiteSubmission;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactReceiptSender
{
    public function send(WebsiteSubmission $submission): string
    {
        if (! MailDelivery::isConfigured()) {
            $submission->update(['receipt_mail_status' => 'not_configured']);

            return 'not_configured';
        }

        try {
            $submission->update(['receipt_mail_status' => 'queued', 'receipt_mail_sent_at' => null]);
            SendContactReceipt::dispatch($submission->id);

            return 'queued';
        } catch (Throwable $exception) {
            $submission->update(['receipt_mail_status' => 'failed']);
            Log::error('Contact receipt could not be queued.', ['submission_id' => $submission->id, 'exception' => $exception]);

            return 'failed';
        }
    }
}
