# Integración Porth - Sincronización de Embarques Actualizados

## 📋 Resumen

Esta documentación describe la implementación completa de la integración con **Porth**, un sistema que permite sincronizar automáticamente los embarques actualizados desde la API de Porth hacia Raga Orders. La integración funciona en dos fases:

1. **Fase 1: Listado de embarques actualizados** - Obtiene la lista de IDs de embarques que han sido actualizados en un rango de tiempo
2. **Fase 2: Actualización de datos** - Para cada ID, obtiene el detalle completo y actualiza los documentos de envío y órdenes de compra asociadas

## 🎯 Objetivos Cumplidos

- ✅ Sincronización automática de embarques actualizados desde Porth
- ✅ Actualización bidireccional de datos (Shipping Documents y Purchase Orders)
- ✅ Sistema de tracking de sincronizaciones con registro de corridas
- ✅ Notificaciones a usuarios sobre actualizaciones
- ✅ Sincronización programada (cron) y manual
- ✅ Modo dry-run para pruebas sin modificar datos
- ✅ Manejo robusto de errores y reintentos

## 🏗️ Arquitectura de la Solución

### Flujo de Sincronización

```
┌─────────────────────┐
│  Scheduler (Cron)   │  Cada hora ejecuta: porth:sync-recent
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ PorthSyncRecent     │  Comando que orquesta la sincronización
│ Command             │
└──────────┬──────────┘
           │
           ├─────────────────────────────────────┐
           │                                     │
           ▼                                     ▼
┌─────────────────────┐              ┌─────────────────────┐
│ PorthApiService     │              │ PorthSyncUpdated    │
│                     │              │ Service             │
│ - listLastUpdated() │              │                     │
│ - getShipmentById() │              │ - syncRange()        │
└──────────┬──────────┘              │ - syncRecent()       │
           │                         └──────────┬──────────┘
           │                                     │
           │                                     ▼
           │                         ┌─────────────────────┐
           │                         │ PorthImportService  │
           │                         │                     │
           │                         │ - importShipment()  │
           │                         │ - syncCargos()       │
           │                         │ - syncPhases()      │
           │                         │ - syncItineraries() │
           │                         └──────────┬──────────┘
           │                                     │
           └─────────────────────────────────────┘
                         │
                         ▼
           ┌─────────────────────────────┐
           │  ShippingDocument           │
           │  PurchaseOrder              │
           │  PorthCargo                 │
           │  PorthPhase                 │
           │  PorthItinerary             │
           └─────────────────────────────┘
```

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────┐
│                    PorthSyncRecent Command                   │
│  - Orquesta la sincronización                                │
│  - Registra corridas en porth_sync_runs                      │
│  - Envía notificaciones                                      │
└─────────────────────────────────────────────────────────────┘
                            │
        ┌───────────────────┴───────────────────┐
        │                                       │
        ▼                                       ▼
┌───────────────────┐              ┌──────────────────────┐
│ PorthApiService   │              │ PorthSyncUpdated     │
│                   │              │ Service             │
│ - listLastUpdated │              │                      │
│ - getShipmentById │              │ - syncRange()        │
└───────────────────┘              │ - syncRecent()       │
                                   └──────────┬───────────┘
                                              │
                                              ▼
                                   ┌──────────────────────┐
                                   │ PorthImportService   │
                                   │                      │
                                   │ - importShipment()   │
                                   │ - updateShippingDoc │
                                   │ - syncPurchaseOrders │
                                   └──────────┬───────────┘
                                              │
                    ┌────────────────────────┼────────────────────────┐
                    │                        │                        │
                    ▼                        ▼                        ▼
         ┌──────────────────┐   ┌──────────────────┐   ┌──────────────────┐
         │ PorthImportHelper │   │ ShippingDocument │   │  PurchaseOrder    │
         │                   │   │                  │   │                   │
         │ - getBlFields()    │   │ - porthCargos()  │   │                   │
         │ - getDateFields() │   │ - porthPhases()  │   │                   │
         │ - getPorthFields()│   │ - porthItineraries│   │                   │
         └──────────────────┘   └──────────────────┘   └──────────────────┘
```

## 📁 Estructura de Archivos

### Nuevos Archivos Creados

```
app/
├── Models/
│   ├── PorthCargo.php                    # Modelo para cargos de Porth
│   ├── PorthPhase.php                    # Modelo para fases de Porth
│   ├── PorthItinerary.php                # Modelo para itinerarios de Porth
│   └── PorthSyncRun.php                  # Modelo para corridas de sincronización
├── Services/
│   ├── PorthApiService.php               # Servicio para comunicación con API de Porth
│   ├── PorthSyncService.php              # Servicio para sincronización inicial (lookup/create)
│   ├── PorthSyncUpdatedService.php        # Servicio para sincronizar embarques actualizados
│   └── PorthImportService.php            # Servicio para importar datos de Porth
├── Helpers/
│   └── PorthImportHelper.php             # Helper para mapeo y transformación de datos
├── Jobs/
│   └── PorthSyncJob.php                  # Job para sincronización asíncrona
└── Console/Commands/
    └── PorthSyncRecent.php               # Comando para sincronizar embarques recientes

database/migrations/
├── 2025_12_19_152651_add_porth_id_to_shipping_documents.php
├── 2025_12_22_000001_create_porth_structures.php
├── 2025_12_22_000002_create_porth_sync_runs_table.php
├── 2025_12_22_000003_add_porth_fields_and_modality_to_documents.php
├── 2026_01_05_161346_add_porth_fields_to_purchase_orders_table.php
└── 2026_01_05_181836_remove_unique_constraint_from_porth_id_in_shipping_documents.php
```

### Archivos Modificados

```
app/
├── Console/Kernel.php                    # Agregado schedule para porth:sync-recent
├── Models/
│   ├── ShippingDocument.php              # Agregados campos y relaciones de Porth
│   └── PurchaseOrder.php                 # Agregados campos de Porth
└── Services/
    └── NotificationService.php            # Soporte para notificaciones de Porth

config/
└── services.php                          # Configuración de Porth
```

## 🗄️ Esquema de Base de Datos

### Tabla: `porth_sync_runs`

Registra cada corrida de sincronización con métricas y estado.

```sql
CREATE TABLE porth_sync_runs (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    trigger             VARCHAR(50) DEFAULT 'manual',      -- cron, schedule, manual, webhook
    scope               VARCHAR(255) NULL,                 -- ej: "recent:2h"
    status              VARCHAR(50) DEFAULT 'running',     -- running, success, partial, failed
    started_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at         TIMESTAMP NULL,
    duration_ms         UNSIGNED INT NULL,
    total               UNSIGNED INT DEFAULT 0,            -- Total de IDs encontrados
    processed           UNSIGNED INT DEFAULT 0,             -- Total procesados
    updated             UNSIGNED INT DEFAULT 0,             -- Actualizados exitosamente
    no_change           UNSIGNED INT DEFAULT 0,             -- Sin cambios
    skipped             UNSIGNED INT DEFAULT 0,            -- Omitidos (sin match)
    failed              UNSIGNED INT DEFAULT 0,             -- Fallidos
    dry_run             UNSIGNED INT DEFAULT 0,              -- Modo prueba
    error_summary       TEXT NULL,
    meta                JSON NULL,                          -- Metadata adicional
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP,
    
    INDEX (started_at),
    INDEX (status),
    INDEX (trigger)
);
```

### Tabla: `porth_cargos`

Almacena información de cargos/contenedores de Porth.

```sql
CREATE TABLE porth_cargos (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    shipping_document_id    BIGINT NOT NULL,
    porth_cargo_id          VARCHAR(255) NULL,
    type                    VARCHAR(255) NULL,
    number                  VARCHAR(255) NULL INDEX,
    seal                    VARCHAR(255) NULL,
    amount                  INT NULL,
    width                   DECIMAL(10,2) NULL,
    height                  DECIMAL(10,2) NULL,
    depth                   DECIMAL(10,2) NULL,
    weight                  DECIMAL(12,3) NULL,
    notes                   TEXT NULL,
    phase                   VARCHAR(255) NULL,
    created_at              TIMESTAMP,
    updated_at              TIMESTAMP,
    
    FOREIGN KEY (shipping_document_id) REFERENCES shipping_documents(id) ON DELETE CASCADE
);
```

### Tabla: `porth_phases`

Almacena información de fases del embarque.

```sql
CREATE TABLE porth_phases (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    shipping_document_id    BIGINT NOT NULL,
    porth_phase_id         VARCHAR(255) NULL,
    name                    VARCHAR(255) NULL INDEX,
    estimated_dates         JSON NULL,
    actual_date             TIMESTAMP NULL,
    created_at              TIMESTAMP,
    updated_at              TIMESTAMP,
    
    FOREIGN KEY (shipping_document_id) REFERENCES shipping_documents(id) ON DELETE CASCADE
);
```

### Tabla: `porth_itineraries`

Almacena información de itinerarios del embarque.

```sql
CREATE TABLE porth_itineraries (
    id                      BIGINT PRIMARY KEY AUTO_INCREMENT,
    shipping_document_id    BIGINT NOT NULL,
    porth_id               VARCHAR(255) NULL,
    porth_itinerary_id     VARCHAR(255) NULL,
    porth_cargo_id         VARCHAR(255) NULL,
    phase                   VARCHAR(255) NULL INDEX,
    name                    VARCHAR(255) NULL,
    place                   VARCHAR(255) NULL,
    vessel_voyage          VARCHAR(255) NULL,
    date                    TIMESTAMP NULL,
    created_at_porth       TIMESTAMP NULL,
    updated_at_porth        TIMESTAMP NULL,
    done                    BOOLEAN DEFAULT FALSE,
    raw                     JSON NULL,
    created_at              TIMESTAMP,
    updated_at              TIMESTAMP,
    
    FOREIGN KEY (shipping_document_id) REFERENCES shipping_documents(id) ON DELETE CASCADE
);
```

### Campos Agregados a `shipping_documents`

```sql
ALTER TABLE shipping_documents ADD COLUMN porth_id VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD INDEX (porth_id);

