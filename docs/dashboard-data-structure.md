# Estructura de Datos del Dashboard

## 1. Estructura de Datos del Backend

Los datos se envían desde el backend en el siguiente formato JSON:

```json
{
    "success": true,
    "data": {
        "metrics": {
            "total_pos": 16,
            "on_time_percentage": 0,
            "delayed_percentage": 100,
            "material_count": 2
        },
        "charts": {
            "hub_distribution": [
                {
                    "name": "Sin Hub",
                    "value": 8,
                    "percentage": 50
                },
                {
                    "name": "MIA",
                    "value": 4,
                    "percentage": 25
                },
                // ... más hubs
            ],
            "delivery_status": [
                {
                    "name": "Atrasado",
                    "value": 16,
                    "percentage": 100
                }
            ],
            "transport_type": [
                {
                    "name": "SEA",
                    "value": 3,
                    "percentage": 18.8
                },
                {
                    "name": "SIN ESPECIFICAR",
                    "value": 11,
                    "percentage": 68.8
                },
                // ... más tipos de transporte
            ],
            "delay_reasons": [...],
            "pos_by_stage": [...]
        },
        "detail_table": [
            {
                "po_number": "PO-001",
                "fecha_salida": "01/01/2024",
                "fecha_estimada": "15/01/2024",
                "cantidad_kg": "1500.00"
            },
            // ... más registros
        ]
    }
}
```

## 2. Campos para Filtrado

Los siguientes campos están disponibles para filtrado:

### Filtros Principales
```php
[
    'date_from' => 'YYYY-MM-DD',     // Fecha inicial
    'date_to' => 'YYYY-MM-DD',       // Fecha final
    'product_id' => [1, 2, 3],       // Array de IDs de productos
    'material_type' => ['type1', 'type2'], // Array de tipos de material
    'hub_id' => [1, 2, 3],          // Array de IDs de hubs
    'vendor_id' => 1,               // ID del proveedor
    'status' => 'status_value'      // Estado de la orden
]
```

### Campos en Base de Datos para Filtrado

#### Tabla: purchase_orders
- `order_date`: Fecha de la orden
- `date_eta`: Fecha estimada de llegada
- `date_ata`: Fecha real de llegada
- `date_required_in_destination`: Fecha requerida en destino
- `company_id`: ID de la compañía
- `vendor_id`: ID del proveedor
- `kanban_status_id`: Estado en el kanban
- `order_number`: Número de orden

#### Tabla: purchase_order_product
- `product_id`: ID del producto
- `quantity`: Cantidad
- `unit_price`: Precio unitario

#### Tabla: products
- `material_id`: ID del material
- `name`: Nombre del producto

#### Tabla: hubs
- `id`: ID del hub
- `name`: Nombre del hub
- `code`: Código del hub

## 3. Procesamiento de Filtros

Los filtros se procesan en el backend de la siguiente manera:

```php
private function getBaseQuery(array $filters)
{
    $query = DB::table('purchase_orders');

    if (!empty($filters['date_from'])) {
        $query->where('order_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->where('order_date', '<=', $filters['date_to']);
    }

    if (!empty($filters['product_id'])) {
        $query->whereExists(function ($query) use ($filters) {
            $query->select(DB::raw(1))
                  ->from('purchase_order_product')
                  ->whereIn('product_id', $filters['product_id'])
                  ->whereRaw('purchase_order_product.purchase_order_id = purchase_orders.id');
        });
    }

    if (!empty($filters['material_type'])) {
        $query->whereExists(function ($query) use ($filters) {
            $query->select(DB::raw(1))
                  ->from('purchase_order_product')
                  ->join('products', 'products.id', '=', 'purchase_order_product.product_id')
                  ->whereIn('products.material_id', $filters['material_type'])
                  ->whereRaw('purchase_order_product.purchase_order_id = purchase_orders.id');
        });
    }

    if (!empty($filters['hub_id'])) {
        $query->whereIn('hub_id', $filters['hub_id']);
    }

    return $query;
}
```

## 4. Actualización de Datos en Frontend

El frontend actualiza los datos usando JavaScript:

```javascript
updateDashboard(data) {
    // Actualizar métricas
    this.updateMetrics(data.metrics);

    // Actualizar gráficos
    this.updateCharts(data.charts);

    // Actualizar tabla de detalles
    this.updateDetailTable(data.detail_table);
}
```

Los datos se actualizan en tiempo real cuando se aplican los filtros, manteniendo una experiencia de usuario fluida y responsive.
