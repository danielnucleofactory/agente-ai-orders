<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KanbanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Desactivar temporalmente las restricciones de clave foránea
            Schema::disableForeignKeyConstraints();

            // Limpiar datos existentes relacionados con Kanban
            // No eliminamos los tableros y estados existentes para preservar la configuración

            // Obtener todas las compañías
            $companies = Company::all();

            foreach ($companies as $company) {
                // Verificar si ya existe un tablero Kanban para esta compañía
                $board = KanbanBoard::where('company_id', $company->id)
                    ->where('type', 'purchase_orders')
                    ->first();

                // Si no existe, crear uno nuevo
                if (!$board) {
                    $board = KanbanBoard::create([
                        'name' => 'Órdenes de Compra',
                        'description' => 'Tablero Kanban para gestionar órdenes de compra',
                        'company_id' => $company->id,
                        'type' => 'purchase_orders',
                        'is_active' => true,
                    ]);
                }

                // Crear los estados del Kanban
                $statuses = [
                    [
                        'name' => 'Por Procesar',
                        'slug' => 'por-procesar-' . $company->id,
                        'description' => 'Órdenes de compra pendientes de procesamiento',
                        'position' => 1,
                        'color' => '#3490dc', // Azul
                        'is_default' => true,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'En Proceso',
                        'slug' => 'en-proceso-' . $company->id,
                        'description' => 'Órdenes de compra en proceso de gestión',
                        'position' => 2,
                        'color' => '#f6993f', // Naranja
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Aprobadas',
                        'slug' => 'aprobadas-' . $company->id,
                        'description' => 'Órdenes de compra aprobadas',
                        'position' => 3,
                        'color' => '#38c172', // Verde
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'En Tránsito',
                        'slug' => 'en-transito-' . $company->id,
                        'description' => 'Órdenes de compra en tránsito',
                        'position' => 4,
                        'color' => '#ffed4a', // Amarillo
                        'is_default' => false,
                        'is_final' => false,
                    ],
                    [
                        'name' => 'Entregadas',
                        'slug' => 'entregadas-' . $company->id,
                        'description' => 'Órdenes de compra entregadas',
                        'position' => 5,
                        'color' => '#9561e2', // Púrpura
                        'is_default' => false,
                        'is_final' => true,
                    ],
                ];

                // Crear los estados para el tablero si no existen
                foreach ($statuses as $status) {
                    KanbanStatus::firstOrCreate(
                        [
                            'kanban_board_id' => $board->id,
                            'slug' => $status['slug'],
                        ],
                        [
                            'name' => $status['name'],
                            'description' => $status['description'],
                            'position' => $status['position'],
                            'color' => $status['color'],
                            'is_default' => $status['is_default'],
                            'is_final' => $status['is_final'],
                        ]
                    );
                }

                // Asignar órdenes de compra existentes a estados del Kanban
                $purchaseOrders = PurchaseOrder::where('company_id', $company->id)->get();
                $defaultStatus = KanbanStatus::where('kanban_board_id', $board->id)
                    ->where('is_default', true)
                    ->first();

                foreach ($purchaseOrders as $order) {
                    // Solo actualizar si no tiene un estado de Kanban asignado
                    if (!$order->kanban_status_id) {
                        // Asignar estado basado en el status actual de la orden
                        $statusSlug = match ($order->status) {
                            'draft' => 'por-procesar-' . $company->id,
                            'pending' => 'en-proceso-' . $company->id,
                            'approved' => 'aprobadas-' . $company->id,
                            'shipped' => 'en-transito-' . $company->id,
                            'delivered' => 'entregadas-' . $company->id,
                            default => 'por-procesar-' . $company->id,
                        };

                        $status = KanbanStatus::where('kanban_board_id', $board->id)
                            ->where('slug', $statusSlug)
                            ->first();

                        $order->update([
                            'kanban_status_id' => $status ? $status->id : ($defaultStatus ? $defaultStatus->id : null),
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
