# Guía de Configuración: Importación de Datos de Porth para POs Recién Vinculadas

## 📋 Resumen

Esta guía explica cómo configurar el sistema para que las Purchase Orders (POs) recién vinculadas con Porth (al agregar MBL/Container/Booking) importen sus datos completos de forma automática en un tiempo razonable (máximo 5 minutos), sin causar timeouts en las requests del usuario.

## 🎯 Objetivo

- **Request del usuario**: Rápida (~1-2 segundos) - Solo asigna `porth_id`
- **Importación de datos**: Automática en máximo 5 minutos mediante cronjob
- **Sin timeouts**: No bloquea la request del usuario

## 🔄 Flujo Actual

```
1. Usuario agrega MBL/Container a PO
   ↓
2. PorthSyncJob busca/crea embarque en Porth (sincrónico)
   ↓
3. Se asigna porth_id a la PO (rápido, ~1-2 segundos)
   ↓
4. Cronjob 'porth:import-pending' se ejecuta cada 5 minutos
   ↓
5. Importa datos completos de Porth (fechas, puertos, estados, etc.)
   ↓
6. Actualiza last_porth_sync_at (marca como importada)
```

## 📦 Archivos Modificados/Creados

### Archivos Modificados:
1. `app/Services/PorthSyncService.php` - Eliminado dispatch de PorthImportJob
2. `app/Console/Kernel.php` - Agregado cronjob cada 5 minutos

### Archivos Creados:
1. `app/Console/Commands/PorthImportPending.php` - Nuevo comando

## 🚀 Pasos para Producción

### Paso 1: Verificar que los cambios estén en el servidor

```bash
# En el servidor de producción
cd /ruta/al/proyecto

# Verificar que el nuevo comando existe
php artisan list | grep porth:import-pending

# Debe mostrar:
# porth:import-pending  Importa datos de Porth para POs recién vinculadas...
```

### Paso 2: Verificar configuración del scheduler

Verificar que el cronjob de Laravel esté configurado en el servidor:

```bash
# Verificar crontab
crontab -l

# Debe incluir algo como:
# * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Si no está configurado, agregar al crontab:

```bash
# Editar crontab
crontab -e

# Agregar esta línea (ajustar la ruta):
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Paso 3: Probar el comando manualmente

```bash
# Probar en modo dry-run (no modifica datos)
php artisan porth:import-pending --dry-run

# Probar importación real (limitado a 5 POs)
php artisan porth:import-pending --limit=5

# Ver logs
tail -f storage/logs/laravel.log | grep "porth:import-pending"
```

### Paso 4: Verificar que el scheduler está funcionando

```bash
# Verificar que el scheduler detecta el comando
php artisan schedule:list

# Debe mostrar (según routes/console.php):
# porth:import-pending --limit=20  ... Every Five Minutes
# porth:sync-recent --trigger=schedule  ... Every Two Hours
```

### Paso 5: Monitorear la primera ejecución

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep -E "porth:import-pending|PorthImportPending"

# O verificar directamente
php artisan porth:import-pending --limit=1
```

## 🔍 Verificación y Monitoreo

### Verificar POs pendientes de importación

```sql
-- Ver POs con porth_id pero sin datos importados
SELECT 
    id,
    order_number,
    porth_id,
    mbl_number,
    container_number,
    last_porth_sync_at,
    created_at
FROM purchase_orders
WHERE porth_id IS NOT NULL
  AND last_porth_sync_at IS NULL
ORDER BY created_at DESC
LIMIT 20;
```

### Verificar que el cronjob está ejecutándose

```bash
# Ver logs del scheduler
grep "porth:import-pending" storage/logs/laravel.log | tail -20

