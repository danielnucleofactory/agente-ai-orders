<?php

namespace App\Services;

use App\Models\PorthSyncFailure;
use App\Models\PurchaseOrder;
use App\Models\ShippingDocument;
use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\Log;

class PorthSyncFailureService
{
    public function __construct(
        protected PorthSyncService $porthSyncService,
        protected PorthSyncUpdatedService $porthSyncUpdatedService,
    ) {
    }

    public function recordSyncJobFailure(int $documentId, string $documentType, Throwable $exception, int $queueAttempts = 0): PorthSyncFailure
    {
        $failureKey = $this->syncFailureKey($documentType, $documentId);
        $context = $this->resolveDocumentContext($documentType, $documentId);

        return $this->upsertFailure(
            failureKey: $failureKey,
            failureSource: 'sync_job',
            jobClass: \App\Jobs\PorthSyncJob::class,
            exception: $exception,
            queueAttempts: $queueAttempts,
            context: $context,
        );
    }

    public function recordImportJobFailure(string $porthId, Throwable $exception, int $queueAttempts = 0): PorthSyncFailure
    {
        $failureKey = $this->importFailureKey($porthId);
        $context = $this->resolvePorthIdContext($porthId);

        return $this->upsertFailure(
            failureKey: $failureKey,
            failureSource: 'import_job',
            jobClass: \App\Jobs\PorthImportJob::class,
            exception: $exception,
            queueAttempts: $queueAttempts,
            context: $context,
        );
    }

    public function retryFailure(PorthSyncFailure $failure): bool
    {
        $failure->markRetrying();

        try {
            $this->performRetry($failure);
            $failure->refresh();
            $failure->markRecovered();

            Log::info('porth_failure:recovered', [
                'failure_id' => $failure->id,
                'failure_key' => $failure->failure_key,
                'failure_source' => $failure->failure_source,
            ]);

            return true;
        } catch (Throwable $exception) {
            $failure->refresh();

            $errorContext = array_merge($failure->error_context ?? [], [
                'retry_exception_class' => $exception::class,
            ]);

            if ((int) $failure->retry_attempts >= (int) $failure->max_retry_attempts) {
                $failure->markPermanentlyFailed($exception->getMessage(), $errorContext);

                Log::error('porth_failure:permanently_failed', [
                    'failure_id' => $failure->id,
                    'failure_key' => $failure->failure_key,
                    'retry_attempts' => $failure->retry_attempts,
                    'error' => $exception->getMessage(),
                ]);
            } else {
                $failure->markPendingRetry(
                    $exception->getMessage(),
                    $errorContext,
                    $this->nextRetryAt((int) $failure->retry_attempts)
                );

                Log::warning('porth_failure:retry_failed', [
                    'failure_id' => $failure->id,
                    'failure_key' => $failure->failure_key,
                    'retry_attempts' => $failure->retry_attempts,
                    'next_retry_at' => optional($failure->fresh()->next_retry_at)?->toIso8601String(),
                    'error' => $exception->getMessage(),
                ]);
            }

            return false;
        }
    }

    protected function performRetry(PorthSyncFailure $failure): void
    {
        if ($failure->failure_source === 'sync_job') {
            if (blank($failure->document_type) || blank($failure->document_id)) {
                throw new RuntimeException('Fallo de sync sin document_type/document_id para reintentar.');
            }

            $result = $this->porthSyncService->syncDocument((int) $failure->document_id, $failure->document_type);

            if (! $result) {
                throw new RuntimeException('La sincronización de Porth no produjo enlace ni creación.');
            }

            return;
        }

        if ($failure->failure_source === 'import_job') {
            if (blank($failure->porth_id)) {
                throw new RuntimeException('Fallo de import sin porth_id para reintentar.');
            }

            $result = $this->porthSyncUpdatedService->syncById($failure->porth_id, false);
            $status = $result['results'][$failure->porth_id]['status'] ?? 'unknown';

            if ($status !== 'imported') {
                throw new RuntimeException("La importación de Porth terminó con estado {$status}.");
            }

            return;
        }

        throw new RuntimeException("Origen de fallo no soportado: {$failure->failure_source}");
    }

