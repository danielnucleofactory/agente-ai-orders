<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Reenvía purchase_order.updated por webhook para POs que fueron
 * sincronizadas con Porth recientemente. El timestamp del webhook
 * será el momento original de la sincronización (last_porth_sync_at),
 * no el momento de ejecución del comando.
 */
class PorthDispatchWebhookSynced extends Command
{
    protected $signature = 'porth:dispatch-webhook-synced
                            {--days=2 : Días hacia atrás para buscar POs con last_porth_sync_at}
                            {--limit= : Límite de POs a procesar (por defecto todas)}
                            {--dry-run : Solo listar, no enviar}';

    protected $description = 'Reenvía webhook purchase_order.updated para POs sincronizadas con Porth recientemente, usando el timestamp original del sync.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');

        if (!function_exists('dispatch_webhook')) {
            $this->error('dispatch_webhook no está disponible.');
            return self::FAILURE;
        }

        $since = now()->subDays($days);
        $query = PurchaseOrder::whereNotNull('porth_id')
            ->whereNotNull('last_porth_sync_at')
            ->where('last_porth_sync_at', '>=', $since)
            ->orderBy('last_porth_sync_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        $pos = $query->get();
        $total = $pos->count();

        if ($total === 0) {
            $this->info("No hay POs con last_porth_sync_at en los últimos {$days} días.");
            return self::SUCCESS;
        }

        $this->info("POs a procesar: {$total}" . ($dryRun ? ' (dry-run)' : ''));

        $sent = 0;
        $failed = 0;

        foreach ($pos as $po) {
            $syncAt = $po->last_porth_sync_at;
            $this->line("  PO #{$po->id} {$po->order_number} (sync: {$syncAt})");

            if ($dryRun) {
                $sent++;
                continue;
            }

            try {
                $po->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                $freshPo = $po->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                $poData = json_decode(json_encode($freshPo->toArray()), true);

                dispatch_webhook('purchase_order.updated', [
                    'purchase_order_id' => $po->id,
                    'order_number' => $po->order_number,
                    'source' => 'porth_sync_replay',
                    'timestamp' => format_webhook_date($syncAt),
                    'changes' => ['_tracking_updated' => true],
                    'data' => $poData,
                ]);

                $sent++;
                $this->info("    -> Webhook enviado (timestamp: {$syncAt})");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("    -> Error: {$e->getMessage()}");
                Log::error('porth:dispatch-webhook-synced:error', [
                    'purchase_order_id' => $po->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->line("  - Enviados: {$sent}");
        if ($failed > 0) {
            $this->line("  - Fallidos: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
