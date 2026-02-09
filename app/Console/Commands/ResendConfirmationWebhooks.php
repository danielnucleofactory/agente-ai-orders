<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResendConfirmationWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'po-confirmation:resend-webhooks
                            {--hours=24 : Número de horas hacia atrás para buscar confirmaciones}
                            {--from= : Fecha de inicio (formato: Y-m-d H:i:s)}
                            {--to= : Fecha de fin (formato: Y-m-d H:i:s)}
                            {--po-id=* : IDs específicos de Purchase Orders (puede repetirse)}
                            {--dry-run : Mostrar qué se haría sin ejecutar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-envía webhooks para Purchase Orders confirmadas recientemente por proveedores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!function_exists('dispatch_webhook')) {
            $this->error('El módulo de webhooks no está disponible.');
            return 1;
        }

        $this->info('🔄 Iniciando re-envío de webhooks de confirmaciones...');
        $this->newLine();

        // Construir query base
        $query = PurchaseOrder::query()
            ->where('confirm_update_date_po', true)
            ->whereNotNull('date_variable_date')
            ->with(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

        // Filtrar por IDs específicos si se proporcionaron
        $poIds = $this->option('po-id');
        if (!empty($poIds)) {
            $query->whereIn('id', $poIds);
            $this->info('Filtrando por IDs específicos: ' . implode(', ', $poIds));
        } else {
            // Filtrar por rango de fechas
            if ($this->option('from') && $this->option('to')) {
                $from = $this->option('from');
                $to = $this->option('to');
                $query->whereBetween('updated_at', [$from, $to]);
                $this->info("Filtrando confirmaciones entre {$from} y {$to}");
            } else {
                $hours = (int) $this->option('hours');
                $since = now()->subHours($hours);
                $query->where('updated_at', '>=', $since);
                $this->info("Filtrando confirmaciones de las últimas {$hours} horas (desde {$since->format('Y-m-d H:i:s')})");
            }
        }

        // Obtener POs confirmadas
        $purchaseOrders = $query->get();

        if ($purchaseOrders->isEmpty()) {
            $this->warn('⚠️  No se encontraron Purchase Orders confirmadas en el rango especificado.');
            return 0;
        }

        $this->info("📦 Se encontraron {$purchaseOrders->count()} Purchase Orders confirmadas.");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 Modo DRY-RUN: No se enviarán webhooks realmente.');
            $this->newLine();
        }

        // Tabla de resumen
        $tableData = [];
        $successCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($purchaseOrders->count());
        $progressBar->start();

        foreach ($purchaseOrders as $po) {
            $status = '✓';
            $error = null;

            if (!$this->option('dry-run')) {
                try {
                    Log::info('resend_confirmation_webhook:dispatching', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'date_variable_date' => $po->date_variable_date,
                        'updated_at' => $po->updated_at,
                    ]);

                    dispatch_webhook('purchase_order.updated', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'action' => 'po_confirmed_by_vendor_resent',
                        'confirmed_date' => $po->date_variable_date?->format('Y-m-d'),
                        'resent_at' => now()->toISOString(),
                        'data' => $po->toArray(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $status = '✗';
                    $error = $e->getMessage();
                    $errorCount++;

                    Log::error('resend_confirmation_webhook:error', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $successCount++;
            }

            $tableData[] = [
                $status,
                $po->id,
                $po->order_number,
                $po->date_variable_date?->format('Y-m-d') ?? 'N/A',
                $po->updated_at->format('Y-m-d H:i:s'),
                $error ? substr($error, 0, 50) . '...' : '',
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar tabla de resultados
        $this->table(
            ['Estado', 'ID', 'Order Number', 'Fecha Confirmada', 'Última Actualización', 'Error'],
            $tableData
        );

        $this->newLine();

        // Resumen final
        if ($this->option('dry-run')) {
            $this->info("✓ DRY-RUN completado: Se habrían enviado {$successCount} webhooks.");
        } else {
            $this->info("✓ Webhooks enviados exitosamente: {$successCount}");
            if ($errorCount > 0) {
                $this->error("✗ Webhooks con errores: {$errorCount}");
            }
        }

        return $errorCount > 0 ? 1 : 0;
    }
}
