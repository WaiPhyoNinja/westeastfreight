<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'quote_request_id', 'invoice_number', 'date', 'carrier', 'origin', 'destination', 'total_amount',
        'currency', 'name', 'company_name', 'email', 'phone', 'address',
        'etd_pol', 'eta_dest', 'volume', 'weight', 'commodity', 'terms_of_payment', 'bl_no', 'cargo_type',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'total_amount' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::saving(function (Invoice $invoice): void {
            if ($invoice->total_amount === null || $invoice->total_amount === '') {
                $invoice->total_amount = 0;
            }
        });
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()->get(['unit_price', 'quantity'])->sum(
            fn (InvoiceItem $item): float => (float) $item->unit_price * (int) $item->quantity
        );

        $this->forceFill(['total_amount' => round($total, 2)])->saveQuietly();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }
}