-- Campos de Porth
ALTER TABLE shipping_documents ADD COLUMN porth_shipment_number UNSIGNED BIGINT NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_carrier_code VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_pol VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_pod VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_pol_name VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_pod_name VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_phase VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_priority VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_modality VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN freight_type VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_vessel_voyage JSON NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_name VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_organization_id VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_origin VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_final_destination VARCHAR(255) NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_first_eta TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_first_etd TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_free_time_at_destination INT NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_manual_tracking BOOLEAN DEFAULT FALSE;
ALTER TABLE shipping_documents ADD COLUMN porth_tags JSON NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_ready TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_to_origin_port TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_at_origin_port TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_in_transit TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_at_destination_port TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_to_final_destination TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_delivered TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN last_porth_sync_at TIMESTAMP NULL;
ALTER TABLE shipping_documents ADD COLUMN porth_raw JSON NULL;
```

### Campos Agregados a `purchase_orders`

```sql
-- Campos de Porth propagados desde ShippingDocument
ALTER TABLE purchase_orders ADD COLUMN porth_pol VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_pol_name VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_pod VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_pod_name VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_origin VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_final_destination VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_carrier_code VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_vessel_voyage JSON NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_shipment_number VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_modality VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN freight_type VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_first_eta TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_first_etd TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_ready TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_to_origin_port TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_at_origin_port TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_in_transit TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_at_destination_port TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_to_final_destination TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_delivered TIMESTAMP NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_phase VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_priority VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_manual_tracking BOOLEAN DEFAULT FALSE;
ALTER TABLE purchase_orders ADD COLUMN porth_free_time_at_destination INT NULL;
ALTER TABLE purchase_orders ADD COLUMN porth_id VARCHAR(255) NULL;
ALTER TABLE purchase_orders ADD COLUMN last_porth_sync_at TIMESTAMP NULL;
```

## ⚙️ Configuración

### Variables de Entorno (.env)

```env
# API de Porth
PORTH_API_KEY=tu_api_key_aqui
PORTH_API_URL=https://api.porth.app
PORTH_AUTH_HEADER=apikey

# Configuración de sincronización
PORTH_SYNC_ENABLED=true
PORTH_MAX_RETRIES=5
PORTH_TIMEOUT=90

# Sincronización de embarques actualizados
PORTH_SYNC_DRY_RUN=false                    # true para modo prueba (no modifica datos)
PORTH_SYNC_LOOKBACK_HOURS=2                 # Horas hacia atrás para buscar actualizaciones

# Notificaciones
PORTH_SYNC_NOTIFICATION_USER_IDS=1,2,3       # IDs de usuarios a notificar (separados por coma)
```

### Configuración en `config/services.php`

```php
'porth' => [
    'api_key' => env('PORTH_API_KEY'),
    'api_url' => env('PORTH_API_URL', 'https://api.porth.app'),
    'auth_header' => env('PORTH_AUTH_HEADER', 'apikey'),
    'enabled' => env('PORTH_SYNC_ENABLED', true),
    'max_retries' => env('PORTH_MAX_RETRIES', 5),
    'timeout' => env('PORTH_TIMEOUT', 90),
    'sync_dry_run' => env('PORTH_SYNC_DRY_RUN', false),
    'sync_lookback_hours' => env('PORTH_SYNC_LOOKBACK_HOURS', 2),
    'notification_user_ids' => env('PORTH_SYNC_NOTIFICATION_USER_IDS', ''),
],
```

### Programación en `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule): void
{
    // Sincronizar embarques actualizados cada hora
    $schedule->command('porth:sync-recent --trigger=schedule')->hourly();
}
```

## 🔄 Flujo de Sincronización Detallado

### Paso 1: Obtener Lista de Embarques Actualizados

El comando `PorthSyncRecent` ejecuta `PorthSyncUpdatedService::syncRecent()` que:

1. Calcula el rango de tiempo (por defecto, últimas 2 horas)
2. Llama a `PorthApiService::listLastUpdated($startIso, $endIso)`
3. La API de Porth devuelve un array de IDs de embarques actualizados

**Endpoint de Porth:**
```
GET /api/v2/shipments/lastUpdated?startdate={ISO8601}&endDate={ISO8601}
Headers:
  apikey: {API_KEY}
```

**Respuesta:**
```json
[
  "shipment_id_1",
  "shipment_id_2",
  "shipment_id_3"
]
```

### Paso 2: Obtener Detalle de Cada Embarque

Para cada ID obtenido:

1. Se llama a `PorthApiService::getShipmentById($id)`
2. Se obtiene el detalle completo del embarque

**Endpoint de Porth:**
```
GET /api/shipment/byId/{id}
Headers:
  apikey: {API_KEY}
```

**Respuesta completa (estructura del payload):**

```json
{
  "id": "shipment_id_1",
  "shipmentNumber": 12345,
  "name": "Shipment Name",
  "organizationId": "org_123",
  "masterBl": "MBL123456",
  "houseBl": "HBL789012",
  "bookingNumber": "BK001",
  "freightType": "ocean",
  "modality": "FCL",
  "carrierCode": "MAEU",
  "pol": "USNYC",
  "pod": "MXVER",
  "polName": "New York",
  "podName": "Veracruz",
  "origin": "New York, USA",
  "finalDestination": "Mexico City, Mexico",
  "etd": "2025-01-15T10:00:00Z",
  "eta": "2025-02-20T14:00:00Z",
  "atd": "2025-01-16T08:00:00Z",
  "ata": "2025-02-21T12:00:00Z",
  "incoterm": "FOB",
  "phase": "in_transit",
  "priority": "high",
  "vesselVoyage": {
    "vessel": "MSC OSCAR",
    "voyage": "V001"
  },
  "firstEta": "2025-02-20T14:00:00Z",
  "firstEtd": "2025-01-15T10:00:00Z",
  "ready": "2025-01-14T00:00:00Z",
  "toOriginPort": "2025-01-14T12:00:00Z",
  "atOriginPort": "2025-01-15T08:00:00Z",
  "inTransit": "2025-01-16T10:00:00Z",
  "atDestinationPort": "2025-02-20T10:00:00Z",
  "toFinalDestination": "2025-02-21T08:00:00Z",
  "delivered": "2025-02-22T14:00:00Z",
  "freeTimeAtDestination": 7,
  "manualTracking": false,
  "tags": ["urgent", "fragile"],
  "cargo": [
    {
      "id": "cargo_1",
      "type": "container",
      "number": "CONT1234567",
      "name": "CONT1234567",
      "seal": "SEAL001",
      "amount": 1,
      "width": 2.4,
      "height": 2.6,
      "depth": 12.0,
      "weight": 15000.5,
      "notes": "Handle with care",
      "phase": "loaded"
    }
  ],
  "phases": [
    {
      "id": "phase_1",
      "name": "At Origin Port",
      "estimatedDates": [
        "2025-01-15T08:00:00Z",
        "2025-01-15T10:00:00Z"
      ],
      "actualDate": "2025-01-15T10:00:00Z"
    }
  ],
  "itinerary": [
    {
      "id": "itinerary_1",
      "shipmentCargoId": "cargo_1",
      "phase": "at_origin_port",
      "name": "Loaded at Origin",
      "place": "New York Port",
      "vesselVoyage": "MSC OSCAR V001",
      "date": "2025-01-15T10:00:00Z",
      "createdAt": "2025-01-15T10:05:00Z",
      "updatedAt": "2025-01-15T10:05:00Z",
      "done": true
    }
  ]
}
```

**Notas importantes sobre el payload:**
- `vesselVoyage` puede ser un objeto `{vessel: string, voyage: string}` o un string
- `cargo[].number` y `cargo[].name` son intercambiables (el helper usa `number ?? name`)
- `estimatedDates` en phases es un array de strings ISO8601
- `tags` es un array de strings
- Todas las fechas vienen en formato ISO8601
- Campos opcionales pueden estar ausentes o ser `null`

### Paso 3: Importar y Actualizar Datos

`PorthImportService::importShipment()` procesa el detalle:

1. **Busca Shipping Documents** que tengan el mismo `porth_id`
2. **Actualiza Shipping Document** con campos de Porth:
   - BL numbers (masterBl, houseBl, bookingNumber)
   - Fechas (etd, eta, atd, ata)
   - Información de puertos (pol, pod, polName, podName)
   - Campos específicos de Porth (phase, priority, modality, etc.)
   - Fechas de eventos (ready, toOriginPort, inTransit, etc.)
3. **Propaga a Purchase Orders** asociadas:
   - Fechas clave (etd, eta, atd, ata)
   - Incoterms
   - Freight type
   - Todos los campos de Porth
4. **Sincroniza Cargos** (contenedores):
   - Crea/actualiza registros en `porth_cargos`
   - Elimina cargos que ya no existen en Porth
5. **Sincroniza Fases**:
   - Crea/actualiza registros en `porth_phases`
6. **Sincroniza Itinerarios**:
   - Crea/actualiza registros en `porth_itineraries`

#### 🔄 Detalle de Propagación de Datos: ShippingDocument → PurchaseOrder

La propagación de datos desde `ShippingDocument` hacia las `PurchaseOrder` asociadas se realiza en **dos pasos secuenciales** dentro de `PorthImportService::importShipment()`:

**Paso 3.1: `syncPurchaseOrdersFromPayload()`**
- Propaga campos **directamente del payload de Porth** a las Purchase Orders
- Campos propagados:
  - `date_etd` (desde `etd` del payload)
  - `date_eta` (desde `eta` del payload)
  - `date_atd` (desde `atd` del payload)
  - `date_ata` (desde `ata` del payload)
  - `incoterms` (desde `incoterm` del payload)
  - `freight_type` (desde `freightType` del payload, solo si no está vacío)

**Paso 3.2: `syncPurchaseOrdersFromShippingDocument()`**
- Propaga **todos los campos de Porth** desde el `ShippingDocument` ya actualizado hacia las Purchase Orders
- Mapeo completo de 26 campos:
  - Puertos: `porth_pol`, `porth_pol_name`, `porth_pod`, `porth_pod_name`
  - Ubicaciones: `porth_origin`, `porth_final_destination`
  - Transporte: `porth_carrier_code`, `porth_vessel_voyage`, `porth_shipment_number`
  - Configuración: `porth_modality`, `freight_type`, `porth_phase`, `porth_priority`
  - Fechas de eventos: `porth_first_eta`, `porth_first_etd`, `porth_ready`, `porth_to_origin_port`, `porth_at_origin_port`, `porth_in_transit`, `porth_at_destination_port`, `porth_to_final_destination`, `porth_delivered`
  - Otros: `porth_manual_tracking`, `porth_free_time_at_destination`, `porth_id`, `last_porth_sync_at`

**Características importantes:**
- ✅ **Transaccional**: Todo se ejecuta dentro de `DB::transaction()`
- ✅ **Múltiples documentos**: Si hay varios `ShippingDocument` con el mismo `porth_id`, todos se actualizan
- ✅ **Manejo especial de `freight_type`**: Solo se actualiza si viene un valor válido (no vacío) de Porth
- ✅ **Actualización condicional**: Solo actualiza campos con valores no nulos
- ✅ **Relación**: Solo procesa documentos que tienen `PurchaseOrder` asociadas

### 📄 Visualización en Frontend: Shipping Document

El HTML completo de cómo se muestran los campos de Porth en la vista de Shipping Document (`resources/views/livewire/forms/pucharse-order-consolidate-detail.blade.php`):

#### Sección: Información del Embarque

```blade
<!-- Sección: Información del embarque -->
<div class="mb-8 p-6 bg-white rounded-lg border border-gray-200 shadow-sm">
    <h3 class="mb-6 text-lg font-bold text-[#7288FF]">Información del embarque</h3>
    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
        {{-- Campo ID Porth ocultado --}}
        {{-- <div>
            <p class="text-sm font-medium text-gray-500">ID Porth</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_id ?? '-' }}</p>
        </div> --}}
        
        <div>
            <p class="text-sm font-medium text-gray-500">Número de Embarque</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_shipment_number ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Nombre</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_name ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Puerto de Origen (POL)</p>
            <p class="text-base font-semibold text-gray-900">
                {{ $shippingDocument->porth_pol_name ?? $shippingDocument->porth_pol ?? '-' }}
                @if($shippingDocument->porth_pol && $shippingDocument->porth_pol_name)
                    <span class="text-gray-500 text-sm">({{ $shippingDocument->porth_pol }})</span>
                @endif
            </p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Puerto de Destino (POD)</p>
            <p class="text-base font-semibold text-gray-900">
                {{ $shippingDocument->porth_pod_name ?? $shippingDocument->porth_pod ?? '-' }}
                @if($shippingDocument->porth_pod && $shippingDocument->porth_pod_name)
                    <span class="text-gray-500 text-sm">({{ $shippingDocument->porth_pod }})</span>
                @endif
            </p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Origen</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_origin ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Destino Final</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_final_destination ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Código Transportista</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_carrier_code ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Modalidad</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_modality ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Buque/Vuelo</p>
            <p class="text-base font-semibold text-gray-900">
                @if($shippingDocument->porth_vessel_voyage)
                    @if(is_array($shippingDocument->porth_vessel_voyage))
                        {{ $shippingDocument->porth_vessel_voyage['vessel'] ?? '-' }}
                        @if(isset($shippingDocument->porth_vessel_voyage['voyage']))
                            <span class="text-gray-500 text-sm">({{ $shippingDocument->porth_vessel_voyage['voyage'] }})</span>
                        @endif
                    @else
                        {{ $shippingDocument->porth_vessel_voyage }}
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Fase</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_phase ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Prioridad</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_priority ?? '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Primera ETA</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_first_eta ? $shippingDocument->porth_first_eta->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Primera ETD</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_first_etd ? $shippingDocument->porth_first_etd->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Listo</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_ready ? $shippingDocument->porth_ready->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Hacia Puerto Origen</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_to_origin_port ? $shippingDocument->porth_to_origin_port->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">En Puerto Origen</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_at_origin_port ? $shippingDocument->porth_at_origin_port->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">En Tránsito</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_in_transit ? $shippingDocument->porth_in_transit->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">En Puerto Destino</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_at_destination_port ? $shippingDocument->porth_at_destination_port->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Hacia Destino Final</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_to_final_destination ? $shippingDocument->porth_to_final_destination->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500">Entregado</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_delivered ? $shippingDocument->porth_delivered->format('d/m/Y H:i') : '-' }}</p>
        </div>
        
        {{-- Campo ocultado: Tiempo Libre en Destino --}}
        {{-- <div>
            <p class="text-sm font-medium text-gray-500">Tiempo Libre en Destino</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->porth_free_time_at_destination ? $shippingDocument->porth_free_time_at_destination . ' días' : '-' }}</p>
        </div> --}}
        
        {{-- Campo ocultado: Tracking Manual --}}
        {{-- <div>
            <p class="text-sm font-medium text-gray-500">Tracking Manual</p>
            <p class="text-base font-semibold text-gray-900">
                @if($shippingDocument->porth_manual_tracking !== null)
                    {{ $shippingDocument->porth_manual_tracking ? 'Sí' : 'No' }}
                @else
                    -
                @endif
            </p>
        </div> --}}
        
        <div>
            <p class="text-sm font-medium text-gray-500">Última Sincronización</p>
            <p class="text-base font-semibold text-gray-900">{{ $shippingDocument->last_porth_sync_at ? $shippingDocument->last_porth_sync_at->format('d/m/Y H:i') : '-' }}</p>
        </div>
    </div>
