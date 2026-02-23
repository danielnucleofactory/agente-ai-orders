# Solución: Webhooks de Confirmación de Proveedores

**Fecha:** 9 de febrero de 2026  
**Problema resuelto:** Los webhooks no se disparaban cuando los proveedores confirmaban fechas de entrega

## Resumen del Problema

Cuando un proveedor confirmaba una fecha de entrega a través del sistema de confirmación de Purchase Orders, los cambios se guardaban en la base de datos pero **no se disparaba ningún webhook** para notificar a sistemas externos.

### Causa Raíz

El `PurchaseOrderObserver` estaba diseñado para evitar duplicar webhooks, asumiendo que los controladores y componentes Livewire se encargarían de dispararlos. Sin embargo, el proceso de confirmación de proveedores usa directamente el método `update()` del modelo, sin pasar por controladores ni Livewire.

```php
// PurchaseOrderObserver.php - líneas 208-222
// Dispatch webhook event if status changed
// NOTE: We do NOT dispatch purchase_order.updated here to avoid duplicates
// The controllers/Livewire components already dispatch purchase_order.updated
// This observer only handles audit comments and status_changed events
if ($isStatusChange && function_exists('dispatch_webhook')) {
    // Solo se dispara webhook si cambió el status
    dispatch_webhook('purchase_order.status_changed', [...]);
}
// NOTE: purchase_order.updated is NOT dispatched here to avoid duplicate webhooks
```

## Solución Implementada

### 1. Modificación del Trait `HasPOConfirmationWrapper`

Se agregó lógica para disparar webhooks en los métodos `confirmPO()` y `updateDeliveryDate()`:

**Archivo:** `app/Traits/HasPOConfirmationWrapper.php`

#### Cambios en `confirmPO()` (líneas 184-213)

```php
public function confirmPO(?string $newDeliveryDate = null): bool
{
    // ... código existente ...
    
    // Mark as confirmed
    $this->update([
        'confirm_update_date_po' => true,
        'confirmation_hash' => null,
        'hash_expires_at' => null,
    ]);

    // ✅ NUEVO: Dispatch webhook event for confirmed purchase order
    if (function_exists('dispatch_webhook')) {
        try {
            $this->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
            
            \Log::info('po_confirmation:dispatching_webhook', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
                'new_delivery_date' => $newDeliveryDate,
            ]);
            
            dispatch_webhook('purchase_order.updated', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
                'action' => 'po_confirmed_by_vendor',
                'confirmed_date' => $newDeliveryDate,
                'data' => $this->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user'])->toArray(),
            ]);
            
            \Log::info('po_confirmation:webhook_dispatched', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
            ]);
        } catch (\Exception $e) {
            \Log::error('po_confirmation:webhook_error', [
                'purchase_order_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    return true;
}
```

#### Cambios en `updateDeliveryDate()` (líneas 215-238)

```php
public function updateDeliveryDate(string $newDate): bool
{
    // ... código existente ...
    
    $this->update([
        'date_variable_date' => $newDate,
        'carga_lista_validada' => true,
        'update_date_po' => $newDate,
    ]);

    // ✅ NUEVO: Dispatch webhook event for delivery date update
    if (function_exists('dispatch_webhook')) {
        try {
            $this->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
            
            \Log::info('po_confirmation:dispatching_webhook_date_update', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
                'new_date' => $newDate,
            ]);
            
            dispatch_webhook('purchase_order.updated', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
                'action' => 'delivery_date_updated_by_vendor',
                'new_delivery_date' => $newDate,
                'data' => $this->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user'])->toArray(),
            ]);
            
            \Log::info('po_confirmation:webhook_dispatched_date_update', [
                'purchase_order_id' => $this->id,
                'order_number' => $this->order_number,
            ]);
        } catch (\Exception $e) {
            \Log::error('po_confirmation:webhook_error_date_update', [
                'purchase_order_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    return true;
}
```

### 2. Comando Artisan para Re-enviar Webhooks

Se creó un comando para re-enviar webhooks de confirmaciones que no se enviaron anteriormente.

**Archivo:** `app/Console/Commands/ResendConfirmationWebhooks.php`

