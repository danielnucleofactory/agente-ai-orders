<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthSyncFailure extends Model
{
    use HasFactory;

    public const STATUS_PENDING_RETRY = 'pending_retry';
    public const STATUS_RETRYING = 'retrying';
    public const STATUS_RECOVERED = 'recovered';
    public const STATUS_PERMANENTLY_FAILED = 'permanently_failed';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'failure_key',
        'failure_source',
        'job_class',
        'document_type',
        'document_id',
        'purchase_order_id',
        'shipping_document_id',
        'order_number',
        'container_number',
        'porth_id',
        'status',
        'queue_attempts',
        'retry_attempts',
        'max_retry_attempts',
        'error_message',
        'error_context',
        'first_failed_at',
        'last_failed_at',
        'last_retry_at',
        'next_retry_at',
        'recovered_at',
        'last_retry_error',
    ];

    protected $casts = [
        'document_id' => 'integer',
        'purchase_order_id' => 'integer',
        'shipping_document_id' => 'integer',
        'queue_attempts' => 'integer',
        'retry_attempts' => 'integer',
        'max_retry_attempts' => 'integer',
        'error_context' => 'array',
        'first_failed_at' => 'datetime',
        'last_failed_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }

    public function scopePendingRetry($query)
    {
        return $query->where('status', self::STATUS_PENDING_RETRY);
    }

    public function scopeEligible($query)
    {
        return $query->pendingRetry()
            ->where(function ($query) {
                $query->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    public function markRetrying(): void
    {
        $this->update([
            'status' => self::STATUS_RETRYING,
            'last_retry_at' => now(),
            'retry_attempts' => (int) $this->retry_attempts + 1,
        ]);
    }

    public function markRecovered(): void
    {
        $this->update([
            'status' => self::STATUS_RECOVERED,
            'recovered_at' => now(),
            'next_retry_at' => null,
            'last_retry_error' => null,
        ]);
    }

    public function markPendingRetry(string $errorMessage, ?array $errorContext = null, ?\DateTimeInterface $nextRetryAt = null): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_RETRY,
            'last_retry_error' => $errorMessage,
            'error_context' => $errorContext ?? $this->error_context,
            'last_failed_at' => now(),
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    public function markPermanentlyFailed(string $errorMessage, ?array $errorContext = null): void
    {
        $this->update([
            'status' => self::STATUS_PERMANENTLY_FAILED,
            'last_retry_error' => $errorMessage,
            'error_context' => $errorContext ?? $this->error_context,
            'last_failed_at' => now(),
            'next_retry_at' => null,
        ]);
    }
}
