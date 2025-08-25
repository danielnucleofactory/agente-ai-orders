## RAGA AI - Orders

# Sistema de Notificaciones

Este sistema permite enviar notificaciones a los usuarios a través de diferentes canales (email, móvil, web) basándose en sus preferencias.

## Configuración inicial

1. Ejecutar las migraciones para crear las tablas necesarias:
   ```bash
   php artisan migrate
   ```

2. Ejecutar el seeder para cargar los tipos de notificaciones predefinidos:
   ```bash
   php artisan db:seed --class=NotificationTypesSeeder
   ```

## Estructura de datos

El sistema utiliza las siguientes tablas:

- `notification_types`: Define los diferentes tipos de notificaciones disponibles.
- `notification_preferences`: Almacena las preferencias de cada usuario para cada tipo de notificación.
- `user_frequencies`: Guarda las frecuencias de notificación seleccionadas por cada usuario.

## Envío de notificaciones

### Uso básico

Para enviar una notificación desde cualquier parte de la aplicación:

```php
// Inyectar el servicio
use App\Services\NotificationService;

class MiControlador
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function algunaAccion()
    {
        // Acción que genera una notificación...

        // Enviar notificación
        $this->notificationService->notify(
            'order_creation_changes',  // tipo de notificación
            auth()->user(),           // usuario destinatario
            [                         // datos adicionales
                'order_id' => $orderId,
                'customer' => $customerName,
                'status' => 'created'
            ]
        );

        return redirect()->back()->with('success', 'Orden creada correctamente');
    }
}
```

### Uso con Facade (alternativa)

Si prefieres usar una sintaxis más simple, puedes utilizar una Facade:

```php
use Notification;

// En cualquier método
Notification::send($user, new OrderCreated($order));
```

### Ejemplo en un modelo (Observer)

Puedes enviar notificaciones automáticamente en respuesta a eventos del modelo:

```php
// app/Observers/OrderObserver.php
namespace App\Observers;

use App\Models\Order;
use App\Services\NotificationService;

class OrderObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function created(Order $order)
    {
        // Notificar al creador de la orden
        $this->notificationService->notify(
            'order_creation_changes',
            $order->user,
            [
                'order_id' => $order->id,
                'total' => $order->total
            ]
        );

        // Notificar a los administradores
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            $this->notificationService->notify(
                'order_creation_changes',
                $admin,
                [
                    'order_id' => $order->id,
                    'user' => $order->user->name,
                    'total' => $order->total
                ]
            );
        }
    }

    public function updated(Order $order)
    {
        if ($order->isDirty('status')) {
            $this->notificationService->notify(
                'status_update',
                $order->user,
                [
                    'order_id' => $order->id,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => $order->status
                ]
            );
        }
    }
}
```

No olvides registrar el observer en `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    \App\Models\Order::observe(\App\Observers\OrderObserver::class);
}
```

## Tipos de notificaciones disponibles

- **Notificaciones móviles**: Alertas en la app móvil.
- **Notificaciones por correo electrónico**: Mensajes enviados por email.
- **Notificaciones en la plataforma**: Pop-ups o banners en el dashboard.

### Categorías de notificaciones

- **Cargas y envíos**:
  - Actualización de estado
  - Problemas detectados
  - Entregas exitosas

- **Recordatorios**:
  - Tareas pendientes
  - Vencimientos próximos
  - Personalización por usuario

- **Órdenes**:
  - Creación o cambios en PO's
  - Al consolidar una orden

## Frecuencias de notificación

Los usuarios pueden elegir recibir notificaciones con las siguientes frecuencias:

- **Inmediato**: Notificaciones enviadas al instante.
- **Diario**: Resumen de actividad al final del día.
- **Semanal**: Resumen consolidado de la semana.

Los usuarios pueden seleccionar múltiples frecuencias simultáneamente.

## Configuración de preferencias

Los usuarios pueden configurar sus preferencias de notificación en la sección "Notificaciones" dentro de su perfil. Pueden:

1. Activar/desactivar tipos de notificaciones
2. Seleccionar las frecuencias deseadas

## Programación de notificaciones

Las notificaciones diarias y semanales se envían mediante tareas programadas:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Resúmenes diarios a las 20:00
    $schedule->command('notifications:send-daily')->dailyAt('20:00');

    // Resúmenes semanales los domingos a las 18:00
    $schedule->command('notifications:send-weekly')->weeklyOn(0, '18:00');
}
```

## Cómo extender el sistema

### Agregar un nuevo tipo de notificación

1. Añadir la nueva notificación al seeder:

```php
// database/seeders/NotificationTypesSeeder.php
$types[] = [
    'key' => 'payment_received',
    'name' => 'Pago recibido',
    'category' => 'pagos'
];
```

2. Ejecutar el seeder:
```bash
php artisan db:seed --class=NotificationTypesSeeder
```

3. Usar el nuevo tipo al enviar notificaciones:
```php
$notificationService->notify('payment_received', $user, $data);
```

### Crear un nuevo canal de notificación

1. Modificar la migración para agregar el nuevo canal
2. Actualizar el componente Livewire de preferencias para incluir el nuevo canal
3. Ampliar el `NotificationService` para enviar a través del nuevo canal

## Solución de problemas

Si las notificaciones no se están enviando, verifica:

1. Que el usuario tenga habilitado el tipo de notificación correspondiente
2. Que existan registros en la tabla `notification_types` para el tipo que intentas enviar
3. Que los logs no muestren errores durante el envío
4. Que las configuraciones de correo (para notificaciones por email) sean correctas

## Comandos útiles

- Para enviar resúmenes diarios manualmente:
  ```bash
  php artisan notifications:send-daily
  ```

- Para enviar resúmenes semanales manualmente:
  ```bash
  php artisan notifications:send-weekly
  ```

- Para limpiar notificaciones antiguas:
  ```bash
  php artisan notifications:prune
  ```



### Enviar notificaciones

```php
// Ejemplo 1: Notificar a todos los usuarios
app(\App\Services\NotificationService::class)->notifyAll(
    'new_comment',
    'Nuevo comentario',
    "El usuario {$user->name} ha comentado en la orden {$order->number}",
    ['order_id' => $order->id, 'comment_id' => $comment->id]
);

// Ejemplo 2: Notificar a usuarios específicos
app(\App\Services\NotificationService::class)->notifyUsers(
    [$manager->id, $supervisor->id],
    'order_approved',
    'Orden aprobada',
    "La orden {$order->number} ha sido aprobada",
    ['order_id' => $order->id]
);

// Ejemplo 3: Notificar a un solo usuario
app(\App\Services\NotificationService::class)->createForUser(
    $user,
    'task_assigned',
    'Tarea asignada',
    "Se te ha asignado la tarea {$task->name}",
    ['task_id' => $task->id]
);
```

# olo-raga-orders