</div>
```

#### Sección: Cargos/Contenedores

```blade
<!-- Sección: Cargos de Porth -->
@if($shippingDocument->porthCargos && $shippingDocument->porthCargos->isNotEmpty())
<div class="mb-8 p-6 bg-white rounded-lg border border-gray-200 shadow-sm">
    <h3 class="mb-6 text-lg font-bold text-[#7288FF]">Cargos/Contenedores</h3>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sello</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensiones</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peso</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fase</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($shippingDocument->porthCargos as $cargo)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->type ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->number ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->seal ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->amount ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($cargo->width && $cargo->height && $cargo->depth)
                            {{ number_format($cargo->width, 2) }} x {{ number_format($cargo->height, 2) }} x {{ number_format($cargo->depth, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->weight ? number_format($cargo->weight, 2) . ' kg' : '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cargo->phase ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
```

**Notas importantes:**
- ✅ Los campos están organizados en un grid responsivo (`grid-cols-2 md:grid-cols-3`)
- ✅ Los campos ocultos están comentados: `porth_id`, `porth_free_time_at_destination`, `porth_manual_tracking`
- ✅ Las fechas se formatean como `d/m/Y H:i` (día/mes/año hora:minuto)
- ✅ Los puertos muestran el nombre completo y el código entre paréntesis
- ✅ El campo `porth_vessel_voyage` puede ser un array o string
- ✅ La sección de Cargos solo se muestra si hay datos (`@if($shippingDocument->porthCargos->isNotEmpty())`)

### Paso 4: Registrar Corrida y Notificar

1. Se registra la corrida en `porth_sync_runs` con:
   - Estado (success, partial, failed)
   - Métricas (total, processed, updated, skipped, failed)
   - Duración
2. Se envían notificaciones a usuarios configurados sobre:
   - Embarques actualizados
   - Embarques sin coincidencia
   - Errores

## 📝 Implementación Paso a Paso

### 1. Ejecutar Migraciones

**IMPORTANTE: Ejecutar las migraciones en el orden correcto:**

```bash
php artisan migrate
```

**Orden de ejecución de migraciones (por timestamp):**

1. `2025_12_19_152651_add_porth_id_to_shipping_documents.php` - Agrega `porth_id` a shipping_documents
2. `2025_12_22_000001_create_porth_structures.php` - Crea tablas `porth_cargos`, `porth_phases`, `porth_itineraries`
3. `2025_12_22_000002_create_porth_sync_runs_table.php` - Crea tabla `porth_sync_runs`
4. `2025_12_22_000003_add_porth_fields_and_modality_to_documents.php` - Agrega campos Porth a shipping_documents
5. `2026_01_05_161346_add_porth_fields_to_purchase_orders_table.php` - Agrega campos Porth a purchase_orders
6. `2026_01_05_181836_remove_unique_constraint_from_porth_id_in_shipping_documents.php` - Remueve constraint único de porth_id

**Esto creará las tablas:**
- `porth_sync_runs` - Registro de corridas de sincronización
- `porth_cargos` - Cargos/contenedores de Porth
- `porth_phases` - Fases del embarque
- `porth_itineraries` - Itinerarios del embarque

**Y agregará campos a:**
- `shipping_documents` - ~30 campos nuevos de Porth
- `purchase_orders` - ~26 campos nuevos de Porth

### 2. Crear Modelos

**app/Models/PorthCargo.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_document_id',
        'porth_cargo_id',
        'type',
        'number',
        'seal',
        'amount',
        'width',
        'height',
        'depth',
        'weight',
        'notes',
        'phase',
    ];

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }
}
```

**app/Models/PorthPhase.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_document_id',
        'porth_phase_id',
        'name',
        'estimated_dates',
        'actual_date',
    ];

    protected $casts = [
        'estimated_dates' => 'array',
        'actual_date' => 'datetime',
    ];

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }
}
```

**app/Models/PorthItinerary.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthItinerary extends Model
{
    use HasFactory;

    protected $table = 'porth_itineraries';

    protected $fillable = [
        'shipping_document_id',
        'porth_id',
        'porth_itinerary_id',
        'porth_cargo_id',
        'phase',
        'name',
        'place',
        'vessel_voyage',
        'date',
        'created_at_porth',
        'updated_at_porth',
        'done',
        'raw',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at_porth' => 'datetime',
        'updated_at_porth' => 'datetime',
        'done' => 'boolean',
        'raw' => 'array',
    ];

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }
}
```

**app/Models/PorthSyncRun.php:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorthSyncRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger',
        'scope',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'total',
        'processed',
        'updated',
        'no_change',
        'skipped',
        'failed',
        'dry_run',
        'error_summary',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'meta' => 'array',
    ];
}
```

### 3. Crear Servicios

**app/Services/PorthApiService.php:**
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PorthApiService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $authHeader;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.porth.api_key', '');
        $this->apiUrl = rtrim(config('services.porth.api_url', 'https://api.porth.app'), '/');
        $this->authHeader = config('services.porth.auth_header', 'apikey');
        $this->timeout = (int) config('services.porth.timeout', 90);
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Lista shipments actualizados en un rango de fechas (ISO).
     */
    public function listLastUpdated(string $startIso, string $endIso): array
    {
        $data = [];

        if ($this->isEnabled()) {
            $endpoint = "{$this->apiUrl}/api/v2/shipments/lastUpdated";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint, [
                    'startdate' => $startIso,
                    'endDate' => $endIso,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (is_array($responseData)) {
                    $data = $responseData;
                }
            } else {
                Log::error('porth_api:lastUpdated_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } else {
            Log::warning('porth_api:disabled_missing_key');
        }

        return $data;
    }

    /**
     * Obtiene detalle por id.
     */
    public function getShipmentById(string $id): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        $endpoint = "{$this->apiUrl}/api/shipment/byId/{$id}";

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                $this->authHeader => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->get($endpoint);

        if ($response->failed()) {
            Log::error('porth_api:get_by_id_failed', [
                'id' => $id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }
}
```

