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
        // Verificar si el usuario tiene habilitado este tipo de notificación
        if (!$this->userHasNotificationEnabled($user, $type)) {
            Log::info("Notification skipped for user {$user->id} - type '{$type}' is disabled");
            return null;
        }

        return Notification::create([
            'type' => $type,
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
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
        $users = User::all();
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
     * @param User $user
     * @param string $type
     * @return bool
     */
    private function userHasNotificationEnabled(User $user, string $type): bool
    {
        $notificationType = NotificationType::where('key', $type)->first();

        if (!$notificationType) {
            Log::warning("Notification type '{$type}' not found in database");
            return false;
        }

        $preference = $user->notificationPreferences()
            ->where('notification_type_id', $notificationType->id)
            ->first();

        // Si no tiene preferencia establecida, por defecto está deshabilitado
        return $preference ? $preference->enabled : false;
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