#### Uso del Comando

```bash
# Re-enviar webhooks de las últimas 24 horas (por defecto)
php artisan po-confirmation:resend-webhooks

# Re-enviar webhooks de las últimas 48 horas
php artisan po-confirmation:resend-webhooks --hours=48

# Re-enviar webhooks de un rango de fechas específico
php artisan po-confirmation:resend-webhooks --from="2026-02-08 00:00:00" --to="2026-02-09 23:59:59"

# Re-enviar webhooks de POs específicas (por ID)
php artisan po-confirmation:resend-webhooks --po-id=123 --po-id=456 --po-id=789

# Ver qué se haría sin ejecutar (dry-run)
php artisan po-confirmation:resend-webhooks --dry-run
```

#### Características del Comando

1. **Filtrado flexible:**
   - Por horas hacia atrás (default: 24 horas)
   - Por rango de fechas específico
   - Por IDs de PO específicos

2. **Modo dry-run:**
   - Permite ver qué webhooks se enviarían sin ejecutarlos realmente

3. **Logging completo:**
   - Registra cada intento de envío en los logs
   - Muestra tabla de resultados en consola
   - Reporta éxitos y errores

4. **Información detallada:**
   - Progress bar durante el procesamiento
   - Tabla con resultados de cada PO
   - Resumen final con contadores

## Payload del Webhook

El webhook `purchase_order.updated` se dispara con el siguiente payload:

```json
{
  "purchase_order_id": 123,
  "order_number": "PO-2026-001",
  "action": "po_confirmed_by_vendor",
  "confirmed_date": "2026-03-15",
  "data": {
    // Todos los campos de la PO incluyendo relaciones
    "id": 123,
    "order_number": "PO-2026-001",
    "date_variable_date": "2026-03-15",
    "carga_lista_validada": true,
    "confirm_update_date_po": true,
    "products": [...],
    "vendor": {...},
    "shipTo": {...},
    "kanbanStatus": {...},
    "comments": [...]
  }
}
```

### Valores del campo `action`

- **`po_confirmed_by_vendor`**: Proveedor confirmó la PO completa
- **`delivery_date_updated_by_vendor`**: Proveedor actualizó solo la fecha de entrega
- **`po_confirmed_by_vendor_resent`**: Webhook re-enviado por comando artisan

## Flujo Completo

```
1. Proveedor recibe email con link de confirmación
   └─> Click en link con hash único
       └─> Formulario de confirmación de fecha
           ├─> Opción A: Confirmar fecha actual
           │   └─> confirmPO() sin parámetro
           │       └─> update([confirm_update_date_po => true])
           │       └─> dispatch_webhook('purchase_order.updated') ✅
           │
           └─> Opción B: Proponer nueva fecha
               └─> confirmPO(newDate)
                   └─> updateDeliveryDate(newDate)
                       ├─> update([date_variable_date => newDate])
                       └─> dispatch_webhook('purchase_order.updated') ✅
                   └─> update([confirm_update_date_po => true])
                   └─> dispatch_webhook('purchase_order.updated') ✅

2. Sistema externo recibe webhook
   └─> Procesa actualización de PO
       └─> Sincroniza con sus propios sistemas
```

## Testing

### 1. Test Manual - Confirmación de Proveedor

1. Crear una PO de prueba
2. Generar hash de confirmación
3. Enviar email al proveedor (o usar el link directamente)
4. Proveedor confirma fecha
5. Verificar en logs que se disparó el webhook:

```bash
tail -f storage/logs/laravel.log | grep "po_confirmation:dispatching_webhook"
```

### 2. Test Manual - Re-envío de Webhooks

```bash
# Dry-run primero para ver qué se enviará
php artisan po-confirmation:resend-webhooks --hours=48 --dry-run

# Si todo se ve bien, ejecutar sin dry-run
php artisan po-confirmation:resend-webhooks --hours=48
```

### 3. Verificar Logs

Los logs incluyen información completa:

```bash
# Ver webhooks disparados desde confirmaciones
tail -f storage/logs/laravel.log | grep "po_confirmation:"

# Ver webhooks re-enviados por comando
tail -f storage/logs/laravel.log | grep "resend_confirmation_webhook:"
```

