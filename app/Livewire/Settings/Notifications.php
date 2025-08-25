<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\NotificationType;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class Notifications extends Component
{
    public array $preferences = [];
    public array $activeNotifications = [];

    // Añadimos listeners para los eventos de toggle
    protected $listeners = [
        'togglePreference' => 'togglePreference'
    ];

    public function mount()
    {
        Log::info('Mounting notifications component');

        // Obtener solo las notificaciones que están realmente implementadas
        $this->loadActiveNotifications();

        // Inicializar preferencias del usuario
        $this->loadUserPreferences();
    }

    private function loadActiveNotifications()
    {
        // Solo mostrar las notificaciones que están realmente implementadas en el código
        $this->activeNotifications = [
            'task_moved' => [
                'name' => 'Movimiento de tareas en Kanban',
                'description' => 'Notificaciones cuando se mueven tareas entre columnas del tablero Kanban',
                'category' => 'kanban',
                'implemented' => true,
                'controller' => 'KanbanBoard'
            ],
            'po_hub_real' => [
                'name' => 'Cambio de Hub en PO',
                'description' => 'Notificaciones cuando una orden se crea con un hub real diferente al planificado',
                'category' => 'purchase_orders',
                'implemented' => true,
                'controller' => 'CreatePurchaseOrder'
            ]
        ];
    }

    private function loadUserPreferences()
    {
        $user = auth()->user();

        foreach ($this->activeNotifications as $key => $notification) {
            $notificationType = NotificationType::where('key', $key)->first();

            if ($notificationType) {
                $userPref = $user->notificationPreferences()
                    ->where('notification_type_id', $notificationType->id)
                    ->first();

                $this->preferences[$key] = [
                    'id' => $notificationType->id,
                    'name' => $notification['name'],
                    'description' => $notification['description'],
                    'category' => $notification['category'],
                    'enabled' => $userPref ? $userPref->enabled : false,
                ];
            } else {
                // Si no existe el tipo de notificación, crear placeholder
                $this->preferences[$key] = [
                    'id' => 0,
                    'name' => $notification['name'],
                    'description' => $notification['description'],
                    'category' => $notification['category'],
                    'enabled' => false,
                ];
            }
        }
    }

    public function togglePreference($key, $field = 'enabled')
    {
        Log::info("Toggling preference: {$key}.{$field}");

        if (isset($this->preferences[$key])) {
            $oldValue = $this->preferences[$key][$field];
            $this->preferences[$key][$field] = !$oldValue;
            Log::info("Changed from {$oldValue} to {$this->preferences[$key][$field]}");
        } else {
            Log::warning("Key not found: {$key}");
        }
    }

    public function save()
    {
        Log::info('Saving preferences...');
        Log::info('Preferences: ' . json_encode($this->preferences));

        try {
            $user = auth()->user();

            // Guardar cada preferencia
            foreach ($this->preferences as $key => $values) {
                $notificationType = NotificationType::where('key', $key)->first();

                if (!$notificationType) {
                    Log::warning("Notification type not found for key: {$key}. Creating it...");
                    // Crear el tipo de notificación si no existe
                    $notificationType = NotificationType::create([
                        'key' => $key,
                        'name' => $values['name'],
                        'category' => $values['category'],
                        'description' => $values['description'] ?? '',
                    ]);
                }

                Log::info("Saving preference for type: {$key} with value: " . ($values['enabled'] ? 'true' : 'false'));

                $user->notificationPreferences()->updateOrCreate(
                    ['notification_type_id' => $notificationType->id],
                    ['enabled' => $values['enabled']]
                );
            }

            $this->dispatch('open-modal', 'modal-notifications-saved');
            Log::info('Preferences saved successfully');

            // Re-cargar las preferencias
            $this->mount();
        } catch (\Exception $e) {
            Log::error('Error saving preferences: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            session()->flash('error', 'Error al guardar las preferencias: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.notifications')
            ->layout('layouts.settings.preferences');
    }
}