    protected function upsertFailure(
        string $failureKey,
        string $failureSource,
        string $jobClass,
        Throwable $exception,
        int $queueAttempts,
        array $context
    ): PorthSyncFailure {
        $existing = PorthSyncFailure::query()->where('failure_key', $failureKey)->first();

        $payload = [
            'failure_source' => $failureSource,
            'job_class' => $jobClass,
            'document_type' => $context['document_type'] ?? null,
            'document_id' => $context['document_id'] ?? null,
            'purchase_order_id' => $context['purchase_order_id'] ?? null,
            'shipping_document_id' => $context['shipping_document_id'] ?? null,
            'order_number' => $context['order_number'] ?? null,
            'container_number' => $context['container_number'] ?? null,
            'porth_id' => $context['porth_id'] ?? null,
            'status' => PorthSyncFailure::STATUS_PENDING_RETRY,
            'queue_attempts' => $queueAttempts,
            'max_retry_attempts' => (int) config('services.porth.failed_retry_max_attempts', 5),
            'error_message' => $exception->getMessage(),
            'error_context' => array_merge($context, [
                'exception_class' => $exception::class,
            ]),
            'last_failed_at' => now(),
            'next_retry_at' => $this->nextRetryAt($existing ? ((int) $existing->retry_attempts) : 0),
        ];

        if (! $existing) {
            $payload['failure_key'] = $failureKey;
            $payload['retry_attempts'] = 0;
            $payload['first_failed_at'] = now();

            $failure = PorthSyncFailure::create($payload);
        } else {
            $existing->fill($payload)->save();
            $failure = $existing->fresh();
        }

        Log::error('porth_failure:recorded', [
            'failure_id' => $failure->id,
            'failure_key' => $failure->failure_key,
            'failure_source' => $failure->failure_source,
            'queue_attempts' => $queueAttempts,
            'error' => $exception->getMessage(),
        ]);

        return $failure;
    }

    protected function resolveDocumentContext(string $documentType, int $documentId): array
    {
        $context = [
            'document_type' => $documentType,
            'document_id' => $documentId,
            'purchase_order_id' => null,
            'shipping_document_id' => null,
            'order_number' => null,
            'container_number' => null,
            'porth_id' => null,
        ];

        if ($documentType === PurchaseOrder::class) {
            $po = PurchaseOrder::find($documentId);
            if ($po) {
                $context['purchase_order_id'] = $po->id;
                $context['order_number'] = $po->order_number;
                $context['container_number'] = $po->container_number;
                $context['porth_id'] = $po->porth_id;
            }
        }

        if ($documentType === ShippingDocument::class) {
            $shippingDocument = ShippingDocument::find($documentId);
            if ($shippingDocument) {
                $context['shipping_document_id'] = $shippingDocument->id;
                $context['container_number'] = $shippingDocument->container_number;
                $context['porth_id'] = $shippingDocument->porth_id;
            }
        }

        return $context;
    }

    protected function resolvePorthIdContext(string $porthId): array
    {
        $po = PurchaseOrder::where('porth_id', $porthId)->first();
        $shippingDocument = ShippingDocument::where('porth_id', $porthId)->first();

        return [
            'document_type' => $po ? PurchaseOrder::class : ($shippingDocument ? ShippingDocument::class : null),
            'document_id' => $po?->id ?? $shippingDocument?->id,
            'purchase_order_id' => $po?->id,
            'shipping_document_id' => $shippingDocument?->id,
            'order_number' => $po?->order_number,
            'container_number' => $po?->container_number ?? $shippingDocument?->container_number,
            'porth_id' => $porthId,
        ];
    }

    protected function nextRetryAt(int $retryAttempts): \Illuminate\Support\Carbon
    {
        $baseMinutes = max(5, (int) config('services.porth.failed_retry_base_minutes', 60));
        $multiplier = max(1, 2 ** max(0, $retryAttempts));

        return now()->addMinutes(min($baseMinutes * $multiplier, 24 * 60));
    }

    protected function syncFailureKey(string $documentType, int $documentId): string
    {
        return sprintf('sync_job:%s:%s', $documentType, $documentId);
    }

    protected function importFailureKey(string $porthId): string
    {
        return sprintf('import_job:%s', $porthId);
    }
}
