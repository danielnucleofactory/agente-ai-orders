<?php

namespace App\Console\Commands;

use App\Models\PorthSyncRun;
use App\Services\NotificationService;
use App\Services\PorthSyncUpdatedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PorthSyncRecent extends Command
{
    protected $signature = 'porth:sync-recent 
                            {--hours= : Horas hacia atrás para buscar actualizaciones}
                            {--dry-run : Modo prueba, no modifica datos}
                            {--trigger=manual : Origen de la ejecución (manual, schedule, webhook)}';
    
    protected $description = 'Sincroniza shipments recientes desde Porth, registra el run y notifica el detalle.';

    public function __construct(
        protected PorthSyncUpdatedService $syncService,
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = $this->option('hours');
        $hours = $hours !== null ? max(1, (int) $hours) : (int) config('services.porth.sync_lookback_hours', 2);
        $dryRun = (bool) ($this->option('dry-run') ?? config('services.porth.sync_dry_run', false));
        $trigger = $this->option('trigger') ?: 'manual';

        $this->info("Iniciando sync Porth (hours={$hours}, dryRun=" . ($dryRun ? 'true' : 'false') . ", trigger={$trigger})");
        $startedAt = now();

        // Registrar corrida
        $run = PorthSyncRun::create([
            'trigger' => $trigger,
            'scope' => "recent:{$hours}h",
            'status' => 'running',
            'started_at' => $startedAt,
            'meta' => [
                'hours' => $hours,
                'dry_run' => $dryRun,
            ],
        ]);

        try {
            $summary = $this->syncService->syncRecent($hours, $dryRun);
            $ids = $summary['ids'] ?? [];
            $results = $summary['results'] ?? [];

            $counts = $this->calculateCounts($ids, $results);

            $status = $this->determineStatus($counts);

            $run->fill(array_merge($counts, [
                'status' => $status,
                'finished_at' => now(),
                'duration_ms' => abs((int) now()->diffInMilliseconds($startedAt, false)),
            ]))->save();

            Log::info('porth_sync_run_completed', [
                'run_id' => $run->id,
                'status' => $status,
                'counts' => $counts,
            ]);

            // Enviar notificaciones
            $this->sendNotifications($run, $results);

            // Mostrar resumen
            $this->displaySummary($run, $counts);

            $this->info("Sync Porth finalizado. Run #{$run->id} status={$status}");

        } catch (\Throwable $e) {
            $run->fill([
                'status' => 'failed',
                'error_summary' => $e->getMessage(),
                'finished_at' => now(),
                'duration_ms' => abs((int) now()->diffInMilliseconds($startedAt, false)),
            ])->save();

            Log::error('porth_sync_run_failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Sync Porth falló. Run #{$run->id} error={$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Calcula los contadores de resultados
     */
    private function calculateCounts(array $ids, array $results): array
    {
        $counts = [
            'total' => is_countable($ids) ? count($ids) : 0,
            'processed' => is_countable($results) ? count($results) : 0,
            'updated' => 0,
            'no_change' => 0,
            'skipped' => 0,
            'failed' => 0,
            'dry_run' => 0,
        ];

        foreach ($results as $result) {
            $status = $result['status'] ?? 'unknown';
            if ($status === 'imported') {
                $counts['updated']++;
            } elseif ($status === 'dry_run') {
                $counts['dry_run']++;
            } elseif ($status === 'skipped') {
                $counts['skipped']++;
            } elseif ($status === 'no_change') {
                $counts['no_change']++;
            } else {
                $counts['failed']++;
            }
        }

        return $counts;
    }

    /**
     * Determina el estado final de la corrida
     */
    private function determineStatus(array $counts): string
    {
        if ($counts['failed'] > 0) {
            return $counts['updated'] > 0 ? 'partial' : 'failed';
        }
        return 'success';
    }

    /**
     * Muestra resumen en consola
     */
    private function displaySummary(PorthSyncRun $run, array $counts): void
    {
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Run ID', $run->id],
                ['Total IDs', $counts['total']],
                ['Procesados', $counts['processed']],
                ['Actualizados', $counts['updated']],
                ['Sin cambios', $counts['no_change']],
                ['Omitidos', $counts['skipped']],
                ['Fallidos', $counts['failed']],
                ['Dry Run', $counts['dry_run']],
                ['Duración', ($run->duration_ms ?? 0) . 'ms'],
            ]
        );
    }

    /**
     * Envía notificaciones a usuarios configurados
     */
    private function sendNotifications(PorthSyncRun $run, array $results): void
    {
        $userIds = $this->notificationUserIds();
        if (empty($userIds)) {
            $this->info('Sin usuarios configurados para notificar (PORTH_SYNC_NOTIFICATION_USER_IDS vacío).');
            return;
        }

        foreach ($results as $porthId => $result) {
            $data = $this->buildNotificationData($run, $porthId, $result);
            $title = 'Sync Porth';
            $message = $this->buildNotificationMessage($data);

            $this->notificationService->notifyUsers(
                $userIds,
                'porth_sync',
                $title,
                $message,
                $data
            );
        }

        $this->info("Notificaciones enviadas a " . count($userIds) . " usuario(s).");
    }

    /**
     * Construye datos de notificación
     */
    private function buildNotificationData(PorthSyncRun $run, $porthId, array $result): array
    {
        $status = $result['status'] ?? 'unknown';
        $purchaseOrderId = $result['purchase_order_id'] ?? null;
        $orderNumber = $result['order_number'] ?? null;
        $updated = $status === 'imported';

        return [
            'schema_version' => 1,
            'porth_sync_runs_id' => $run->id,
            'purchase_order_id' => $purchaseOrderId,
            'order_number' => $orderNumber,
            'porth_id' => is_string($porthId) ? $porthId : ($result['porth_id'] ?? null),
            'porth_payload' => $result['porth_payload'] ?? null,
            'result' => [
                'action' => $updated ? 'update' : 'none',
                'status' => $status,
                'updated' => $updated,
                'changed_fields' => $result['changed_fields'] ?? null,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Construye mensaje de notificación
     */
    private function buildNotificationMessage(array $data): string
    {
        $status = $data['result']['status'] ?? 'unknown';
        $porthId = $data['porth_id'] ?? 'N/A';
        $purchaseOrderId = $data['purchase_order_id'] ?? null;
        $orderNumber = $data['order_number'] ?? null;

        $statusText = match ($status) {
            'imported' => 'fue actualizado',
            'skipped' => 'no tuvo coincidencia en Raga',
            'dry_run' => 'se revisó en modo prueba',
            'no_change' => 'no tuvo cambios',
            'failed' => 'falló al procesar',
            default => "tiene estado {$status}",
        };

        $parts = ["El embarque {$porthId}"];

        if ($orderNumber) {
            $parts[] = "de la orden de compra {$orderNumber}";
        } elseif ($purchaseOrderId) {
            $parts[] = "de la PO #{$purchaseOrderId}";
        }

        return implode(' ', $parts) . " {$statusText}.";
    }

    /**
     * Obtiene IDs de usuarios a notificar desde configuración
     */
    private function notificationUserIds(): array
    {
        $configured = config('services.porth.notification_user_ids');

        if (is_string($configured)) {
            $configured = array_filter(array_map('trim', explode(',', $configured)));
        }

        if (!is_array($configured)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $configured)));
    }
}
