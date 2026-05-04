# Integración con Power BI - Endpoints de Purchase Orders

Esta guía explica cómo conectar Power BI con los endpoints de Purchase Orders de la API.

## Información General

- **Base URL**: `http://olo-raga-orders.test/api` (desarrollo) o `https://[tu-dominio]/api` (producción)
- **Formato**: JSON
- **Autenticación**: Algunos endpoints requieren token Bearer (ver detalles abajo)

## Endpoints de Purchase Orders

### 1. Verificar Estado de la API

**Endpoint**: `GET /api/status`

**Autenticación**: No requerida

**Power BI - Código M**:
```m
let
    url = "https://tu-dominio.com/api/status",
    response = Web.Contents(url),
    json = Json.Document(response)
in
    json
```

**Respuesta**:
```json
{
    "status": "API funcionando correctamente",
    "timestamp": "2024-01-15T10:30:00.000000Z",
    "version": "1.0.0"
}
```

---

### 2. Listar Órdenes de Compra

**Endpoint**: `GET /api/purchase-orders`

**Autenticación**: No requerida

**Parámetros opcionales**:
- `order_number`: Filtrar por número de orden
- `company`: Filtrar por trading company

**Power BI - Código M (Básico)**:
```m
let
    url = "https://tu-dominio.com/api/purchase-orders",
    response = Web.Contents(url),
    json = Json.Document(response),
    data = json[data],
    table = Table.FromList(data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    expanded = Table.ExpandRecordColumn(table, "Column1", 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"}, 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"})
in
    expanded
```

**Power BI - Con Filtros**:
```m
let
    orderNumber = "PO-2024-001", // Cambiar según necesidad
    company = "Company ABC",     // Cambiar según necesidad
    url = "https://tu-dominio.com/api/purchase-orders?order_number=" & orderNumber & "&company=" & company,
    response = Web.Contents(url),
    json = Json.Document(response),
    data = json[data],
    table = Table.FromList(data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    expanded = Table.ExpandRecordColumn(table, "Column1", 
        {"id", "order_number", "trading_company", "status", "net_total", "total"}, 
        {"id", "order_number", "trading_company", "status", "net_total", "total"})
in
    expanded
```

**Power BI - Expandir Vendor y Products**:
```m
let
    url = "https://tu-dominio.com/api/purchase-orders",
    response = Web.Contents(url),
    json = Json.Document(response),
    data = json[data],
    table = Table.FromList(data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    
    // Expandir columnas principales
    expanded = Table.ExpandRecordColumn(table, "Column1", 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"}, 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"}),
    
    // Expandir vendor
    vendorExpanded = Table.ExpandRecordColumn(expanded, "vendor", 
        {"id", "name", "vendo_code"}, 
        {"vendor_id", "vendor_name", "vendor_code"}),
    
    // Expandir products (array)
    productsExpanded = Table.ExpandListColumn(vendorExpanded, "products"),
    productsDetails = Table.ExpandRecordColumn(productsExpanded, "products", 
        {"id", "material_id", "short_text"}, 
        {"product_id", "material_id", "product_name"}),
    
    // Cambiar tipos de datos
    typed = Table.TransformColumnTypes(productsDetails, {
        {"id", Int64.Type},
        {"order_number", type text},
        {"created_at", type datetime},
        {"net_total", type number},
        {"total", type number}
    })
in
    typed
```

---

### 3. Obtener Órdenes del Usuario (Requiere Autenticación)

**Endpoint**: `GET /api/my-purchase-orders`

**Autenticación**: Requerida (API Token)

**Parámetros opcionales**:
- `page`: Número de página (paginación)
- `per_page`: Elementos por página

**Power BI - Código M con Autenticación**:
```m
let
    // Configuración
    baseUrl = "https://tu-dominio.com/api",
    token = "tu_token_aqui", // Reemplazar con tu token
    
    // URL con paginación
    page = 1,
    perPage = 100,
    url = baseUrl & "/my-purchase-orders?page=" & Number.ToText(page) & "&per_page=" & Number.ToText(perPage),
    
    // Headers con autenticación
    headers = [
        #"Authorization" = "Bearer " & token,
        #"Content-Type" = "application/json"
    ],
    
    // Obtener datos
    response = Web.Contents(url, [Headers=headers]),
    json = Json.Document(response),
    data = json[data][data], // Acceder a los datos paginados
    
    // Convertir a tabla
    table = Table.FromList(data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    expanded = Table.ExpandRecordColumn(table, "Column1", 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"}, 
        {"id", "order_number", "trading_company", "status", "vendor", "products", "created_at", "net_total", "total"})
in
    expanded
```

