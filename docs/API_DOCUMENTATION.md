# Documentación de API - OLO Raga Orders

## 📋 Tabla de Contenidos

1. [Información General](#información-general)
2. [Autenticación](#autenticación)
3. [Endpoints Públicos](#endpoints-públicos)
4. [Endpoints Protegidos con API Token](#endpoints-protegidos-con-api-token)
5. [Endpoints Dashboard KPI](#endpoints-dashboard-kpi)
6. [Códigos de Estado HTTP](#códigos-de-estado-http)
7. [Manejo de Errores](#manejo-de-errores)
8. [Ejemplos de Uso](#ejemplos-de-uso)

---

## Información General

### Especificaciones Técnicas

- **Versión de API**: 1.0.0
- **Base URL**: `http://olo-raga-orders.test/api` (desarrollo) o `https://[tu-dominio]/api` (producción)
- **Formato de Datos**: JSON
- **Codificación**: UTF-8
- **Content-Type**: `application/json` o `application/x-www-form-urlencoded`

### Tipos de Endpoints

1. **Endpoints Públicos**: No requieren autenticación
2. **Endpoints Protegidos con API Token**: Requieren token Bearer en el header `Authorization`
3. **Endpoints Dashboard KPI**: Requieren autenticación Sanctum (sesión web)

---

## Autenticación

### Obtención de Token API

1. Inicie sesión en el sistema web
2. Navegue a **Settings > API Tokens**
3. Haga clic en **"Crear Nuevo Token"**
4. Proporcione un nombre descriptivo
5. Establezca fecha de expiración (opcional)
6. Copie el token generado (se muestra solo una vez)

### Uso del Token

Incluya el token en el header de todas las peticiones protegidas:

```http
Authorization: Bearer {tu_token}
```

### Ejemplo con cURL

```bash
curl -X GET "https://tu-dominio.com/api/user" \
  -H "Authorization: Bearer tu_token_aqui" \
  -H "Content-Type: application/json"
```

---

## Endpoints Públicos

### 1. Verificar Estado de la API

Verifica que los servicios API estén funcionando correctamente.

**Endpoint**: `GET /api/status`

**Autenticación**: No requerida

**Respuesta Exitosa** (200):
```json
{
    "status": "API funcionando correctamente",
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "version": "1.0.0"
}
```

**Ejemplo**:
```bash
curl -X GET "http://olo-raga-orders.test/api/status"
```

---

### 2. Crear Orden(es) de Compra

Crea una o múltiples órdenes de compra desde sistemas externos.

**Endpoint**: `POST /api/purchase-orders`

**Autenticación**: No requerida

**Content-Type**: `application/json` o `application/x-www-form-urlencoded`

**Cuerpo de la Petición**:

El cuerpo puede contener:
- Una orden única con estructura `{general: {...}, items: [...]}`
- Múltiples órdenes con estructura `{purchase_orders: [...]}` o `{orders: [...]}`

**Estructura de una Orden**:
```json
{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC",
    "currency": "USD",
    "general": {
        "order_number": "PO-2024-001",
        "trading_company": "Company ABC",
        "currency": "USD",
        "vendor_id": "VENDOR001",
        "vendor": "Vendor Name",
        "net_total": 5000.00,
        "date_theorical_load": "2024-02-15",
        "date_etd": "2024-02-20",
        "date_eta": "2024-03-10",
        "shipping_line": "Maersk",
        "departure_port": "Shanghai",
        "arrival_port": "Los Angeles",
        "route_label": "Asia-USA",
        "incoterms": "FOB",
        "logistics_incoterm": "CIF",
        "price_incoterm": "EXW",
        "category": "Electronics",
        "reason": "Restock",
        "mode": "Sea",
        "container_type": "40HC",
        "container_number": "CONT123456",
        "mbl_number": "MBL789012",
        "factory_proforma_number": "PROF345678",
        "weight_kg": 1500.00,
        "cbm": 25.5,
        "pallet_quantity": 10,
        "is_dropship": false,
        "applies_tlc": true,
        "date_booking_request": "2024-01-20",
        "date_etd_initial": "2024-02-20",
        "date_eta_initial": "2024-03-10"
    },
    "items": [
        {
            "material": "MAT001",
            "peso_kg": 500.00,
            "price_per_unit": 10.50
        },
        {
            "material": "MAT002",
            "peso_kg": 1000.00,
            "price_per_unit": 15.75
        }
    ]
}
```

**Ejemplo con Múltiples Órdenes**:
```json
{
    "purchase_orders": [
        {
            "general": {...},
            "items": [...]
        },
        {
            "general": {...},
            "items": [...]
        }
    ]
}
```

**Respuesta Exitosa** (201):
```json
{
    "success": true,
    "message": "Purchase orders created successfully",
    "data": [
        {
            "order_number": "PO-2024-001",
            "id": 123,
            "status": "success"
        }
    ]
}
```

**Errores**:
- `422`: Error de validación
- `400`: Error del servidor

**Ejemplo**:
```bash
curl -X POST "http://olo-raga-orders.test/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC",
    "currency": "USD",
    "general": {
        "order_number": "PO-2024-001",
        "trading_company": "Company ABC",
        "currency": "USD"
    },
    "items": []
}'
```

---

### 3. Actualizar Orden de Compra

Actualiza una orden de compra existente utilizando su `order_number`.

**Endpoint**: `PUT /api/purchase-orders/{po_id}`

**Autenticación**: No requerida

**Parámetros de Ruta**:
- `po_id` (string, requerido): Número de orden de compra (`order_number`)

**Headers Opcionales**:
- `Idempotency-Key`: Clave para garantizar idempotencia (opcional)

**Cuerpo de la Petición**:
Puede contener cualquier campo de la orden de compra que se desee actualizar.

**Ejemplo**:
```json
{
    "date_etd": "2024-02-25",
    "date_eta": "2024-03-15",
    "status": "approved",
    "shipping_line": "COSCO",
    "container_number": "CONT999888"
}
```

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "message": "Purchase order updated successfully",
    "data": {
        "id": 123,
        "order_number": "PO-2024-001",
        "status": "approved",
        "updated_at": "2024-01-20T10:30:00.000000Z",
        "changes": {
            "date_etd": {
                "old": "2024-02-20",
                "new": "2024-02-25"
            },
            "shipping_line": {
                "old": "Maersk",
                "new": "COSCO"
            }
        }
    }
}
```

**Errores**:
- `404`: Orden no encontrada
- `409`: Orden en estado no editable (shipped, delivered, cancelled)
- `422`: Error de validación o sin cambios
- `400`: Error del servidor

**Ejemplo**:
```bash
curl -X PUT "http://olo-raga-orders.test/api/purchase-orders/PO-2024-001" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: unique-key-123" \
  -d '{
    "date_etd": "2024-02-25",
    "status": "approved"
}'
```

---

### 4. Cancelar Orden de Compra

Cancela o elimina una orden de compra existente.

**Endpoint**: `DELETE /api/purchase-orders/cancel`

**Autenticación**: No requerida

**Cuerpo de la Petición**:
```json
{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC"
}
```

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "message": "Orden de compra anulada con éxito."
}
```

**Errores**:
- `404`: Orden no encontrada

**Ejemplo**:
```bash
curl -X DELETE "http://olo-raga-orders.test/api/purchase-orders/cancel" \
  -H "Content-Type: application/json" \
  -d '{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC"
}'
```

---

### 5. Eliminar Orden de Compra (Soft Delete)

Elimina una orden de compra (soft delete) utilizando su número de orden y trading company.

**Endpoint**: `DELETE /api/purchase-orders`

**Autenticación**: No requerida

**Cuerpo de la Petición**:
```json
{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC"
}
```

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "message": "Purchase order deleted successfully."
}
```

**Errores**:
- `404`: Orden no encontrada

---

### 6. Listar Órdenes de Compra

Obtiene una lista de órdenes de compra. Puede filtrarse por `order_number` y/o `company` (trading_company).

**Endpoint**: `GET /api/purchase-orders`

**Autenticación**: No requerida

**Parámetros de Query** (opcionales):
- `order_number` (string): Filtrar por número de orden
- `company` (string): Filtrar por trading company

**Respuesta Exitosa** (200):
```json
{
    "message": "Purchase orders fetched successfully",
    "data": [
        {
            "id": 123,
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "status": "pending",
            "vendor": {...},
            "products": [...]
        }
    ],
    "filters_applied": {
        "order_number": "PO-2024-001",
        "company": null
    },
    "deleted_info": null
}
```

**Ejemplo**:
```bash
curl -X GET "http://olo-raga-orders.test/api/purchase-orders?order_number=PO-2024-001&company=Company%20ABC"
```

**Nota para Business Intelligence**: Para obtener datos desde Next para Business Intelligence, se debe usar el endpoint de listar PO (`GET /api/purchase-orders`), lo que entregará un JSON con el detalle de todas las PO. Este endpoint puede ser utilizado sin autenticación y permite filtrar por `order_number` y/o `company` (trading_company) según sea necesario. La respuesta incluye todos los campos de la orden de compra, incluyendo relaciones con vendor, products, y otros datos asociados.

**Ejemplo para BI**:
```bash
# Obtener todas las POs (sin filtros)
curl -X GET "http://olo-raga-orders.test/api/purchase-orders"

# Obtener POs filtradas por trading company
curl -X GET "http://olo-raga-orders.test/api/purchase-orders?company=Company%20ABC"
```

---

### 7. Buscar Órdenes de Compra

Busca órdenes de compra usando los mismos filtros que `GET /api/purchase-orders`.

**Endpoint**: `POST /api/purchase-orders/search`

**Autenticación**: No requerida

**Cuerpo de la Petición** (opcional):
```json
{
    "order_number": "PO-2024-001",
    "company": "Company ABC"
}
```

**Respuesta**: Igual que `GET /api/purchase-orders`

---

### 8. Crear Múltiples Órdenes de Compra (Bulk)

Crea múltiples órdenes de compra en una sola petición. Si una orden ya existe, será rechazada.

**Endpoint**: `POST /api/purchase-orders/bulk`

**Autenticación**: No requerida

**Cuerpo de la Petición**:
```json
{
    "items": [
        {
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "currency": "USD",
            "general": {...},
            "items": [...]
        },
        {
            "order_number": "PO-2024-002",
            "trading_company": "Company ABC",
            "currency": "USD",
            "general": {...},
            "items": [...]
        }
    ]
}
```

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "message": "Bulk create processed",
    "summary": {
        "total": 2,
        "created": 1,
        "already_exists": 1,
        "deleted": 0,
        "failed": 0,
        "validation_error": 0
    },
    "results": [
        {
            "index": 0,
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "status": "created",
            "message": "Purchase order created successfully",
            "id": 123,
            "created_at": "2024-01-15T10:30:00.000000Z"
        },
        {
            "index": 1,
            "order_number": "PO-2024-002",
            "trading_company": "Company ABC",
            "status": "already_exists",
            "message": "Purchase order already exists",
            "id": 124
        }
    ]
}
```

**Estados de Resultado**:
- `created`: Orden creada exitosamente
- `already_exists`: La orden ya existe
- `deleted`: La orden existe pero está eliminada
- `failed`: Error al crear la orden
- `validation_error`: Error de validación

---

### 9. Actualizar Múltiples Órdenes de Compra (Bulk Update)

Actualiza múltiples órdenes de compra en una sola petición. Solo actualiza órdenes existentes, no crea nuevas.

**Endpoint**: `POST /api/purchase-orders/bulk-update`

**Autenticación**: No requerida

**Cuerpo de la Petición**:
```json
{
    "items": [
        {
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "date_etd": "2024-02-25",
            "status": "approved"
        },
        {
            "order_number": "PO-2024-002",
            "trading_company": "Company ABC",
            "shipping_line": "COSCO"
        }
    ]
}
```

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "message": "Bulk update processed",
    "summary": {
        "total": 2,
        "updated": 2,
        "not_found": 0,
        "deleted": 0,
        "skipped": 0,
        "validation_error": 0,
        "error": 0
    },
    "results": [
        {
            "index": 0,
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "status": "updated",
            "updated_at": "2024-01-20T10:30:00.000000Z",
            "changes": {...}
        }
    ]
}
```

**Estados de Resultado**:
- `updated`: Orden actualizada exitosamente
- `not_found`: Orden no encontrada
- `deleted`: La orden existe pero está eliminada
- `skipped`: No se proporcionaron campos para actualizar
- `validation_error`: Error de validación
- `error`: Error del servidor

---

## Endpoints Protegidos con API Token

Todos estos endpoints requieren el header `Authorization: Bearer {token}`.

### 10. Obtener Información del Usuario

Obtiene información del usuario autenticado y su compañía.

**Endpoint**: `GET /api/user`

**Autenticación**: Requerida (API Token)

**Respuesta Exitosa** (200):
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
        "name": "Mi Empresa",
        ...
    }
}
```

**Errores**:
- `401`: No autenticado

---

### 11. Obtener Órdenes de Compra del Usuario

Obtiene las órdenes de compra de la empresa del usuario autenticado.

**Endpoint**: `GET /api/my-purchase-orders`

**Autenticación**: Requerida (API Token)

**Parámetros de Query** (opcionales):
- `page` (integer): Número de página (paginación)
- `per_page` (integer): Elementos por página

**Respuesta Exitosa** (200):
```json
{
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 123,
                "order_number": "PO-2024-001",
                "vendor": {...},
                "products": [...]
            }
        ],
        "per_page": 10,
        "total": 50
    },
    "user": "Juan Pérez",
    "company": "Mi Empresa"
}
```

---

### 12. Crear Orden de Compra Simple

Crea una nueva orden de compra simple para el usuario autenticado.

**Endpoint**: `POST /api/my-purchase-orders`

**Autenticación**: Requerida (API Token)

**Cuerpo de la Petición**:
```json
{
    "vendor_id": 1,
    "description": "Orden de compra de prueba",
    "total_amount": 2500.00
}
```

**Validaciones**:
- `vendor_id`: Requerido, debe existir en la tabla `vendors`
- `description`: Requerido, string, máximo 255 caracteres
- `total_amount`: Requerido, numérico, mínimo 0

**Respuesta Exitosa** (201):
```json
{
    "message": "Orden de compra creada exitosamente",
    "data": {
        "vendor_id": 1,
        "description": "Orden de compra de prueba",
        "total_amount": 2500.00
    },
    "created_by": "Juan Pérez"
}
```

**Errores**:
- `422`: Error de validación
- `401`: No autenticado

---

### 13. Obtener Estadísticas del Dashboard

Obtiene estadísticas resumidas de órdenes de compra para el usuario autenticado.

**Endpoint**: `GET /api/dashboard-stats`

**Autenticación**: Requerida (API Token)

**Respuesta Exitosa** (200):
```json
{
    "stats": {
        "total_orders": 100,
        "pending_orders": 25,
        "completed_orders": 75
    },
    "user_info": {
        "name": "Juan Pérez",
        "email": "juan@empresa.com",
        "company": "Mi Empresa"
    }
}
```

---

### 14. Actualizar Perfil del Usuario

Actualiza la información del perfil del usuario autenticado.

**Endpoint**: `PUT /api/profile`

**Autenticación**: Requerida (API Token)

**Cuerpo de la Petición** (todos los campos son opcionales):
```json
{
    "name": "Juan Pérez",
    "email": "juan@empresa.com",
    "phone": "+506 8888-8888"
}
```

**Validaciones**:
- `name`: String, máximo 255 caracteres
- `email`: Email válido, único en la tabla `users`
- `phone`: String, máximo 20 caracteres

**Respuesta Exitosa** (200):
```json
{
    "message": "Perfil actualizado exitosamente",
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@empresa.com",
        "phone": "+506 8888-8888"
    }
}
```

**Errores**:
- `422`: Error de validación
- `401`: No autenticado

---

## Endpoints Dashboard KPI

Todos estos endpoints requieren autenticación Sanctum (sesión web) y están bajo el prefijo `/api/dashboard-kpi`.

### 15. Obtener Datos Generales del Dashboard

Obtiene todos los datos del dashboard KPI con filtros opcionales.

**Endpoint**: `GET /api/dashboard-kpi`

**Autenticación**: Requerida (Sanctum)

**Parámetros de Query** (opcionales):
- `date_from` (date): Fecha de inicio
- `date_to` (date): Fecha de fin
- `trading_company` (string): Filtrar por trading company
- `vendor_id` (integer): Filtrar por ID de proveedor
- `shipping_line` (string): Filtrar por naviera
- `service_provider` (string): Filtrar por proveedor de servicios
- `departure_port` (string): Filtrar por puerto de salida
- `arrival_port` (string): Filtrar por puerto de llegada
- `route_label` (string): Filtrar por ruta logística
- `stage` (integer): Filtrar por etapa
- `order_number` (string): Filtrar por número de orden

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "data": {
        ...
    }
}
```

---

### 16. POs por Etapa

Obtiene la cantidad de Purchase Orders agrupadas por etapa.

**Endpoint**: `GET /api/dashboard-kpi/pos-by-stage`

**Autenticación**: Requerida (Sanctum)

**Parámetros de Query**: Mismos filtros que el endpoint anterior

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "data": {
        "total_pos": 150,
        "by_stage": [
            {
                "stage_id": 1,
                "stage_name": "Recepción",
                "count": 25
            },
            ...
        ]
    }
}
```

---

### 17. POs con Retraso según Carga Lista

Obtiene Purchase Orders con retraso según la fecha de carga lista.

**Endpoint**: `GET /api/dashboard-kpi/pos-delay-cl`

**Autenticación**: Requerida (Sanctum)

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_pos": 30,
            "total_delay_days": 150
        },
        "details": [...]
    }
}
```

---

### 18. POs con Adelanto según Carga Lista

Obtiene Purchase Orders con adelanto según la fecha de carga lista.

**Endpoint**: `GET /api/dashboard-kpi/pos-advance-cl`

**Autenticación**: Requerida (Sanctum)

---

### 19. Capacidad (Allocation)

Obtiene datos de capacidad y asignación.

**Endpoint**: `GET /api/dashboard-kpi/capacity`

**Autenticación**: Requerida (Sanctum)

---

### 20. POs en Puerto de Transbordo

Obtiene Purchase Orders en puerto de transbordo.

**Endpoint**: `GET /api/dashboard-kpi/transshipment`

**Autenticación**: Requerida (Sanctum)

---

### 21. POs con ATA

Obtiene Purchase Orders con ATA (Actual Time of Arrival).

**Endpoint**: `GET /api/dashboard-kpi/pos-with-ata`

**Autenticación**: Requerida (Sanctum)

---

### 22. Tiempo de Tránsito

Obtiene datos de tiempo de tránsito.

**Endpoint**: `GET /api/dashboard-kpi/transit-time`

**Autenticación**: Requerida (Sanctum)

---

### 23. Comparativo de POs con ATD

Compara Purchase Orders con ATD (Actual Time of Departure) entre dos períodos.

**Endpoint**: `POST /api/dashboard-kpi/compare-atd`

**Autenticación**: Requerida (Sanctum)

**Cuerpo de la Petición**:
```json
{
    "period1_start": "2024-01-01",
    "period1_end": "2024-03-31",
    "period2_start": "2024-04-01",
    "period2_end": "2024-06-30"
}
```

**Validaciones**:
- `period1_start`: Requerido, fecha
- `period1_end`: Requerido, fecha
- `period2_start`: Requerido, fecha
- `period2_end`: Requerido, fecha

**Respuesta Exitosa** (200):
```json
{
    "success": true,
    "data": {
        "period1": {...},
        "period2": {...},
        "comparison": {...}
    }
}
```

---

### 24. Comparativo de POs con ATA

Compara Purchase Orders con ATA entre dos períodos.

**Endpoint**: `POST /api/dashboard-kpi/compare-ata`

**Autenticación**: Requerida (Sanctum)

**Cuerpo de la Petición**: Igual que `compare-atd`

---

### 25. Comparativo de POs con Retraso CL

Compara Purchase Orders con retraso según Carga Lista entre dos períodos.

**Endpoint**: `POST /api/dashboard-kpi/compare-delay-cl`

**Autenticación**: Requerida (Sanctum)

**Cuerpo de la Petición**: Igual que `compare-atd`

---

### 26. Comparativo de POs con Adelanto CL

Compara Purchase Orders con adelanto según Carga Lista entre dos períodos.

**Endpoint**: `POST /api/dashboard-kpi/compare-advance-cl`

**Autenticación**: Requerida (Sanctum)

**Cuerpo de la Petición**: Igual que `compare-atd`

---

### 27. PO vs TEUs por Etapa

Obtiene comparación de Purchase Orders vs TEUs agrupados por etapa.

**Endpoint**: `GET /api/dashboard-kpi/po-vs-teus/stage`

**Autenticación**: Requerida (Sanctum)

---

### 28. PO vs TEUs por Período

Obtiene comparación de Purchase Orders vs TEUs agrupados por período.

**Endpoint**: `GET /api/dashboard-kpi/po-vs-teus/period`

**Autenticación**: Requerida (Sanctum)

---

### 29. PO vs TEUs por Proveedor

Obtiene comparación de Purchase Orders vs TEUs agrupados por proveedor.

**Endpoint**: `GET /api/dashboard-kpi/po-vs-teus/vendor`

**Autenticación**: Requerida (Sanctum)

---

### 30. PO vs TEUs por Naviera

Obtiene comparación de Purchase Orders vs TEUs agrupados por naviera.

**Endpoint**: `GET /api/dashboard-kpi/po-vs-teus/shipping-line`

**Autenticación**: Requerida (Sanctum)

---

### 31. Llegadas Futuras

Obtiene proyección de llegadas futuras.

**Endpoint**: `GET /api/dashboard-kpi/future-arrivals`

**Autenticación**: Requerida (Sanctum)

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Petición exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Error en la petición |
| 401 | Unauthorized - No autenticado o token inválido |
| 404 | Not Found - Recurso no encontrado |
| 409 | Conflict - Conflicto (ej: orden en estado no editable) |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Manejo de Errores

### Formato de Error Estándar

```json
{
    "success": false,
    "message": "Descripción del error",
    "error_code": "ERROR_CODE",
    "errors": {
        "campo": ["Mensaje de error específico"]
    }
}
```

### Ejemplos de Errores

**Error de Validación (422)**:
```json
{
    "success": false,
    "message": "Validación fallida.",
    "errors": {
        "order_number": ["El campo \"P.O.\" es obligatorio."],
        "trading_company": ["El campo \"Compañía\" es obligatorio."]
    },
    "data": null
}
```

**Error de Recurso No Encontrado (404)**:
```json
{
    "success": false,
    "message": "Purchase order not found",
    "error_code": "PO_NOT_FOUND"
}
```

**Error de Estado No Editable (409)**:
```json
{
    "success": false,
    "message": "No se puede actualizar una orden en estado: shipped",
    "error_code": "STATUS_NOT_EDITABLE"
}
```

---

## Ejemplos de Uso

### Ejemplo Completo: Crear y Actualizar una Orden

```bash
# 1. Crear una orden
curl -X POST "http://olo-raga-orders.test/api/purchase-orders" \
  -H "Content-Type: application/json" \
  -d '{
    "order_number": "PO-2024-001",
    "trading_company": "Company ABC",
    "currency": "USD",
    "general": {
        "order_number": "PO-2024-001",
        "trading_company": "Company ABC",
        "currency": "USD",
        "vendor": "Vendor Name",
        "net_total": 5000.00,
        "date_theorical_load": "2024-02-15",
        "date_etd": "2024-02-20",
        "date_eta": "2024-03-10"
    },
    "items": []
}'

