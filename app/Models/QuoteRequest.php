<?php

namespace App\Models;

use App\Support\MailDelivery;
use App\Support\QuoteMailDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Throwable;

class QuoteRequest extends Model
{
    protected static function booted(): void
    {
        static::updated(function (QuoteRequest $quote): void {
            if (! $quote->wasChanged('status') || ! in_array($quote->status, ['pending', 'reviewed', 'approved', 'rejected'], true)
                || ! MailDelivery::isConfigured()) {
                return;
            }

            try {
                app(QuoteMailDispatcher::class)->queueStatus($quote, $quote->status);
            } catch (Throwable $exception) {
                Log::error('Quote status email could not be queued.', [
                    'quote_id' => $quote->id, 'status' => $quote->status, 'exception' => $exception,
                ]);
            }
        });
    }

    protected $fillable = ['user_id', 'status', 'details', 'quotation_message', 'quotation_attachment', 'quotation_sent_at', 'quotation_mail_status', 'quotation_details'];

    protected function casts(): array
    {
        return ['details' => 'array', 'quotation_details' => 'array', 'quotation_sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailDeliveries(): HasMany
    {
        return $this->hasMany(QuoteEmailDelivery::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function frontendPayload(): array
    {
        return [...$this->details, 'id' => $this->id, 'status' => $this->status,
            'user_created' => $this->user_id, 'date_created' => $this->created_at->toIso8601String(),
            'date_updated' => $this->updated_at->toIso8601String()];
    }
}