**Power BI - Con Paginación Completa**:
```m
let
    baseUrl = "https://tu-dominio.com/api",
    token = "tu_token_aqui",
    
    // Función para obtener una página
    GetPage = (pageNum) => 
        let
            url = baseUrl & "/my-purchase-orders?page=" & Number.ToText(pageNum) & "&per_page=100",
            headers = [#"Authorization" = "Bearer " & token],
            response = Web.Contents(url, [Headers=headers]),
            json = Json.Document(response),
            data = json[data][data],
            totalPages = json[data][last_page]
        in
            {data, totalPages},
    
    // Obtener primera página para saber el total
    FirstPage = GetPage(1),
    TotalPages = FirstPage[totalPages],
    
    // Generar lista de todas las páginas
    AllPages = List.Generate(
        () => {1, GetPage(1)[data]},
        each _{0} <= TotalPages,
        each {_{0} + 1, GetPage(_{0} + 1)[data]},
        each _{1}
    ),
    
    // Combinar todas las páginas
    Combined = Table.Combine(AllPages),
    
    // Expandir columnas
    expanded = Table.ExpandRecordColumn(Combined, "Column1", 
        {"id", "order_number", "trading_company", "status", "net_total", "total", "created_at"}, 
        {"id", "order_number", "trading_company", "status", "net_total", "total", "created_at"})
in
    expanded
```

---

### 4. Obtener Estadísticas del Dashboard (Requiere Autenticación)

**Endpoint**: `GET /api/dashboard-stats`

**Autenticación**: Requerida (API Token)

**Power BI - Código M**:
```m
let
    url = "https://tu-dominio.com/api/dashboard-stats",
    token = "tu_token_aqui",
    headers = [
        #"Authorization" = "Bearer " & token,
        #"Content-Type" = "application/json"
    ],
    response = Web.Contents(url, [Headers=headers]),
    json = Json.Document(response),
    stats = json[stats],
    statsTable = Record.ToTable(stats),
    expanded = Table.ExpandRecordColumn(statsTable, "Value", {"total_orders", "pending_orders", "completed_orders"}, {"total_orders", "pending_orders", "completed_orders"})
in
    expanded
```

---

## Cómo Obtener el Token API

1. Inicia sesión en el sistema web
2. Ve a **Settings > API Tokens**
3. Haz clic en **"Crear Nuevo Token"**
4. Proporciona un nombre descriptivo
5. Copia el token generado (se muestra solo una vez)

## Pasos para Conectar en Power BI

### Método 1: Usando el Editor Avanzado

1. Abre Power BI Desktop
2. Ve a **Obtener datos > Web**
3. Ingresa la URL del endpoint
4. Si requiere autenticación, haz clic en **Avanzado** y agrega:
   - **Parámetro**: `Authorization`
   - **Valor**: `Bearer tu_token_aqui`
5. Haz clic en **Aceptar**
6. En el Editor de Power Query, haz clic en **Editor avanzado**
7. Pega el código M correspondiente
8. Reemplaza `tu_token_aqui` con tu token real
9. Reemplaza `tu-dominio.com` con tu dominio real
10. Haz clic en **Listo**

### Método 2: Crear Función Reutilizable para el Token

1. Crea una nueva consulta en blanco
2. En el Editor avanzado, pega:
```m
() => "tu_token_aqui"
```
3. Renombra la consulta a `API_Token`
4. En tus otras consultas, usa:
```m
let
    token = API_Token(),
    url = "https://tu-dominio.com/api/endpoint",
    headers = [#"Authorization" = "Bearer " & token],
    ...
```

## Ejemplo Completo: Tabla de Purchase Orders