# 2. Actualizar la orden
curl -X PUT "http://olo-raga-orders.test/api/purchase-orders/PO-2024-001" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: update-123" \
  -d '{
    "date_etd": "2024-02-25",
    "status": "approved"
}'

# 3. Consultar la orden
curl -X GET "http://olo-raga-orders.test/api/purchase-orders?order_number=PO-2024-001"
```

### Ejemplo: Uso con Autenticación

```bash
# Obtener información del usuario
curl -X GET "http://olo-raga-orders.test/api/user" \
  -H "Authorization: Bearer tu_token_aqui"

# Obtener órdenes del usuario
curl -X GET "http://olo-raga-orders.test/api/my-purchase-orders?page=1&per_page=10" \
  -H "Authorization: Bearer tu_token_aqui"
```

### Ejemplo: Bulk Operations

```bash
# Crear múltiples órdenes
curl -X POST "http://olo-raga-orders.test/api/purchase-orders/bulk" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
        {
            "order_number": "PO-2024-001",
            "trading_company": "Company ABC",
            "currency": "USD",
            "general": {...}
        },
        {
            "order_number": "PO-2024-002",
            "trading_company": "Company ABC",
            "currency": "USD",
            "general": {...}
        }
    ]
}'
```

---

## Notas Adicionales

### Campos Importantes de Purchase Orders

Algunos campos importantes que pueden ser enviados en las peticiones:

**Campos de Texto**:
- `order_number`, `trading_company`, `vendor_id`, `vendor_number`, `shipping_line`, `service_provider`, `departure_port`, `arrival_port`, `route_label`, `container_type`, `container_number`, `mbl_number`, `factory_proforma_number`, `category`, `reason`, `incoterms`, `logistics_incoterm`, `price_incoterm`, `case_number_file`, `tracking_id`, `invoice`, `factura_merca`, `customs_dua`, `receipt_note`, `visibility_notes`, `consolidator_name`, `forwarder_name`, `cargo_invoice_number`, `tariff_type`, `arrival_status`, `retail_group`, `customer_type`, `insurance_type`

**Campos Numéricos**:
- `net_total`, `total`, `Invoice_amount`, `freight_amount`, `other_expenses`, `total_amount`, `estimated_pallet_cost`, `real_cost_estimated_po`, `real_cost_real_po`, `weight_kg`, `weight_lb`, `cbm`, `pallet_quantity`, `pallet_quantity_real`, `delay_days`, `container_free_days`, `etd_dates_difference`, `eta_dates_difference`, `length_cm`, `width_cm`, `height_cm`

**Campos de Fecha**:
- `date_booking_request`, `date_booking_authorized`, `date_theorical_load`, `date_variable_date`, `carga_lista_validada`, `date_received`, `date_etd_initial`, `date_etd_updated`, `date_eta_updated`, `date_eta_initial`, `date_etd`, `date_atd`, `date_eta`, `date_ata`, `date_estimated_hub_arrival`, `date_actual_hub_arrival`, `inspection_date`, `vgm_cut_date`, `balance_payment_date`, `local_charges_payment_date`, `bonded_warehouse_enter`, `bonded_warehouse_exit`, `receipt_note_date`, `estimated_dc_availability_date`, `date_invoice_received`, `date_vendor_document_received`, `dif_load_date`, `emision_date_po`, `forwader_date`, `date_consolidation`, `release_date`, `date_required_in_destination`

**Campos Booleanos**:
- `is_dropship`, `applies_tlc`, `applies_af`, `port_of_loading_validated`, `has_facture_merca`, `uses_bonded_warehouse`, `apply_technical_note`, `etd_initial_validated`, `used_rate_ok`

### Idempotencia

Para operaciones de actualización, puedes usar el header `Idempotency-Key` para garantizar que una petición no se procese múltiples veces. Si envías la misma clave en un período de 1 hora, recibirás la misma respuesta sin procesar la petición nuevamente.

### Webhooks

Cuando se crean o actualizan órdenes de compra, el sistema puede disparar eventos de webhook si están configurados. Los eventos disponibles son:
- `purchase_order.created`
- `purchase_order.updated`

---

**Última actualización**: Enero 2024  
**Versión de API**: 1.0.0



