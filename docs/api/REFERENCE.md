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

### 1. Gestión de Órdenes de Compra (Público)

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

#### POST /api/purchase-orders

Crea una o múltiples órdenes de compra desde sistemas externos. El cuerpo de la petición debe contener los campos detallados en la sección [Campos de la API](#campos-de-la-api).

**Autenticación**: No requerida  
**Content-Type**: `application/json` o `application/x-www-form-urlencoded`

#### PUT /api/purchase-orders/{po_id}

Actualiza una orden de compra existente utilizando su `po_id`. El cuerpo de la petición puede contener cualquiera de los campos detallados en la sección [Campos de la API](#campos-de-la-api) para ser actualizados.

**Autenticación**: No requerida  
**Content-Type**: `application/json`

**Parámetros de URL**:
- `po_id` (integer) - ID de la orden de compra a actualizar (requerido).

#### DELETE /api/purchase-orders/cancel/{order_number}

Cancela o elimina una orden de compra existente utilizando su número de orden.

**Autenticación**: No requerida

**Parámetros de URL**:
- `order_number` (string) - Número de la orden de compra a cancelar (requerido).

### 2. Información del Usuario

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

### 3. Gestión de Órdenes del Usuario

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

### 4. Estadísticas del Dashboard

#### GET /api/dashboard-stats

Obtiene estadísticas resumidas de órdenes de compra para el usuario autenticado.

**Autenticación**: Token requerido

### 5. Actualización de Perfil

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

## Ejemplos de Código y Clientes

Esta sección proporciona clientes base y ejemplos de uso en varios lenguajes de programación para facilitar la integración con la API.

### JavaScript/Node.js

#### Cliente Base

```javascript
const axios = require('axios');

class OLOClient {
    constructor(baseURL, token = null) {
        this.api = axios.create({
            baseURL,
            timeout: 30000,
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

    async getUserInfo() {
        try {
            const response = await this.api.get('/user');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async getMyPurchaseOrders(page = 1, perPage = 10) {
        try {
            const response = await this.api.get('/my-purchase-orders', {
                params: { page, per_page: perPage }
            });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async getDashboardStats() {
        try {
            const response = await this.api.get('/dashboard-stats');
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async updateProfile(profileData) {
        try {
            const response = await this.api.put('/profile', profileData);
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    }

    async getStatus() {
        try {
            const response = await this.api.get('/status');
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
                errors: error.response.data.errors || null,
                data: error.response.data.data || null
            };
        } else if (error.request) {
            return {
                status: 0,
                message: 'Error de conexión',
                errors: null,
                data: null
            };
        } else {
            return {
                status: -1,
                message: error.message,
                errors: null,
                data: null
            };
        }
    }
}

module.exports = OLOClient;
```

#### Ejemplo de Uso Básico

```javascript
const OLOClient = require('./olo-client');

async function createSimpleOrder() {
    const client = new OLOClient('https://su-dominio.com/api');
    
    const orderData = {
        order_number: 'PO-2024-001',
        category: 'Electronics',
        factory_proforma_number: 'PRF-001',
        route_label: 'RUTA-A',
        date_theorical_load: '2024-01-20',
        bonded_warehouse_enter: '2024-01-15',
        bonded_warehouse_exit: '2024-01-25',
        reason: 'Stock replenishment',
        incoterms: 'FOB',
        logistics_incoterm: 'FOB',
        price_incoterm: 'FOB',
        vendor_name: 'Proveedor ABC',
        net_total: 2500.00,
        currency: 'USD',
        mode: 'SEA',
        items: [
            {
                material: 'MAT-001',
                price_per_unit: 25.50,
                peso_kg: 100.0
            }
        ]
    };

    try {
        const result = await client.createPurchaseOrder(orderData);
        console.log('Orden creada exitosamente:', result);
        return result;
    } catch (error) {
        console.error('Error al crear orden:', error);
        throw error;
    }
}

createSimpleOrder();
```

#### Cliente con Reintentos

```javascript
class OLOClientWithRetry extends OLOClient {
    constructor(baseURL, token = null, maxRetries = 3) {
        super(baseURL, token);
        this.maxRetries = maxRetries;
    }

    async createPurchaseOrderWithRetry(orderData) {
        let lastError;
        
        for (let attempt = 1; attempt <= this.maxRetries; attempt++) {
            try {
                const result = await this.createPurchaseOrder(orderData);
                console.log(`Orden creada exitosamente en el intento ${attempt}`);
                return result;
            } catch (error) {
                lastError = error;
                
                // No reintentar errores de validación
                if (error.status === 422) {
                    console.error('Error de validación, no se reintenta:', error);
                    throw error;
                }
                
                // No reintentar errores de autenticación
                if (error.status === 401 || error.status === 403) {
                    console.error('Error de autenticación, no se reintenta:', error);
                    throw error;
                }
                
                if (attempt === this.maxRetries) {
                    console.error(`Falló después de ${this.maxRetries} intentos`);
                    throw lastError;
                }
                
                // Esperar antes del siguiente intento (backoff exponencial)
                const delay = Math.pow(2, attempt - 1) * 1000;
                console.log(`Intento ${attempt} falló, reintentando en ${delay}ms...`);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
        
        throw lastError;
    }
}
```

#### Utilidades

```javascript
class OLOUtils {
    static validateOrderData(orderData) {
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
        
        return true;
    }
    
    static formatDate(date) {
        if (date instanceof Date) {
            return date.toISOString().split('T')[0];
        }
        return date;
    }
    
    static formatDateTime(date) {
        if (date instanceof Date) {
            return date.toISOString().replace('T', ' ').split('.')[0];
        }
        return date;
    }
    
    static calculateTotalWeight(items) {
        return items.reduce((total, item) => total + (parseFloat(item.peso_kg) || 0), 0);
    }
    
    static generateOrderNumber(prefix = 'PO') {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 1000);
        return `${prefix}-${timestamp}-${random}`;
    }
}
```

---

### Python

#### Cliente Base

```python
import requests
import json
from datetime import datetime
from typing import Dict, List, Optional, Union
import time

class OLOClient:
    def __init__(self, base_url: str, token: Optional[str] = None):
        self.base_url = base_url.rstrip('/')
        self.session = requests.Session()
        self.session.headers.update({
            'Content-Type': 'application/json',
            'User-Agent': 'OLO-Python-Client/1.0'
        })
        
        if token:
            self.session.headers['Authorization'] = f'Bearer {token}'
    
    def _make_request(self, method: str, endpoint: str, **kwargs) -> Dict:
        """Realizar petición HTTP con manejo de errores"""
        url = f"{self.base_url}{endpoint}"
        
        try:
            response = self.session.request(method, url, **kwargs)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.HTTPError as e:
            error_data = {}
            try:
                error_data = response.json()
            except:
                error_data = {'message': str(e)}
            
            raise OLOAPIError(
                status_code=response.status_code,
                message=error_data.get('message', 'Error HTTP'),
                errors=error_data.get('errors'),
                data=error_data.get('data')
            )
        except requests.exceptions.RequestException as e:
            raise OLOAPIError(
                status_code=0,
                message=f'Error de conexión: {str(e)}',
                errors=None,
                data=None
            )
    
    def create_purchase_order(self, order_data: Union[Dict, List[Dict]]) -> Dict:
        """Crear una o múltiples órdenes de compra"""
        return self._make_request('POST', '/purchase-orders', json=order_data)
    
    def get_user_info(self) -> Dict:
        """Obtener información del usuario (requiere token)"""
        return self._make_request('GET', '/user')
    
    def get_my_purchase_orders(self, page: int = 1, per_page: int = 10) -> Dict:
        """Obtener órdenes del usuario (requiere token)"""
        params = {'page': page, 'per_page': per_page}
        return self._make_request('GET', '/my-purchase-orders', params=params)
    
    def get_dashboard_stats(self) -> Dict:
        """Obtener estadísticas del dashboard (requiere token)"""
        return self._make_request('GET', '/dashboard-stats')
    
    def update_profile(self, profile_data: Dict) -> Dict:
        """Actualizar perfil del usuario (requiere token)"""
        return self._make_request('PUT', '/profile', json=profile_data)
    
    def get_status(self) -> Dict:
        """Verificar estado de la API"""
        return self._make_request('GET', '/status')

class OLOAPIError(Exception):
    """Excepción personalizada para errores de la API OLO"""
    def __init__(self, status_code: int, message: str, errors: Optional[Dict] = None, data: Optional[Dict] = None):
        self.status_code = status_code
        self.message = message
        self.errors = errors
        self.data = data
        super().__init__(f"[{status_code}] {message}")
```

#### Ejemplo de Uso Básico

```python
def create_simple_order():
    client = OLOClient('https://su-dominio.com/api')
    
    order_data = {
        'order_number': 'PO-2024-001',
        'category': 'Electronics',
        'factory_proforma_number': 'PRF-001',
        'route_label': 'RUTA-A',
        'date_theorical_load': '2024-01-20',
        'bonded_warehouse_enter': '2024-01-15',
        'bonded_warehouse_exit': '2024-01-25',
        'reason': 'Stock replenishment',
        'incoterms': 'FOB',
        'logistics_incoterm': 'FOB',
        'price_incoterm': 'FOB',
        'vendor_name': 'Proveedor ABC',
        'net_total': 2500.00,
        'currency': 'USD',
        'mode': 'SEA',
        'items': [
            {
                'material': 'MAT-001',
                'price_per_unit': 25.50,
                'peso_kg': 100.0
            }
        ]
    }
    
    try:
        result = client.create_purchase_order(order_data)
        print('Orden creada exitosamente:', result)
        return result
    except OLOAPIError as e:
        print(f'Error al crear orden: {e.message}')
        if e.errors:
            print('Errores de validación:', e.errors)
        raise

if __name__ == '__main__':
    create_simple_order()
```

#### Cliente con Reintentos

```python
class OLOClientWithRetry(OLOClient):
    def __init__(self, base_url: str, token: Optional[str] = None, max_retries: int = 3):
        super().__init__(base_url, token)
        self.max_retries = max_retries
    
    def create_purchase_order_with_retry(self, order_data: Union[Dict, List[Dict]]) -> Dict:
        """Crear orden con reintentos automáticos"""
        last_error = None
        
        for attempt in range(1, self.max_retries + 1):
            try:
                result = self.create_purchase_order(order_data)
                print(f'Orden creada exitosamente en el intento {attempt}')
                return result
            except OLOAPIError as e:
                last_error = e
                
                # No reintentar errores de validación
                if e.status_code == 422:
                    print(f'Error de validación, no se reintenta: {e.message}')
                    raise e
                
                # No reintentar errores de autenticación
                if e.status_code in [401, 403]:
                    print(f'Error de autenticación, no se reintenta: {e.message}')
                    raise e
                
                if attempt == self.max_retries:
                    print(f'Falló después de {self.max_retries} intentos')
                    raise last_error
                
                # Esperar antes del siguiente intento (backoff exponencial)
                delay = (2 ** (attempt - 1)) * 1000
                print(f'Intento {attempt} falló, reintentando en {delay}ms...')
                time.sleep(delay / 1000)
        
        raise last_error
```

#### Utilidades

```python
class OLOUtils:
    @staticmethod
    def validate_order_data(order_data: Dict) -> bool:
        """Validar datos de orden antes del envío"""
        required_fields = [
            'order_number', 'category', 'factory_proforma_number',
            'route_label', 'date_theorical_load', 'bonded_warehouse_enter',
            'bonded_warehouse_exit', 'reason', 'incoterms',
            'logistics_incoterm', 'price_incoterm'
        ]
        
        missing_fields = [field for field in required_fields if not order_data.get(field)]
        
        if missing_fields:
            raise ValueError(f'Campos obligatorios faltantes: {", ".join(missing_fields)}')
        
        # Validar al menos un campo de proveedor
        vendor_fields = ['vendor_id', 'vendor', 'vendor_name']
        has_vendor = any(order_data.get(field) for field in vendor_fields)
        
        if not has_vendor:
            raise ValueError('Debe proporcionar al menos un campo de proveedor')
        
        return True
    
    @staticmethod
    def format_date(date: Union[str, datetime]) -> str:
        """Formatear fecha para la API"""
        if isinstance(date, datetime):
            return date.strftime('%Y-%m-%d')
        return date
    
    @staticmethod
    def format_datetime(date: Union[str, datetime]) -> str:
        """Formatear datetime para la API"""
        if isinstance(date, datetime):
            return date.strftime('%Y-%m-%d %H:%M:%S')
        return date
    
    @staticmethod
    def calculate_total_weight(items: List[Dict]) -> float:
        """Calcular peso total de items"""
        return sum(float(item.get('peso_kg', 0)) for item in items)
    
    @staticmethod
    def generate_order_number(prefix: str = 'PO') -> str:
        """Generar número de orden único"""
        timestamp = int(time.time() * 1000)
        random_num = int(time.time() * 1000) % 1000
        return f'{prefix}-{timestamp}-{random_num}'
```

---

### PHP

#### Cliente Base

```php
<?php

class OLOClient {
    private $baseUrl;
    private $headers;
    
    public function __construct($baseUrl, $token = null) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = ['Content-Type: application/json'];
        
        if ($token) {
            $this->headers[] = "Authorization: Bearer $token";
        }
    }
    
    public function createPurchaseOrder($orderData) {
        $options = [
            'http' => [
                'header' => implode("\r\n", $this->headers),
                'method' => 'POST',
                'content' => json_encode($orderData)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents(
            $this->baseUrl . '/purchase-orders',
            false,
            $context
        );
        
        if ($result === false) {
            throw new Exception('Error de conexión');
        }
        
        $response = json_decode($result, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar respuesta JSON');
        }
        
        return $response;
    }
    
    public function getUserInfo() {
        return $this->makeRequest('GET', '/user');
    }
    
    public function getMyPurchaseOrders($page = 1, $perPage = 10) {
        $params = http_build_query(['page' => $page, 'per_page' => $perPage]);
        return $this->makeRequest('GET', '/my-purchase-orders?' . $params);
    }
    
    public function getDashboardStats() {
        return $this->makeRequest('GET', '/dashboard-stats');
    }
    
    public function updateProfile($profileData) {
        return $this->makeRequest('PUT', '/profile', $profileData);
    }
    
    public function getStatus() {
        return $this->makeRequest('GET', '/status');
    }
    
    private function makeRequest($method, $endpoint, $data = null) {
        $options = [
            'http' => [
                'header' => implode("\r\n", $this->headers),
                'method' => $method
            ]
        ];
        
        if ($data !== null) {
            $options['http']['content'] = json_encode($data);
        }
        
        $context = stream_context_create($options);
        $result = file_get_contents(
            $this->baseUrl . $endpoint,
            false,
            $context
        );
        
        if ($result === false) {
            throw new Exception('Error de conexión');
        }
        
        $response = json_decode($result, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar respuesta JSON');
        }
        
        return $response;
    }
}

// Uso
$client = new OLOClient('https://su-dominio.com/api', 'tu_token');

$orderData = [
    'order_number' => 'PO-2024-001',
    'category' => 'Electronics',
    'factory_proforma_number' => 'PRF-001',
    'route_label' => 'RUTA-A',
    'date_theorical_load' => '2024-01-20',
    'bonded_warehouse_enter' => '2024-01-15',
    'bonded_warehouse_exit' => '2024-01-25',
    'reason' => 'Stock replenishment',
    'incoterms' => 'FOB',
    'logistics_incoterm' => 'FOB',
    'price_incoterm' => 'FOB',
    'vendor_name' => 'Proveedor ABC',
    'net_total' => 2500.00,
    'items' => [
        [
            'material' => 'MAT-001',
            'price_per_unit' => 25.50,
            'peso_kg' => 100.0
        ]
    ]
];

try {
    $result = $client->createPurchaseOrder($orderData);
    echo 'Orden creada: ' . json_encode($result);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
```

---

### Java

#### Cliente Base

```java
import com.fasterxml.jackson.databind.ObjectMapper;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.net.URI;
import java.time.Duration;
import java.util.Map;
import java.util.HashMap;

public class OLOClient {
    private final String baseUrl;
    private final HttpClient httpClient;
    private final ObjectMapper objectMapper;
    private final Map<String, String> headers;
    
    public OLOClient(String baseUrl, String token) {
        this.baseUrl = baseUrl.endsWith("/") ? baseUrl.substring(0, baseUrl.length() - 1) : baseUrl;
        this.httpClient = HttpClient.newBuilder()
            .connectTimeout(Duration.ofSeconds(30))
            .build();
        this.objectMapper = new ObjectMapper();
        this.headers = new HashMap<>();
        this.headers.put("Content-Type", "application/json");
        if (token != null && !token.isEmpty()) {
            this.headers.put("Authorization", "Bearer " + token);
        }
    }
    
    public Map<String, Object> createPurchaseOrder(Map<String, Object> orderData) throws Exception {
        String json = objectMapper.writeValueAsString(orderData);
        
        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(baseUrl + "/purchase-orders"))
            .header("Content-Type", "application/json")
            .header("Authorization", headers.get("Authorization"))
            .POST(HttpRequest.BodyPublishers.ofString(json))
            .build();
        
        HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());
        
        if (response.statusCode() >= 400) {
            throw new RuntimeException("Error HTTP: " + response.statusCode() + " - " + response.body());
        }
        
        return objectMapper.readValue(response.body(), Map.class);
    }
    
    public Map<String, Object> getUserInfo() throws Exception {
        return makeRequest("GET", "/user", null);
    }
    
    public Map<String, Object> getMyPurchaseOrders(int page, int perPage) throws Exception {
        String params = "?page=" + page + "&per_page=" + perPage;
        return makeRequest("GET", "/my-purchase-orders" + params, null);
    }
    
    public Map<String, Object> getDashboardStats() throws Exception {
        return makeRequest("GET", "/dashboard-stats", null);
    }
    
    public Map<String, Object> updateProfile(Map<String, Object> profileData) throws Exception {
        return makeRequest("PUT", "/profile", profileData);
    }
    
    public Map<String, Object> getStatus() throws Exception {
        return makeRequest("GET", "/status", null);
    }
    
    private Map<String, Object> makeRequest(String method, String endpoint, Map<String, Object> data) throws Exception {
        HttpRequest.Builder requestBuilder = HttpRequest.newBuilder()
            .uri(URI.create(baseUrl + endpoint))
            .header("Content-Type", "application/json");
        
        if (headers.containsKey("Authorization")) {
            requestBuilder.header("Authorization", headers.get("Authorization"));
        }
        
        if (data != null) {
            String json = objectMapper.writeValueAsString(data);
            requestBuilder.method(method, HttpRequest.BodyPublishers.ofString(json));
        } else {
            requestBuilder.method(method, HttpRequest.BodyPublishers.noBody());
        }
        
        HttpRequest request = requestBuilder.build();
        HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());
        
        if (response.statusCode() >= 400) {
            throw new RuntimeException("Error HTTP: " + response.statusCode() + " - " + response.body());
        }
        
        return objectMapper.readValue(response.body(), Map.class);
    }
}
```

#### Ejemplo de Uso

```java
import java.util.Map;
import java.util.HashMap;
import java.util.List;
import java.util.ArrayList;

public class Example {
    public static void main(String[] args) {
        try {
            OLOClient client = new OLOClient("https://su-dominio.com/api", "tu_token");
            
            Map<String, Object> orderData = new HashMap<>();
            orderData.put("order_number", "PO-2024-001");
            orderData.put("category", "Electronics");
            // ... (resto de los campos)
            
            Map<String, Object> result = client.createPurchaseOrder(orderData);
            System.out.println("Orden creada: " + result);
            
        } catch (Exception e) {
            System.err.println("Error: " + e.getMessage());
        }
    }
}
```

---

### C#

#### Cliente Base

```csharp
using System;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;
using System.Collections.Generic;

public class OLOClient
{
    private readonly HttpClient httpClient;
    // ... (resto de la implementación)
}
```

#### Ejemplo de Uso

```csharp
class Program
{
    static async Task Main(string[] args)
    {
        using var client = new OLOClient("https://su-dominio.com/api", "tu_token");
        
        try
        {
            var orderData = new { /* ... datos de la orden ... */ };
            var result = await client.CreatePurchaseOrderAsync(orderData);
            Console.WriteLine($"Orden creada: {result}");
        }
        catch (Exception e)
        {
            Console.WriteLine($"Error: {e.Message}");
        }
    }
}
```

---

### Go

#### Cliente Base

```go
package main

import (
    "bytes"
    "encoding/json"
    "fmt"
    "io"
    "net/http"
    "time"
)

type OLOClient struct {
    // ... (implementación del struct)
}

func NewOLOClient(baseURL, token string) *OLOClient {
    // ... (implementación del constructor)
}

func (c *OLOClient) CreatePurchaseOrder(orderData interface{}) (map[string]interface{}, error) {
    return c.makeRequest("POST", "/purchase-orders", orderData)
}

// ... (otros métodos)
```

#### Ejemplo de Uso

```go
package main

import (
    "fmt"
    "log"
)

func main() {
    client := NewOLOClient("https://su-dominio.com/api", "tu_token")
    
    orderData := map[string]interface{}{
        // ... datos de la orden ...
    }
    
    result, err := client.CreatePurchaseOrder(orderData)
    if err != nil {
        log.Fatal("Error:", err)
    }
    
    fmt.Printf("Orden creada: %+v\n", result)
}
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