**app/Services/PorthSyncUpdatedService.php:**
```php
<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PorthSyncUpdatedService
{
    public function __construct(
        protected PorthApiService $api,
        protected PorthImportService $importer
    ) {
    }

    /**
     * Sincroniza shipments actualizados en el rango dado.
     *
     * @return array Resumen con ids procesados y resultados por id.
     */
    public function syncRange(Carbon $start, Carbon $end, ?bool $dryRun = null): array
    {
        $dryRun = $dryRun ?? (bool) config('services.porth.sync_dry_run', false);
        $startIso = $start->toIso8601String();
        $endIso = $end->toIso8601String();

        $ids = $this->api->listLastUpdated($startIso, $endIso);

        Log::info('porth_sync_range:ids_fetched', [
            'start' => $startIso,
            'end' => $endIso,
            'dry_run' => $dryRun,
            'ids' => $ids,
        ]);

        if (!is_array($ids)) {
            Log::warning('porth_sync_range:unexpected_response', [
                'start' => $startIso,
                'end' => $endIso,
                'response_type' => gettype($ids),
            ]);
            return ['ids' => [], 'results' => []];
        }

        $results = [];

        foreach ($ids as $id) {
            if (!is_string($id)) {
                continue;
            }

            $detail = $this->api->getShipmentById($id);

            if (!$detail) {
                $results[$id] = [
                    'status' => 'not_found_or_failed',
                    'shipping_document_id' => null,
                    'purchase_order_ids' => [],
                    'porth_payload' => null,
                ];
                continue;
            }

            if ($dryRun) {
                $results[$id] = [
                    'status' => 'dry_run',
                    'shipping_document_id' => null,
                    'purchase_order_ids' => [],
                    'porth_payload' => $detail,
                ];
                continue;
            }

            $doc = $this->importer->importShipment($detail);
            $purchaseOrderIds = $doc
                ? $doc->purchaseOrders()->pluck('purchase_orders.id')->values()->all()
                : [];

            $results[$id] = [
                'status' => $doc ? 'imported' : 'skipped',
                'shipping_document_id' => $doc?->id,
                'purchase_order_ids' => $purchaseOrderIds,
                'porth_payload' => $detail,
            ];
        }

        return [
            'ids' => $ids,
            'results' => $results,
        ];
    }

    /**
     * Sincroniza usando horas de retroceso configurables en .env
     * (por defecto 2 horas).
     */
    public function syncRecent(?int $lookbackHours = null, ?bool $dryRun = null): array
    {
        $hours = $lookbackHours ?? (int) config('services.porth.sync_lookback_hours', 2);
        if ($hours < 1) {
            $hours = 1; // evita rangos vacíos
        }

        $end = Carbon::now();
        $start = $end->copy()->subHours($hours);

        return $this->syncRange($start, $end, $dryRun);
    }
}
```

**app/Services/PorthImportService.php:**
```php
<?php

namespace App\Services;

use App\Helpers\PorthImportHelper;
use App\Models\ShippingDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PorthImportService
{
    public function __construct(
        protected PorthImportHelper $helper
    ) {
    }

    /**
     * Importa un detalle de envío de Porth y lo guarda en las tablas locales.
     * Actualiza TODOS los documentos que tienen el mismo porth_id.
     */
    public function importShipment(array $data): ?ShippingDocument
    {
        return DB::transaction(function () use ($data) {
            $documents = $this->resolveShippingDocuments($data);

            if ($documents->isEmpty()) {
                Log::warning('porth_import:no_shipping_document_match', [
                    'porth_id' => $data['id'] ?? null,
                ]);
                return null;
            }

            $firstDocument = null;
            $updatedCount = 0;

            foreach ($documents as $document) {
                // Solo procesar documentos que tienen Purchase Orders asociadas
                if (!$document->purchaseOrders()->exists()) {
                    continue;
                }

                $this->updateShippingDocument($document, $data);
                $this->syncPurchaseOrdersFromPayload($document, $data);
                $this->syncPurchaseOrdersFromShippingDocument($document);
                $this->syncCargos($document, $data['cargo'] ?? []);
                $this->syncPhases($document, $data['phases'] ?? []);
                $this->syncItineraries($document, $data['itinerary'] ?? [], $data['id'] ?? null);

                if (!$firstDocument) {
                    $firstDocument = $document->fresh();
                }
                $updatedCount++;
            }

            return $firstDocument;
        });
    }

    protected function resolveShippingDocuments(array $data)
    {
        $porthId = $data['id'] ?? null;
        if (!$porthId) {
            return collect();
        }

        return ShippingDocument::where('porth_id', $porthId)->get();
    }

    protected function updateShippingDocument(ShippingDocument $document, array $data): void
    {
        $fieldsToUpdate = [];
        $fieldsToUpdate = array_merge($fieldsToUpdate, $this->helper->getBlFields($data));
        $fieldsToUpdate = array_merge($fieldsToUpdate, $this->helper->getContainerFields($data));
        $fieldsToUpdate = array_merge($fieldsToUpdate, $this->helper->getDateFields($data));
        $porthFields = $this->helper->getPorthFields($data);
        
        // Manejar freight_type: solo actualizar si viene un valor válido de Porth
        if (array_key_exists('freightType', $data) && !empty($data['freightType'])) {
            $normalized = strtoupper(trim($data['freightType']));
            if ($normalized) {
                $porthFields['freight_type'] = strtolower($normalized);
            }
        }
        
        $fieldsToUpdate = array_merge($fieldsToUpdate, $porthFields);

        $document->fill($fieldsToUpdate);
        $document->save();
    }

    protected function syncPurchaseOrdersFromPayload(ShippingDocument $document, array $data): void
    {
        $payloadValues = $this->helper->buildPurchaseOrderPayloadValues($data);
        $fieldsMap = $this->helper->getPurchaseOrderFieldsMap();

        $presentKeys = array_intersect(array_keys($fieldsMap), array_keys($data));
        $specialKeys = array_intersect(array_keys($fieldsMap), array_keys($payloadValues));
        $allKeys = array_unique(array_merge($presentKeys, $specialKeys));
        
        if (empty($allKeys)) {
            return;
        }

        $purchaseOrders = $document->purchaseOrders()->get();
        foreach ($purchaseOrders as $po) {
            $updates = [];
            foreach ($allKeys as $key) {
                $fieldName = $fieldsMap[$key];
                $value = $payloadValues[$key] ?? null;
                
                if ($fieldName === 'freight_type') {
                    if (!empty($value)) {
                        $updates[$fieldName] = $value;
                    }
                } elseif (!empty($value) || ($value !== null && $key !== 'freightTypeForFreightType')) {
                    $updates[$fieldName] = $value;
                }
            }

            if (!empty($updates)) {
                $po->fill($updates)->save();
            }
        }
    }

    protected function syncPurchaseOrdersFromShippingDocument(ShippingDocument $document): void
    {
        $purchaseOrders = $document->purchaseOrders()->get();
        
        if ($purchaseOrders->isEmpty()) {
            return;
        }

        $fieldMap = [
            'porth_pol' => 'porth_pol',
            'porth_pol_name' => 'porth_pol_name',
            'porth_pod' => 'porth_pod',
            'porth_pod_name' => 'porth_pod_name',
            'porth_origin' => 'porth_origin',
            'porth_final_destination' => 'porth_final_destination',
            'porth_carrier_code' => 'porth_carrier_code',
            'porth_vessel_voyage' => 'porth_vessel_voyage',
            'porth_shipment_number' => 'porth_shipment_number',
            'porth_modality' => 'porth_modality',
            'freight_type' => 'freight_type',
            'porth_first_eta' => 'porth_first_eta',
            'porth_first_etd' => 'porth_first_etd',
            'porth_ready' => 'porth_ready',
            'porth_to_origin_port' => 'porth_to_origin_port',
            'porth_at_origin_port' => 'porth_at_origin_port',
            'porth_in_transit' => 'porth_in_transit',
            'porth_at_destination_port' => 'porth_at_destination_port',
            'porth_to_final_destination' => 'porth_to_final_destination',
            'porth_delivered' => 'porth_delivered',
            'porth_phase' => 'porth_phase',
            'porth_priority' => 'porth_priority',
            'porth_manual_tracking' => 'porth_manual_tracking',
            'porth_free_time_at_destination' => 'porth_free_time_at_destination',
            'porth_id' => 'porth_id',
            'last_porth_sync_at' => 'last_porth_sync_at',
        ];

        foreach ($purchaseOrders as $po) {
            $updates = [];
            
            foreach ($fieldMap as $shippingDocField => $poField) {
                $value = $document->{$shippingDocField};
                if ($poField === 'freight_type') {
                    if (!empty($value)) {
                        $updates[$poField] = $value;
                    }
                } elseif ($value !== null) {
                    $updates[$poField] = $value;
                }
            }

            if (!empty($updates)) {
                $po->fill($updates)->save();
            }
        }
    }

    protected function syncCargos(ShippingDocument $document, array $cargos): void
    {
        $this->syncRelatedRecords(
            $document,
            $cargos,
            'porthCargos',
            'porth_cargo_id',
            fn (array $cargo) => $this->helper->buildCargoPayload($cargo)
        );
    }

    protected function syncPhases(ShippingDocument $document, array $phases): void
    {
        $this->syncRelatedRecords(
            $document,
            $phases,
            'porthPhases',
            'porth_phase_id',
            fn (array $phase) => $this->helper->buildPhasePayload($phase)
        );
    }

    protected function syncItineraries(ShippingDocument $document, array $itineraries, ?string $porthId): void
    {
        $this->syncRelatedRecords(
            $document,
            $itineraries,
            'porthItineraries',
            'porth_itinerary_id',
            fn (array $item) => $this->helper->buildItineraryPayload($item, $porthId)
        );
    }

    protected function syncRelatedRecords(
        ShippingDocument $document,
        array $items,
        string $relation,
        string $idKey,
        callable $payloadBuilder
    ): void {
        $incomingIds = [];
        $nullPayloads = [];

        foreach ($items as $item) {
            $payload = $payloadBuilder($item);
            $porthId = $payload[$idKey] ?? null;

            if ($porthId) {
                $incomingIds[] = $porthId;
                $document->{$relation}()->updateOrCreate(
                    [$idKey => $porthId],
                    $payload
                );
            } else {
                $nullPayloads[] = $payload;
            }
        }

        $query = $document->{$relation}()->whereNotNull($idKey);
        if (!empty($incomingIds)) {
            $query->whereNotIn($idKey, $incomingIds);
        }
        $query->delete();

        $document->{$relation}()->whereNull($idKey)->delete();
        foreach ($nullPayloads as $payload) {
            $document->{$relation}()->create($payload);
        }
    }
}
```

### 4. Crear Helper

**app/Helpers/PorthImportHelper.php:**

Este helper contiene métodos para mapeo y transformación de datos. **Implementación completa:**

