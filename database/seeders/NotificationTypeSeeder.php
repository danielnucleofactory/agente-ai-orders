<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            [
                'key' => 'task_moved',
                'name' => 'Movimiento de tareas',
                'category' => 'kanban',
                'description' => 'Notificaciones cuando se mueven tareas en el tablero Kanban'
            ],
            [
                'key' => 'po_hub_real',
                'name' => 'Hub Real Diferente',
                'category' => 'ordenes',
                'description' => 'Notificaciones cuando una orden se crea con un hub real diferente al planificado'
            ]
        ];

        foreach ($types as $type) {
            NotificationType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}
