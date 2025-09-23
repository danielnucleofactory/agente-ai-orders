# Endpoint de Actualización de Purchase Orders

## Resumen

Endpoint para actualizar campos específicos de una Purchase Order existente mediante API externa.

## Especificaciones

### Ruta
```
PUT /api/purchase-orders/{po_id}
```

### Headers
```http
Content-Type: application/json
Idempotency-Key: {opcional} - Para garantizar idempotencia
X-Request-ID: {opcional} - Para tracking de requests
```

### Parámetros
- `po_id` (int): ID de la Purchase Order a actualizar

## Campos Actualizables

### Campos Básicos
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `PO` | `order_number` | string | Número de la PO (único) |
| `PROVEEDOR_NO` | `vendor_number` | string | Número de proveedor |
| `PROVEEDOR_NOMBRE` | `vendor_name` | string | Nombre del proveedor |
| `RUTA_LOGISTICA` | `route_label` | string | Ruta logística |
| `MONTO_PO` | `net_total` | decimal | Valor de la PO |
| `MONEDA_PO` | `currency` | string | Tipo de moneda |
| `FECHA_EMISION_PO` | `emision_date_po` | date | Fecha de emisión |
| `CATEGORIA` | `category` | string | Categoría del producto |
| `ESTADO` | `status` | string | Estado de la PO |
| `MOTIVO` | `reason` | string | Motivo de la PO |

### Incoterms
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `INCOTERM_COMPRA` | `incoterms` | string | Incoterm de compra |
| `INCOTERM_LOGISTICA` | `logistics_incoterm` | string | Incoterm de logística |
| `INCOTERM_PRECIOS` | `price_incoterm` | string | Incoterm de precios |

### Fechas
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `FECHA_CARGOLIST` | `date_theorical_load` | date | Fecha de carga lista |
| `ETD_ESTIMADO` | `date_etd` | date | Fecha estimada de salida |
| `ETA_ESTIMADO` | `date_eta` | date | Fecha estimada de llegada |
| `FECHA_NR` | `receipt_note_date` | date | Fecha de nota de recibo |

### Diferencias de Fechas
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `DIF_FECHA_CARGA` | `dif_load_date` | int | Diferencia en días de carga |
| `DIF_FECHAS_ETD` | `etd_dates_difference` | int | Diferencia en días ETD |
| `DIF_FECHAS_ETA` | `eta_dates_difference` | int | Diferencia en días ETA |

### Puertos y Logística
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `PUERTO_EMBARQUE` | `departure_port` | string | Puerto de embarque |
| `PUERTO_ARRIBO` | `arrival_port` | string | Puerto de arribo |
| `DUA_INTERNAMIENTO` | `customs_dua` | string | DUA de internamiento |

### Documentos
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `EXPEDIENTE` | `case_number_file` | string | Número de expediente |
| `NOTA_RECIBO` | `receipt_note` | string | Nota de recibo |
| `PROFORMA_FABRICA` | `factory_proforma_number` | string | Proforma de fábrica |
| `FACTURA` | `invoice` | string | Factura del proveedor |
| `MONTO_FACTURA` | `Invoice_amount` | decimal | Monto de la factura |

### Flags Booleanos
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `APLICA_TLC` | `applies_tlc` | boolean | Aplica TLC |
| `APLICA_NOTA_TECNICA` | `apply_technical_note` | boolean | Aplica nota técnica |

### Clasificación
| Campo API | Campo Modelo | Tipo | Descripción |
|-----------|--------------|------|-------------|
| `TIPO_CLIENTE` | `customer_type` | string | Tipo de cliente |
| `GRUPO_REPOSITOR` | `retail_group` | string | Grupo retail |

## Ejemplos de Uso

### Actualización Básica
```bash
curl -X PUT "https://api.olo-orders.com/api/purchase-orders/123" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: update-123-2024-01-15" \
  -d '{
    "ESTADO": "approved",
    "MONTO_PO": 2500.00,
    "MONEDA_PO": "USD",
    "ETD_ESTIMADO": "2024-02-01",
    "ETA_ESTIMADO": "2024-02-15"
  }'
```

### Actualización de Proveedor
```bash
curl -X PUT "https://api.olo-orders.com/api/purchase-orders/123" \
  -H "Content-Type: application/json" \
  -d '{
    "PROVEEDOR_NOMBRE": "Nuevo Proveedor S.A.",
    "PROFORMA_FABRICA": "PF-2024-001",
    "FACTURA": "INV-2024-001",
    "MONTO_FACTURA": 2300.00
  }'
```

