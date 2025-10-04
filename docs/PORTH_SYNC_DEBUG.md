# Debugging Porth Sync Issues

## Problema Identificado

El MBL number se guarda correctamente en local pero no en el servidor de desarrollo. Esto se debe a la sincronización automática con Porth que puede estar sobrescribiendo los datos locales.

## Causa Raíz

1. **Sincronización Automática**: Cada vez que se guarda un `ShippingDocument`, se dispara automáticamente un `PorthSyncJob`
2. **Datos de Porth**: El servidor de desarrollo puede tener datos en Porth que sobrescriben los valores locales
3. **Diferencia de Entornos**: En local, Porth puede no estar configurado o no tener datos, por lo que no sobrescribe

## Soluciones Implementadas

### 1. Logs Detallados
- Agregados logs en `PorthSyncService` para rastrear qué datos devuelve Porth
- Logs en `updateDocumentWithPorthData` para detectar sobrescritura de datos

### 2. Protección de Datos Locales
- El código ahora preserva los valores locales de `mbl_number` y `container_number`
- Solo se actualiza el `porth_shipment_id`

### 3. Configuración de Sincronización
- Agregada configuración `PORTH_SYNC_ENABLED` para deshabilitar la sincronización
- Por defecto está habilitada, pero se puede deshabilitar en desarrollo

## Comandos de Debugging

### Verificar Estado de Sincronización
```bash
php artisan porth:check-sync
```

### Verificar Documento Específico
```bash
php artisan porth:check-sync {document_id}
```

### Deshabilitar Sincronización Temporalmente
Agregar al archivo `.env` del servidor de desarrollo:
```
PORTH_SYNC_ENABLED=false
```

## Logs a Revisar

1. **Laravel Logs**: `storage/logs/laravel.log`
2. **Buscar por**: `PorthSyncService`, `PorthSyncJob`
3. **Verificar**: Si Porth está devolviendo datos que sobrescriben los locales

## Pasos para Resolver en Desarrollo

1. **Deshabilitar sincronización temporalmente**:
   ```bash
   # En el servidor de desarrollo
   echo "PORTH_SYNC_ENABLED=false" >> .env
   ```

2. **Verificar logs**:
   ```bash
   tail -f storage/logs/laravel.log | grep -i porth
   ```

3. **Probar guardado de MBL**:
   - Crear/editar un documento de embarque
   - Verificar que el MBL se guarda correctamente
   - Revisar logs para confirmar que no hay sobrescritura

4. **Re-habilitar sincronización** (cuando esté resuelto):
   ```bash
   # Remover o comentar la línea
   # PORTH_SYNC_ENABLED=false
   ```

## Variables de Entorno

```env
# Porth Configuration
PORTH_API_KEY=your_api_key_here
PORTH_BASE_URL=https://porth-api.fly.dev
PORTH_SYNC_ENABLED=true  # Set to false to disable sync
```

## Monitoreo

Para monitorear el estado de la sincronización:

```bash
# Ver jobs en cola
php artisan queue:work --queue=porth-sync

# Ver jobs fallidos
php artisan queue:failed

# Verificar estado
php artisan porth:check-sync
```
