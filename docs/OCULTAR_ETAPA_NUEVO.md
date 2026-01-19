# Guía de Implementación: Ocultar Etapa "Nuevo"

## Resumen

Esta implementación permite ocultar la etapa "Nuevo" del Kanban sin eliminarla, permitiendo una reversión fácil si es necesario. La etapa quedará oculta visualmente pero permanecerá en la base de datos.

## Archivos Modificados

### 1. Migración de Base de Datos
- **`database/migrations/2026_01_19_085017_add_is_hidden_to_kanban_statuses_table.php`**
  - Agrega el campo `is_hidden` (boolean, default: false) a la tabla `kanban_statuses`
  - Incluye índice para optimizar consultas

### 2. Modelos
- **`app/Models/KanbanStatus.php`**
  - Agregado `is_hidden` a `$fillable` y `$casts`

- **`app/Models/KanbanBoard.php`**
  - Modificado `defaultStatus()` para excluir status ocultos

### 3. Componentes Livewire
- **`app/Livewire/Kanban/KanbanBoard.php`**
  - Modificado `loadColumns()` para filtrar status ocultos
  - Agregada validación en `saveAndMove()` para prevenir movimiento a etapas ocultas

### 4. Controladores
- **`app/Http/Controllers/PurchaseOrderController.php`**
  - Modificado `createFromApi()` para excluir status ocultos al asignar estado inicial
  - Modificado `createSinglePurchaseOrder()` para excluir status ocultos al asignar estado inicial

### 5. Script SQL
- **`database/migrations/2026_01_19_085018_hide_nuevo_stage_and_migrate_pos.sql`**
  - Script para migrar PO existentes y ocultar la etapa "Nuevo"

## Pasos de Implementación

### Paso 1: Ejecutar Migración de Base de Datos

```bash
php artisan migrate
```

Esto agregará el campo `is_hidden` a la tabla `kanban_statuses`.

### Paso 2: Verificar PO en Etapa "Nuevo"

Antes de migrar, verifica cuántas PO están en la etapa "Nuevo":

```sql
SELECT 
    ks.id as status_id,
    ks.name as status_name,
    kb.name as board_name,
    COUNT(po.id) as po_count
FROM kanban_statuses ks
INNER JOIN kanban_boards kb ON kb.id = ks.kanban_board_id
LEFT JOIN purchase_orders po ON po.kanban_status_id = ks.id AND po.deleted_at IS NULL
WHERE ks.name = 'Nuevo' 
   AND kb.type = 'po_stages'
GROUP BY ks.id, ks.name, kb.name;
```

### Paso 3: Ejecutar Script SQL de Migración

**IMPORTANTE:** Haz un backup de la base de datos antes de ejecutar este script.

Ejecuta el script SQL manualmente o a través de un cliente MySQL:

```bash
mysql -u usuario -p nombre_base_datos < database/migrations/2026_01_19_085018_hide_nuevo_stage_and_migrate_pos.sql
```

O ejecuta las queries manualmente en tu cliente SQL preferido.

El script:
1. Identifica la etapa "Nuevo" en todos los tableros de tipo 'po_stages'
2. Identifica el estado destino ("Recepción" o defaultStatus)
3. Migra todas las PO de etapa "Nuevo" al estado destino
4. Oculta la etapa "Nuevo" (`is_hidden = true`)

### Paso 4: Verificar Implementación

#### 4.1. Verificar que la etapa está oculta

```sql
SELECT id, name, is_hidden 
FROM kanban_statuses 
WHERE name = 'Nuevo';
```

Debería mostrar `is_hidden = 1` (true).

#### 4.2. Verificar que no quedan PO en etapa "Nuevo"

```sql
SELECT COUNT(*) as po_count
FROM purchase_orders po
INNER JOIN kanban_statuses ks ON ks.id = po.kanban_status_id
WHERE ks.name = 'Nuevo' 
AND po.deleted_at IS NULL;
```

Debería retornar `0`.

#### 4.3. Probar Creación de PO Nueva

1. Crear una PO nueva desde la UI
2. Verificar que se asigna a "Recepción" (o estado por defecto)
3. Verificar que NO aparece en etapa "Nuevo"

#### 4.4. Probar Creación desde API

```bash
curl -X POST http://tu-dominio/api/purchase-orders \
  -H "Content-Type: application/json" \
  -d '{
    "general": {
      "order_number": "TEST-001",
      "trading_company": "Test Company"
    },
    "items": []
  }'
```

Verificar que la PO creada tiene `kanban_status_id` diferente a la etapa "Nuevo".

#### 4.5. Verificar Kanban Visual

1. Abrir el Kanban board
2. Verificar que la columna "Nuevo" NO aparece
3. Intentar mover una PO (no debería aparecer "Nuevo" en el dropdown)

## Reversión

Si necesitas mostrar la etapa "Nuevo" de nuevo:

```sql
-- Mostrar la etapa "Nuevo"
UPDATE kanban_statuses 
SET is_hidden = false 
WHERE name = 'Nuevo' 
AND kanban_board_id IN (
    SELECT id FROM kanban_boards WHERE type = 'po_stages'
);
```

**Nota:** Las PO que fueron migradas NO volverán automáticamente a "Nuevo". Si necesitas revertir la migración de PO, deberás ejecutar un script adicional.

## Consideraciones Importantes

### 1. API Externa que Envía `kanban_status_id`

Si un sistema externo envía `kanban_status_id: 1` (etapa "Nuevo") en el payload de creación de PO, el código actualmente:
- Ignorará el `kanban_status_id` si está oculto
- Usará el estado por defecto ("Recepción")

**Recomendación:** Si tienes sistemas externos que envían `kanban_status_id`, actualízalos para que no envíen el ID de la etapa "Nuevo".

### 2. Datos Históricos

Los datos históricos se preservan:
- Las PO que estaban en "Nuevo" ahora están en "Recepción"
- El historial de cambios de etapa se mantiene
- Los webhooks y notificaciones siguen funcionando

### 3. Dashboards y KPIs

Los dashboards y KPIs que agrupan por `kanban_status_id` seguirán funcionando correctamente. La etapa "Nuevo" simplemente no aparecerá en visualizaciones que filtren por `is_hidden = false`.

## Troubleshooting

### Problema: La etapa "Nuevo" sigue apareciendo en el Kanban

**Solución:**
1. Verificar que la migración se ejecutó correctamente: `SELECT * FROM kanban_statuses WHERE name = 'Nuevo';`
2. Verificar que `is_hidden = 1`
3. Limpiar caché de Laravel: `php artisan cache:clear`
4. Verificar que el código en `loadColumns()` tiene el filtro `where('is_hidden', false)`

### Problema: PO nuevas se asignan a etapa "Nuevo"

**Solución:**
1. Verificar que `KanbanBoard::defaultStatus()` filtra `is_hidden = false`
2. Verificar que `PurchaseOrderController::createFromApi()` y `createSinglePurchaseOrder()` filtran ocultos
3. Verificar que existe un estado con `is_default = true` y `is_hidden = false`

### Problema: Error al ejecutar script SQL

**Solución:**
1. Verificar que la migración `add_is_hidden_to_kanban_statuses_table` se ejecutó primero
2. Verificar que los nombres de tableros y estados coinciden con tu base de datos
3. Ajustar el script SQL según tus tableros específicos

## Soporte

Si encuentras problemas durante la implementación, revisa los logs de Laravel:

```bash
tail -f storage/logs/laravel.log
```

Y los logs de la aplicación para ver errores específicos relacionados con Kanban.
