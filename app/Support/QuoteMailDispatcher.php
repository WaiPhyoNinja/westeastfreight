<?php

namespace App\Support;

use App\Jobs\SendQuoteEmailDelivery;
use App\Models\QuoteEmailDelivery;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class QuoteMailDispatcher
{
    public function queueQuotation(QuoteRequest $quote, string $message, array $pricing): int
    {
        return $this->queue($quote, 'quotation', 'quoted', $message, $pricing);
    }

    public function queueStatus(QuoteRequest $quote, string $stage): int
    {
        return $this->queue($quote, 'status', $stage);
    }

    private function queue(QuoteRequest $quote, string $type, string $stage, string $message = '', array $pricing = []): int
    {
        $recipients = $this->recipients($quote);

        if ($recipients === []) {
            throw new RuntimeException('No valid quotation email recipients are available.');
        }

        return DB::transaction(function () use ($quote, $type, $stage, $message, $pricing, $recipients): int {
            if ($type === 'quotation') {
                if ($quote->quotation_mail_status === 'queued') {
                    throw new RuntimeException('A quotation email is already queued.');
                }
                $quote->update(['quotation_mail_status' => 'queued']);
            }
            $batch = (string) Str::uuid();

            foreach ($recipients as $recipient) {
                $delivery = QuoteEmailDelivery::create([
                    'quote_request_id' => $quote->id,
                    'batch_id' => $batch,
                    'type' => $type,
                    'stage' => $stage,
                    'recipient_email' => $recipient['email'],
                    'recipient_name' => $recipient['name'],
                    'status' => 'queued',
                ]);

                SendQuoteEmailDelivery::dispatch($delivery->id, $message, $pricing);
            }

            return count($recipients);
        });
    }

    private function recipients(QuoteRequest $quote): array
    {
        $details = $quote->details ?? [];
        $people = [
            ['email' => $details['sender_email'] ?? $quote->user?->email,
                'name' => trim(($details['sender_first_name'] ?? '').' '.($details['sender_last_name'] ?? '')) ?: $quote->user?->name],
            ['email' => $details['receiver_email'] ?? null,
                'name' => trim(($details['receiver_first_name'] ?? '').' '.($details['receiver_last_name'] ?? ''))],
        ];
        $recipients = [];

        foreach ($people as $person) {
            $email = strtolower(trim((string) $person['email']));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || isset($recipients[$email])) {
                continue;
            }
            $recipients[$email] = ['email' => $email, 'name' => trim((string) ($person['name'] ?? ''))];
        }

        return array_values($recipients);
    }
}
