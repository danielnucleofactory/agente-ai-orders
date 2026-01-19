-- Script SQL para ocultar la etapa "Nuevo" y migrar PO existentes
-- IMPORTANTE: Ejecutar después de la migración add_is_hidden_to_kanban_statuses_table.php
-- 
-- Este script:
-- 1. Identifica la etapa "Nuevo" en todos los tableros de tipo 'po_stages'
-- 2. Identifica el estado destino ("Recepción" o defaultStatus)
-- 3. Migra todas las PO de etapa "Nuevo" al estado destino
-- 4. Oculta la etapa "Nuevo"

-- ============================================
-- PASO 1: Identificar y mostrar información
-- ============================================
-- Verificar PO en etapa "Nuevo" antes de migrar
SELECT 
    ks.id as status_id,
    ks.name as status_name,
    ks.slug,
    kb.id as board_id,
    kb.name as board_name,
    COUNT(po.id) as po_count
FROM kanban_statuses ks
INNER JOIN kanban_boards kb ON kb.id = ks.kanban_board_id
LEFT JOIN purchase_orders po ON po.kanban_status_id = ks.id AND po.deleted_at IS NULL
WHERE ks.name = 'Nuevo' 
   OR (ks.id = 1 AND kb.type = 'po_stages')
   AND kb.type = 'po_stages'
GROUP BY ks.id, ks.name, ks.slug, kb.id, kb.name;

-- ============================================
-- PASO 2: Migrar PO de etapa "Nuevo" a "Recepción"
-- ============================================
-- Para cada tablero, migrar PO de "Nuevo" a "Recepción" o defaultStatus

-- Tablero 1 (ajustar según tus tableros)
SET @board_id_1 = (SELECT id FROM kanban_boards WHERE type = 'po_stages' AND is_active = true LIMIT 1);

-- Identificar estado destino para tablero 1
SET @destino_status_id_1 = (
    SELECT id FROM kanban_statuses 
    WHERE kanban_board_id = @board_id_1
    AND (
        (name = 'Recepción' AND is_hidden = false)
        OR (is_default = true AND is_hidden = false)
    )
    ORDER BY 
        CASE WHEN name = 'Recepción' THEN 1 ELSE 2 END,
        position ASC
    LIMIT 1
);

-- Migrar PO de etapa "Nuevo" al estado destino (tablero 1)
UPDATE purchase_orders 
SET kanban_status_id = @destino_status_id_1
WHERE kanban_status_id IN (
    SELECT id FROM kanban_statuses 
    WHERE kanban_board_id = @board_id_1
    AND (name = 'Nuevo' OR id = 1)
)
AND deleted_at IS NULL;

-- ============================================
-- PASO 3: Ocultar la etapa "Nuevo"
-- ============================================
UPDATE kanban_statuses 
SET is_hidden = true 
WHERE (name = 'Nuevo' OR id = 1) 
AND kanban_board_id IN (
    SELECT id FROM kanban_boards WHERE type = 'po_stages'
);

-- ============================================
-- PASO 4: Verificar migración
-- ============================================
-- Verificar que no quedan PO en etapa "Nuevo" visible
SELECT 
    ks.id,
    ks.name,
    ks.is_hidden,
    COUNT(po.id) as po_count
FROM kanban_statuses ks
LEFT JOIN purchase_orders po ON po.kanban_status_id = ks.id AND po.deleted_at IS NULL
WHERE ks.name = 'Nuevo'
GROUP BY ks.id, ks.name, ks.is_hidden;

-- Verificar PO migradas al estado destino
SELECT 
    ks.id,
    ks.name,
    COUNT(po.id) as po_count
FROM kanban_statuses ks
INNER JOIN purchase_orders po ON po.kanban_status_id = ks.id AND po.deleted_at IS NULL
WHERE ks.id = @destino_status_id_1
GROUP BY ks.id, ks.name;