```m
let
    // Configuración
    baseUrl = "https://tu-dominio.com/api",
    token = "tu_token_aqui",
    
    // Obtener datos
    url = baseUrl & "/purchase-orders",
    response = Web.Contents(url),
    json = Json.Document(response),
    data = json[data],
    
    // Convertir a tabla
    table = Table.FromList(data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    
    // Expandir columnas principales
    expanded = Table.ExpandRecordColumn(table, "Column1", 
        {"id", "order_number", "trading_company", "status", "vendor", "products", 
         "created_at", "updated_at", "net_total", "total", "currency", 
         "date_etd", "date_eta", "shipping_line", "departure_port", "arrival_port"}, 
        {"id", "order_number", "trading_company", "status", "vendor", "products", 
         "created_at", "updated_at", "net_total", "total", "currency", 
         "date_etd", "date_eta", "shipping_line", "departure_port", "arrival_port"}),
    
    // Expandir vendor
    vendorExpanded = Table.ExpandRecordColumn(expanded, "vendor", 
        {"id", "name", "vendo_code"}, 
        {"vendor_id", "vendor_name", "vendor_code"}),
    
    // Expandir products
    productsExpanded = Table.ExpandListColumn(vendorExpanded, "products"),
    productsDetails = Table.ExpandRecordColumn(productsExpanded, "products", 
        {"id", "material_id", "short_text", "price_per_unit"}, 
        {"product_id", "material_id", "product_name", "product_price"}),
    
    // Cambiar tipos de datos
    typed = Table.TransformColumnTypes(productsDetails, {
        {"id", Int64.Type},
        {"order_number", type text},
        {"trading_company", type text},
        {"status", type text},
        {"created_at", type datetime},
        {"updated_at", type datetime},
        {"net_total", type number},
        {"total", type number},
        {"currency", type text},
        {"date_etd", type datetime},
        {"date_eta", type datetime},
        {"shipping_line", type text},
        {"departure_port", type text},
        {"arrival_port", type text},
        {"vendor_id", Int64.Type},
        {"vendor_name", type text},
        {"vendor_code", type text},
        {"product_id", Int64.Type},
        {"material_id", type text},
        {"product_name", type text},
        {"product_price", type number}
    }),
    
    // Filtrar filas nulas de products (si una orden no tiene productos)
    filtered = Table.SelectRows(typed, each [product_id] <> null)
in
    filtered
```

## Endpoints Adicionales de Purchase Orders

### 5. Buscar Órdenes (POST)

**Endpoint**: `POST /api/purchase-orders/search`

**Autenticación**: No requerida

**Power BI - Código M**:
```m
let
    url = "https://tu-dominio.com/api/purchase-orders/search",
    body = Json.FromValue([
        order_number = "PO-2024-001",
        company = "Company ABC"
    ]),
    response = Web.Contents(url, [
        Headers = [#"Content-Type" = "application/json"],
        Content = body
    ]),
    json = Json.Document(response),
    data = json[data]
in
    data
```

## Notas Importantes

1. **Endpoints Públicos**: No requieren autenticación (`/api/purchase-orders`, `/api/status`)
2. **Endpoints Privados**: Requieren token Bearer (`/api/my-purchase-orders`, `/api/dashboard-stats`)
3. **Paginación**: El endpoint `/api/my-purchase-orders` soporta paginación. Usa el ejemplo de paginación completa para obtener todos los datos.
4. **Transformaciones**: Los objetos anidados (vendor, products) deben expandirse manualmente en Power Query.
5. **Actualización**: Configura la actualización programada en Power BI Service para mantener los datos actualizados.

## Troubleshooting

### Error: "No se puede autenticar"
- Verifica que el token esté correcto
- Asegúrate de incluir "Bearer " antes del token
- Verifica que el token no haya expirado

### Error: "No se puede convertir el valor"
- Verifica que la respuesta JSON sea válida
- Revisa la estructura de datos en el Editor de Power Query
- Asegúrate de expandir correctamente los objetos anidados

### Datos incompletos
- Si usas paginación, verifica que estés obteniendo todas las páginas
- Revisa los filtros aplicados en la URL

---

**Última actualización**: Enero 2024  
**Versión de API**: 1.0.0