Ejemplos de logs:

```
[2026-02-09 10:30:15] local.INFO: po_confirmation:dispatching_webhook {"purchase_order_id":123,"order_number":"PO-2026-001","new_delivery_date":"2026-03-15"}
[2026-02-09 10:30:15] local.INFO: po_confirmation:webhook_dispatched {"purchase_order_id":123,"order_number":"PO-2026-001"}
```

## Casos de Uso del Comando

### Caso 1: Confirmaciones Perdidas

Si el módulo de webhooks no estaba activo cuando se confirmaron POs:

```bash
# Re-enviar todas las confirmaciones de la última semana
php artisan po-confirmation:resend-webhooks --hours=168
```

### Caso 2: Fallos en Webhooks

Si algunos webhooks fallaron al enviarse:

```bash
# Re-enviar webhooks de ayer
php artisan po-confirmation:resend-webhooks --from="2026-02-08 00:00:00" --to="2026-02-08 23:59:59"
```

### Caso 3: Re-enviar POs Específicas

Si un cliente reporta que no recibió notificación de POs específicas:

```bash
# Re-enviar solo esas POs
php artisan po-confirmation:resend-webhooks --po-id=123 --po-id=456
```

### Caso 4: Auditoría/Testing

Para verificar qué POs se procesarían sin enviar webhooks reales:

```bash
# Dry-run para ver qué se haría
php artisan po-confirmation:resend-webhooks --hours=24 --dry-run
```

## Monitoreo y Alertas

### Métricas a Monitorear

1. **Webhooks enviados por confirmaciones**
   - Log: `po_confirmation:webhook_dispatched`
   - Métrica: Conteo por día

2. **Errores en webhooks de confirmación**
   - Log: `po_confirmation:webhook_error`
   - Alerta: Si hay más de 5 errores en 1 hora

3. **POs confirmadas sin webhook**
   - Query: POs donde `confirm_update_date_po = true` pero sin webhook reciente
   - Alerta: Si hay discrepancia

### Query para Detectar Confirmaciones sin Webhook

```sql
-- POs confirmadas en las últimas 24 horas
SELECT id, order_number, updated_at, date_variable_date
FROM purchase_orders
WHERE confirm_update_date_po = true
  AND updated_at >= NOW() - INTERVAL 24 HOUR
ORDER BY updated_at DESC;

-- Luego verificar en logs si se dispararon webhooks para estos IDs
```

## Consideraciones

### Performance

- El comando procesa POs en lote pero despacha webhooks síncronamente
- Para volúmenes grandes (>100 POs), considerar usar jobs en cola
- El comando muestra progress bar para monitorear avance

### Idempotencia

- Los webhooks re-enviados incluyen el campo `action: 'po_confirmed_by_vendor_resent'`
- Los sistemas receptores pueden usar este campo para identificar re-envíos
- Se incluye timestamp `resent_at` para auditoría

### Seguridad

- El comando requiere acceso a la consola del servidor
- Los webhooks incluyen todos los datos de la PO (143 campos)
- Asegurar que los endpoints receptores estén autenticados

## Rollback

Si necesitas deshacer estos cambios:

1. **Revertir cambios en el trait:**
```bash
git checkout HEAD -- app/Traits/HasPOConfirmationWrapper.php
```

2. **Eliminar el comando:**
```bash
rm app/Console/Commands/ResendConfirmationWebhooks.php
```

3. **Limpiar caché de Laravel:**
```bash
php artisan cache:clear
php artisan config:clear
```

## Próximos Pasos

1. ✅ Webhooks implementados en proceso de confirmación
2. ✅ Comando para re-enviar webhooks creado
3. ⏳ Testing en ambiente de staging
4. ⏳ Documentar payload en wiki para clientes externos
5. ⏳ Configurar alertas de monitoreo
6. ⏳ Añadir tests automatizados

---

**Estado:** ✅ Implementado y listo para testing  
**Prioridad:** ALTA  
**Impacto:** Los webhooks ahora se disparan correctamente cuando proveedores confirman fechas