```php
<?php

namespace App\Helpers;

use Carbon\Carbon;

class PorthImportHelper
{
    /**
     * Extrae campos de BL (masterBl, houseBl, bookingNumber)
     */
    public function getBlFields(array $data): array
    {
        $fields = [];
        $mapping = [
            'masterBl' => 'mbl_number',
            'houseBl' => 'hbl_number',
            'bookingNumber' => 'booking_code',
        ];

        foreach ($mapping as $payloadKey => $field) {
            if (array_key_exists($payloadKey, $data) && !empty($data[$payloadKey])) {
                $normalized = $this->normalize($data[$payloadKey]);
                if ($normalized) {
                    $fields[$field] = $normalized;
                }
            }
        }

        return $fields;
    }

    /**
     * Extrae números de contenedor del array cargo
     */
    public function getContainerFields(array $data): array
    {
        $fields = [];

        if (array_key_exists('cargo', $data) && !empty($data['cargo'])) {
            $containers = $this->extractContainerNumbers($data);
            if (!empty($containers) && !empty($containers[0])) {
                $fields['container_number'] = $containers[0];
            }
        }

        return $fields;
    }

    /**
     * Extrae y parsea fechas (etd, eta, atd, ata)
     */
    public function getDateFields(array $data): array
    {
        $fields = [];
        $mapping = [
            'etd' => 'estimated_departure_date',
            'eta' => 'estimated_arrival_date',
            'atd' => 'actual_departure_date',
            'ata' => 'actual_arrival_date',
        ];

        foreach ($mapping as $payloadKey => $field) {
            if (array_key_exists($payloadKey, $data) && !empty($data[$payloadKey])) {
                $parsed = $this->parseDate($data[$payloadKey]);
                if ($parsed) {
                    $fields[$field] = $parsed;
                }
            }
        }

        return $fields;
    }

    /**
     * Extrae todos los campos específicos de Porth
     * Mapea camelCase de Porth a snake_case de base de datos
     */
    public function getPorthFields(array $data): array
    {
        $fields = [
            'porth_id' => $data['id'] ?? null,
            'porth_shipment_number' => $data['shipmentNumber'] ?? ($data['porthShipmentNumber'] ?? null),
            'porth_carrier_code' => $data['carrierCode'] ?? null,
            'porth_pol' => $this->normalize($data['pol'] ?? null),
            'porth_pod' => $this->normalize($data['pod'] ?? null),
            'porth_pol_name' => $data['polName'] ?? null,
            'porth_pod_name' => $data['podName'] ?? null,
            'porth_phase' => $data['phase'] ?? null,
            'porth_priority' => $data['priority'] ?? null,
            'porth_modality' => $data['modality'] ?? null,
            'porth_vessel_voyage' => $data['vesselVoyage'] ?? null,
            'porth_name' => $data['name'] ?? null,
            'porth_organization_id' => $data['organizationId'] ?? null,
            'porth_origin' => $data['origin'] ?? null,
            'porth_final_destination' => $data['finalDestination'] ?? null,
            'porth_first_eta' => $this->parseDateTime($data['firstEta'] ?? null),
            'porth_first_etd' => $this->parseDateTime($data['firstEtd'] ?? null),
            'porth_free_time_at_destination' => $data['freeTimeAtDestination'] ?? null,
            'porth_ready' => $this->parseDateTime($data['ready'] ?? null),
            'porth_to_origin_port' => $this->parseDateTime($data['toOriginPort'] ?? null),
            'porth_at_origin_port' => $this->parseDateTime($data['atOriginPort'] ?? null),
            'porth_in_transit' => $this->parseDateTime($data['inTransit'] ?? null),
            'porth_at_destination_port' => $this->parseDateTime($data['atDestinationPort'] ?? null),
            'porth_to_final_destination' => $this->parseDateTime($data['toFinalDestination'] ?? null),
            'porth_delivered' => $this->parseDateTime($data['delivered'] ?? null),
            'last_porth_sync_at' => now(),
            'porth_raw' => $data,
        ];

        // manualTracking solo se actualiza si existe la key en el payload
        if (array_key_exists('manualTracking', $data)) {
            $fields['porth_manual_tracking'] = (bool) $data['manualTracking'];
        }
        
        // tags solo se actualiza si existe la key en el payload
        if (array_key_exists('tags', $data)) {
            $fields['porth_tags'] = $data['tags'];
        }

        return $fields;
    }

    /**
     * Construye valores para Purchase Orders desde el payload de Porth
     */
    public function buildPurchaseOrderPayloadValues(array $data): array
    {
        $payloadValues = [];
        $transformers = [
            'etd' => [$this, 'parseDateTime'],
            'atd' => [$this, 'parseDateTime'],
            'eta' => [$this, 'parseDateTime'],
            'ata' => [$this, 'parseDateTime'],
            'incoterm' => [$this, 'normalize'],
        ];

        foreach ($transformers as $key => $transform) {
            $payloadValues[$key] = $this->payloadValue($data, $key, $transform);
        }
        
        // freightType solo si viene un valor válido
        if (array_key_exists('freightType', $data) && !empty($data['freightType'])) {
            $normalized = $this->normalize($data['freightType']);
            if ($normalized) {
                $payloadValues['freightType'] = $normalized;
            }
        }

        return $payloadValues;
    }

    /**
     * Mapeo de campos de Porth a campos de PurchaseOrder
     */
    public function getPurchaseOrderFieldsMap(): array
    {
        return [
            'etd' => 'date_etd',
            'atd' => 'date_atd',
            'eta' => 'date_eta',
            'ata' => 'date_ata',
            'incoterm' => 'incoterms',
            'freightType' => 'freight_type',
        ];
    }

    /**
     * Construye payload para cargos
     */
    public function buildCargoPayload(array $cargo): array
    {
        return [
            'porth_cargo_id' => $cargo['id'] ?? null,
            'type' => $cargo['type'] ?? null,
            'number' => $cargo['number'] ?? ($cargo['name'] ?? null),
            'seal' => $cargo['seal'] ?? null,
            'amount' => $cargo['amount'] ?? null,
            'width' => $this->toDecimal($cargo['width'] ?? null),
            'height' => $this->toDecimal($cargo['height'] ?? null),
            'depth' => $this->toDecimal($cargo['depth'] ?? null),
            'weight' => $this->toDecimal($cargo['weight'] ?? null),
            'notes' => $cargo['notes'] ?? null,
            'phase' => $cargo['phase'] ?? null,
        ];
    }

    /**
     * Construye payload para fases
     */
    public function buildPhasePayload(array $phase): array
    {
        return [
            'porth_phase_id' => $phase['id'] ?? null,
            'name' => $phase['name'] ?? null,
            'estimated_dates' => $phase['estimatedDates'] ?? [],
            'actual_date' => $this->parseDateTime($phase['actualDate'] ?? null),
        ];
    }

    /**
     * Construye payload para itinerarios
     */
    public function buildItineraryPayload(array $item, ?string $porthId): array
    {
        return [
            'porth_id' => $porthId,
            'porth_itinerary_id' => $item['id'] ?? null,
            'porth_cargo_id' => $item['shipmentCargoId'] ?? null,
            'phase' => $item['phase'] ?? null,
            'name' => $item['name'] ?? null,
            'place' => $item['place'] ?? null,
            'vessel_voyage' => $item['vesselVoyage'] ?? null,
            'date' => $this->parseDateTime($item['date'] ?? null),
            'created_at_porth' => $this->parseDateTime($item['createdAt'] ?? null),
            'updated_at_porth' => $this->parseDateTime($item['updatedAt'] ?? null),
            'done' => $item['done'] ?? false,
            'raw' => $item,
        ];
    }

    // ========== MÉTODOS AUXILIARES PRIVADOS ==========

    /**
     * Normaliza strings: trim + uppercase
     */
    private function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        return strtoupper(trim($value));
    }

    /**
     * Extrae números de contenedor del array cargo
     */
    private function extractContainerNumbers(array $data): array
    {
        $cargoList = $data['cargo'] ?? [];
        if (empty($cargoList)) {
            return [];
        }

        $numbers = [];
        foreach ($cargoList as $cargo) {
            $number = $cargo['number'] ?? ($cargo['name'] ?? null);
            $normalized = $this->normalize($number);
            if ($normalized) {
                $numbers[] = $normalized;
            }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * Parsea fecha a formato date (string)
     */
    private function parseDate(?string $value): ?string
    {
        return !empty($value) ? Carbon::parse($value)->toDateString() : null;
    }

    /**
     * Parsea fecha a Carbon datetime
     */
    private function parseDateTime(?string $value): ?Carbon
    {
        return !empty($value) ? Carbon::parse($value) : null;
    }

    /**
     * Helper para transformar valores del payload
     */
    private function payloadValue(array $data, string $key, callable $transform)
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        return $transform($data[$key] ?? null);
    }

    /**
     * Convierte valores a float (decimal)
     */
    private function toDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        return null;
    }
}
```

### 5. Crear Comando

**app/Console/Commands/PorthSyncRecent.php:**

Este comando orquesta la sincronización completa. **Implementación completa:**

