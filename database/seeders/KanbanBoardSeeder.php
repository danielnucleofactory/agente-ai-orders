<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\PurchaseOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class KanbanBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar temporalmente las restricciones de clave foránea
        Schema::disableForeignKeyConstraints();

        try {
            // Obtener todas las compañías
            $companies = Company::all();

            foreach ($companies as $company) {
                // 1. Crear un tablero Kanban para Etapas PO
                $boardPO = KanbanBoard::create([
                    'name' => 'Etapas PO',
                    'description' => 'Tablero Kanban para gestionar etapas de PO',
                    'company_id' => $company->id,
                    'type' => 'po_stages',
                    'is_active' => true,
                ]);

                // Crear estados para el tablero de Etapas PO
                $poStages = [
                    [
                        'name' => 'Recepción',
                        'slug' => 'po-recepcion-' . $company->id,
                        'description' => 'Etapa de recepción',
                        'position' => 1,
                        'color' => '#3498db',
                        'is_default' => true,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Consolidación en Hub teorico',
                        'slug' => 'po-consolidacion-hub-teorico-' . $company->id,
                        'description' => 'Etapa de consolidación en hub teórico',
                        'position' => 2,
                        'color' => '#f39c12',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Validación operativa con el cliente',
                        'slug' => 'po-validacion-operativa-cliente-1-' . $company->id,
                        'description' => 'Primera etapa de validación operativa con el cliente',
                        'position' => 3,
                        'color' => '#2ecc71',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Pick Up',
                        'slug' => 'po-pick-up-' . $company->id,
                        'description' => 'Etapa de pick up',
                        'position' => 4,
                        'color' => '#9b59b6',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'En tránsito terrestre',
                        'slug' => 'po-transito-terrestre-' . $company->id,
                        'description' => 'Etapa de tránsito terrestre',
                        'position' => 5,
                        'color' => '#e74c3c',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Llegada al hub',
                        'slug' => 'po-llegada-hub-' . $company->id,
                        'description' => 'Etapa de llegada al hub',
                        'position' => 6,
                        'color' => '#1abc9c',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Validación operativa con el cliente',
                        'slug' => 'po-validacion-operativa-cliente-2-' . $company->id,
                        'description' => 'Segunda etapa de validación operativa con el cliente',
                        'position' => 7,
                        'color' => '#d35400',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Consolidación en Hub real',
                        'slug' => 'po-consolidacion-hub-real-' . $company->id,
                        'description' => 'Etapa de consolidación en hub real',
                        'position' => 8,
                        'color' => '#27ae60',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                ];

                foreach ($poStages as $status) {
                    KanbanStatus::create([
                        'name' => $status['name'],
                        'slug' => $status['slug'],
                        'description' => $status['description'],
                        'kanban_board_id' => $boardPO->id,
                        'position' => $status['position'],
                        'color' => $status['color'],
                        'is_default' => $status['is_default'],
                        'is_final' => $status['is_final'],
                    ]);
                }

                // 2. Crear un tablero Kanban para Documentación de embarque
                $boardDE = KanbanBoard::create([
                    'name' => 'Documentación de embarque',
                    'description' => 'Tablero Kanban para gestionar documentación de embarque',
                    'company_id' => $company->id,
                    'type' => 'shipping_documentation',
                    'is_active' => true,
                ]);

                // Crear estados para el tablero de Documentación de embarque
                $deStages = [
                    [
                        'name' => 'Gestión documental',
                        'slug' => 'de-gestion-documental-' . $company->id,
                        'description' => 'Etapa de gestión documental',
                        'position' => 1,
                        'color' => '#8e44ad',
                        'is_default' => true,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Coordinación de salida - Zarpe',
                        'slug' => 'de-coordinacion-salida-zarpe-' . $company->id,
                        'description' => 'Etapa de coordinación de salida y zarpe',
                        'position' => 2,
                        'color' => '#3498db',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'En tránsito - seguimiento',
                        'slug' => 'de-transito-seguimiento-' . $company->id,
                        'description' => 'Etapa de tránsito y seguimiento',
                        'position' => 3,
                        'color' => '#f39c12',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Notificación de arribo',
                        'slug' => 'de-notificacion-arribo-' . $company->id,
                        'description' => 'Etapa de notificación de arribo',
                        'position' => 4,
                        'color' => '#2ecc71',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Digitaciones',
                        'slug' => 'de-digitaciones-' . $company->id,
                        'description' => 'Etapa de digitaciones',
                        'position' => 5,
                        'color' => '#9b59b6',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Transito interno destino',
                        'slug' => 'de-transito-interno-destino-' . $company->id,
                        'description' => 'Etapa de tránsito interno a destino',
                        'position' => 6,
                        'color' => '#e74c3c',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Liberación/entrega y Facturación',
                        'slug' => 'de-liberacion-entrega-facturacion-' . $company->id,
                        'description' => 'Etapa de liberación, entrega y facturación',
                        'position' => 7,
                        'color' => '#1abc9c',
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Archivado',
                        'slug' => 'de-archivado-' . $company->id,
                        'description' => 'Etapa de archivado',
                        'position' => 8,
                        'color' => '#d35400',
                        'is_default' => false,
                        'is_final' => true,
                    ],
                ];

                foreach ($deStages as $status) {
                    KanbanStatus::create([
                        'name' => $status['name'],
                        'slug' => $status['slug'],
                        'description' => $status['description'],
                        'kanban_board_id' => $boardDE->id,
                        'position' => $status['position'],
                        'color' => $status['color'],
                        'is_default' => $status['is_default'],
                        'is_final' => $status['is_final'],
                    ]);
                }

                // Asignar órdenes de compra existentes al estado por defecto del tablero PO
                $defaultPOStatus = KanbanStatus::where('kanban_board_id', $boardPO->id)
                    ->where('is_default', true)
                    ->first();

                if ($defaultPOStatus) {
                    $purchaseOrders = PurchaseOrder::where('company_id', $company->id)->get();
                    foreach ($purchaseOrders as $order) {
                        $order->update([
                            'kanban_status_id' => $defaultPOStatus->id,
                        ]);
                    }
                }
            }
        } finally {
            // Asegurarse de que las restricciones de clave foránea se reactiven
            Schema::enableForeignKeyConstraints();
        }
    }
}