# Verificar última ejecución
php artisan schedule:test
```

### Monitorear rendimiento

```bash
# Ver estadísticas de importación
grep "porth:import-pending:completed" storage/logs/laravel.log | tail -10
```

## ⚙️ Configuración Opcional

### Ajustar límite de POs por ejecución

Editar `app/Console/Kernel.php`:

```php
$schedule->command('porth:import-pending --limit=50')  // Cambiar de 20 a 50
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
```

### Cambiar frecuencia de ejecución

```php
// Cada 3 minutos
$schedule->command('porth:import-pending --limit=20')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Cada 10 minutos
$schedule->command('porth:import-pending --limit=20')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
```

## 🐛 Troubleshooting

### El comando no se ejecuta automáticamente

**Problema**: El cronjob no está corriendo.

**Solución**:
1. Verificar que el crontab esté configurado (Paso 2)
2. Verificar permisos del archivo `schedule:run`
3. Verificar logs del sistema: `grep CRON /var/log/syslog`

### POs no se están importando

**Problema**: Las POs siguen con `last_porth_sync_at IS NULL`.

**Solución**:
1. Ejecutar manualmente: `php artisan porth:import-pending --limit=5`
2. Revisar logs: `tail -f storage/logs/laravel.log | grep "porth:import-pending"`
3. Verificar que `porth_id` esté correctamente asignado en la PO
4. Verificar que la API de Porth esté accesible

### Errores de timeout en el comando

**Problema**: El comando tarda mucho o da timeout.

**Solución**:
1. Reducir el límite: `--limit=10` en lugar de `--limit=20`
2. Verificar conectividad con Porth API
3. Revisar si hay muchas POs pendientes (puede tomar tiempo procesarlas todas)

### El scheduler se solapa (overlapping)

**Problema**: Múltiples instancias del comando ejecutándose al mismo tiempo.

**Solución**:
- El flag `->withoutOverlapping()` ya está configurado
- Si persiste, verificar que no haya múltiples workers del scheduler corriendo

## 📊 Métricas Esperadas

### Tiempos normales:
- **Asignación de porth_id**: 1-2 segundos
- **Importación de datos**: 2-5 segundos por PO
- **Tiempo máximo hasta importación**: 5 minutos (frecuencia del cronjob)

### Volumen esperado:
- **POs pendientes típicas**: 0-10 por ejecución
- **Tiempo de ejecución típico**: 10-30 segundos
- **POs procesadas por hora**: ~240 (20 cada 5 minutos)

## ✅ Checklist de Producción

- [ ] Comando `porth:import-pending` existe y funciona
- [ ] Scheduler de Laravel configurado en crontab
- [ ] Comando probado manualmente en producción
- [ ] `schedule:list` muestra el comando configurado
- [ ] Logs muestran ejecuciones exitosas
- [ ] POs pendientes se están importando correctamente
- [ ] No hay timeouts en las requests del usuario
- [ ] Monitoreo configurado para alertas

## 📝 Notas Importantes

1. **No cambiar `QUEUE_CONNECTION`**: Debe permanecer en `sync` para no afectar otros procesos.

2. **El comando es idempotente**: Puede ejecutarse múltiples veces sin problemas. Solo procesa POs con `last_porth_sync_at IS NULL`.

3. **El cronjob existente (`porth:sync-recent`) sigue funcionando**: Se ejecuta cada **2 horas** para actualizar embarques que ya tienen datos importados. Para ventanas manuales: `php artisan porth:sync-recent --trigger=manual --hours=N`.

4. **Si hay muchas POs pendientes**: El comando procesa hasta el límite configurado (20 por defecto) en cada ejecución. Las restantes se procesarán en la siguiente ejecución (5 minutos después).

## 🔗 Comandos Relacionados

- `php artisan porth:sync-recent` - Sincroniza embarques actualizados (programado cada 2 h; manual con `--hours=` / `--dry-run`)
- `php artisan porth:link-existing` - Vincula POs existentes con Porth
- `php artisan porth:check-sync` - Verifica estado de sincronización

## 📞 Soporte

Si encuentras problemas:
1. Revisar logs: `storage/logs/laravel.log`
2. Ejecutar en modo dry-run: `php artisan porth:import-pending --dry-run`
3. Verificar configuración: `php artisan schedule:list`
4. Consultar documentación: `docs/porth-integration.md`