```php
<?php

namespace App\Console\Commands;

use App\Models\PorthSyncRun;
use App\Services\NotificationService;
use App\Services\PorthSyncUpdatedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PorthSyncRecent extends Command
{
    protected $signature = 'porth:sync-recent {--hours=} {--dry-run} {--trigger=manual}';
    protected $description = 'Sincroniza shipments recientes desde Porth, registra el run y notifica el detalle.';

    public function __construct(
        protected PorthSyncUpdatedService $syncService,
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = $this->option('hours');
        $hours = $hours !== null ? max(1, (int) $hours) : (int) config('services.porth.sync_lookback_hours', 2);
        $dryRun = (bool) ($this->option('dry-run') ?? config('services.porth.sync_dry_run', false));
        $trigger = $this->option('trigger') ?: 'manual';

        $this->info("Iniciando sync Porth (hours={$hours}, dryRun=" . ($dryRun ? 'true' : 'false') . ", trigger={$trigger})");
        $startedAt = now();

        $run = PorthSyncRun::create([
            'trigger' => $trigger,
            'scope' => "recent:{$hours}h",
            'status' => 'running',
            'started_at' => $startedAt,
            'meta' => [
                'hours' => $hours,
                'dry_run' => $dryRun,
            ],
        ]);

        try {
            $summary = $this->syncService->syncRecent($hours, $dryRun);
            $ids = $summary['ids'] ?? [];
            $results = $summary['results'] ?? [];

            $counts = [
                'total' => is_countable($ids) ? count($ids) : 0,
                'processed' => is_countable($results) ? count($results) : 0,
                'updated' => 0,
                'no_change' => 0,
                'skipped' => 0,
                'failed' => 0,
                'dry_run' => 0,
            ];

            foreach ($results as $result) {
                $status = $result['status'] ?? 'unknown';
                if ($status === 'imported') {
                    $counts['updated']++;
                } elseif ($status === 'dry_run') {
                    $counts['dry_run']++;
                } elseif ($status === 'skipped') {
                    $counts['skipped']++;
                } elseif ($status === 'no_change') {
                    $counts['no_change']++;
                } else {
                    $counts['failed']++;
                }
            }

            $status = 'success';
            if ($counts['failed'] > 0) {
                $status = $counts['updated'] > 0 ? 'partial' : 'failed';
            }

            $run->fill(array_merge($counts, [
                'status' => $status,
                'finished_at' => now(),
                'duration_ms' => abs((int) now()->diffInMilliseconds($startedAt, false)),
            ]))->save();

            Log::info('porth_sync_run_completed', [
                'run_id' => $run->id,
                'status' => $status,
                'counts' => $counts,
            ]);

            $this->sendNotifications($run, $results);
            $this->info("Sync Porth finalizado. Run #{$run->id} status={$status}");
        } catch (\Throwable $e) {
            $run->fill([
                'status' => 'failed',
                'error_summary' => $e->getMessage(),
                'finished_at' => now(),
                'duration_ms' => abs((int) now()->diffInMilliseconds($startedAt, false)),
            ])->save();

            Log::error('porth_sync_run_failed', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Sync Porth falló. Run #{$run->id} error={$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Envía notificaciones a usuarios configurados
     */
    private function sendNotifications(PorthSyncRun $run, array $results): void
    {
        $userIds = $this->notificationUserIds();
        if (empty($userIds)) {
            $this->info('Sin usuarios configurados para notificar (PORTH_SYNC_NOTIFICATION_USER_IDS vacío).');
            return;
        }

        foreach ($results as $porthId => $result) {
            $data = $this->buildNotificationData($run, $porthId, $result);
            $title = 'Sync Porth';
            $message = $this->buildNotificationMessage($data);

            $this->notificationService->notifyUsers(
                $userIds,
                'porth_sync',
                $title,
                $message,
                $data
            );
        }
    }

    /**
     * Construye datos de notificación
     */
    private function buildNotificationData(PorthSyncRun $run, $porthId, array $result): array
    {
        $status = $result['status'] ?? 'unknown';
        $shippingDocumentId = $result['shipping_document_id'] ?? null;
        $purchaseOrderIds = $result['purchase_order_ids'] ?? [];
        $updated = $status === 'imported';

        return [
            'schema_version' => 1,
            'porth_sync_runs_id' => $run->id,
            'shipping_document_id' => $shippingDocumentId,
            'purchase_order_ids' => $purchaseOrderIds,
            'porth_id' => is_string($porthId) ? $porthId : ($result['porth_id'] ?? null),
            'porth_payload' => $result['porth_payload'] ?? null,
            'result' => [
                'action' => $updated ? 'update' : 'none',
                'status' => $status,
                'updated' => $updated,
                'changed_fields' => $result['changed_fields'] ?? null,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Construye mensaje de notificación
     */
    private function buildNotificationMessage(array $data): string
    {
        $status = $data['result']['status'] ?? 'unknown';
        $runId = $data['porth_sync_runs_id'] ?? null;
        $shippingDocumentId = $data['shipping_document_id'] ?? null;
        $porthId = $data['porth_id'] ?? 'N/A';
        $purchaseOrderIds = $data['purchase_order_ids'] ?? [];

        $statusText = match ($status) {
            'imported' => 'fue actualizado',
            'skipped' => 'no tuvo coincidencia en Raga',
            'dry_run' => 'se reviso en modo prueba',
            'no_change' => 'no tuvo cambios',
            'failed' => 'fallo al procesar',
            default => "tiene estado {$status}",
        };

        $parts = ["El embarque {$porthId}"];

        if (is_array($purchaseOrderIds) && !empty($purchaseOrderIds)) {
            $poLabel = count($purchaseOrderIds) === 1 ? 'la orden de compra' : 'las ordenes de compra';
            $parts[] = "con {$poLabel} " . implode(', ', $purchaseOrderIds);
        }

        if ($shippingDocumentId) {
            $parts[] = "y shipping document id {$shippingDocumentId}";
        }

        return implode(' ', $parts) . " {$statusText}.";
    }

    /**
     * Obtiene IDs de usuarios a notificar desde configuración
     */
    private function notificationUserIds(): array
    {
        $configured = config('services.porth.notification_user_ids');

        if (is_string($configured)) {
            $configured = array_filter(array_map('trim', explode(',', $configured)));
        }

        if (!is_array($configured)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $configured)));
    }
}
```

### 6. Actualizar Modelos Existentes

**app/Models/ShippingDocument.php:**
Agregar relaciones:

```php
public function porthCargos(): HasMany
{
    return $this->hasMany(PorthCargo::class);
}

public function porthPhases(): HasMany
{
    return $this->hasMany(PorthPhase::class);
}

public function porthItineraries(): HasMany
{
    return $this->hasMany(PorthItinerary::class);
}
```

Agregar campos al `$fillable` y `$casts` según la migración.

**Campos a agregar a `ShippingDocument::$fillable`:**

```php
protected $fillable = [
    // ... campos existentes ...
    'porth_shipment_number',
    'porth_carrier_code',
    'porth_pol',
    'porth_pod',
    'porth_pol_name',
    'porth_pod_name',
    'porth_phase',
    'porth_priority',
    'porth_modality',
    'freight_type',
    'porth_vessel_voyage',
    'porth_raw',
    'porth_id',
    'porth_name',
    'porth_organization_id',
    'porth_origin',
    'porth_final_destination',
    'porth_first_eta',
    'porth_first_etd',
    'porth_free_time_at_destination',
    'porth_manual_tracking',
    'porth_tags',
    'porth_ready',
    'porth_to_origin_port',
    'porth_at_origin_port',
    'porth_in_transit',
    'porth_at_destination_port',
    'porth_to_final_destination',
    'porth_delivered',
    'last_porth_sync_at',
];
```

**Campos a agregar a `ShippingDocument::$casts`:**

```php
protected $casts = [
    // ... casts existentes ...
    'porth_vessel_voyage' => 'array',
    'porth_raw' => 'array',
    'porth_tags' => 'array',
    'porth_manual_tracking' => 'boolean',
    'porth_first_eta' => 'datetime',
    'porth_first_etd' => 'datetime',
    'porth_ready' => 'datetime',
    'porth_to_origin_port' => 'datetime',
    'porth_at_origin_port' => 'datetime',
    'porth_in_transit' => 'datetime',
    'porth_at_destination_port' => 'datetime',
    'porth_to_final_destination' => 'datetime',
    'porth_delivered' => 'datetime',
    'last_porth_sync_at' => 'datetime',
];
```

**app/Models/PurchaseOrder.php:**

**Campos a agregar a `PurchaseOrder::$fillable`:**

```php
protected $fillable = [
    // ... campos existentes ...
    'porth_pol',
    'porth_pol_name',
    'porth_pod',
    'porth_pod_name',
    'porth_origin',
    'porth_final_destination',
    'porth_carrier_code',
    'porth_vessel_voyage',
    'porth_shipment_number',
    'porth_modality',
    'porth_first_eta',
    'porth_first_etd',
    'porth_ready',
    'porth_to_origin_port',
    'porth_at_origin_port',
    'porth_in_transit',
    'porth_at_destination_port',
    'porth_to_final_destination',
    'porth_delivered',
    'porth_phase',
    'porth_priority',
    'porth_manual_tracking',
    'porth_free_time_at_destination',
    'porth_id',
    'last_porth_sync_at',
];
```

**Campos a agregar a `PurchaseOrder::$casts`:**

```php
protected $casts = [
    // ... casts existentes ...
    'porth_vessel_voyage' => 'array',
    'porth_manual_tracking' => 'boolean',
    'porth_first_eta' => 'datetime',
    'porth_first_etd' => 'datetime',
    'porth_ready' => 'datetime',
    'porth_to_origin_port' => 'datetime',
    'porth_at_origin_port' => 'datetime',
    'porth_in_transit' => 'datetime',
    'porth_at_destination_port' => 'datetime',
    'porth_to_final_destination' => 'datetime',
    'porth_delivered' => 'datetime',
    'last_porth_sync_at' => 'datetime',
];
```

## 🚀 Uso

### Sincronización Manual

```bash
# Sincronizar embarques de las últimas 2 horas (configuración por defecto)
php artisan porth:sync-recent

# Sincronizar embarques de las últimas 6 horas
php artisan porth:sync-recent --hours=6

# Modo dry-run (no modifica datos, solo muestra qué se haría)
php artisan porth:sync-recent --dry-run

# Especificar trigger
php artisan porth:sync-recent --trigger=manual
```

### Sincronización Automática

La sincronización se ejecuta automáticamente cada hora mediante el scheduler de Laravel (configurado en `app/Console/Kernel.php`).

### Verificar Estado de Sincronizaciones

```php
use App\Models\PorthSyncRun;

// Última corrida
$lastRun = PorthSyncRun::latest('started_at')->first();

// Corridas exitosas
$successfulRuns = PorthSyncRun::where('status', 'success')->get();

// Estadísticas
$stats = PorthSyncRun::selectRaw('
    COUNT(*) as total_runs,
    SUM(updated) as total_updated,
    SUM(skipped) as total_skipped,
    SUM(failed) as total_failed
')->first();
```

## 🔍 Debugging y Logs

### Logs Importantes

La integración registra logs detallados:

```
# Lista de IDs obtenidos
porth_sync_range:ids_fetched

# Detalle de cada embarque antes de importar
porth_sync_range:detail_preview

# Embarques sin coincidencia
porth_import:no_shipping_document_match

# Embarques importados exitosamente
porth_import:shipment_imported

# Errores de API
porth_api:lastUpdated_failed
porth_api:get_by_id_failed

# Corridas completadas
porth_sync_run_completed
porth_sync_run_failed
```

### Ver Logs

```bash
# Ver logs en tiempo real
php artisan pail

# Filtrar logs de Porth
php artisan pail --filter=porth
```

## ⚠️ Consideraciones Importantes

### 1. Matching de Embarques

La sincronización busca `ShippingDocument` que tengan el mismo `porth_id`. Es importante que:

- Los documentos tengan el `porth_id` asignado previamente (mediante `PorthSyncService` o manualmente)
- Solo se actualizan documentos que tienen `PurchaseOrder` asociadas

