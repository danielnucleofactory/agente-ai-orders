<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationType;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Crear una nueva notificación para un usuario verificando sus preferencias
     *
     * @param User $user Usuario destinatario
     * @param string $type Tipo de notificación
     * @param string $title Título de la notificación
     * @param string $message Mensaje de la notificación
     * @param array $data Datos adicionales (opcional)
     * @return Notification|null
     */
    public function createForUser(User $user, string $type, string $title, string $message, array $data = [])
    {
        Log::info("NotificationService::createForUser called", [
            'user_id' => $user->id,
            'type' => $type
        ]);

        // Verificar si el usuario tiene habilitado este tipo de notificación
        $isEnabled = $this->userHasNotificationEnabled($user, $type);
        
        Log::info("Notification enabled check result", [
            'user_id' => $user->id,
            'type' => $type,
            'enabled' => $isEnabled
        ]);

        if (!$isEnabled) {
            Log::info("Notification skipped for user {$user->id} - type '{$type}' is disabled");
            return null;
        }

        try {
            $notification = Notification::create([
                'type' => $type,
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'data' => $data
            ]);

            Log::info("Notification created in database", [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'type' => $type
            ]);

            return $notification;
        } catch (\Exception $e) {
            Log::error("Error creating notification in database", [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Notificar a todos los usuarios
     *
     * @param string $type Tipo de notificación
     * @param string $title Título de la notificación
     * @param string $message Mensaje de la notificación
     * @param array $data Datos adicionales (opcional)
     * @return array Arreglo de notificaciones creadas
     */
    public function notifyAll(string $type, string $title, string $message, array $data = [])
    {
        Log::info("NotificationService::notifyAll called", [
            'type' => $type,
            'title' => $title,
            'users_count' => User::count()
        ]);

        $users = User::all();
        $notifications = [];

        foreach ($users as $user) {
            try {
                Log::info("Processing notification for user", [
                    'user_id' => $user->id,
                    'type' => $type
                ]);

                $notification = $this->createForUser($user, $type, $title, $message, $data);
                if ($notification) {
                    $notifications[] = $notification;
                    Log::info("Notification created successfully", [
                        'user_id' => $user->id,
                        'notification_id' => $notification->id
                    ]);
                } else {
                    Log::info("Notification not created (skipped or disabled)", [
                        'user_id' => $user->id,
                        'type' => $type
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error creando notificación para el usuario {$user->id}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info("NotificationService::notifyAll completed", [
            'type' => $type,
            'notifications_created' => count($notifications),
            'total_users' => count($users)
        ]);

        return $notifications;
    }

    /**
     * Notificar a usuarios específicos
     *
     * @param array $userIds IDs de usuarios
     * @param string $type Tipo de notificación
     * @param string $title Título de la notificación
     * @param string $message Mensaje de la notificación
     * @param array $data Datos adicionales (opcional)
     * @return array Arreglo de notificaciones creadas
     */
    public function notifyUsers(array $userIds, string $type, string $title, string $message, array $data = [])
    {
        $users = User::whereIn('id', $userIds)->get();
        $notifications = [];

        foreach ($users as $user) {
            try {
                $notification = $this->createForUser($user, $type, $title, $message, $data);
                if ($notification) {
                    $notifications[] = $notification;
                }
            } catch (\Exception $e) {
                Log::error("Error creando notificación para el usuario {$user->id}: " . $e->getMessage());
            }
        }

        return $notifications;
    }

    /**
     * Verificar si un usuario tiene habilitado un tipo de notificación
     * 
     * Nota: El sistema solo maneja notificaciones de escritorio (platform_notifications).
     * Si no hay preferencia definida, por defecto se asume que está habilitada para escritorio.
     *
     * @param User $user
     * @param string $type
     * @return bool
     */
    private function userHasNotificationEnabled(User $user, string $type): bool
    {
        // Ignorar tipos de canal (solo se manejan notificaciones de escritorio)
        if (in_array($type, ['mobile_notifications', 'email_notifications', 'platform_notifications'])) {
            // Estos son tipos de canal, no tipos de evento
            // Las notificaciones de escritorio siempre están habilitadas si el evento está habilitado
            Log::info("Channel type detected, returning true", ['type' => $type]);
            return true;
        }

        $notificationType = NotificationType::where('key', $type)->first();

        if (!$notificationType) {
            Log::warning("Notification type '{$type}' not found in database. Attempting to create it.");
            
            // Intentar crear el tipo de notificación si no existe
            // Esto es útil para desarrollo y para tipos nuevos
            $notificationType = $this->createNotificationTypeIfMissing($type);
            
            if (!$notificationType) {
                Log::error("Could not create notification type '{$type}'");
                return false;
            }
            
            Log::info("Notification type created", [
                'type' => $type,
                'type_id' => $notificationType->id
            ]);
        }

        $preference = $user->notificationPreferences()
            ->where('notification_type_id', $notificationType->id)
            ->first();

        $isEnabled = $preference ? $preference->enabled : true;

        Log::info("Notification preference check", [
            'user_id' => $user->id,
            'type' => $type,
            'type_id' => $notificationType->id,
            'has_preference' => $preference !== null,
            'preference_enabled' => $preference ? $preference->enabled : null,
            'final_enabled' => $isEnabled
        ]);

        // Si no tiene preferencia establecida, por defecto está habilitada para escritorio
        // Solo se deshabilita si explícitamente el usuario lo desactiva
        // Como solo hay notificaciones de escritorio, si está habilitado el tipo, se envía
        return $isEnabled;
    }

    /**
     * Crear un tipo de notificación si no existe
     *
     * @param string $type
     * @return NotificationType|null
     */
    private function createNotificationTypeIfMissing(string $type): ?NotificationType
    {
        // Mapeo de tipos comunes a sus nombres y categorías
        $typeMap = [
            'task_moved' => [
                'name' => 'Movimiento de tareas',
                'category' => 'kanban',
                'description' => 'Notificaciones cuando se mueven tareas en el tablero Kanban'
            ],
            'hub_changed' => [
                'name' => 'Cambio de Hub',
                'category' => 'ordenes',
                'description' => 'Notificaciones cuando cambia el hub de una orden'
            ],
            'po_hub_real' => [
                'name' => 'Hub Real Diferente',
                'category' => 'ordenes',
                'description' => 'Notificaciones cuando una orden se crea con un hub real diferente al planificado'
            ],
            'vendor_created' => [
                'name' => 'Proveedor Nuevo Creado',
                'category' => 'proveedores',
                'description' => 'Notificaciones cuando se crea un nuevo proveedor sin correo electrónico'
            ],
            'authorization_approved' => [
                'name' => 'Solicitud aprobada',
                'category' => 'autorizaciones',
                'description' => 'Notificaciones cuando una solicitud es aprobada'
            ],
            'authorization_rejected' => [
                'name' => 'Solicitud rechazada',
                'category' => 'autorizaciones',
                'description' => 'Notificaciones cuando una solicitud es rechazada'
            ],
        ];

        $typeData = $typeMap[$type] ?? [
            'name' => ucfirst(str_replace('_', ' ', $type)),
            'category' => 'general',
            'description' => 'Notificación automática'
        ];

        try {
            return NotificationType::create([
                'key' => $type,
                'name' => $typeData['name'],
                'category' => $typeData['category'],
                'description' => $typeData['description'] ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error("Error creating notification type '{$type}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los tipos de notificaciones disponibles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableNotificationTypes()
    {
        return NotificationType::all();
    }

    /**
     * Obtener estadísticas de notificaciones por tipo
     *
     * @return array
     */
    public function getNotificationStats()
    {
        $types = NotificationType::with(['preferences' => function($query) {
            $query->where('enabled', true);
        }])->get();

        $stats = [];
        foreach ($types as $type) {
            $stats[] = [
                'key' => $type->key,
                'name' => $type->name,
                'category' => $type->category,
                'enabled_users_count' => $type->preferences->count(),
                'total_notifications_sent' => Notification::where('type', $type->key)->count(),
            ];
        }

        return $stats;
    }
}
