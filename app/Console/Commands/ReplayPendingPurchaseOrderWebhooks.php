<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ReplayPendingPurchaseOrderWebhooks extends Command
{
    protected $signature = 'webhooks:replay-purchase-order-updates
                            {--from= : Fecha/hora inicial (ej. "2026-05-08 00:00:00")}
                            {--to= : Fecha/hora final (ej. "2026-05-15 23:59:59")}
                            {--batch=50 : Cantidad máxima de eventos a procesar}
                            {--offset=0 : Desplazamiento para procesar el siguiente lote}
                            {--company= : Filtrar por company_id}
                            {--po-id= : Filtrar una PO específica}
                            {--dry-run : Solo listar eventos candidatos, no enviar}';

    protected $description = 'Reenvía webhooks purchase_order.updated pendientes a partir de la auditoría de purchase_order_comments, en lotes controlados.';

    public function handle(): int
    {
        if (!function_exists('dispatch_webhook')) {
            $this->error('dispatch_webhook no está disponible.');
            return self::FAILURE;
        }

        $from = $this->option('from');
        if (empty($from)) {
            $this->error('Debes indicar --from para acotar el replay.');
            return self::INVALID;
        }

        $to = $this->option('to') ?: now()->toDateTimeString();
        $batch = max(1, (int) $this->option('batch'));
        $offset = max(0, (int) $this->option('offset'));
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $poId = $this->option('po-id') ? (int) $this->option('po-id') : null;
        $dryRun = (bool) $this->option('dry-run');

        $query = PurchaseOrderComment::query()
            ->with(['purchaseOrder' => function ($query) {
                $query->with(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
            }])
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('purchase_order_id')
            ->where(function (Builder $query) {
                $query->whereNotNull('new_values')
                    ->orWhereNotNull('old_values');
            })
            ->orderBy('created_at')
            ->orderBy('id');

        if ($poId) {
            $query->where('purchase_order_id', $poId);
        }

        if ($companyId) {
            $query->whereHas('purchaseOrder', function (Builder $query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        $totalCandidates = (clone $query)->count();
        $comments = $query->offset($offset)->limit($batch)->get();

        if ($comments->isEmpty()) {
            $this->info('No se encontraron eventos pendientes para ese rango/filtro.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Eventos candidatos: %d | Procesando lote offset=%d batch=%d%s',
            $totalCandidates,
            $offset,
            $batch,
            $dryRun ? ' (dry-run)' : ''
        ));

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($comments as $comment) {
            $purchaseOrder = $comment->purchaseOrder;

            if (! $purchaseOrder instanceof PurchaseOrder) {
                $skipped++;
                $this->warn("Comment #{$comment->id}: PO no encontrada, se omite.");
                continue;
            }

            $changes = $this->extractChanges($comment);
            if (empty($changes)) {
                $skipped++;
                $this->line("Comment #{$comment->id} | PO #{$purchaseOrder->id} {$purchaseOrder->order_number}: sin cambios útiles, se omite.");
                continue;
            }

            $timestamp = function_exists('format_webhook_date')
                ? format_webhook_date($comment->created_at)
                : $comment->created_at?->utc()->format('Y-m-d\TH:i:s.v\Z');

            $this->line(sprintf(
                'Comment #%d | PO #%d %s | %s | campos=%s',
                $comment->id,
                $purchaseOrder->id,
                $purchaseOrder->order_number,
                $comment->created_at?->toDateTimeString(),
                implode(',', array_keys($changes))
            ));

            if ($dryRun) {
                $sent++;
                continue;
            }

            try {
                $freshPo = $purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                $poData = json_decode(json_encode($freshPo?->toArray() ?? []), true);

                dispatch_webhook('purchase_order.updated', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'source' => 'pending_updates_replay',
                    'timestamp' => $timestamp,
                    'changes' => $changes,
                    'data' => $poData,
                ]);

                Log::info('webhooks:replay-purchase-order-updates:dispatched', [
                    'comment_id' => $comment->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'changes_keys' => array_keys($changes),
                    'timestamp' => $timestamp,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  -> Error al reenviar comment #{$comment->id}: {$e->getMessage()}");

                Log::error('webhooks:replay-purchase-order-updates:error', [
                    'comment_id' => $comment->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info('Resumen del lote');
        $this->line("  - Candidatos totales: {$totalCandidates}");
        $this->line("  - Lote solicitado: {$comments->count()}");
        $this->line("  - Procesados/enviados: {$sent}");
        $this->line("  - Omitidos: {$skipped}");
        $this->line("  - Fallidos: {$failed}");

        $nextOffset = $offset + $comments->count();
        if ($nextOffset < $totalCandidates) {
            $this->line("  - Siguiente offset sugerido: {$nextOffset}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractChanges(PurchaseOrderComment $comment): array
    {
        $newValues = is_array($comment->new_values) ? $comment->new_values : [];
        $oldValues = is_array($comment->old_values) ? $comment->old_values : [];

        $changes = [];
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = $newValue;
            }
        }

        return $changes;
    }
}
