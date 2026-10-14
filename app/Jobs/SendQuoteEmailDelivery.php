<?php

namespace App\Jobs;

use App\Mail\QuotationReplyMail;
use App\Mail\QuoteStatusMail;
use App\Mail\QuoteRequestReceivedMail;
use App\Models\QuoteEmailDelivery;
use App\Models\QuoteRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendQuoteEmailDelivery implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public int $deliveryId, public string $messageBody = '', public array $quotationDetails = [])
    {
        $this->onConnection('database');
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $delivery = QuoteEmailDelivery::with('quote.user')->findOrFail($this->deliveryId);
        if ($delivery->status === 'sent') {
            return;
        }

        $mail = match ($delivery->type) {
            'quotation' => new QuotationReplyMail($delivery->quote, $this->messageBody, $this->quotationDetails, $delivery->recipient_name),
            default => $delivery->stage === 'pending'
                ? new QuoteRequestReceivedMail($delivery->quote, $delivery->recipient_name)
                : new QuoteStatusMail($delivery->quote, $delivery->stage, $delivery->recipient_name),
        };

        Mail::to($delivery->recipient_email)->send($mail);

        DB::transaction(function () use ($delivery): void {
            $quote = QuoteRequest::whereKey($delivery->quote_request_id)->lockForUpdate()->firstOrFail();
            $delivery->update(['status' => 'sent', 'sent_at' => now()]);

            if ($delivery->type === 'quotation' && ! QuoteEmailDelivery::query()
                ->where('batch_id', $delivery->batch_id)->where('status', '!=', 'sent')->exists()) {
                $quote->update([
                    'quotation_mail_status' => 'sent',
                    'quotation_message' => $this->messageBody,
                    'quotation_details' => $this->quotationDetails,
                    'quotation_attachment' => null,
                    'quotation_sent_at' => now(),
                    'status' => 'quoted',
                ]);
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = QuoteEmailDelivery::find($this->deliveryId);
        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        $delivery->update(['status' => 'failed']);
        if ($delivery->type === 'quotation') {
            QuoteRequest::whereKey($delivery->quote_request_id)->update(['quotation_mail_status' => 'failed']);
        }
        Log::error('Quote email delivery failed.', [
            'delivery_id' => $this->deliveryId, 'quote_id' => $delivery->quote_request_id,
            'stage' => $delivery->stage, 'exception' => $exception,
        ]);
    }
}
