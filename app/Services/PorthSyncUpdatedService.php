<?php

namespace App\Services;

use App\Exceptions\PorthTemporarilyBlockedException;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PorthSyncUpdatedService
{
    protected const LAST_RECENT_SYNC_CACHE_KEY = 'porth:sync_recent:last_end_at';
    protected const RECENT_SYNC_OVERLAP_MINUTES = 2;

    public function __construct(
        protected PorthApiService $api,
        protected PorthImportService $importer
    ) {
    }

    /**
     * Sincroniza shipments actualizados en el rango dado.
     *
     * @param Carbon $start Fecha de inicio
     * @param Carbon $end Fecha de fin
     * @param bool|null $dryRun Modo prueba (no modifica datos)
     * @return array Resumen con ids procesados y resultados por id
     */
    public function syncRange(Carbon $start, Carbon $end, ?bool $dryRun = null, bool $linkedOnly = true, ?int $maxShipments = null): array
    {
        $dryRun = $dryRun ?? (bool) config('services.porth.sync_dry_run', false);
        $startIso = $start->toIso8601String();
        $endIso = $end->toIso8601String();
        $maxShipments = $maxShipments ?? (int) config('services.porth.sync_max_shipments_per_run', 100);

        $ids = $this->api->listLastUpdated($startIso, $endIso);

        Log::info('porth_sync_range:ids_fetched', [
            'start' => $startIso,
            'end' => $endIso,
            'dry_run' => $dryRun,
            'count' => is_array($ids) ? count($ids) : 0,
        ]);

        if (!is_array($ids)) {
            Log::warning('porth_sync_range:unexpected_response', [
                'start' => $startIso,
                'end' => $endIso,
                'response_type' => gettype($ids),
            ]);
            return ['ids' => [], 'results' => []];
        }

        $candidateIds = array_values(array_filter($ids, fn ($id) => is_string($id) && $id !== ''));
        $filteredOut = 0;

        if ($linkedOnly) {
            $linkedIds = $this->linkedPurchaseOrderPorthIds($candidateIds);
            $linkedLookup = array_flip($linkedIds);
            $filteredIds = array_values(array_filter($candidateIds, fn (string $id) => isset($linkedLookup[$id])));
            $filteredOut = count($candidateIds) - count($filteredIds);
            $candidateIds = $filteredIds;
        }

        if ($maxShipments > 0 && count($candidateIds) > $maxShipments) {
            $candidateIds = array_slice($candidateIds, 0, $maxShipments);
        }

        Log::info('porth_sync_range:ids_selected', [
            'fetched_count' => count($ids),
            'candidate_count' => count($candidateIds),
            'filtered_out_unlinked' => $filteredOut,
            'linked_only' => $linkedOnly,
            'max_shipments' => $maxShipments,
        ]);

        $results = [];

        foreach ($candidateIds as $id) {
            try {
                $result = $this->processShipment($id, $dryRun);
                $results[$id] = $result;
            } catch (PorthTemporarilyBlockedException $e) {
                Log::warning('porth_sync_range:stopped_temporarily_blocked', [
                    'porth_id' => $id,
                    'retry_after_seconds' => $e->retryAfterSeconds(),
                    'processed_count' => count($results),
                    'remaining_count' => max(count($candidateIds) - count($results), 0),
                ]);

                $results[$id] = [
                    'status' => 'temporarily_blocked',
                    'purchase_order_id' => null,
                    'order_number' => null,
                    'porth_payload' => null,
                    'retry_after_seconds' => $e->retryAfterSeconds(),
                ];

                break;
            }
        }

        return [
            'ids' => $candidateIds,
            'fetched_ids_count' => count($ids),
            'filtered_out_unlinked' => $filteredOut,
            'results' => $results,
        ];
    }

    /**
     * Procesa un shipment individual
     * 
     * @param string $id ID del shipment
     * @param bool $dryRun Modo prueba
     * @return array Resultado del procesamiento
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
     * Sincroniza usando horas de retroceso configurables en .env
     * (por defecto 2 horas).
     * 
     * @param int|null $lookbackHours Horas hacia atrás (null usa config)
     * @param bool|null $dryRun Modo prueba
     * @return array Resumen de la sincronización
     */
    public function syncRecent(?int $lookbackHours = null, ?bool $dryRun = null, bool $linkedOnly = true, ?int $maxShipments = null): array
    {
        $hours = $lookbackHours ?? (int) config('services.porth.sync_lookback_hours', 2);
        if ($hours < 1) {
            $hours = 1; // evita rangos vacíos
        }

        $end = Carbon::now();
        $start = $end->copy()->subHours($hours);

        Log::info('porth_sync_recent:starting', [
            'hours' => $hours,
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
            'dry_run' => $dryRun,
        ]);

        return $this->syncRange($start, $end, $dryRun, $linkedOnly, $maxShipments);
    }

    /**
     * Sincroniza de forma incremental para evitar reescaneos completos
     * de la misma ventana en cada corrida programada.
     */
    public function syncRecentIncremental(?int $lookbackHours = null, ?bool $dryRun = null, bool $linkedOnly = true, ?int $maxShipments = null): array
    {
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
        ]);

        $summary = $this->syncRange($start, $end, $dryRun, $linkedOnly, $maxShipments);

        if (! $dryRun && ! $this->hasTemporaryBlock($summary)) {
            Cache::forever(self::LAST_RECENT_SYNC_CACHE_KEY, $end->toIso8601String());
        }

        return $summary;
    }

    /**
     * Sincroniza un shipment específico por ID de Porth
     * 
     * @param string $porthId ID del shipment en Porth
     * @param bool $dryRun Modo prueba
     * @return array Resultado de la sincronización
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

    protected function hasTemporaryBlock(array $summary): bool
    {
        foreach (($summary['results'] ?? []) as $result) {
            if (($result['status'] ?? null) === 'temporarily_blocked') {
                return true;
            }
        }

        return false;
    }

    protected function linkedPurchaseOrderPorthIds(array $ids): array
    {
        return PurchaseOrder::query()
            ->whereIn('porth_id', $ids)
            ->pluck('porth_id')
            ->all();
    }
}
