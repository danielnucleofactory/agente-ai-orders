<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationType;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['key' => 'mobile_notifications', 'name' => 'Notificaciones móviles', 'category' => 'tipos'],
            ['key' => 'email_notifications', 'name' => 'Notificaciones por correo electrónico', 'category' => 'tipos'],
            ['key' => 'platform_notifications', 'name' => 'Notificaciones en la plataforma (desktop)', 'category' => 'tipos'],
            ['key' => 'status_update', 'name' => 'Actualización de estado', 'category' => 'cargas_envios'],
            ['key' => 'issues_detected', 'name' => 'Problemas detectados', 'category' => 'cargas_envios'],
            ['key' => 'successful_deliveries', 'name' => 'Entregas exitosas', 'category' => 'cargas_envios'],
            ['key' => 'pending_tasks', 'name' => 'Tareas pendientes', 'category' => 'recordatorios'],
            ['key' => 'upcoming_deadlines', 'name' => 'Vencimientos próximos', 'category' => 'recordatorios'],
            ['key' => 'user_customization', 'name' => 'Personalización por usuario', 'category' => 'recordatorios'],
            ['key' => 'order_creation_changes', 'name' => 'Creación o cambios en PO\'s', 'category' => 'ordenes'],
            ['key' => 'order_consolidation', 'name' => 'Al consolidar una orden', 'category' => 'ordenes'],
            [
                'key' => 'task_moved',
                'name' => 'Movimiento de tareas en Kanban',
                'category' => 'kanban',
                'description' => 'Notificaciones cuando se mueven tareas entre columnas del tablero Kanban'
            ],
            [
                'key' => 'hub_changed',
                'name' => 'Cambio de Hub',
                'category' => 'purchase_orders',
                'description' => 'Notificaciones cuando el hub real es diferente al hub planificado'
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
