<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Models\KanbanStatus;
use App\Models\KanbanBoard;
use App\Models\PurchaseOrderComment;
use Illuminate\Support\Facades\DB;

class MigrateNuevoStageToRecepcion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kanban:migrate-nuevo-stage 
                            {--dry-run : Ejecutar sin hacer cambios reales}
                            {--force : Forzar ejecución sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra todas las PO de etapa "Nuevo" a "Recepción" y oculta la etapa "Nuevo"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('=== Migración de Etapa "Nuevo" a "Recepción" ===');
        $this->newLine();

        // Paso 1: Identificar tableros de tipo 'po_stages'
        $boards = KanbanBoard::where('type', 'po_stages')
            ->where('is_active', true)
            ->get();

        if ($boards->isEmpty()) {
            $this->error('No se encontraron tableros activos de tipo "po_stages"');
            return 1;
        }

        $this->info("Tableros encontrados: {$boards->count()}");
        foreach ($boards as $board) {
            $this->line("  - {$board->name} (ID: {$board->id})");
        }
        $this->newLine();

        $totalMigrated = 0;
        $totalErrors = 0;

        // Procesar cada tablero
        foreach ($boards as $board) {
            $this->info("Procesando tablero: {$board->name}");
            
            // Identificar etapa "Nuevo"
            $nuevoStatus = $board->statuses()
                ->where(function($q) {
                    $q->where('name', 'Nuevo')
                      ->orWhere('id', 1);
                })
                ->first();

            if (!$nuevoStatus) {
                $this->warn("  No se encontró etapa 'Nuevo' en este tablero");
                continue;
            }

            $this->line("  Etapa 'Nuevo' encontrada: ID {$nuevoStatus->id}");

            // Identificar estado destino
            // Prioridad: 1) "Recepción", 2) Siguiente estado después de "Nuevo", 3) defaultStatus
            $destinoStatus = $board->statuses()
                ->where('is_hidden', false)
                ->where(function($q) {
                    $q->where('name', 'Recepción')
                      ->orWhere('position', '>', 1); // Siguiente estado después de "Nuevo" (position 1)
                })
                ->orderByRaw("CASE WHEN name = 'Recepción' THEN 1 ELSE 2 END")
                ->orderBy('position')
                ->first();
            
            // Si no se encontró, usar defaultStatus que no esté oculto
            if (!$destinoStatus) {
                $destinoStatus = $board->statuses()
                    ->where('is_hidden', false)
                    ->where('is_default', true)
                    ->first();
            }

            if (!$destinoStatus) {
                $this->error("  No se encontró estado destino ('Recepción' o defaultStatus) en este tablero");
                continue;
            }

            $this->line("  Estado destino: {$destinoStatus->name} (ID: {$destinoStatus->id})");

            // Contar PO en etapa "Nuevo"
            $posCount = PurchaseOrder::where('kanban_status_id', $nuevoStatus->id)
                ->whereNull('deleted_at')
                ->count();

            if ($posCount === 0) {
                $this->info("  No hay PO en etapa 'Nuevo' en este tablero");
                
                // Ocultar etapa "Nuevo" si no hay PO
                if (!$dryRun) {
                    $nuevoStatus->update(['is_hidden' => true]);
                    $this->info("  ✓ Etapa 'Nuevo' ocultada");
                } else {
                    $this->line("  [DRY-RUN] Se ocultaría la etapa 'Nuevo'");
                }
                continue;
            }

            $this->info("  PO encontradas en etapa 'Nuevo': {$posCount}");

            // Confirmar si no es dry-run y no es force
            if (!$dryRun && !$force) {
                if (!$this->confirm("  ¿Continuar con la migración de {$posCount} PO?", true)) {
                    $this->warn("  Migración cancelada para este tablero");
                    continue;
                }
            }

            // Obtener todas las PO
            $pos = PurchaseOrder::where('kanban_status_id', $nuevoStatus->id)
                ->whereNull('deleted_at')
                ->get();

            $this->newLine();
            $bar = $this->output->createProgressBar($pos->count());
            $bar->setFormat('  %current%/%max% [%bar%] %percent:3s%% - %message%');
            $bar->setMessage('Iniciando migración...');
            $bar->start();

            $migrated = 0;
            $errors = 0;

            // Obtener usuario del sistema para el historial
            $systemUser = \App\Models\User::where('email', 'system@olo.com')->first();
            if (!$systemUser) {
                // Usar el primer usuario disponible como fallback
                $systemUser = \App\Models\User::first();
            }

            foreach ($pos as $po) {
                try {
                    $bar->setMessage("Migrando PO {$po->order_number}...");

                    if (!$dryRun) {
                        // Usar Eloquent para que se disparen los eventos automáticamente
                        $oldStatusId = $po->kanban_status_id;
                        $po->update(['kanban_status_id' => $destinoStatus->id]);

                        // El Observer ya crea un comentario automático, pero agregamos uno más descriptivo
                        try {
                            PurchaseOrderComment::create([
                                'purchase_order_id' => $po->id,
                                'user_id' => $systemUser->id ?? null,
                                'comment' => "Migración automática: PO movida de etapa 'Nuevo' a '{$destinoStatus->name}' como parte de la ocultación de la etapa 'Nuevo'.",
                                'action_type' => 'status_change',
                                'old_values' => ['kanban_status_id' => $oldStatusId],
                                'new_values' => ['kanban_status_id' => $destinoStatus->id],
                                'ip_address' => '127.0.0.1',
                                'user_agent' => 'Artisan Command: kanban:migrate-nuevo-stage',
                            ]);
                        } catch (\Exception $e) {
                            // Si falla el comentario, continuar (el Observer ya creó uno)
                            \Log::warning("No se pudo crear comentario de migración para PO {$po->id}: " . $e->getMessage());
                        }
                    }

                    $migrated++;
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error("Error migrando PO {$po->id}: " . $e->getMessage());
                    $bar->setMessage("Error en PO {$po->order_number}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($dryRun) {
                $this->info("  [DRY-RUN] Se migrarían {$migrated} PO");
                if ($errors > 0) {
                    $this->warn("  [DRY-RUN] Errores simulados: {$errors}");
                }
            } else {
                if ($migrated > 0) {
                    $this->info("  ✓ Migradas {$migrated} PO exitosamente");
                }
                if ($errors > 0) {
                    $this->error("  ✗ Errores: {$errors}");
                }

                // Ocultar etapa "Nuevo"
                $nuevoStatus->update(['is_hidden' => true]);
                $this->info("  ✓ Etapa 'Nuevo' ocultada");
            }

            $totalMigrated += $migrated;
            $totalErrors += $errors;
            $this->newLine();
        }

        // Resumen final
        $this->newLine();
        $this->info('=== Resumen ===');
        if ($dryRun) {
            $this->line("  [DRY-RUN] Total PO que se migrarían: {$totalMigrated}");
        } else {
            $this->line("  Total PO migradas: {$totalMigrated}");
        }
        if ($totalErrors > 0) {
            $this->error("  Total errores: {$totalErrors}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->comment('Ejecuta sin --dry-run para aplicar los cambios');
        } else {
            $this->newLine();
            $this->info('✓ Migración completada');
        }

        return 0;
    }
}
