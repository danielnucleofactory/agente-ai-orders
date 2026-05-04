# Análisis del Problema: Creación de Embarques en Porth

**Fecha:** 9 de febrero de 2026  
**Reportado por:** Daniel  
**Archivos analizados:**
- `app/Services/PorthApiService.php`
- `app/Services/PorthSyncService.php`
- `app/Services/PorthImportService.php`
- `app/Observers/PurchaseOrderObserver.php`
- `app/Traits/HasPOConfirmationWrapper.php`

## Resumen del Problema

Se están experimentando problemas con la creación de embarques en Porth:

1. **Cuando se crean embarques SIN naviera:** Llegan datos equivocados desde Porth
2. **Cuando se agrega la naviera después:** No hay forma de arreglar la data incorrecta que ya fue guardada

## Análisis de la Causa Raíz

### 1. Flujo de Creación de Embarques (SIN naviera)

**Archivo:** `app/Services/PorthSyncService.php` (líneas 237-302)

Cuando se crea un embarque en Porth, la función `buildCreatePayload()` intenta incluir el `carrierCode`:

```php
// Líneas 292-299
$carrierCode = $document->porth_carrier_code ?? null;
if (empty($carrierCode) && !empty($document->shipping_line)) {
    $carrierCode = $this->translationService->getCarrierCodeFromShippingLineName($document->shipping_line);
}
if (!empty($carrierCode)) {
    $basePayload['carrierCode'] = $carrierCode;
}
```

**PROBLEMA:** Si tanto `porth_carrier_code` como `shipping_line` están vacíos, el embarque se crea en Porth **SIN** `carrierCode`.

**CONSECUENCIA:** Porth devuelve información incorrecta o incompleta cuando no tiene el `carrierCode`, ya que no puede determinar:
- Las rutas correctas
- Los puertos de origen/destino apropiados
- Los tiempos de tránsito correctos
- La información de tracking específica de la naviera

### 2. Flujo de Actualización de Naviera

**Archivo:** `app/Services/PorthApiService.php` (líneas 348-381)

El commit `1d0a46a` (hace 3 días) implementó una lógica para actualizar la naviera:

```php
// B) Si solo cambió la naviera: actualizar el embarque existente en Porth
if (!empty($updateChanges) && !empty($po->porth_id)) {
    // ... actualiza solo el carrierCode en Porth
    $this->updateShipment($porthId, $porthPayload);
}
```

**PROBLEMA:** Esta lógica actualiza el `carrierCode` en Porth, **PERO**:
- Los datos incorrectos YA fueron guardados en la base de datos local
- NO hay un mecanismo para "re-sincronizar" los datos después de actualizar el `carrierCode`
- Porth probablemente actualiza su información interna con el nuevo `carrierCode`, pero nosotros seguimos con la data vieja

### 3. Cambio Reciente en HasPOConfirmationWrapper

**Commit:** `95572e0` (hace 7 horas)  
**Archivo:** `app/Traits/HasPOConfirmationWrapper.php` (líneas 224-228)

**CAMBIO:**
```php
// ANTES:
'date_theorical_load' => $newDate,

// AHORA:
'date_variable_date' => $newDate,       // Fecha validada (confirmada por proveedor)
'carga_lista_validada' => true,         // Marcar como validada
```

**IMPACTO:** Este cambio podría estar afectando otros flujos que dependen de `date_theorical_load`, aunque no está directamente relacionado con el problema de la naviera.

## Diagrama del Flujo Problemático

```
1. Usuario crea PO sin naviera
   └─> PurchaseOrderObserver detecta cambios
       └─> PorthSyncJob se despacha
           └─> PorthSyncService.buildCreatePayload()
               ├─> carrierCode = null (porque no hay shipping_line)
               └─> Se crea en Porth SIN carrierCode
                   └─> Porth devuelve DATOS INCORRECTOS
                       └─> PorthImportService guarda datos incorrectos en DB ❌

2. Usuario agrega naviera después
   └─> PurchaseOrderObserver detecta cambio en shipping_line
       └─> PorthApiService.pushChangesToPorth()
           └─> Solo actualiza carrierCode en Porth ✅
               ├─> Porth actualiza su información interna ✅
               └─> PERO: Datos incorrectos siguen en nuestra DB ❌
                   └─> NO hay re-sincronización automática ❌
```

## Soluciones Propuestas

### Solución 1: Re-sincronización Automática después de Actualizar NavieraDeprés (RECOMENDADA)

Modificar `PorthApiService.pushChangesToPorth()` para disparar una re-sincronización después de actualizar el `carrierCode`:

