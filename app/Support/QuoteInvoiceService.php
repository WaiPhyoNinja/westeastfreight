<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteInvoiceService
{
    public function approve(QuoteRequest $quote, string $billTo = 'sender', ?array $invoicePricing = null): Invoice
    {
        if (! in_array($billTo, ['sender', 'receiver'], true)) {
            throw ValidationException::withMessages(['bill_to' => 'Choose the sender or receiver.']);
        }

        return DB::transaction(function () use ($quote, $billTo, $invoicePricing): Invoice {
            $locked = QuoteRequest::whereKey($quote->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'approved' && $locked->invoice) {
                return $locked->invoice;
            }

            if ($locked->status === 'rejected') {
                throw ValidationException::withMessages(['status' => 'A rejected request cannot be approved.']);
            }

            if ($invoicePricing === null && ! in_array($locked->status, ['quoted', 'approved'], true)) {
                throw ValidationException::withMessages(['status' => 'Send the quotation before approving it.']);
            }

            $invoice = $this->createFromQuote($locked, $billTo, $invoicePricing);
            if ($locked->status !== 'approved') {
                $locked->update(['status' => 'approved']);
            }

            return $invoice;
        });
    }

    public function ensureForApproved(QuoteRequest $quote): Invoice
    {
        if ($quote->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'The quotation must be approved first.']);
        }

        return DB::transaction(function () use ($quote): Invoice {
            $locked = QuoteRequest::whereKey($quote->id)->lockForUpdate()->firstOrFail();

            return $locked->invoice ?: $this->createFromQuote($locked, 'sender');
        });
    }

    public function validateQuote(QuoteRequest $quote): array
    {
        $pricing = $quote->quotation_details ?? [];
        $freight = $this->cents($pricing['freight_charge'] ?? null);
        $other = $this->cents($pricing['additional_charges'] ?? 0);
        $total = $this->cents($pricing['total'] ?? null);
        $currency = strtoupper((string) ($pricing['currency'] ?? ''));

        if ($quote->quotation_mail_status !== 'sent' || $freight <= 0 || $other < 0 || $total !== $freight + $other
            || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw ValidationException::withMessages(['status' => 'A sent quotation with valid charges is required before invoice creation.']);
        }

        return [$freight, $other, $total, $currency];
    }

    private function createFromQuote(QuoteRequest $quote, string $billTo, ?array $invoicePricing = null): Invoice
    {
        [$freight, $other, $total, $currency] = $invoicePricing === null
            ? $this->validateQuote($quote)
            : $this->validateInvoicePricing($invoicePricing);

        $details = $quote->details ?? [];
        $name = trim(($details[$billTo.'_first_name'] ?? '').' '.($details[$billTo.'_last_name'] ?? ''));
        $invoice = Invoice::create([
            'quote_request_id' => $quote->id,
            'date' => now('Asia/Yangon')->toDateString(),
            'currency' => $currency,
            'name' => $name ?: $quote->user?->name,
            'company_name' => $details[$billTo.'_company'] ?? null,
            'email' => $details[$billTo.'_email'] ?? $quote->user?->email,
            'phone' => $details[$billTo.'_phone'] ?? null,
            'address' => implode(', ', array_filter([
                $details[$billTo.'_address'] ?? null,
                $details[$billTo.'_city'] ?? null,
                $details[$billTo.'_state'] ?? null,
                $details[$billTo.'_postal_code'] ?? null,
                $details[$billTo.'_country'] ?? null,
            ], fn ($value) => filled($value))) ?: null,
            'origin' => implode(', ', array_filter([$details['sender_city'] ?? null, $details['sender_country'] ?? null])) ?: null,
            'destination' => implode(', ', array_filter([$details['receiver_city'] ?? null, $details['receiver_country'] ?? null])) ?: null,
            'weight' => isset($details['weight']) ? $details['weight'].' kg' : null,
            'commodity' => $details['description'] ?? null,
            'total_amount' => number_format($total / 100, 2, '.', ''),
        ]);
        $invoice->update(['invoice_number' => sprintf('WEF-INV-%s-%06d', now('Asia/Yangon')->format('Y'), $invoice->id)]);
        $invoice->items()->create(['description' => 'Freight charge', 'unit_price' => $this->money($freight), 'quantity' => 1]);
        if ($other > 0) {
            $invoice->items()->create(['description' => 'Other charges', 'unit_price' => $this->money($other), 'quantity' => 1]);
        }

        return $invoice;
    }

    private function validateInvoicePricing(array $pricing): array
    {
        $freight = $this->cents($pricing['freight_charge'] ?? null);
        $other = $this->cents($pricing['additional_charges'] ?? 0);
        $currency = strtoupper((string) ($pricing['currency'] ?? ''));

        if ($freight <= 0 || $other < 0 || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            throw ValidationException::withMessages(['freight_charge' => 'Enter a valid freight charge, other charges, and three-letter currency.']);
        }

        return [$freight, $other, $freight + $other, $currency];
    }

    private function cents(mixed $amount): int
    {
        return is_numeric($amount) ? (int) round(((float) $amount) * 100) : -1;
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
