# Ejemplos de Código - API de Órdenes de Compra

## JavaScript/Node.js

### Cliente Base

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

### Ejemplo de Uso Básico

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

### Cliente con Reintentos

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

### Utilidades

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

## Python

### Cliente Base

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

### Ejemplo de Uso Básico

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

### Cliente con Reintentos

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

### Utilidades

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

## PHP

### Cliente Base

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

## Java

### Cliente Base

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

### Ejemplo de Uso

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
            orderData.put("factory_proforma_number", "PRF-001");
            orderData.put("route_label", "RUTA-A");
            orderData.put("date_theorical_load", "2024-01-20");
            orderData.put("bonded_warehouse_enter", "2024-01-15");
            orderData.put("bonded_warehouse_exit", "2024-01-25");
            orderData.put("reason", "Stock replenishment");
            orderData.put("incoterms", "FOB");
            orderData.put("logistics_incoterm", "FOB");
            orderData.put("price_incoterm", "FOB");
            orderData.put("vendor_name", "Proveedor ABC");
            orderData.put("net_total", 2500.00);
            
            List<Map<String, Object>> items = new ArrayList<>();
            Map<String, Object> item = new HashMap<>();
            item.put("material", "MAT-001");
            item.put("price_per_unit", 25.50);
            item.put("peso_kg", 100.0);
            items.add(item);
            orderData.put("items", items);
            
            Map<String, Object> result = client.createPurchaseOrder(orderData);
            System.out.println("Orden creada: " + result);
            
        } catch (Exception e) {
            System.err.println("Error: " + e.getMessage());
        }
    }
}
```

---

## C#

### Cliente Base

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
    private readonly string baseUrl;
    private readonly JsonSerializerOptions jsonOptions;

    public OLOClient(string baseUrl, string token = null)
    {
        this.baseUrl = baseUrl.TrimEnd('/');
        this.httpClient = new HttpClient();
        this.httpClient.DefaultRequestHeaders.Add("Content-Type", "application/json");
        
        if (!string.IsNullOrEmpty(token))
        {
            this.httpClient.DefaultRequestHeaders.Add("Authorization", $"Bearer {token}");
        }
        
        this.jsonOptions = new JsonSerializerOptions
        {
            PropertyNamingPolicy = JsonNamingPolicy.CamelCase
        };
    }

    public async Task<Dictionary<string, object>> CreatePurchaseOrderAsync(object orderData)
    {
        var json = JsonSerializer.Serialize(orderData, jsonOptions);
        var content = new StringContent(json, Encoding.UTF8, "application/json");
        
        var response = await httpClient.PostAsync($"{baseUrl}/purchase-orders", content);
        
        if (!response.IsSuccessStatusCode)
        {
            var errorContent = await response.Content.ReadAsStringAsync();
            throw new HttpRequestException($"Error HTTP: {response.StatusCode} - {errorContent}");
        }
        
        var responseContent = await response.Content.ReadAsStringAsync();
        return JsonSerializer.Deserialize<Dictionary<string, object>>(responseContent, jsonOptions);
    }

    public async Task<Dictionary<string, object>> GetUserInfoAsync()
    {
        return await MakeRequestAsync("GET", "/user", null);
    }

    public async Task<Dictionary<string, object>> GetMyPurchaseOrdersAsync(int page = 1, int perPage = 10)
    {
        return await MakeRequestAsync("GET", $"/my-purchase-orders?page={page}&per_page={perPage}", null);
    }

    public async Task<Dictionary<string, object>> GetDashboardStatsAsync()
    {
        return await MakeRequestAsync("GET", "/dashboard-stats", null);
    }

    public async Task<Dictionary<string, object>> UpdateProfileAsync(object profileData)
    {
        return await MakeRequestAsync("PUT", "/profile", profileData);
    }

    public async Task<Dictionary<string, object>> GetStatusAsync()
    {
        return await MakeRequestAsync("GET", "/status", null);
    }

    private async Task<Dictionary<string, object>> MakeRequestAsync(string method, string endpoint, object data)
    {
        var request = new HttpRequestMessage(new HttpMethod(method), $"{baseUrl}{endpoint}");
        
        if (data != null)
        {
            var json = JsonSerializer.Serialize(data, jsonOptions);
            request.Content = new StringContent(json, Encoding.UTF8, "application/json");
        }
        
        var response = await httpClient.SendAsync(request);
        
        if (!response.IsSuccessStatusCode)
        {
            var errorContent = await response.Content.ReadAsStringAsync();
            throw new HttpRequestException($"Error HTTP: {response.StatusCode} - {errorContent}");
        }
        
        var responseContent = await response.Content.ReadAsStringAsync();
        return JsonSerializer.Deserialize<Dictionary<string, object>>(responseContent, jsonOptions);
    }

    public void Dispose()
    {
        httpClient?.Dispose();
    }
}
```