```php
// En línea 373, después de updateShipment
if ($this->updateShipment($porthId, $porthPayload)) {
    // Re-sincronizar para obtener los datos correctos de Porth
    Log::info('porth_api:triggering_resync_after_carrier_update', [
        'purchase_order_id' => $po->id,
        'porth_id' => $porthId,
    ]);
    
    // Despachar job para importar los datos actualizados desde Porth
    \App\Jobs\PorthImportJob::dispatch($porthId)
        ->onQueue('porth-sync')
        ->delay(now()->addSeconds(10)); // Dar tiempo a Porth para actualizar
}
```

### Solución 2: Prevenir Creación sin Naviera

Validar que la naviera esté presente antes de crear el embarque en Porth:

```php
// En PorthSyncService.buildCreatePayload(), línea 292
$carrierCode = $document->porth_carrier_code ?? null;
if (empty($carrierCode) && !empty($document->shipping_line)) {
    $carrierCode = $this->translationService->getCarrierCodeFromShippingLineName($document->shipping_line);
}

// AGREGAR VALIDACIÓN:
if (empty($carrierCode)) {
    Log::warning('porth_sync:missing_carrier_code', [
        'document_id' => $document->id,
        'message' => 'No se puede crear embarque en Porth sin naviera/carrierCode'
    ]);
    return null; // No crear embarque sin carrierCode
}
```

### Solución 3: Híbrida (Mejor Enfoque)

Combinar ambas soluciones:
1. **Prevenir** creación sin naviera (validación)
2. **Permitir** casos excepcionales pero con re-sincronización automática
3. **Re-sincronizar** siempre después de actualizar naviera

## Cambios Específicos Requeridos

### Archivo: `app/Services/PorthApiService.php`

```php
// Línea 373, dentro del try de updateShipment
try {
    $updateResult = $this->updateShipment($porthId, $porthPayload);
    
    if ($updateResult) {
        Log::info('porth_api:carrier_updated_triggering_resync', [
            'purchase_order_id' => $po->id,
            'porth_id' => $porthId,
        ]);
        
        // Disparar re-importación para obtener datos correctos de Porth
        dispatch(function() use ($porthId) {
            $porthApi = app(PorthApiService::class);
            $shipmentData = $porthApi->getShipmentById($porthId);
            
            if ($shipmentData) {
                $importService = app(PorthImportService::class);
                $importService->importShipment($shipmentData);
            }
        })->delay(now()->addSeconds(15))->onQueue('porth-sync');
    }
} catch (\Throwable $e) {
    // ... manejo de error existente
}
```

### Archivo: `app/Services/PorthSyncService.php`

```php
// Línea 292, agregar validación
$carrierCode = $document->porth_carrier_code ?? null;
if (empty($carrierCode) && !empty($document->shipping_line)) {
    $carrierCode = $this->translationService->getCarrierCodeFromShippingLineName($document->shipping_line);
}

// NUEVO: Advertir si no hay carrierCode
if (empty($carrierCode)) {
    Log::warning('porth_sync:creating_shipment_without_carrier', [
        'document_id' => $document->id,
        'document_type' => get_class($document),
        'identifier' => $identifier,
        'message' => 'Creando embarque sin naviera - los datos de Porth podrían ser incorrectos'
    ]);
}

if (!empty($carrierCode)) {
    $basePayload['carrierCode'] = $carrierCode;
}
```

## Commits Relacionados

1. **`1d0a46a`** (hace 3 días): Implementó `pushChangesToPorth()` con separación de comportamiento para identificadores vs naviera
2. **`7be10ca`** (hace 3 días): Agregó la integración inicial de sincronización de cambios a Porth
3. **`95572e0`** (hace 7 horas): Cambió `date_theorical_load` por `date_variable_date` (posible problema colateral)

## Recomendaciones

1. **INMEDIATO:** Implementar Solución 1 (re-sincronización automática)
2. **CORTO PLAZO:** Agregar validación para prevenir creación sin naviera (Solución 2)
3. **MEDIANO PLAZO:** Revisar si el cambio de `date_theorical_load` está causando problemas adicionales
4. **LARGO PLAZO:** Considerar agregar un comando manual para "forzar re-sincronización" de embarques problemáticos

## Pasos para Reproducir el Problema

1. Crear una Purchase Order sin especificar `shipping_line`
2. Guardar y esperar a que se sincronice con Porth
3. Verificar que los datos en la PO son incorrectos (puertos, fechas, etc.)
4. Agregar una naviera (`shipping_line`)
5. Guardar y esperar sincronización
6. Verificar que los datos siguen siendo incorrectos ❌

## Testing Requerido

Después de implementar las soluciones:

1. Crear PO sin naviera → Verificar que no se crea embarque en Porth O se crea con advertencia
2. Crear PO con naviera → Verificar que datos son correctos
3. Crear PO sin naviera, agregar naviera después → Verificar re-sincronización automática y datos correctos

---

**Estado:** Pendiente de implementación  
**Prioridad:** ALTA  
**Impacto:** Datos incorrectos en producción
