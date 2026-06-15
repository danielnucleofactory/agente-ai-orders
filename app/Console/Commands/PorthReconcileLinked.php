<?php

namespace App\Console\Commands;

use App\Services\PorthSyncUpdatedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PorthReconcileLinked extends Command
{
    protected $signature = 'porth:reconcile-linked
                            {--limit=0 : Máximo de POs vinculadas a encolar (0 = todas)}
                            {--stale-only : Encola solo POs con señales locales de sincronización incompleta}
                            {--process : Procesa inmediatamente un batch después de encolar}
                            {--max=100 : Máximo de embarques a procesar si se usa --process}
                            {--dry-run : Muestra qué se encolaría/procesaría sin modificar datos}';

    protected $description = 'Encola POs con porth_id para reconciliar datos que Porth no reportó vía lastUpdated.';

    public function __construct(
        protected PorthSyncUpdatedService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config('services.porth.sync_enabled', true)) {
            $this->warn('Porth sync deshabilitado por PORTH_SYNC_ENABLED=false.');
            Log::info('porth_reconcile_linked:disabled_by_config');

            return self::SUCCESS;
        }

        $limitOption = (int) $this->option('limit');
        $limit = $limitOption > 0 ? $limitOption : null;
        $max = max(1, (int) $this->option('max'));
        $staleOnly = (bool) $this->option('stale-only');
        $process = (bool) $this->option('process');
        $dryRun = (bool) $this->option('dry-run');

        $limitLabel = $limit === null ? 'all' : (string) $limit;
        $this->info("Encolando POs vinculadas a Porth (limit={$limitLabel}, staleOnly=" . ($staleOnly ? 'true' : 'false') . ", dryRun=" . ($dryRun ? 'true' : 'false') . ")");

        $enqueueSummary = $this->syncService->enqueueLinkedPurchaseOrders($limit, $staleOnly, $dryRun);
        $ids = $enqueueSummary['ids'] ?? [];

        $this->line('IDs encontrados: ' . count($ids));
        $this->line('IDs encolados: ' . ($enqueueSummary['enqueued_count'] ?? 0));

        if ($dryRun && ! empty($ids)) {
            $this->line('Primeros IDs: ' . implode(', ', array_slice($ids, 0, 10)));
        }

        if ($process) {
            $this->newLine();
            $this->info("Procesando cola Porth (max={$max})...");
            $processSummary = $this->syncService->processBacklog($max, $dryRun);
            $results = $processSummary['results'] ?? [];
            $counts = collect($results)->countBy(fn ($result) => $result['status'] ?? 'unknown');

            foreach ($counts as $status => $count) {
                $this->line("  - {$status}: {$count}");
            }

            $this->line('Backlog pendiente: ' . ($processSummary['meta']['backlog_remaining'] ?? 'N/A'));
        }

        Log::info('porth_reconcile_linked:completed', [
            'limit' => $limit,
            'stale_only' => $staleOnly,
            'process' => $process,
            'dry_run' => $dryRun,
            'found' => count($ids),
            'enqueued' => $enqueueSummary['enqueued_count'] ?? 0,
            'backlog_remaining' => $enqueueSummary['backlog_remaining'] ?? null,
        ]);

        return self::SUCCESS;
    }
}
