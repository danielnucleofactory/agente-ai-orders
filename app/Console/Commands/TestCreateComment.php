<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestCreateComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-comment {po_id} {comment?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un comentario de prueba para una orden de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $poId = $this->argument('po_id');
        $commentText = $this->argument('comment') ?? 'Comentario de prueba ' . time();

        $this->info("Intentando crear un comentario para PO ID: $poId");

        try {
            // Verificar que existe la orden de compra
            $po = PurchaseOrder::find($poId);
            if (!$po) {
                $this->error("No se encontró la orden de compra con ID: $poId");
                return 1;
            }

            // Intentar crear un comentario directamente
            $this->info("Creando comentario usando create()...");
            $comment1 = $po->comments()->create([
                'user_id' => 1, // Asumiendo que existe un usuario con ID 1
                'comment' => $commentText . ' (método 1)',
                'operacion' => 'Test desde Artisan (create)'
            ]);

            $this->info("Comentario creado con ID: " . $comment1->id);

            // Intentar crear un comentario con el método new + save
            $this->info("Creando comentario usando new + save()...");
            $comment2 = new PurchaseOrderComment();
            $comment2->purchase_order_id = $poId;
            $comment2->user_id = 1;
            $comment2->comment = $commentText . ' (método 2)';
            $comment2->operacion = 'Test desde Artisan (save)';
            $comment2->save();

            $this->info("Comentario creado con ID: " . $comment2->id);

            // Verificar que se han guardado ambos comentarios
            $this->info("Verificando comentarios guardados...");
            $comments = PurchaseOrderComment::where('purchase_order_id', $poId)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            $this->table(
                ['ID', 'PO ID', 'User ID', 'Comment', 'Operación', 'Created At'],
                $comments->map(function($item) {
                    return [
                        'ID' => $item->id,
                        'PO ID' => $item->purchase_order_id,
                        'User ID' => $item->user_id,
                        'Comment' => $item->comment,
                        'Operación' => $item->operacion,
                        'Created At' => $item->created_at,
                    ];
                })
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Error al crear comentario: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
