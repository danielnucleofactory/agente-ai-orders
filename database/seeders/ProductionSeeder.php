<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Hub;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\NotificationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     * This seeder creates real data without using Faker.
     */
    public function run(): void
    {
        // Desactivar restricciones de clave foránea
        Schema::disableForeignKeyConstraints();

        try {
            // 1. Crear la compañía principal
            $company = $this->createCompany();

            // 2. Crear el usuario administrador
            $this->createAdminUser($company);

            // 3. Crear los Hubs
            $this->createHubs();

            // 4. Crear los tableros Kanban y sus estados
            $this->createKanbanBoards($company);

            // 5. Crear los tipos de notificaciones
            $this->createNotificationTypes();

        } finally {
            // Reactivar restricciones de clave foránea
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Crea la compañía principal para el entorno de producción.
     */
    private function createCompany(): Company
    {
        return Company::firstOrCreate(
            ['name' => 'Kerry'],
            [
                'address' => 'Dirección Principal',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Crea el usuario administrador principal.
     */
    private function createAdminUser(Company $company): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@raga-orders.com'],
            [
                'name' => 'Cristian Quiroz',
                'password' => bcrypt('99847597'), // Cambiar por una contraseña segura
                'company_id' => $company->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Crea los hubs de logística.
     */
    private function createHubs(): void
    {
        $hubs = [
            [
                'name' => 'Miami Hub',
                'code' => 'MIA',
                'country' => 'Estados Unidos'
            ],
            [
                'name' => 'Shanghai Hub',
                'code' => 'SHA',
                'country' => 'China'
            ],
            [
                'name' => 'Rotterdam Hub',
                'code' => 'RTM',
                'country' => 'Países Bajos'
            ],
            [
                'name' => 'Dubai Hub',
                'code' => 'DXB',
                'country' => 'Emiratos Árabes Unidos'
            ],
            [
                'name' => 'Singapore Hub',
                'code' => 'SIN',
                'country' => 'Singapur'
            ],
        ];

        foreach ($hubs as $hubData) {
            Hub::firstOrCreate(
                ['code' => $hubData['code']],
                [
                    'name' => $hubData['name'],
                    'country' => $hubData['country'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Crea los tableros Kanban y sus estados.
     */
    private function createKanbanBoards(Company $company): void
    {
        // 1. Crear un tablero Kanban para Etapas PO
        $boardPO = KanbanBoard::firstOrCreate(
            [
                'company_id' => $company->id,
                'type' => 'po_stages'
            ],
            [
                'name' => 'Etapas PO',
                'description' => 'Tablero Kanban para gestionar etapas de PO',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Estados para el tablero Etapas PO
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

        foreach ($poStages as $statusData) {
            KanbanStatus::firstOrCreate(
                [
                    'kanban_board_id' => $boardPO->id,
                    'name' => $statusData['name'],
                ],
                [
                    'slug' => $statusData['slug'],
                    'description' => $statusData['description'],
                    'position' => $statusData['position'],
                    'color' => $statusData['color'],
                    'is_default' => $statusData['is_default'],
                    'is_final' => $statusData['is_final'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Crear un tablero Kanban para Documentación de embarque
        $boardDE = KanbanBoard::firstOrCreate(
            [
                'company_id' => $company->id,
                'type' => 'shipping_documentation'
            ],
            [
                'name' => 'Documentación de embarque',
                'description' => 'Tablero Kanban para gestionar documentación de embarque',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Estados para el tablero Documentación de embarque
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
                'is_final' => true,
            ],
        ];

        foreach ($deStages as $statusData) {
            KanbanStatus::firstOrCreate(
                [
                    'kanban_board_id' => $boardDE->id,
                    'name' => $statusData['name'],
                ],
                [
                    'slug' => $statusData['slug'],
                    'description' => $statusData['description'],
                    'position' => $statusData['position'],
                    'color' => $statusData['color'],
                    'is_default' => $statusData['is_default'],
                    'is_final' => $statusData['is_final'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Crea los tipos de notificaciones.
     */
    private function createNotificationTypes(): void
    {
        $types = [
            [
                'key' => 'mobile_notifications',
                'name' => 'Notificaciones móviles',
                'category' => 'tipos',
                'description' => 'Activa/desactiva alertas en la app móvil sobre actualizaciones importantes'
            ],
            [
                'key' => 'email_notifications',
                'name' => 'Notificaciones por correo electrónico',
                'category' => 'tipos',
                'description' => 'Selecciona qué eventos deben enviarse por email'
            ],
            [
                'key' => 'platform_notifications',
                'name' => 'Notificaciones en la plataforma',
                'category' => 'tipos',
                'description' => 'Activa pop-ups o banners dentro del dashboard para tareas urgentes o recordatorios'
            ],
            [
                'key' => 'status_update',
                'name' => 'Actualización de estado',
                'category' => 'cargas_envios',
                'description' => 'Notificaciones sobre actualizaciones de estado'
            ],
            [
                'key' => 'issues_detected',
                'name' => 'Problemas detectados',
                'category' => 'cargas_envios',
                'description' => 'Alertas sobre problemas detectados en el sistema'
            ],
            [
                'key' => 'successful_deliveries',
                'name' => 'Entregas exitosas',
                'category' => 'cargas_envios',
                'description' => 'Notificaciones sobre entregas completadas con éxito'
            ],
            [
                'key' => 'pending_tasks',
                'name' => 'Tareas pendientes',
                'category' => 'recordatorios',
                'description' => 'Recordatorios de tareas pendientes'
            ],
            [
                'key' => 'upcoming_deadlines',
                'name' => 'Vencimientos próximos',
                'category' => 'recordatorios',
                'description' => 'Alertas sobre fechas límite próximas'
            ],
            [
                'key' => 'user_customization',
                'name' => 'Personalización por usuario',
                'category' => 'recordatorios',
                'description' => 'Opciones de personalización específicas del usuario'
            ],
            [
                'key' => 'order_creation_changes',
                'name' => 'Creación o cambios en PO\'S',
                'category' => 'ordenes',
                'description' => 'Notificaciones sobre creación o modificación de órdenes'
            ],
            [
                'key' => 'order_consolidation',
                'name' => 'Consolidación de orden',
                'category' => 'ordenes',
                'description' => 'Alertas cuando se consolida una orden'
            ],
        ];

        foreach ($types as $typeData) {
            NotificationType::firstOrCreate(
                ['key' => $typeData['key']],
                [
                    'name' => $typeData['name'],
                    'category' => $typeData['category'],
                    'description' => $typeData['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