### 2. Modo Dry-Run

Siempre probar primero con `--dry-run` para verificar qué se actualizaría sin modificar datos.

### 3. Notificaciones

Las notificaciones se envían a los usuarios configurados en `PORTH_SYNC_NOTIFICATION_USER_IDS`. Si está vacío, no se envían notificaciones.

### 4. Performance

- La sincronización procesa embarques secuencialmente
- Para grandes volúmenes, considerar implementar procesamiento en cola
- El timeout por defecto es 90 segundos por request

### 5. Campos Sensibles

Algunos campos como `freight_type` solo se actualizan si vienen valores válidos de Porth, para evitar sobrescribir datos locales importantes.

## 🚨 Problemas Comunes y Soluciones

### 1. Timeout en Llamadas HTTP

**Problema:**
- Llamadas HTTP sin timeout explícito pueden causar timeouts indefinidos
- Especialmente en componentes Livewire que ejecutan llamadas en `mount()`

**Solución implementada:**
- `PorthApiService` usa `Http::timeout(90)` en todas las llamadas
- Configurable mediante `PORTH_TIMEOUT` en `.env`

**Verificación:**
```php
// ✅ CORRECTO (PorthApiService)
$response = Http::timeout($this->timeout)->get($endpoint);

// ❌ INCORRECTO (evitar en otros servicios)
$response = Http::get($endpoint); // Sin timeout explícito
```

**Recomendación para otros servicios:**
- Agregar `Http::timeout(10)` a todas las llamadas HTTP en `TrackingService` y otros servicios
- Considerar usar Jobs/Queues para llamadas asíncronas en lugar de ejecutarlas en `mount()` de Livewire

### 2. Campos No Se Propagaban a Purchase Orders

**Problema:**
- Los campos de Porth no se actualizaban en las Purchase Orders asociadas

**Solución implementada:**
- Doble propagación: primero desde payload, luego desde ShippingDocument
- Mapeo explícito de 26 campos en `syncPurchaseOrdersFromShippingDocument()`
- Verificar que el ShippingDocument tenga Purchase Orders asociadas antes de propagar

**Verificación:**
```bash
# Verificar que los campos se propagaron correctamente
php artisan tinker
>>> $sd = ShippingDocument::whereNotNull('porth_id')->first();
>>> $sd->purchaseOrders()->first()->porth_pol; // Debe tener valor
```

### 3. Múltiples ShippingDocuments con Mismo porth_id

**Problema:**
- Varios ShippingDocuments pueden tener el mismo `porth_id` (constraint único removido)

**Solución implementada:**
- `resolveShippingDocuments()` busca TODOS los documentos con el mismo `porth_id`
- Todos se actualizan en el mismo proceso de sincronización
- Cada uno propaga a sus propias Purchase Orders asociadas

**Verificación:**
```sql
-- Verificar documentos con mismo porth_id
SELECT porth_id, COUNT(*) as count 
FROM shipping_documents 
WHERE porth_id IS NOT NULL 
GROUP BY porth_id 
HAVING count > 1;
```

### 4. Migraciones No Ejecutadas

**Problema:**
- Campos faltantes en base de datos causan errores al sincronizar

**Solución:**
- Verificar que todas las migraciones estén ejecutadas antes del despliegue
- Verificar que los campos existan en las tablas

**Verificación:**
```bash
# Verificar estado de migraciones
php artisan migrate:status

# Verificar campos en tabla
php artisan tinker
>>> Schema::hasColumn('shipping_documents', 'porth_id'); // true
>>> Schema::hasColumn('purchase_orders', 'porth_pol'); // true
```

### 5. Configuración de .env Incorrecta

**Problema:**
- Variables de entorno faltantes o incorrectas causan fallos silenciosos

**Solución:**
- Verificar todas las variables requeridas antes del despliegue
- Usar valores por defecto en `config/services.php` cuando sea apropiado

**Verificación:**
```bash
# Verificar variables de entorno
php artisan tinker
>>> config('services.porth.api_key'); // No debe estar vacío
>>> config('services.porth.timeout'); // Debe ser 90
>>> config('services.porth.api_url'); // Debe ser https://api.porth.app
```

### 6. Scheduler No Configurado

**Problema:**
- La sincronización automática no se ejecuta si el scheduler no está configurado

**Solución:**
- Verificar que `app/Console/Kernel.php` tenga el schedule configurado
- Verificar que el cron esté ejecutando `php artisan schedule:run`

**Verificación:**
```bash
# Verificar schedule configurado
php artisan schedule:list | grep porth

# Verificar que el cron esté activo (en servidor)
crontab -l | grep schedule
```

## 📊 Monitoreo

### Métricas de Corridas

Cada corrida registra:
- `total`: Total de IDs encontrados
- `processed`: Total procesados
- `updated`: Actualizados exitosamente
- `no_change`: Sin cambios detectados
- `skipped`: Omitidos (sin match en Raga)
- `failed`: Fallidos
- `duration_ms`: Duración en milisegundos

### Consultas Útiles

```sql
-- Últimas 10 corridas
SELECT * FROM porth_sync_runs 
ORDER BY started_at DESC 
LIMIT 10;

-- Estadísticas por día
SELECT 
    DATE(started_at) as date,
    COUNT(*) as runs,
    SUM(updated) as total_updated,
    SUM(skipped) as total_skipped,
    SUM(failed) as total_failed
FROM porth_sync_runs
WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(started_at)
ORDER BY date DESC;

-- Corridas con errores
SELECT * FROM porth_sync_runs
WHERE status = 'failed'
ORDER BY started_at DESC;
```

## 🔗 Relación con Otras Integraciones

Esta integración trabaja junto con:

1. **PorthSyncService**: Sincronización inicial que busca/crea embarques en Porth y asigna `porth_id`
2. **PorthSyncJob**: Job asíncrono para sincronización individual de documentos
3. **NotificationService**: Sistema de notificaciones para usuarios

### Relación con PorthSyncService

**PorthSyncService** se usa para la **sincronización inicial** (asignar `porth_id`):

- **Cuándo usar**: Cuando un `ShippingDocument` se crea o actualiza y necesita buscar/crear el shipment en Porth
- **Qué hace**: 
  1. Busca el shipment en Porth usando identificadores (MBL, HBL, bookingNumber, containerNumber)
  2. Si no existe, lo crea en Porth
  3. Asigna el `porth_id` al `ShippingDocument`
  4. Opcionalmente importa los datos completos usando `PorthImportService`

**PorthSyncRecent** se usa para **actualizaciones periódicas**:

- **Cuándo usar**: Cada hora (automático) o manualmente para traer actualizaciones desde Porth
- **Qué hace**:
  1. Obtiene lista de embarques actualizados en Porth
  2. Para cada uno, obtiene el detalle completo
  3. Actualiza los `ShippingDocument` que tienen el mismo `porth_id`
  4. Propaga datos a `PurchaseOrder` asociadas

**Flujo completo:**

```
1. Usuario crea ShippingDocument
   ↓
2. PorthSyncService.processSync() busca/crea en Porth
   ↓
3. Se asigna porth_id al ShippingDocument
   ↓
4. PorthImportService.importShipment() trae datos iniciales
   ↓
5. Cada hora, PorthSyncRecent sincroniza actualizaciones
   ↓
6. Los datos se propagan automáticamente a PurchaseOrders
```

## 🛡️ Manejo de Errores de API

### Códigos HTTP y Manejo

**PorthApiService** maneja los siguientes casos:

```php
// En listLastUpdated() y getShipmentById()
if ($response->successful()) {
    // Procesar respuesta exitosa
} else {
    // Log del error con status y body
    Log::error('porth_api:lastUpdated_failed', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);
}
```

**Códigos HTTP comunes:**

- **200 OK**: Respuesta exitosa, procesar normalmente
- **401 Unauthorized**: API key inválida o expirada
  - Verificar `PORTH_API_KEY` en `.env`
  - Verificar que el header de autenticación sea correcto
- **403 Forbidden**: Sin permisos para acceder al recurso
  - Verificar permisos de la API key en Porth
- **404 Not Found**: El shipment no existe
  - Se registra como `skipped` en la corrida
- **429 Too Many Requests**: Rate limiting
  - El timeout de 90s debería prevenir esto
  - Si ocurre, considerar aumentar el intervalo entre requests
- **500/503 Server Error**: Error del servidor de Porth
  - Se registra como `failed` en la corrida
  - Se reintenta en la próxima ejecución

**Manejo de errores en el comando:**

```php
try {
    $summary = $this->syncService->syncRecent($hours, $dryRun);
    // Procesar éxito
} catch (\Throwable $e) {
    // Registrar error en la corrida
    $run->fill([
        'status' => 'failed',
        'error_summary' => $e->getMessage(),
        'finished_at' => now(),
    ])->save();
    
    Log::error('porth_sync_run_failed', [
        'run_id' => $run->id,
        'error' => $e->getMessage(),
    ]);
    
    return self::FAILURE;
}
```

### Validación de Respuestas

**Validaciones implementadas:**

1. **Array de IDs**: Se verifica que `listLastUpdated()` devuelva un array
2. **Strings válidos**: Se verifica que cada ID sea un string antes de procesarlo
3. **Payload válido**: Se verifica que `getShipmentById()` devuelva un array no vacío
4. **Campos requeridos**: El helper valida existencia de campos antes de mapearlos

**Ejemplo de validación:**

```php
if (!is_array($ids)) {
    Log::warning('porth_sync_range:unexpected_response', [
        'response_type' => gettype($ids),
    ]);
    return ['ids' => [], 'results' => []];
}

foreach ($ids as $id) {
    if (!is_string($id)) {
        continue; // Saltar IDs inválidos
    }
    // Procesar ID válido
}
```

## 📊 Estructura de Datos JSON

### Campo: `porth_vessel_voyage`

Este campo puede almacenarse como:
- **Objeto JSON**: `{"vessel": "MSC OSCAR", "voyage": "V001"}`
- **String**: `"MSC OSCAR V001"`

**Manejo en el código:**
- Se almacena tal cual viene de Porth (sin transformación)
- En el frontend se maneja ambos casos (ver HTML de visualización)

### Campo: `porth_tags`

- **Tipo**: Array de strings
- **Ejemplo**: `["urgent", "fragile", "priority"]`
- **Cast**: `'array'` en Eloquent

### Campo: `estimated_dates` (en PorthPhase)

- **Tipo**: Array de strings ISO8601
- **Ejemplo**: `["2025-01-15T08:00:00Z", "2025-01-15T10:00:00Z"]`
- **Cast**: `'array'` en Eloquent