### Actualización de Fechas y Diferencias
```bash
curl -X PUT "https://api.olo-orders.com/api/purchase-orders/123" \
  -H "Content-Type: application/json" \
  -d '{
    "FECHA_CARGOLIST": "2024-01-20",
    "ETD_ESTIMADO": "2024-01-25",
    "ETA_ESTIMADO": "2024-02-10",
    "DIF_FECHAS_ETD": 2,
    "DIF_FECHAS_ETA": -1
  }'
```

### Actualización de Incoterms
```bash
curl -X PUT "https://api.olo-orders.com/api/purchase-orders/123" \
  -H "Content-Type: application/json" \
  -d '{
    "INCOTERM_COMPRA": "FOB",
    "INCOTERM_LOGISTICA": "CIF",
    "INCOTERM_PRECIOS": "FOB"
  }'
```

## Respuestas

### Éxito (200)
```json
{
  "success": true,
  "message": "Purchase order updated successfully",
  "data": {
    "id": 123,
    "order_number": "PO-2024-001",
    "status": "approved",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "changes": {
      "ESTADO": {
        "old": "draft",
        "new": "approved"
      },
      "MONTO_PO": {
        "old": 2000.00,
        "new": 2500.00
      },
      "ETD_ESTIMADO": {
        "old": "2024-01-30",
        "new": "2024-02-01"
      }
    }
  }
}
```

### Error - PO No Encontrada (404)
```json
{
  "success": false,
  "message": "Purchase order not found",
  "error_code": "PO_NOT_FOUND"
}
```

### Error - Estado No Editable (409)
```json
{
  "success": false,
  "message": "No se puede actualizar una orden en estado: shipped",
  "error_code": "STATUS_NOT_EDITABLE"
}
```

### Error - Validación (400)
```json
{
  "success": false,
  "message": "Invalid status: invalid_status. Allowed: draft, pending, approved, shipped, delivered, cancelled",
  "error_code": "UPDATE_ERROR"
}
```

### Error - Sin Cambios (422)
```json
{
  "success": false,
  "message": "Debe proporcionar al menos un campo para actualizar",
  "error_code": "NO_CHANGES"
}
```

## Validaciones

### Estados Permitidos
- `draft`, `pending`, `approved`, `shipped`, `delivered`, `cancelled`

### Monedas Permitidas
- `USD`, `EUR`, `CRC`

### Incoterms Permitidos
- `CIF`, `CIP`, `CFR`, `CPT`, `DAT`, `DAP`, `DDP`, `DEQ`, `DES`, `EXD`, `EXQ`, `EXW`, `FCA`, `FOB`

### Formatos de Fecha
- ISO 8601: `YYYY-MM-DD` o `YYYY-MM-DD HH:mm:ss`

### Estados No Editables
- `shipped`, `delivered`, `cancelled`

## Características Especiales

### Idempotencia
- Usar header `Idempotency-Key` para garantizar que requests duplicados devuelvan la misma respuesta
- Cache válido por 1 hora

### Auditoría
- Todos los cambios se registran en logs con:
  - Timestamp
  - IP del cliente
  - User-Agent
  - Request-ID
  - Idempotency-Key
  - Cambios realizados (antes/después)

### Transacciones
- Todas las actualizaciones se realizan en transacciones de base de datos
- Rollback automático en caso de error

### Búsqueda de Proveedores
- `PROVEEDOR_NOMBRE`: Busca por nombre exacto
- `PROVEEDOR_NO`: Busca por código de proveedor
- Error si el proveedor no existe

## Limitaciones

1. **No se pueden actualizar líneas de productos** - Solo campos de cabecera
2. **Estados finales no editables** - `shipped`, `delivered`, `cancelled`
3. **Validaciones estrictas** - Valores deben estar en catálogos permitidos
4. **Proveedores deben existir** - No se crean automáticamente

## Casos de Uso Típicos

1. **Actualización de estado** - Cambiar de `draft` a `approved`
2. **Corrección de fechas** - Ajustar ETD/ETA estimados
3. **Actualización de montos** - Corregir valores de PO o factura
4. **Cambio de proveedor** - Asignar nuevo proveedor
5. **Actualización de documentos** - Agregar números de factura/proforma
6. **Ajuste de incoterms** - Cambiar términos comerciales
7. **Corrección de puertos** - Actualizar puertos de embarque/arribo
