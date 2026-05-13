<?php

namespace App\Console\Commands;

use App\Models\PorthSyncFailure;
use App\Services\PorthSyncFailureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PorthRetryFailed extends Command
{
    protected $signature = 'porth:retry-failed
                            {--limit=20 : Máximo de fallos a reintentar por ejecución}
                            {--dry-run : Solo mostrar qué casos serían reintentados}';

    protected $description = 'Reintenta sincronizaciones de Porth marcadas como fallidas definitivas y pendientes de retry.';

    public function __construct(
        protected PorthSyncFailureService $failureService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config('services.porth.sync_enabled', true)) {
            $this->warn('Porth sync deshabilitado por PORTH_SYNC_ENABLED=false.');
            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $failures = PorthSyncFailure::query()
            ->eligible()
            ->orderBy('next_retry_at')
            ->orderBy('last_failed_at')
            ->limit($limit)
            ->get();

        if ($failures->isEmpty()) {
            $this->info('No hay fallos de Porth pendientes de reintento.');
            return self::SUCCESS;
        }

        $this->info("Se encontraron {$failures->count()} fallo(s) de Porth pendientes de reintento.");

        if ($dryRun) {
            foreach ($failures as $failure) {
                $this->line("- {$failure->failure_key} | source={$failure->failure_source} | order={$failure->order_number} | next_retry_at=".optional($failure->next_retry_at)->format('Y-m-d H:i:s'));
            }

            return self::SUCCESS;
        }

        $recovered = 0;
        $failed = 0;

        foreach ($failures as $failure) {
            $this->line("Retry {$failure->failure_key} | source={$failure->failure_source} | order=".($failure->order_number ?? 'N/A'));

            try {
                $result = $this->failureService->retryFailure($failure);

                if ($result) {
                    $recovered++;
                    $this->info('  ✓ Recuperado');
                } else {
                    $failed++;
                    $this->warn('  ⚠ Reintento falló; caso reagendado o marcado permanente');
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->error('  ✗ Error: '.$exception->getMessage());

                Log::error('porth_retry_failed:unexpected_exception', [
                    'failure_id' => $failure->id,
                    'failure_key' => $failure->failure_key,
                    'error' => $exception->getMessage(),
                ]);
            }

            usleep(500000);
        }

        $this->newLine();
        $this->info("Resumen: recovered={$recovered}, failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
