# API de Órdenes de Compra - Documentación Completa

## 📋 Índice

1. [Información General](#información-general)
2. [Autenticación](#autenticación)
3. [Endpoints Disponibles](#endpoints-disponibles)
4. [Campos de la API](#campos-de-la-api)
5. [Ejemplos de Uso](#ejemplos-de-uso)
6. [Manejo de Errores](#manejo-de-errores)
7. [Códigos de Estado HTTP](#códigos-de-estado-http)
8. [Mejores Prácticas](#mejores-prácticas)
9. [Límites y Restricciones](#límites-y-restricciones)
10. [Soporte](#soporte)

---

## Información General

### Especificaciones Técnicas

- **Versión**: v1.1.0
- **Base URL**: `https://[su-dominio]/api`
- **Formato**: JSON
- **Protocolo**: HTTPS (recomendado)
- **Codificación**: UTF-8
- **Rate Limit**: 1000 requests/minuto

### Tipos de Endpoints

1. **Endpoints Públicos**: No requieren autenticación
2. **Endpoints Protegidos**: Requieren token Bearer

---

## Autenticación

### Obtención de Token

1. Inicie sesión en el sistema web
2. Navegue a **Configuraciones > Tokens API**
3. Haga clic en **"Crear Nuevo Token"**
4. Proporcione un nombre descriptivo
5. Establezca fecha de expiración (opcional)
6. Copie el token generado (se muestra solo una vez)

### Uso del Token

```bash
Authorization: Bearer {su_token}
```

---

## Endpoints Disponibles

### 1. Verificación de Estado

#### GET /api/status

Verifica que los servicios API estén funcionando correctamente.

**Autenticación**: No requerida

**Respuesta**:
```json
{
    "status": "API funcionando correctamente",
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "version": "1.0.0"
}
```

**Ejemplo**:
```bash
curl -X GET "https://su-dominio.com/api/status"
```

### 2. Creación de Órdenes de Compra (Público)

#### POST /api/purchase-orders

Crea una o múltiples órdenes de compra desde sistemas externos.

**Autenticación**: No requerida  
**Content-Type**: `application/json` o `application/x-www-form-urlencoded`

### 3. Información del Usuario

#### GET /api/user

Obtiene información del usuario autenticado y su compañía.

**Autenticación**: Token requerido

**Respuesta**:
```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@empresa.com",
        "company_id": 1
    },
    "company": {
        "id": 1,
        "name": "Empresa S.A.",
        "address": "San José, Costa Rica"
    }
}
```

### 4. Gestión de Órdenes del Usuario

#### GET /api/my-purchase-orders

Obtiene las órdenes de compra de la empresa del usuario autenticado.

**Autenticación**: Token requerido

**Parámetros de Query**:
- `page` - Número de página (default: 1)
- `per_page` - Elementos por página (default: 10)

#### POST /api/my-purchase-orders

Crea una nueva orden de compra simple para el usuario autenticado.

**Autenticación**: Token requerido

**Campos Requeridos**:
- `vendor_id` (integer) - ID del proveedor existente
- `description` (string, max 255) - Descripción de la orden
- `total_amount` (numeric, min 0) - Monto total

### 5. Estadísticas del Dashboard

#### GET /api/dashboard-stats

Obtiene estadísticas resumidas de órdenes de compra para el usuario autenticado.

**Autenticación**: Token requerido

### 6. Actualización de Perfil

#### PUT /api/profile

Actualiza la información del perfil del usuario autenticado.

**Autenticación**: Token requerido

---

## Campos de la API

### Campos Obligatorios

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `order_number` | string | Número único de la orden | "PO-2024-001" |
| `category` | string | Categoría del producto | "Electronics" |
| `factory_proforma_number` | string | Número de proforma de fábrica | "PRF-001" |
| `route_label` | string | Etiqueta de ruta logística | "RUTA-A" |
| `date_theorical_load` | date | Fecha teórica de carga | "2024-01-20" |
| `bonded_warehouse_enter` | date | Fecha de ingreso a Almacén Fiscal | "2024-01-15" |
| `bonded_warehouse_exit` | date | Fecha de salida de Almacén Fiscal | "2024-01-25" |
| `reason` | string | Motivo de la orden | "Stock replenishment" |
| `incoterms` | string | Incoterms de compra | "FOB" |
| `logistics_incoterm` | string | Incoterms de logística | "FOB" |
| `price_incoterm` | string | Incoterms de precios | "FOB" |

### Campos de Proveedor (al menos uno requerido)

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `vendor_id` | integer | ID del proveedor existente | 1 |
| `vendor` | string | Nombre del proveedor | "Proveedor ABC" |
| `vendor_name` | string | Nombre alternativo del proveedor | "ABC Corp" |

### Campos Opcionales Básicos

| Campo | Tipo | Descripción | Ejemplo | Default |
|-------|------|-------------|---------|---------|
| `net_total` | decimal | Monto total neto | 2500.00 | - |
| `ship_to` | string | Dirección de envío | "Almacén Central" | - |
| `bill_to` | string | Dirección de facturación | "Oficina Principal" | - |
| `hub` | string | Hub planificado | "MIA" | - |
| `currency` | string | Moneda | "USD" | "USD" |
| `mode` | string | Modo de transporte | "SEA" | "AIR" |
| `length_cm` | decimal | Largo en centímetros | 100.5 | - |
| `width_cm` | decimal | Ancho en centímetros | 80.0 | - |
| `height_cm` | decimal | Alto en centímetros | 50.0 | - |

### Campos OLO - Información Textual

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `mbl_number` | string | Número Master Bill of Lading | "MBL123456" |
| `container_type` | string | Tipo de contenedor | "20FT" |
| `container_number` | string | Número de contenedor | "CONT123456" |
| `shipping_line` | string | Línea naviera | "Maersk" |
| `forwarder_name` | string | Nombre del freight forwarder | "Forwarder ABC" |
| `customer_name` | string | Nombre del cliente | "Cliente XYZ" |
| `cargo_invoice_number` | string | Número de factura de carga | "INV789" |
| `tariff_type` | string | Tipo de tarifa | "Standard" |
| `arrival_status` | string | Estado de llegada | "On Time" |
| `arrival_port` | string | Puerto de llegada | "Puerto Limón" |
| `departure_port` | string | Puerto de salida | "Puerto de Miami" |
| `retail_group` | string | Grupo retail | "Retail Group A" |
| `customer_type` | string | Tipo de cliente | "Premium" |
| `trading_company` | string | Empresa comercializadora | "Trading Co" |
| `service_provider` | string | Proveedor de servicios | "Service Provider" |
| `customs_dua` | string | DUA de aduanas | "DUA123456" |
| `invoice` | string | Número de factura | "INV001" |
| `factura_merca` | string | Factura de mercancía | "FM001" |
| `case_number_file` | string | Número de caso/archivo | "CASE001" |
| `receipt_note` | string | Nota de recibo | "Receipt note" |
| `visibility_notes` | string | Notas de visibilidad | "Visibility notes" |
| `etd_notes` | string | Notas sobre ETD | "ETD notes" |
| `consolidator_name` | string | Nombre del consolidador | "Consolidator ABC" |
| `vendor_number` | string | Número del proveedor | "VENDOR001" |

### Campos OLO - Booleanos

| Campo | Tipo | Descripción | Default |
|-------|------|-------------|---------|
| `is_dropship` | boolean | Es envío directo | false |
| `applies_tlc` | boolean | Aplica TLC | false |
| `applies_af` | boolean | Aplica AF | false |
| `port_of_loading_validated` | boolean | Puerto de carga validado | false |
| `has_facture_merca` | boolean | Tiene factura de mercancía | false |
| `used_rate_ok` | boolean | Tasa utilizada OK | false |
| `uses_bonded_warehouse` | boolean | Usa almacén aduanero | false |
| `apply_technical_note` | boolean | Aplica nota técnica | false |
| `etd_initial_validated` | boolean | ETD inicial validado | false |

### Campos OLO - Enteros

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `delay_days` | integer | Días de retraso | 5 |
| `container_free_days` | integer | Días libres de contenedor | 7 |
| `etd_dates_difference` | integer | Diferencia fechas ETD | -2 |
| `eta_dates_difference` | integer | Diferencia fechas ETA | 1 |

### Campos OLO - Decimales

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `Invoice_amount` | decimal | Monto de factura | 2500.00 |
| `freight_amount` | decimal | Monto de flete | 500.00 |
| `cbm` | decimal | Metros cúbicos | 15.5 |

### Fechas Adicionales

| Campo | Tipo | Formato | Descripción |
|-------|------|---------|-------------|
| `date_booking_request` | datetime | YYYY-MM-DD HH:MM:SS | Fecha solicitud booking |
| `date_booking_authorized` | datetime | YYYY-MM-DD HH:MM:SS | Fecha booking autorizado |
| `date_variable_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha variable |
| `date_carga_po` | datetime | YYYY-MM-DD HH:MM:SS | Fecha carga PO |
| `date_received` | datetime | YYYY-MM-DD HH:MM:SS | Fecha recibido |
| `date_etd_initial` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ETD inicial |
| `date_etd_updated` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ETD actualizada |
| `date_eta_updated` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ETA actualizada |
| `date_etd` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ETD |
| `date_atd` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ATD |
| `date_eta` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ETA |
| `date_ata` | datetime | YYYY-MM-DD HH:MM:SS | Fecha ATA |
| `date_estimated_hub_arrival` | datetime | YYYY-MM-DD HH:MM:SS | Fecha estimada llegada hub |
| `date_actual_hub_arrival` | datetime | YYYY-MM-DD HH:MM:SS | Fecha real llegada hub |
| `inspection_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha inspección |
| `vgm_cut_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha corte VGM |
| `balance_payment_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha pago balance |
| `local_charges_payment_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha pago cargos locales |
| `receipt_note_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha nota recibo |
| `estimated_dc_availability_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha estimada disponibilidad DC |
| `date_invoice_received` | datetime | YYYY-MM-DD HH:MM:SS | Fecha factura recibida |
| `date_vendor_document_received` | datetime | YYYY-MM-DD HH:MM:SS | Fecha documentos proveedor recibidos |
| `date_required_in_destination` | datetime | YYYY-MM-DD HH:MM:SS | Fecha requerida en destino |
| `dif_load_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha diferencia de carga |
| `emision_date_po` | date | YYYY-MM-DD | Fecha emisión PO |
| `forwader_date` | datetime | YYYY-MM-DD HH:MM:SS | Fecha forwarder |

### Estructura de Items

Los items de la orden de compra se envían en el campo `items` como un array de objetos:

```json
{
  "items": [
    {
      "material": "MAT-001",
      "price_per_unit": 25.50,
      "peso_kg": 100.0
    }
  ]
}
```

**Campos de Items**:
- `material` (string) - ID del material/producto (requerido)
- `price_per_unit` (decimal) - Precio por unidad (opcional)
- `peso_kg` (decimal) - Peso en kilogramos (opcional)

---

## Ejemplos de Uso

### Ejemplo 1: Orden Básica

```bash
curl -X POST "https://su-dominio.com/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "order_number": "PO-2024-001",
    "category": "Electronics",
    "factory_proforma_number": "PRF-001",
    "route_label": "RUTA-A",
    "date_theorical_load": "2024-01-20",
    "bonded_warehouse_enter": "2024-01-15",
    "bonded_warehouse_exit": "2024-01-25",
    "reason": "Stock replenishment",
    "incoterms": "FOB",
    "logistics_incoterm": "FOB",
    "price_incoterm": "FOB",
    "vendor_name": "Proveedor ABC",
    "net_total": 2500.00,
    "currency": "USD",
    "mode": "SEA",
    "items": [
      {
        "material": "MAT-001",
        "price_per_unit": 25.50,
        "peso_kg": 100.0
      }
    ]
  }'
```

### Ejemplo 2: Orden Completa con Campos OLO

```bash
curl -X POST "https://su-dominio.com/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "order_number": "PO-2024-002",
    "category": "Textiles",
    "factory_proforma_number": "PRF-002",
    "route_label": "RUTA-B",
    "date_theorical_load": "2024-02-01",
    "bonded_warehouse_enter": "2024-01-28",
    "bonded_warehouse_exit": "2024-02-05",
    "reason": "Seasonal inventory",
    "incoterms": "CIF",
    "logistics_incoterm": "CIF",
    "price_incoterm": "CIF",
    "vendor_name": "Textile Supplier Ltd",
    "net_total": 5000.00,
    "currency": "USD",
    "mode": "SEA",
    "ship_to": "Warehouse Central",
    "bill_to": "Main Office",
    "hub": "MIA",
    "length_cm": 120.0,
    "width_cm": 80.0,
    "height_cm": 60.0,
    "mbl_number": "MBL789012",
    "container_type": "40FT",
    "container_number": "CONT789012",
    "shipping_line": "MSC",
    "forwarder_name": "Global Forwarder",
    "customer_name": "Retail Chain ABC",
    "cargo_invoice_number": "INV456",
    "tariff_type": "Preferential",
    "arrival_status": "On Time",
    "arrival_port": "Puerto Limón",
    "departure_port": "Puerto de Miami",
    "retail_group": "Fashion Group",
    "customer_type": "Premium",
    "trading_company": "Trading Corp",
    "service_provider": "Logistics Pro",
    "customs_dua": "DUA789012",
    "invoice": "INV002",
    "factura_merca": "FM002",
    "case_number_file": "CASE002",
    "receipt_note": "Receipt for textile order",
    "visibility_notes": "High priority shipment",
    "consolidator_name": "Consolidator XYZ",
    "vendor_number": "VENDOR002",
    "is_dropship": false,
    "applies_tlc": true,
    "applies_af": false,
    "port_of_loading_validated": true,
    "has_facture_merca": true,
    "used_rate_ok": true,
    "uses_bonded_warehouse": true,
    "apply_technical_note": false,
    "etd_initial_validated": true,
    "delay_days": 0,
    "container_free_days": 7,
    "etd_dates_difference": 0,
    "eta_dates_difference": 0,
    "Invoice_amount": 5000.00,
    "freight_amount": 800.00,
    "cbm": 25.0,
    "date_booking_request": "2024-01-15 10:00:00",
    "date_booking_authorized": "2024-01-16 14:30:00",
    "date_etd_initial": "2024-02-01 08:00:00",
    "date_etd": "2024-02-01 08:00:00",
    "date_eta": "2024-02-15 16:00:00",
    "date_estimated_hub_arrival": "2024-02-16 10:00:00",
    "inspection_date": "2024-02-17 09:00:00",
    "balance_payment_date": "2024-02-20 12:00:00",
    "date_invoice_received": "2024-01-20 11:00:00",
    "date_vendor_document_received": "2024-01-22 15:30:00",
    "date_required_in_destination": "2024-02-25 17:00:00",
    "emision_date_po": "2024-01-10",
    "items": [
      {
        "material": "TEXT-001",
        "price_per_unit": 12.50,
        "peso_kg": 200.0
      },
      {
        "material": "TEXT-002",
        "price_per_unit": 8.75,
        "peso_kg": 150.0
      }
    ]
  }'
```

### Ejemplo 3: Múltiples Órdenes

```bash
curl -X POST "https://su-dominio.com/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "purchase_orders": [
      {
        "order_number": "PO-2024-003",
        "category": "Electronics",
        "factory_proforma_number": "PRF-003",
        "route_label": "RUTA-C",
        "date_theorical_load": "2024-02-10",
        "bonded_warehouse_enter": "2024-02-05",
        "bonded_warehouse_exit": "2024-02-15",
        "reason": "New product launch",
        "incoterms": "EXW",
        "logistics_incoterm": "EXW",
        "price_incoterm": "EXW",
        "vendor_name": "Electronics Corp",
        "net_total": 3000.00,
        "items": [
          {
            "material": "ELEC-001",
            "price_per_unit": 150.00,
            "peso_kg": 50.0
          }
        ]
      },
      {
        "order_number": "PO-2024-004",
        "category": "Home & Garden",
        "factory_proforma_number": "PRF-004",
        "route_label": "RUTA-D",
        "date_theorical_load": "2024-02-12",
        "bonded_warehouse_enter": "2024-02-08",
        "bonded_warehouse_exit": "2024-02-18",
        "reason": "Seasonal preparation",
        "incoterms": "FOB",
        "logistics_incoterm": "FOB",
        "price_incoterm": "FOB",
        "vendor_name": "Garden Supplies Inc",
        "net_total": 1800.00,
        "items": [
          {
            "material": "GARDEN-001",
            "price_per_unit": 25.00,
            "peso_kg": 75.0
          }
        ]
      }
    ]
  }'
```

### Ejemplo 4: JavaScript/Node.js

```javascript
const axios = require('axios');

class OLOClient {
    constructor(baseURL, token = null) {
        this.api = axios.create({
            baseURL,
            headers: {
                'Content-Type': 'application/json',
                ...(token && { 'Authorization': `Bearer ${token}` })
            }
        });
    }

    async createPurchaseOrder(orderData) {
        try {
            const response = await this.api.post('/purchase-orders', orderData);
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    handleError(error) {
        if (error.response) {
            return {
                status: error.response.status,
                message: error.response.data.message || 'Error del servidor',
                errors: error.response.data.errors || null
            };
        }
        return {
            status: 0,
            message: 'Error de conexión',
            errors: null
        };
    }
}

// Uso
const client = new OLOClient('https://su-dominio.com/api');
const order = await client.createPurchaseOrder({
    order_number: 'PO-2024-001',
    category: 'Electronics',
    // ... otros campos
});
```

### Ejemplo 5: Python

```python
import requests

class OLOClient:
    def __init__(self, base_url, token=None):
        self.base_url = base_url
        self.headers = {'Content-Type': 'application/json'}
        if token:
            self.headers['Authorization'] = f'Bearer {token}'
    
    def create_purchase_order(self, order_data):
        response = requests.post(
            f'{self.base_url}/purchase-orders',
            json=order_data,
            headers=self.headers
        )
        response.raise_for_status()
        return response.json()

# Uso
client = OLOClient('https://su-dominio.com/api')
order = client.create_purchase_order({
    'order_number': 'PO-2024-001',
    'category': 'Electronics',
    # ... otros campos
})
```

---

## Manejo de Errores

### Error de Validación (422)

```json
{
    "success": false,
    "message": "Validación fallida.",
    "errors": {
        "order_number": ["El campo 'P.O.' es obligatorio."],
        "category": ["El campo 'Categoria' es obligatorio."],
        "factory_proforma_number": ["El campo 'Proforma Fábrica' es obligatorio."],
        "route_label": ["El campo 'Ruta Logística' es obligatorio."],
        "date_theorical_load": ["El campo 'Carga Lista Teórica' es obligatorio."],
        "bonded_warehouse_enter": ["El campo 'Entrada Almacen Fiscal' es obligatorio."],
        "bonded_warehouse_exit": ["El campo 'Salida Almacen Fiscal' es obligatorio."],
        "reason": ["El campo 'Motivo' es obligatorio."],
        "incoterms": ["El 'Incoterm de compra' es obligatorio."],
        "logistics_incoterm": ["El 'Incoterm de logística' es obligatorio."],
        "price_incoterm": ["El 'Incoterm de precios' es obligatorio."],
        "vendor_id": ["Debe enviar al menos uno de: vendor_id, vendor o vendor_name."]
    },
    "data": null
}
```

### Error de Autenticación (401)

```json
{
    "error": "Token de acceso requerido",
    "message": "Debe proporcionar un token de acceso válido en el header Authorization"
}
```

### Token Expirado (401)

```json
{
    "error": "Token expirado",
    "message": "El token ha expirado"
}
```

### Token Inválido (401)

```json
{
    "error": "Token inválido",
    "message": "El token proporcionado no es válido o ha expirado"
}
```

### Error del Servidor (400)

```json
{
    "success": false,
    "message": "Error interno del servidor: [mensaje específico]",
    "data": null
}
```

### Error de Conexión (0)

```json
{
    "status": 0,
    "message": "Error de conexión",
    "errors": null,
    "data": null
}
```

---

## Códigos de Estado HTTP

| Código | Descripción | Cuándo Ocurre |
|--------|-------------|---------------|
| 200 | OK | Solicitud exitosa |
| 201 | Created | Orden(es) creada(s) exitosamente |
| 400 | Bad Request | Error en la solicitud o servidor |
| 401 | Unauthorized | Token inválido o faltante |
| 403 | Forbidden | Sin permisos suficientes |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error interno del servidor |

---

## Mejores Prácticas

### 1. Validación de Datos

```javascript
// Validar antes del envío
function validateOrderData(orderData) {
    const requiredFields = [
        'order_number', 'category', 'factory_proforma_number',
        'route_label', 'date_theorical_load', 'bonded_warehouse_enter',
        'bonded_warehouse_exit', 'reason', 'incoterms',
        'logistics_incoterm', 'price_incoterm'
    ];
    
    const missingFields = requiredFields.filter(field => !orderData[field]);
    
    if (missingFields.length > 0) {
        throw new Error(`Campos obligatorios faltantes: ${missingFields.join(', ')}`);
    }
    
    // Validar al menos un campo de proveedor
    const vendorFields = ['vendor_id', 'vendor', 'vendor_name'];
    const hasVendor = vendorFields.some(field => orderData[field]);
    
    if (!hasVendor) {
        throw new Error('Debe proporcionar al menos un campo de proveedor');
    }
}
```

### 2. Manejo de Errores

```javascript
try {
    const result = await client.createPurchaseOrder(orderData);
    console.log('Orden creada:', result);
} catch (error) {
    if (error.status === 422) {
        console.error('Errores de validación:', error.errors);
    } else if (error.status === 401) {
        console.error('Error de autenticación:', error.message);
    } else {
        console.error('Error del servidor:', error.message);
    }
}
```

### 3. Reintentos con Backoff Exponencial

```javascript
async function createOrderWithRetry(orderData, maxRetries = 3) {
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            const result = await client.createPurchaseOrder(orderData);
            return result;
        } catch (error) {
            if (error.status === 422 || error.status === 401) {
                throw error; // No reintentar errores de validación o autenticación
            }
            
            if (attempt === maxRetries) {
                throw error;
            }
            
            // Esperar antes del siguiente intento
            const delay = Math.pow(2, attempt - 1) * 1000;
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
}
```

### 4. Formateo de Fechas

```javascript
// Formatear fecha para la API
function formatDate(date) {
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    return date;
}

// Formatear datetime para la API
function formatDateTime(date) {
    if (date instanceof Date) {
        return date.toISOString().replace('T', ' ').split('.')[0];
    }
    return date;
}
```

---

## Límites y Restricciones

| Límite | Valor | Descripción |
|--------|-------|-------------|
| Peticiones por minuto | 1000 | Rate limit por IP |
| Tamaño máximo de payload | 10 MB | Tamaño máximo del JSON |
| Tiempo de timeout | 30 segundos | Timeout de la petición |
| Items por orden | Sin límite | Número de items en una orden |
| Órdenes por petición | Sin límite | Número de órdenes en una petición |

---

## Soporte

- **Email**: soporte-api@empresa.com
- **Horario**: Lunes a Viernes, 8:00 AM - 6:00 PM
- **Tiempo de respuesta**: 24 horas hábiles
- **Documentación**: https://docs.empresa.com/api

---

## Changelog

### v1.1.0 (2024-01-15)
- ✨ Documentación completa con todos los campos OLO
- ✨ Ejemplos en JavaScript y Python
- ✨ Manejo de errores mejorado
- ✨ Mejores prácticas y utilidades
- ✨ Estructura consolidada en un solo archivo

### v1.0.0 (2024-01-10)
- 📋 Documentación inicial básica
- 📋 Ejemplos simples de uso

---

*Esta documentación se actualiza regularmente. Para ver el historial completo de cambios, consulta el [Changelog de Documentación](CHANGELOG_DOCUMENTACION.md).*