### Campo: `raw` (en PorthItinerary y ShippingDocument)

- **Tipo**: Objeto JSON completo del payload original
- **Uso**: Para debugging y datos adicionales no mapeados
- **Cast**: `'array'` en Eloquent

## 📚 Referencias

- API de Porth: https://api.porth.app
- Documentación de endpoints:
  - `GET /api/v2/shipments/lastUpdated?startdate={ISO8601}&endDate={ISO8601}` - Lista de embarques actualizados
  - `GET /api/shipment/byId/{id}` - Detalle de embarque
  - `POST /api/shipment/add` - Crear shipment (usado por PorthSyncService)

## ✅ Checklist de Implementación

### Pre-Despliegue

- [ ] **Verificar migraciones ejecutadas**
  ```bash
  php artisan migrate:status
  # Verificar que todas las migraciones de Porth estén ejecutadas
  ```

- [ ] **Verificar estructura de base de datos**
  ```sql
  -- Verificar tablas creadas
  SHOW TABLES LIKE 'porth_%';
  
  -- Verificar campos en shipping_documents
  DESCRIBE shipping_documents;
  -- Debe incluir: porth_id, porth_pol, porth_pod, freight_type, etc.
  
  -- Verificar campos en purchase_orders
  DESCRIBE purchase_orders;
  -- Debe incluir: porth_pol, porth_pod, freight_type, etc.
  ```

- [ ] **Verificar variables de entorno (.env)**
  ```bash
  # Verificar que estas variables estén configuradas:
  # PORTH_API_KEY (obligatorio)
  # PORTH_API_URL (opcional, default: https://api.porth.app)
  # PORTH_TIMEOUT (opcional, default: 90)
  # PORTH_SYNC_ENABLED (opcional, default: true)
  # PORTH_SYNC_LOOKBACK_HOURS (opcional, default: 2)
  ```

- [ ] **Verificar configuración en config/services.php**
  - Debe incluir todas las claves: `api_key`, `api_url`, `auth_header`, `enabled`, `max_retries`, `timeout`, `sync_dry_run`, `sync_lookback_hours`, `notification_user_ids`

- [ ] **Verificar scheduler configurado**
  ```bash
  # Verificar que Kernel.php tenga:
  # $schedule->command('porth:sync-recent --trigger=schedule')->hourly();
  ```

- [ ] **Verificar relaciones en modelos**
  - `ShippingDocument` debe tener relaciones: `porthCargos()`, `porthPhases()`, `porthItineraries()`, `purchaseOrders()`
  - `PurchaseOrder` debe tener relación: `shippingDocument()`

### Implementación

- [ ] Ejecutar migraciones
- [ ] Crear modelos (PorthCargo, PorthPhase, PorthItinerary, PorthSyncRun)
- [ ] Crear servicios (PorthApiService, PorthSyncUpdatedService, PorthImportService)
- [ ] Crear helper (PorthImportHelper)
- [ ] Crear comando (PorthSyncRecent)
- [ ] Actualizar modelos (ShippingDocument, PurchaseOrder) con relaciones y campos fillable

### Post-Despliegue

- [ ] **Probar con --dry-run**
  ```bash
  php artisan porth:sync-recent --dry-run
  # Verificar que no haya errores y que muestre qué se actualizaría
  ```

- [ ] **Verificar primera sincronización real**
  ```bash
  php artisan porth:sync-recent --trigger=manual
  # Verificar logs y métricas en porth_sync_runs
  ```

- [ ] **Verificar propagación de datos**
  ```sql
  -- Verificar que Purchase Orders tengan datos de Porth
  SELECT po.id, po.porth_pol, po.porth_pod, po.freight_type
  FROM purchase_orders po
  INNER JOIN shipping_documents sd ON po.shipping_document_id = sd.id
  WHERE sd.porth_id IS NOT NULL
  LIMIT 10;
  ```

- [ ] **Verificar notificaciones**
  - Verificar que usuarios configurados en `PORTH_SYNC_NOTIFICATION_USER_IDS` reciban notificaciones

- [ ] **Monitorear logs y métricas**
  ```bash
  # Ver logs en tiempo real
  php artisan pail --filter=porth
  
  # Verificar corridas exitosas
  php artisan tinker
  >>> App\Models\PorthSyncRun::where('status', 'success')->latest()->first();
  ```

- [ ] **Verificar scheduler activo**
  ```bash
  # En el servidor, verificar que el cron esté ejecutando schedule:run
  # Debe ejecutarse cada minuto: * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```

---

---

## 📋 Mapeo Completo de Campos

### Mapeo: Payload de Porth → ShippingDocument

| Campo Porth (camelCase) | Campo DB (snake_case) | Tipo | Notas |
|------------------------|----------------------|------|-------|
| `id` | `porth_id` | string | ID único del shipment en Porth |
| `shipmentNumber` / `porthShipmentNumber` | `porth_shipment_number` | bigint | Número de embarque |
| `carrierCode` | `porth_carrier_code` | string | Código de transportista |
| `pol` | `porth_pol` | string | Puerto de origen (normalizado uppercase) |
| `pod` | `porth_pod` | string | Puerto de destino (normalizado uppercase) |
| `polName` | `porth_pol_name` | string | Nombre del puerto de origen |
| `podName` | `porth_pod_name` | string | Nombre del puerto de destino |
| `origin` | `porth_origin` | string | Origen del embarque |
| `finalDestination` | `porth_final_destination` | string | Destino final |
| `phase` | `porth_phase` | string | Fase actual |
| `priority` | `porth_priority` | string | Prioridad |
| `modality` | `porth_modality` | string | Modalidad (FCL, LCL, etc.) |
| `vesselVoyage` | `porth_vessel_voyage` | json | Objeto o string |
| `name` | `porth_name` | string | Nombre del shipment |
| `organizationId` | `porth_organization_id` | string | ID de organización |
| `firstEta` | `porth_first_eta` | datetime | Primera ETA estimada |
| `firstEtd` | `porth_first_etd` | datetime | Primera ETD estimada |
| `ready` | `porth_ready` | datetime | Fecha de listo |
| `toOriginPort` | `porth_to_origin_port` | datetime | Hacia puerto origen |
| `atOriginPort` | `porth_at_origin_port` | datetime | En puerto origen |
| `inTransit` | `porth_in_transit` | datetime | En tránsito |
| `atDestinationPort` | `porth_at_destination_port` | datetime | En puerto destino |
| `toFinalDestination` | `porth_to_final_destination` | datetime | Hacia destino final |
| `delivered` | `porth_delivered` | datetime | Entregado |
| `freeTimeAtDestination` | `porth_free_time_at_destination` | int | Días de tiempo libre |
| `manualTracking` | `porth_manual_tracking` | boolean | Solo si existe la key |
| `tags` | `porth_tags` | json | Array de strings |
| `masterBl` | `mbl_number` | string | Master BL (normalizado) |
| `houseBl` | `hbl_number` | string | House BL (normalizado) |
| `bookingNumber` | `booking_code` | string | Código de booking (normalizado) |
| `etd` | `estimated_departure_date` | date | Fecha estimada de salida |
| `eta` | `estimated_arrival_date` | date | Fecha estimada de llegada |
| `atd` | `actual_departure_date` | date | Fecha real de salida |
| `ata` | `actual_arrival_date` | date | Fecha real de llegada |
| `freightType` | `freight_type` | string | Solo si no está vacío |
| `cargo[0].number` | `container_number` | string | Primer contenedor (normalizado) |
| - | `last_porth_sync_at` | datetime | Siempre `now()` |
| - | `porth_raw` | json | Payload completo original |

### Mapeo: ShippingDocument → PurchaseOrder

Los siguientes 26 campos se propagan directamente desde `ShippingDocument` a `PurchaseOrder`:

| Campo ShippingDocument | Campo PurchaseOrder | Tipo |
|------------------------|---------------------|------|
| `porth_pol` | `porth_pol` | string |
| `porth_pol_name` | `porth_pol_name` | string |
| `porth_pod` | `porth_pod` | string |
| `porth_pod_name` | `porth_pod_name` | string |
| `porth_origin` | `porth_origin` | string |
| `porth_final_destination` | `porth_final_destination` | string |
| `porth_carrier_code` | `porth_carrier_code` | string |
| `porth_vessel_voyage` | `porth_vessel_voyage` | json |
| `porth_shipment_number` | `porth_shipment_number` | string |
| `porth_modality` | `porth_modality` | string |
| `freight_type` | `freight_type` | string |
| `porth_first_eta` | `porth_first_eta` | datetime |
| `porth_first_etd` | `porth_first_etd` | datetime |
| `porth_ready` | `porth_ready` | datetime |
| `porth_to_origin_port` | `porth_to_origin_port` | datetime |
| `porth_at_origin_port` | `porth_at_origin_port` | datetime |
| `porth_in_transit` | `porth_in_transit` | datetime |
| `porth_at_destination_port` | `porth_at_destination_port` | datetime |
| `porth_to_final_destination` | `porth_to_final_destination` | datetime |
| `porth_delivered` | `porth_delivered` | datetime |
| `porth_phase` | `porth_phase` | string |
| `porth_priority` | `porth_priority` | string |
| `porth_manual_tracking` | `porth_manual_tracking` | boolean |
| `porth_free_time_at_destination` | `porth_free_time_at_destination` | int |
| `porth_id` | `porth_id` | string |
| `last_porth_sync_at` | `last_porth_sync_at` | datetime |

### Mapeo: Payload de Porth → PurchaseOrder (directo)

Los siguientes campos se propagan directamente del payload a PurchaseOrder:

| Campo Porth | Campo PurchaseOrder | Tipo | Transformación |
|-------------|---------------------|------|----------------|
| `etd` | `date_etd` | datetime | `parseDateTime()` |
| `atd` | `date_atd` | datetime | `parseDateTime()` |
| `eta` | `date_eta` | datetime | `parseDateTime()` |
| `ata` | `date_ata` | datetime | `parseDateTime()` |
| `incoterm` | `incoterms` | string | `normalize()` |
| `freightType` | `freight_type` | string | `normalize()` (solo si no vacío) |

---

**Última actualización:** Enero 2025  
**Versión:** 3.0  
**Mejoras:** 
- Implementación completa de PorthImportHelper con métodos auxiliares
- Implementación completa de PorthSyncRecent con notificaciones
- Mapeo completo de campos Payload → ShippingDocument → PurchaseOrder
- Especificación exacta de $fillable y $casts
- Estructura completa del payload de Porth
- Manejo de errores de API
- Relación con PorthSyncService
- Estructura de datos JSON
