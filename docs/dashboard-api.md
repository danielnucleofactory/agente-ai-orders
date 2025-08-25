# Estructura de la API del Dashboard

## 1. Estructura de la API de Laravel

El dashboard tiene 3 endpoints principales definidos en `routes/web.php`:

```php
Route::middleware(['auth', 'verified', 'permission:has_view_dashboard'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
});
```

- `GET /dashboard`: Renderiza la vista inicial con datos
- `GET /dashboard/data`: Endpoint AJAX para actualizar datos filtrados
- `GET /dashboard/export`: Endpoint para exportar datos a CSV

## 2. Envío de Filtros al Backend

Los filtros se envían como query parameters en las peticiones GET. El proceso es el siguiente:

### Frontend (`dashboard-dynamic.js`):
```javascript
const params = new URLSearchParams();
// Campos individuales
const singleFields = ['date_from', 'date_to'];
// Campos múltiples
const multipleFields = ['product_id[]', 'material_type[]', 'hub_id[]'];
```

### Backend (`DashboardController.php`):
```php
private function getFilters(Request $request): array
{
    return [
        'date_from' => $request->get('date_from'),
        'date_to' => $request->get('date_to'),
        'product_id' => $request->get('product_id', []),
        'material_type' => $request->get('material_type', []),
        'hub_id' => $request->get('hub_id', []),
        'vendor_id' => $request->get('vendor_id'),
        'status' => $request->get('status'),
    ];
}
```

## 3. Endpoints y Manejo de Filtros

Se usa el mismo endpoint base (`/dashboard/data`) para todos los tipos de filtros. La lógica de filtrado se maneja en el `DashboardService`:

```php
class DashboardService
{
    public function getMetrics(array $filters): array
    {
        // Lógica de filtrado para métricas
    }

    public function getChartsData(array $filters): array
    {
        // Lógica de filtrado para gráficos
    }

    private function getBaseQuery(array $filters)
    {
        $query = DB::table('purchase_orders');

        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }

        // Más lógica de filtrado...

        return $query;
    }
}
```

## Características Importantes

1. **Filtros Múltiples**:
   - Los filtros como `product_id[]`, `material_type[]`, y `hub_id[]` permiten selección múltiple.

2. **Consistencia**:
   - El mismo conjunto de filtros se aplica en todas las secciones (métricas, gráficos, tabla).

3. **Seguridad**:
   - Los endpoints están protegidos con middleware de autenticación y permisos:
   ```php
   middleware(['auth', 'verified', 'permission:has_view_dashboard'])
   ```

4. **Manejo de Errores**:
   - Incluye logging extensivo y manejo de errores tanto en frontend como backend.

5. **Formato de Respuesta**:
   - Las respuestas JSON siguen un formato consistente:
   ```json
   {
       "success": true,
       "data": {
           "metrics": { ... },
           "charts": { ... },
           "detail_table": [ ... ]
       }
   }
   ```

Esta estructura permite una fácil extensión para nuevos tipos de filtros y mantiene la consistencia en el manejo de datos a través de toda la aplicación.
