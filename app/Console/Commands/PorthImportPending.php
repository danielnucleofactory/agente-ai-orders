<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\PorthSyncUpdatedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Comando para importar datos de Porth para POs recién vinculadas.
 * 
 * Busca PurchaseOrders que tienen porth_id asignado pero aún no tienen
 * last_porth_sync_at (pendientes de primera importación).
 * 
 * Se ejecuta cada 5 minutos mediante el scheduler para asegurar que
 * los datos se importen rápidamente después de vincular un embarque.
 */
class PorthImportPending extends Command
{
    protected $signature = 'porth:import-pending 
                            {--limit=50 : Máximo de POs a procesar por ejecución}
                            {--dry-run : Modo prueba, no modifica datos}';
    
    protected $description = 'Importa datos de Porth para POs recién vinculadas (con porth_id pero sin last_porth_sync_at)';

    public function __construct(
        protected PorthSyncUpdatedService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Buscando POs pendientes de importación desde Porth...");

        // Buscar POs pendientes de primera importación
        $pendingPOs = PurchaseOrder::whereNotNull('porth_id')
            ->whereNull('last_porth_sync_at')
            ->limit($limit)
            ->get();

        $count = $pendingPOs->count();
        
        if ($count === 0) {
            $this->info("No hay POs pendientes de importación.");
            return self::SUCCESS;
        }

        $this->info("Encontradas {$count} PO(s) pendiente(s) de importación.");

        if ($dryRun) {
            $this->warn("Modo DRY-RUN activado - no se modificarán datos.");
            $this->newLine();
        }

        $imported = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($pendingPOs as $po) {
            $this->line("Procesando PO #{$po->id} (Order: {$po->order_number}, Porth ID: {$po->porth_id})");

            if ($dryRun) {
                $this->info("  [DRY-RUN] Se importaría: {$po->porth_id}");
                $imported++;
                continue;
            }

            try {
                $result = $this->syncService->syncById($po->porth_id, false);
                $status = $result['results'][$po->porth_id]['status'] ?? 'unknown';

                if ($status === 'imported') {
                    $imported++;
                    $this->info("  ✓ Importado correctamente");
                } elseif ($status === 'skipped') {
                    $skipped++;
                    $this->warn("  ⚠ Omitido (no se encontró coincidencia en el sistema)");
                } else {
                    $failed++;
                    $this->error("  ✗ Status: {$status}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("  ✗ Error: {$e->getMessage()}");
                Log::error('porth:import-pending:error', [
                    'purchase_order_id' => $po->id,
                    'order_number' => $po->order_number,
                    'porth_id' => $po->porth_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->line("  - Importadas: {$imported}");
        if ($skipped > 0) {
            $this->line("  - Omitidas: {$skipped}");
        }
        if ($failed > 0) {
            $this->line("  - Fallidas: {$failed}");
        }

        Log::info('porth:import-pending:completed', [
            'total' => $count,
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'dry_run' => $dryRun,
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
