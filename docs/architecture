# Sistema de Autorizaciones

Este documento describe el nuevo sistema centralizado de autorizaciones implementado en el proyecto.

## Descripción General

El sistema de autorizaciones permite gestionar los procesos de aprobación de diferentes entidades en la aplicación de manera escalable y unificada. Cualquier modelo que requiera autorización puede utilizar este sistema sin necesidad de modificar su estructura de tabla.

## Estructura

### Tablas Principales

1. **authorizations**: Tabla central que registra todas las solicitudes de autorización.
   - Reemplaza la antigua tabla `authorization_requests`
   - Mantiene relaciones polimórficas con las entidades a autorizar

2. **purchase_order_comments**: Ahora incluye un campo `status` que refleja el estado de autorización.
   - `pending`: Comentario pendiente de aprobación
   - `approved`: Comentario aprobado
   - `rejected`: Comentario rechazado

### Relaciones

- Relación polimórfica `morphMany` entre los modelos y sus autorizaciones
- No se requieren campos adicionales en las tablas de entidades

## Uso del Sistema

### 1. Preparar un Modelo para Autorización

Para hacer que un modelo soporte autorizaciones, añade el trait `HasAuthorizations`:

```php
use App\Models\Traits\HasAuthorizations;

class MiModelo extends Model
{
    use HasAuthorizations;

    // ...
}
```

### 2. Crear una Solicitud de Autorización

```php
// Desde la instancia del modelo
$comentario = PurchaseOrderComment::find(1);
$comentario->createAuthorizationRequest('operation_type', [
    'additional_data' => 'valor',
    'otro_dato' => 123
]);

// O usando el servicio
$authService = app(AuthorizationService::class);
$authService->createRequest($comentario, 'operation_type', [
    'additional_data' => 'valor'
]);
```

### 3. Gestionar Solicitudes

```php
// Aprobar una solicitud
$authorizationService->approve($authorization, 'Nota de aprobación');

// Rechazar una solicitud
$authorizationService->reject($authorization, 'Motivo de rechazo');
```

### 4. Consultar Solicitudes

```php
// Obtener todas las solicitudes pendientes para un modelo
$modelo->pendingAuthorizations()->get();

// Obtener todas las solicitudes aprobadas
$modelo->approvedAuthorizations()->get();

// Verificar si hay una solicitud pendiente de cierto tipo
$modelo->hasAuthorizationPending('operation_type');
```

## Flujo para Comentarios en Órdenes de Compra

1. Al crear un comentario:
   - Se crea inmediatamente el registro en `purchase_order_comments` con estado "pending" o "approved"
   - Si tiene adjuntos, se crea automáticamente una autorización vinculada al comentario

2. Al aprobar la autorización:
   - Se actualiza el estado del comentario a "approved"
   - Se pueden adjuntar los archivos si la solicitud los incluía

3. Para mostrar comentarios:
   - Se consulta directamente la tabla `purchase_order_comments`
   - Se filtra por estado si es necesario

## Beneficios del Nuevo Sistema

- **Escalabilidad**: Cualquier entidad puede soportar autorizaciones fácilmente sin modificar su tabla
- **Consistencia**: Un enfoque unificado para todos los procesos de autorización
- **Trazabilidad**: Historial claro de solicitudes y sus estados
- **Rendimiento**: Consultas optimizadas al eliminar joins innecesarios
- **Mantenibilidad**: Código más limpio y centralizado

## Migración desde el Sistema Anterior

Se ha creado una migración que:
1. Crea la nueva tabla `authorizations` si no existe
2. Migra los datos de `authorization_requests` a la nueva tabla
3. Elimina la tabla antigua

## Consideraciones Adicionales

- Los modelos con autorizaciones deben incluir un campo `status` para reflejar su estado de aprobación
- Se mantiene compatibilidad con el sistema anterior para evitar problemas durante la transición
