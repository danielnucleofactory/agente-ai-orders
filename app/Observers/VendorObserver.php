<?php

namespace App\Observers;

use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VendorObserver
{
    /**
     * Handle the Vendor "created" event.
     */
    public function created(Vendor $vendor): void
    {
        try {
            // Solo enviar notificación si el vendor fue creado automáticamente (sin email)
            // Esto evita notificar cuando se crea manualmente desde el formulario
            if (!$vendor->email || empty(trim($vendor->email))) {
                $this->notifyVendorCreated($vendor);
            }
        } catch (\Exception $e) {
            // Si falla la notificación, solo loguear el error
            // No interrumpir la creación del vendor
            Log::error("Error enviando notificación de vendor creado en VendorObserver: " . $e->getMessage(), [
                'vendor_id' => $vendor->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Enviar notificación cuando se crea un vendor sin email
     */
    protected function notifyVendorCreated(Vendor $vendor): void
    {
        // Obtener todos los usuarios de la empresa del vendor
        $users = \App\Models\User::where('company_id', $vendor->company_id)->get();

        if ($users->isEmpty()) {
            Log::info("No hay usuarios para notificar sobre vendor creado", [
                'vendor_id' => $vendor->id,
                'company_id' => $vendor->company_id
            ]);
            return;
        }

        $notificationService = app(NotificationService::class);

        // Crear notificación para cada usuario de la empresa
        foreach ($users as $user) {
            try {
                $notificationService->createForUser(
                    $user,
                    'vendor_created',
                    'Nuevo Proveedor Creado',
                    "Se creó un nuevo proveedor: {$vendor->name}. Es necesario agregar el correo electrónico para habilitar las comunicaciones.",
                    [
                        'vendor_id' => $vendor->id,
                        'vendor_name' => $vendor->name,
                        'vendor_code' => $vendor->vendo_code,
                        'company_id' => $vendor->company_id,
                        'type' => 'vendor_created'
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Error creando notificación para usuario {$user->id}: " . $e->getMessage());
            }
        }

        Log::info("Notificaciones de vendor creado enviadas", [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'users_notified' => $users->count()
        ]);
    }
}
