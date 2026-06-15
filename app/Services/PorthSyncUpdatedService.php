<?php

namespace App\Services;

use App\Exceptions\PorthTemporarilyBlockedException;
use App\Models\PorthSyncBacklog;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PorthSyncUpdatedService
{
    protected const LAST_RECENT_SYNC_CACHE_KEY = 'porth:sync_recent:last_end_at';
    protected const RECENT_SYNC_OVERLAP_MINUTES = 2;
    protected const BACKLOG_SOURCE_LAST_UPDATED = 'last_updated';
    protected const BACKLOG_SOURCE_RECONCILE = 'reconcile_linked';

    public function __construct(
        protected PorthApiService $api,
        protected PorthImportService $importer
    ) {
    }

    /**
     * Sincroniza shipments actualizados en el rango dado.
     * Todos los IDs candidatos quedan encolados; cada ejecución procesa solo
     * el límite configurado para no perder ventanas grandes de Porth.
     */
    public function syncRange(
        Carbon $start,
        Carbon $end,
        ?bool $dryRun = null,
        ?int $limit = null,
        bool $linkedOnly = true
    ): array {
        $dryRun = $dryRun ?? (bool) config('services.porth.sync_dry_run', false);
        $limit = $this->normalizeLimit($limit);
        $startIso = $start->toIso8601String();
        $endIso = $end->toIso8601String();

        $fetchedIds = $this->normalizePorthIds($this->api->listLastUpdated($startIso, $endIso));
        $candidateIds = $linkedOnly ? $this->filterLinkedPorthIds($fetchedIds) : $fetchedIds;

        Log::info('porth_sync_range:ids_fetched', [
            'start' => $startIso,
            'end' => $endIso,
            'dry_run' => $dryRun,
            'fetched_count' => count($fetchedIds),
            'candidate_count' => count($candidateIds),
            'filtered_out_unlinked' => count($fetchedIds) - count($candidateIds),
            'limit' => $limit,
            'linked_only' => $linkedOnly,
        ]);

        if (! $dryRun) {
            $this->enqueuePorthIds($candidateIds, self::BACKLOG_SOURCE_LAST_UPDATED, [
                'start' => $startIso,
                'end' => $endIso,
            ]);
        }

        $idsToProcess = $dryRun
            ? array_slice($candidateIds, 0, $limit)
            : $this->claimPendingBacklogIds($limit);

        $results = $this->processIds($idsToProcess, $dryRun);

        return [
            'ids' => $idsToProcess,
            'results' => $results,
            'meta' => [
                'fetched_count' => count($fetchedIds),
                'candidate_count' => count($candidateIds),
                'enqueued_count' => $dryRun ? 0 : count($candidateIds),
                'processed_from_backlog' => count($results),
                'backlog_remaining' => $dryRun ? null : $this->pendingBacklogCount(),
                'filtered_out_unlinked' => count($fetchedIds) - count($candidateIds),
                'limit' => $limit,
                'linked_only' => $linkedOnly,
            ],
        ];
    }

    /**
     * Procesa un shipment individual.
     */
    protected function processShipment(string $id, bool $dryRun): array
    {
        $detail = $this->api->getShipmentById($id);

        if (!$detail) {
            return [
                'status' => 'not_found_or_failed',
                'purchase_order_id' => null,
                'order_number' => null,
                'porth_payload' => null,
            ];
        }

        if ($dryRun) {
            Log::info('porth_sync_range:dry_run_preview', [
                'porth_id' => $id,
                'payload' => $detail,
            ]);
            return [
                'status' => 'dry_run',
                'purchase_order_id' => null,
                'order_number' => null,
                'porth_payload' => $detail,
            ];
        }

        try {
            $po = $this->importer->importShipment($detail);

            return [
                'status' => $po ? 'imported' : 'skipped',
                'purchase_order_id' => $po?->id,
                'order_number' => $po?->order_number,
                'porth_payload' => $detail,
            ];
        } catch (\Exception $e) {
            Log::error('porth_sync_range:import_error', [
                'porth_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return [
                'status' => 'failed',
                'purchase_order_id' => null,
                'order_number' => null,
                'porth_payload' => $detail,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sincroniza usando horas de retroceso configurables en .env.
     */
    public function syncRecent(
        ?int $lookbackHours = null,
        ?bool $dryRun = null,
        ?int $limit = null,
        bool $linkedOnly = true
    ): array {
        $hours = $lookbackHours ?? (int) config('services.porth.sync_lookback_hours', 2);
        if ($hours < 1) {
            $hours = 1;
        }

        $end = Carbon::now();
        $start = $end->copy()->subHours($hours);

        Log::info('porth_sync_recent:starting', [
            'hours' => $hours,
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
            'dry_run' => $dryRun,
            'limit' => $this->normalizeLimit($limit),
            'linked_only' => $linkedOnly,
        ]);

        return $this->syncRange($start, $end, $dryRun, $limit, $linkedOnly);
    }

    /**
     * Sincroniza de forma incremental para evitar reescaneos completos.
     */
    public function syncRecentIncremental(
        ?int $lookbackHours = null,
        ?bool $dryRun = null,
        ?int $limit = null,
        bool $linkedOnly = true
    ): array {
        $hours = $lookbackHours ?? (int) config('services.porth.sync_lookback_hours', 2);
        if ($hours < 1) {
            $hours = 1;
        }

        $dryRun = $dryRun ?? (bool) config('services.porth.sync_dry_run', false);
        $end = Carbon::now();
        $fallbackStart = $end->copy()->subHours($hours);
        $cachedLastEnd = Cache::get(self::LAST_RECENT_SYNC_CACHE_KEY);

        $start = $fallbackStart;
        if ($cachedLastEnd) {
            try {
                $start = Carbon::parse($cachedLastEnd)->subMinutes(self::RECENT_SYNC_OVERLAP_MINUTES);
            } catch (\Throwable $e) {
                Log::warning('porth_sync_recent_incremental:invalid_cached_last_end', [
                    'cached_value' => $cachedLastEnd,
                    'error' => $e->getMessage(),
                ]);
                $start = $fallbackStart;
            }
        }

        if ($start->greaterThan($end)) {
            $start = $fallbackStart;
        }

        Log::info('porth_sync_recent_incremental:starting', [
            'hours' => $hours,
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
            'dry_run' => $dryRun,
            'cached_last_end' => $cachedLastEnd,
            'overlap_minutes' => self::RECENT_SYNC_OVERLAP_MINUTES,
            'limit' => $this->normalizeLimit($limit),
            'linked_only' => $linkedOnly,
        ]);

        $summary = $this->syncRange($start, $end, $dryRun, $limit, $linkedOnly);

        if (! $dryRun && ! $this->hasTemporaryBlock($summary)) {
            Cache::forever(self::LAST_RECENT_SYNC_CACHE_KEY, $end->toIso8601String());
        }

        return $summary;
    }

    /**
     * Sincroniza un shipment específico por ID de Porth.
     */
    public function syncById(string $porthId, bool $dryRun = false): array
    {
        Log::info('porth_sync_by_id:starting', [
            'porth_id' => $porthId,
            'dry_run' => $dryRun,
        ]);

        $result = $this->processShipment($porthId, $dryRun);

        return [
            'ids' => [$porthId],
            'results' => [$porthId => $result],
        ];
    }

    /**
     * Encola POs ya vinculadas para reconciliación manual/operativa.
     */
    public function enqueueLinkedPurchaseOrders(?int $limit = null, bool $staleOnly = false, bool $dryRun = false): array
    {
        $query = PurchaseOrder::query()
            ->whereNotNull('porth_id')
            ->where('porth_id', '!=', '')
            ->orderBy('id');

        if ($staleOnly) {
            $query->where(function ($query) {
                $query->whereNull('last_porth_sync_at')
                    ->orWhereDoesntHave('porthItineraries')
                    ->orWhereColumn('last_porth_sync_at', '<', 'updated_at');
            });
        }

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $ids = $this->normalizePorthIds($query->pluck('porth_id')->all());

        if (! $dryRun) {
            $this->enqueuePorthIds($ids, self::BACKLOG_SOURCE_RECONCILE);
        }

        return [
            'ids' => $ids,
            'enqueued_count' => $dryRun ? 0 : count($ids),
            'dry_run' => $dryRun,
            'stale_only' => $staleOnly,
            'backlog_remaining' => $dryRun ? null : $this->pendingBacklogCount(),
        ];
    }

    public function processBacklog(?int $limit = null, bool $dryRun = false): array
    {
        $limit = $this->normalizeLimit($limit);
        $idsToProcess = $dryRun
            ? $this->peekPendingBacklogIds($limit)
            : $this->claimPendingBacklogIds($limit);

        $results = $this->processIds($idsToProcess, $dryRun);

        return [
            'ids' => $idsToProcess,
            'results' => $results,
            'meta' => [
                'processed_from_backlog' => count($results),
                'backlog_remaining' => $dryRun ? null : $this->pendingBacklogCount(),
                'limit' => $limit,
            ],
        ];
    }

    public function pendingBacklogCount(): int
    {
        return PorthSyncBacklog::query()
            ->whereIn('status', [PorthSyncBacklog::STATUS_PENDING, PorthSyncBacklog::STATUS_FAILED])
            ->where(function ($query) {
                $query->whereNull('next_attempt_at')
                    ->orWhere('next_attempt_at', '<=', now());
            })
            ->count();
    }

    protected function processIds(array $ids, bool $dryRun): array
    {
        $results = [];

        foreach ($ids as $index => $id) {
            try {
                $result = $this->processShipment($id, $dryRun);
            } catch (PorthTemporarilyBlockedException $e) {
                $remainingIds = array_slice($ids, $index + 1);

                Log::warning('porth_sync_range:stopped_temporarily_blocked', [
                    'porth_id' => $id,
                    'retry_after_seconds' => $e->retryAfterSeconds(),
                    'processed_count' => count($results),
                    'remaining_count' => count($remainingIds),
                ]);

                $result = [
                    'status' => 'temporarily_blocked',
                    'purchase_order_id' => null,
                    'order_number' => null,
                    'porth_payload' => null,
                    'retry_after_seconds' => $e->retryAfterSeconds(),
                ];

                $results[$id] = $result;
                if (! $dryRun) {
                    $this->finishBacklogItem($id, $result);
                    $this->releaseUnprocessedClaimedIds($remainingIds, $e->retryAfterSeconds());
                }
                break;
            }

            $results[$id] = $result;

            if (! $dryRun) {
                $this->finishBacklogItem($id, $result);
            }
        }

        return $results;
    }

    protected function enqueuePorthIds(array $ids, string $source, array $metadata = []): int
    {
        $now = now();
        $count = 0;

        foreach ($this->normalizePorthIds($ids) as $id) {
            $item = PorthSyncBacklog::firstOrNew(['porth_id' => $id]);

            if (! $item->exists) {
                $item->discovered_at = $now;
                $item->attempts = 0;
            }

            if ($item->status !== PorthSyncBacklog::STATUS_PROCESSING) {
                $item->status = PorthSyncBacklog::STATUS_PENDING;
                $item->next_attempt_at = $now;
                $item->processing_started_at = null;
                $item->processed_at = null;
                $item->last_error = null;
            }

            $item->source = $source;
            $item->metadata = array_filter(array_merge($item->metadata ?? [], $metadata));
            $item->save();
            $count++;
        }

        return $count;
    }

    protected function claimPendingBacklogIds(int $limit): array
    {
        $this->releaseStaleProcessingItems();

        return DB::transaction(function () use ($limit) {
            $items = PorthSyncBacklog::query()
                ->whereIn('status', [PorthSyncBacklog::STATUS_PENDING, PorthSyncBacklog::STATUS_FAILED])
                ->where(function ($query) {
                    $query->whereNull('next_attempt_at')
                        ->orWhere('next_attempt_at', '<=', now());
                })
                ->orderBy('priority')
                ->orderBy('discovered_at')
                ->orderBy('id')
                ->limit($limit)
                ->lockForUpdate()
                ->get();

            $ids = $items->pluck('porth_id')->all();

            if (! empty($ids)) {
                PorthSyncBacklog::whereIn('id', $items->pluck('id')->all())->update([
                    'status' => PorthSyncBacklog::STATUS_PROCESSING,
                    'processing_started_at' => now(),
                    'attempts' => DB::raw('attempts + 1'),
                    'updated_at' => now(),
                ]);
            }

            return $ids;
        });
    }

    protected function releaseUnprocessedClaimedIds(array $ids, ?int $retryAfterSeconds = null): int
    {
        $ids = $this->normalizePorthIds($ids);
        if (empty($ids)) {
            return 0;
        }

        $nextAttemptAt = $retryAfterSeconds
            ? now()->addSeconds($retryAfterSeconds)
            : now()->addMinutes($this->processingTimeoutMinutes());

        return PorthSyncBacklog::whereIn('porth_id', $ids)
            ->where('status', PorthSyncBacklog::STATUS_PROCESSING)
            ->update([
                'status' => PorthSyncBacklog::STATUS_PENDING,
                'next_attempt_at' => $nextAttemptAt,
                'processing_started_at' => null,
                'last_error' => 'released_after_temporary_block',
                'updated_at' => now(),
            ]);
    }

    protected function releaseStaleProcessingItems(): int
    {
        $cutoff = now()->subMinutes($this->processingTimeoutMinutes());

        return PorthSyncBacklog::query()
            ->where('status', PorthSyncBacklog::STATUS_PROCESSING)
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('processing_started_at')
                    ->orWhere('processing_started_at', '<=', $cutoff);
            })
            ->update([
                'status' => PorthSyncBacklog::STATUS_PENDING,
                'next_attempt_at' => now(),
                'processing_started_at' => null,
                'last_error' => 'released_stale_processing',
                'updated_at' => now(),
            ]);
    }

    protected function peekPendingBacklogIds(int $limit): array
    {
        return PorthSyncBacklog::query()
            ->whereIn('status', [PorthSyncBacklog::STATUS_PENDING, PorthSyncBacklog::STATUS_FAILED])
            ->where(function ($query) {
                $query->whereNull('next_attempt_at')
                    ->orWhere('next_attempt_at', '<=', now());
            })
            ->orderBy('priority')
            ->orderBy('discovered_at')
            ->orderBy('id')
            ->limit($limit)
            ->pluck('porth_id')
            ->all();
    }

    protected function finishBacklogItem(string $porthId, array $result): void
    {
        $status = $result['status'] ?? 'unknown';
        $item = PorthSyncBacklog::where('porth_id', $porthId)->first();

        if (! $item) {
            return;
        }

        if ($status === 'temporarily_blocked') {
            $retryAfterSeconds = $result['retry_after_seconds'] ?? null;
            $item->status = PorthSyncBacklog::STATUS_FAILED;
            $item->last_error = 'temporarily_blocked';
            $item->next_attempt_at = now()->addSeconds($retryAfterSeconds ?: ($this->retryDelayMinutes($item->attempts) * 60));
            $item->processing_started_at = null;
            $item->save();
            return;
        }

        if (in_array($status, ['failed', 'not_found_or_failed'], true)) {
            $item->status = PorthSyncBacklog::STATUS_FAILED;
            $item->last_error = $result['error'] ?? $status;
            $item->next_attempt_at = now()->addMinutes($this->retryDelayMinutes($item->attempts));
            $item->processing_started_at = null;
            $item->save();
            return;
        }

        $item->status = PorthSyncBacklog::STATUS_PROCESSED;
        $item->processed_at = now();
        $item->next_attempt_at = null;
        $item->processing_started_at = null;
        $item->last_error = null;
        $item->save();
    }

    protected function normalizePorthIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => is_string($id) || is_numeric($id))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function filterLinkedPorthIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $linkedIds = PurchaseOrder::query()
            ->whereIn('porth_id', $ids)
            ->pluck('porth_id')
            ->map(fn ($id) => trim((string) $id))
            ->unique()
            ->flip();

        return collect($ids)
            ->filter(fn ($id) => $linkedIds->has($id))
            ->values()
            ->all();
    }

    protected function normalizeLimit(?int $limit): int
    {
        $limit = $limit ?? (int) config('services.porth.sync_max_shipments_per_run', 100);

        return max(1, $limit);
    }

    protected function retryDelayMinutes(int $attempts): int
    {
        return min(240, max(10, $attempts * 30));
    }

    protected function processingTimeoutMinutes(): int
    {
        return max(5, (int) config('services.porth.sync_processing_timeout_minutes', 30));
    }

    protected function hasTemporaryBlock(array $summary): bool
    {
        foreach (($summary['results'] ?? []) as $result) {
            if (($result['status'] ?? null) === 'temporarily_blocked') {
                return true;
            }
        }

        return false;
    }
}