### Ejemplo de Uso

```csharp
using System;
using System.Collections.Generic;
using System.Threading.Tasks;

class Program
{
    static async Task Main(string[] args)
    {
        using var client = new OLOClient("https://su-dominio.com/api", "tu_token");
        
        try
        {
            var orderData = new
            {
                order_number = "PO-2024-001",
                category = "Electronics",
                factory_proforma_number = "PRF-001",
                route_label = "RUTA-A",
                date_theorical_load = "2024-01-20",
                bonded_warehouse_enter = "2024-01-15",
                bonded_warehouse_exit = "2024-01-25",
                reason = "Stock replenishment",
                incoterms = "FOB",
                logistics_incoterm = "FOB",
                price_incoterm = "FOB",
                vendor_name = "Proveedor ABC",
                net_total = 2500.00,
                items = new[]
                {
                    new
                    {
                        material = "MAT-001",
                        price_per_unit = 25.50,
                        peso_kg = 100.0
                    }
                }
            };
            
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

## Go

### Cliente Base

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
    baseURL    string
    httpClient *http.Client
    token      string
}

func NewOLOClient(baseURL, token string) *OLOClient {
    return &OLOClient{
        baseURL: baseURL,
        httpClient: &http.Client{
            Timeout: 30 * time.Second,
        },
        token: token,
    }
}

func (c *OLOClient) CreatePurchaseOrder(orderData interface{}) (map[string]interface{}, error) {
    return c.makeRequest("POST", "/purchase-orders", orderData)
}

func (c *OLOClient) GetUserInfo() (map[string]interface{}, error) {
    return c.makeRequest("GET", "/user", nil)
}

func (c *OLOClient) GetMyPurchaseOrders(page, perPage int) (map[string]interface{}, error) {
    endpoint := fmt.Sprintf("/my-purchase-orders?page=%d&per_page=%d", page, perPage)
    return c.makeRequest("GET", endpoint, nil)
}

func (c *OLOClient) GetDashboardStats() (map[string]interface{}, error) {
    return c.makeRequest("GET", "/dashboard-stats", nil)
}

func (c *OLOClient) UpdateProfile(profileData interface{}) (map[string]interface{}, error) {
    return c.makeRequest("PUT", "/profile", profileData)
}

func (c *OLOClient) GetStatus() (map[string]interface{}, error) {
    return c.makeRequest("GET", "/status", nil)
}

func (c *OLOClient) makeRequest(method, endpoint string, data interface{}) (map[string]interface{}, error) {
    var body io.Reader
    
    if data != nil {
        jsonData, err := json.Marshal(data)
        if err != nil {
            return nil, err
        }
        body = bytes.NewBuffer(jsonData)
    }
    
    req, err := http.NewRequest(method, c.baseURL+endpoint, body)
    if err != nil {
        return nil, err
    }
    
    req.Header.Set("Content-Type", "application/json")
    if c.token != "" {
        req.Header.Set("Authorization", "Bearer "+c.token)
    }
    
    resp, err := c.httpClient.Do(req)
    if err != nil {
        return nil, err
    }
    defer resp.Body.Close()
    
    if resp.StatusCode >= 400 {
        return nil, fmt.Errorf("error HTTP: %d", resp.StatusCode)
    }
    
    var result map[string]interface{}
    if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
        return nil, err
    }
    
    return result, nil
}
```

### Ejemplo de Uso

```go
package main

import (
    "fmt"
    "log"
)

func main() {
    client := NewOLOClient("https://su-dominio.com/api", "tu_token")
    
    orderData := map[string]interface{}{
        "order_number":             "PO-2024-001",
        "category":                 "Electronics",
        "factory_proforma_number":  "PRF-001",
        "route_label":              "RUTA-A",
        "date_theorical_load":      "2024-01-20",
        "bonded_warehouse_enter":   "2024-01-15",
        "bonded_warehouse_exit":    "2024-01-25",
        "reason":                   "Stock replenishment",
        "incoterms":                "FOB",
        "logistics_incoterm":       "FOB",
        "price_incoterm":           "FOB",
        "vendor_name":              "Proveedor ABC",
        "net_total":                2500.00,
        "items": []map[string]interface{}{
            {
                "material":       "MAT-001",
                "price_per_unit": 25.50,
                "peso_kg":        100.0,
            },
        },
    }
    
    result, err := client.CreatePurchaseOrder(orderData)
    if err != nil {
        log.Fatal("Error:", err)
    }
    
    fmt.Printf("Orden creada: %+v\n", result)
}
```

---

*Estos ejemplos proporcionan una base sólida para integrar la API de Órdenes de Compra en diferentes lenguajes de programación. Cada cliente incluye manejo de errores, autenticación y métodos para todos los endpoints disponibles.*
