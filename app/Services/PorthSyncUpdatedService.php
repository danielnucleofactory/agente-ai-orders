<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PorthSyncUpdatedService
{
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
    public function syncRange(Carbon $start, Carbon $end, ?bool $dryRun = null): array
    {
        $dryRun = $dryRun ?? (bool) config('services.porth.sync_dry_run', false);
        $startIso = $start->toIso8601String();
        $endIso = $end->toIso8601String();

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

        $results = [];

        foreach ($ids as $id) {
            if (!is_string($id)) {
                continue;
            }

            $result = $this->processShipment($id, $dryRun);
            $results[$id] = $result;
        }

        return [
            'ids' => $ids,
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
    public function syncRecent(?int $lookbackHours = null, ?bool $dryRun = null): array
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

        return $this->syncRange($start, $end, $dryRun);
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
}
