# Ejemplos Completos de la API - Raga-X Orders

Este documento contiene ejemplos exhaustivos de todos los campos disponibles en la API de Raga-X Orders, organizados por complejidad de operación logística. OLO es un sistema especializado en seguimiento y gestión de importaciones con enfoque en logística internacional.

## 📋 Índice

1. [Importación Simple - Operaciones Logísticas Básicas](#importación-simple---operaciones-logísticas-básicas)
2. [Importación Estándar - Operaciones con Consolidación](#importación-estándar---operaciones-con-consolidación)
3. [Importación Completa - Proyectos Logísticos Complejos](#importación-completa---proyectos-logísticos-complejos)
4. [Campos por Categoría](#campos-por-categoría)
5. [Validaciones y Restricciones](#validaciones-y-restricciones)

---

## 🚀 Importación Simple - Operaciones Logísticas Básicas

**Ideal para:** Envíos directos, sin consolidación, operaciones urgentes  
**Complejidad:** Seguimiento básico de fechas ETD/ETA  
**Seguimiento:** Información esencial de logística  
**Tiempo de procesamiento:** Rápido

### POST /api/purchase-orders

```json
{
  "general": {
    "order_number": "PO-2025-001",
    "vendor_id": 1,
    "vendor_name": "Proveedor Simple S.A.",
    "currency": "USD",
    "incoterms": "FOB",
    "price_incoterm": "FOB",
    "logistics_incoterm": "FOB",
    "category": "Importación",
    "reason": "Reposición urgente",
    "factory_proforma_number": "PROF-2025-001",
    "route_label": "Ruta Simple",
    "date_theorical_load": "2025-08-15",
    "bonded_warehouse_enter": "2025-09-20",
    "bonded_warehouse_exit": "2025-09-25"
  },
  "items": [
    {
      "product_code": "PROD-001",
      "product_name": "Producto Simple A",
      "description": "Producto básico para tienda",
      "quantity": 5,
      "unit_price": 25.50,
      "total_price": 127.50,
      "unit": "pcs"
    },
    {
      "product_code": "PROD-002",
      "product_name": "Producto Simple B",
      "description": "Producto básico para tienda",
      "quantity": 3,
      "unit_price": 15.75,
      "total_price": 47.25,
      "unit": "kg"
    }
  ]
}
```

---

## 🔧 Importación Estándar - Operaciones con Consolidación

**Ideal para:** Operaciones con consolidación, seguimiento de contenedores  
**Complejidad:** Seguimiento completo de fechas y documentos  
**Seguimiento:** Información logística y comercial completa  
**Tiempo de procesamiento:** Estándar

### POST /api/purchase-orders

```json
{
  "general": {
    "order_number": "PO-2025-002",
    "vendor_id": 2,
    "vendor_name": "Proveedor Estándar Ltd.",
    "vendor_email": "sales@proveedor-estandar.com",
    "vendor_phone": "+86 138 0013 8000",
    "vendor_address": "Industrial Zone 88, Shanghai",
    "vendor_city": "Shanghai",
    "vendor_country": "China",
    "bill_to_name": "Distribuidora Estándar",
    "bill_to_address": "Business District 123, Santiago",
    "bill_to_city": "Santiago",
    "bill_to_country": "Chile",
    "ship_to_name": "Puerto de Valparaíso",
    "ship_to_address": "Terminal Portuario, Valparaíso",
    "ship_to_city": "Valparaíso",
    "ship_to_country": "Chile",
    "emision_date_po": "2025-07-18",
    "order_date": "2025-07-18",
    "currency": "USD",
    "incoterms": "CIF",
    "price_incoterm": "CIF",
    "logistics_incoterm": "CIF",
    "payment_terms": "LC 60 días",
    "category": "Importación",
    "reason": "Reposición para temporada",
    "departure_port": "Shanghai Port",
    "arrival_port": "Valparaíso Port",
    "shipping_line": "COSCO Shipping",
    "container_type": "40FT",
    "container_number": "COSU1234567",
    "mbl_number": "MBL-2025-002",
    "factory_proforma_number": "PROF-2025-002",
    "cbm": 45.0,
    "peso_kg": 8500.0,
    "date_theorical_load": "2025-08-15",
    "bonded_warehouse_enter": "2025-09-20",
    "bonded_warehouse_exit": "2025-09-25",
    "mode": "Marítimo",
    "tracking_id": "TRK-2025-002",
    "pallet_quantity": 15,
    "forwarder_name": "Freight Forwarder Chile",
    "route_label": "Ruta Asia-Pacífico",
    "customer_type": "Distribuidor",
    "applies_tlc": true,
    "uses_bonded_warehouse": true
  },
  "items": [
    {
      "product_code": "PROD-003",
      "product_name": "Producto Estándar A",
      "description": "Producto para distribución",
      "quantity": 25,
      "unit_price": 45.00,
      "total_price": 1125.00,
      "unit": "pcs"
    },
    {
      "product_code": "PROD-004",
      "product_name": "Producto Estándar B",
      "description": "Producto para mayorista",
      "quantity": 15,
      "unit_price": 32.50,
      "total_price": 487.50,
      "unit": "kg"
    },
    {
      "product_code": "PROD-005",
      "product_name": "Producto Estándar C",
      "description": "Producto con seguimiento",
      "quantity": 20,
      "unit_price": 18.75,
      "total_price": 375.00,
      "unit": "units"
    },
    {
      "product_code": "PROD-006",
      "product_name": "Producto Estándar D",
      "description": "Producto adicional",
      "quantity": 12,
      "unit_price": 22.50,
      "total_price": 270.00,
      "unit": "pcs"
    }
  ]
}
```

---

## 🎯 Importación Completa - Proyectos Logísticos Complejos

**Ideal para:** Proyectos grandes, seguimiento end-to-end, auditoría completa  
**Complejidad:** Seguimiento total con múltiples documentos y validaciones  
**Seguimiento:** Visibilidad completa y auditoría de todo el proceso  
**Tiempo de procesamiento:** Completo con validaciones

### POST /api/purchase-orders

```json
{
  "general": {
    "order_number": "PO-2025-003",
    "vendor_id": 3,
    "vendor_name": "Proveedor Completo Global S.A.",
    "vendor_number": "VEND-003",
    "vendor_email": "global@proveedor-completo.com",
    "vendor_phone": "+86 139 0013 9000",
    "vendor_address": "Global Industrial Park 999, Guangzhou",
    "vendor_city": "Guangzhou",
    "vendor_country": "China",
    "bill_to_name": "Importadora Completa Ltda.",
    "bill_to_address": "Corporate Center 888, Las Condes",
    "bill_to_city": "Santiago",
    "bill_to_country": "Chile",
    "ship_to_name": "Terminal Logístico Completo",
    "ship_to_address": "Logistics Hub 777, San Antonio",
    "ship_to_city": "San Antonio",
    "ship_to_country": "Chile",
    "emision_date_po": "2025-07-18",
    "order_date": "2025-07-18",
    "currency": "USD",
    "incoterms": "DDP",
    "price_incoterm": "DDP",
    "logistics_incoterm": "DDP",
    "payment_terms": "LC 90 días",
    "category": "Importación Completa",
    "reason": "Proyecto de expansión comercial",
    "departure_port": "Guangzhou Port",
    "arrival_port": "San Antonio Port",
    "shipping_line": "MSC Mediterranean",
    "container_type": "40FT",
    "container_number": "MSCU9876543",
    "mbl_number": "MBL-2025-003",
    "factory_proforma_number": "PROF-2025-003",
    "cbm": 120.0,
    "peso_kg": 25000.0,
    "peso_lb": 55115.5,
    "date_booking_request": "2025-07-20",
    "date_booking_authorized": "2025-07-22",
    "forwader_date": "2025-07-25",
    "inspection_date": "2025-08-01",
    "vgm_cut_date": "2025-08-05",
    "date_theorical_load": "2025-08-10",
    "date_variable_date": "2025-08-12",
    "date_carga_po": "2025-08-15",
    "date_consolidation": "2025-08-18",
    "release_date": "2025-08-20",
    "dif_load_date": "2025-08-22",
    "consolidator_name": "Consolidator Global",
    "date_etd_initial": "2025-08-25",
    "date_etd": "2025-08-28",
    "date_atd": "2025-08-30",
    "date_eta": "2025-09-15",
    "date_eta_updated": "2025-09-18",
    "date_ata": "2025-09-20",
    "bonded_warehouse_enter": "2025-09-22",
    "bonded_warehouse_exit": "2025-09-28",
    "receipt_note_date": "2025-09-30",
    "estimated_dc_availability_date": "2025-10-05",
    "balance_payment_date": "2025-10-10",
    "local_charges_payment_date": "2025-10-15",
    "mode": "Marítimo",
    "tracking_id": "TRK-2025-003",
    "pallet_quantity": 50,
    "pallet_quantity_real": 48,
    "Invoice_amount": 50000.00,
    "freight_amount": 3500.00,
    "other_expenses": 1200.00,
    "estimated_pallet_cost": 25.00,
    "real_cost_estimated_po": 54725.00,
    "real_cost_real_po": 54500.00,
    "forwarder_name": "Global Forwarder Chile",
    "service_provider": "Complete Logistics Solutions",
    "trading_company": "Global Trading Corp",
    "tariff_type": "Preferencial",
    "route_label": "Ruta Asia-Pacífico Completa",
    "retail_group": "Grupo Retail Premium",
    "customer_type": "Importador Directo",
    "invoice": "INV-2025-003",
    "date_invoice_received": "2025-07-25",
    "date_vendor_document_received": "2025-07-28",
    "cargo_invoice_number": "CARG-INV-2025-003",
    "factura_merca": "FACT-MERCA-2025-003",
    "customs_dua": "DUA-2025-003",
    "case_number_file": "EXP-2025-003",
    "receipt_note": "REC-2025-003",
    "visibility_notes": "Orden con seguimiento completo y visibilidad total",
    "arrival_status": "On Time",
    "delay_days": 0,
    "applies_tlc": true,
    "applies_af": true,
    "has_facture_merca": true,
    "used_rate_ok": true,
    "uses_bonded_warehouse": true,
    "apply_technical_note": true,
    "etd_initial_validated": true,
    "port_of_loading_validated": true,
    "container_free_days": 7
  },
  "items": [
    {
      "product_code": "PROD-007",
      "product_name": "Producto Completo A",
      "description": "Producto con todas las especificaciones técnicas completas",
      "quantity": 100,
      "unit_price": 55.00,
      "total_price": 5500.00,
      "unit": "pcs"
    },
    {
      "product_code": "PROD-008",
      "product_name": "Producto Completo B",
      "description": "Producto con certificaciones internacionales",
      "quantity": 75,
      "unit_price": 75.50,
      "total_price": 5662.50,
      "unit": "kg"
    },
    {
      "product_code": "PROD-009",
      "product_name": "Producto Completo C",
      "description": "Producto con empaque premium y etiquetado especial",
      "quantity": 150,
      "unit_price": 42.25,
      "total_price": 6337.50,
      "unit": "units"
    },
    {
      "product_code": "PROD-010",
      "product_name": "Producto Completo D",
      "description": "Producto con garantía extendida y soporte técnico",
      "quantity": 50,
      "unit_price": 125.00,
      "total_price": 6250.00,
      "unit": "sets"
    },
    {
      "product_code": "PROD-011",
      "product_name": "Producto Completo E",
      "description": "Producto adicional para proyecto grande",
      "quantity": 80,
      "unit_price": 35.75,
      "total_price": 2860.00,
      "unit": "pcs"
    },
    {
      "product_code": "PROD-012",
      "product_name": "Producto Completo F",
      "description": "Producto especializado con seguimiento completo",
      "quantity": 60,
      "unit_price": 95.00,
      "total_price": 5700.00,
      "unit": "units"
    }
  ]
}
```

---

## 📊 Campos por Categoría

### 🏢 Información General
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `order_number` | string | ✅ | Número único de la orden |
| `vendor_id` | integer | ✅ | ID del proveedor |
| `vendor_name` | string | ✅ | Nombre del proveedor |
| `vendor_email` | string | ❌ | Email del proveedor |
| `vendor_phone` | string | ❌ | Teléfono del proveedor |
| `vendor_address` | string | ❌ | Dirección del proveedor |
| `vendor_city` | string | ❌ | Ciudad del proveedor |
| `vendor_country` | string | ❌ | País del proveedor |
| `bill_to_name` | string | ❌ | Nombre para facturación |
| `bill_to_address` | string | ❌ | Dirección de facturación |
| `bill_to_city` | string | ❌ | Ciudad de facturación |
| `bill_to_country` | string | ❌ | País de facturación |
| `ship_to_name` | string | ❌ | Nombre para envío |
| `ship_to_address` | string | ❌ | Dirección de envío |
| `ship_to_city` | string | ❌ | Ciudad de envío |
| `ship_to_country` | string | ❌ | País de envío |

### 💰 Información Comercial
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `currency` | string | ❌ | Moneda (USD, EUR, CLP) |
| `incoterms` | string | ✅ | Términos comerciales |
| `price_incoterm` | string | ✅ | Incoterm de precios |
| `logistics_incoterm` | string | ✅ | Incoterm logístico |
| `payment_terms` | string | ❌ | Condiciones de pago |
| `category` | string | ✅ | Categoría de la orden |
| `reason` | string | ✅ | Motivo de la orden |

### 🚢 Información Logística
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `departure_port` | string | ❌ | Puerto de salida |
| `arrival_port` | string | ❌ | Puerto de llegada |
| `shipping_line` | string | ❌ | Línea naviera |
| `container_type` | string | ❌ | Tipo de contenedor |
| `container_number` | string | ❌ | Número de contenedor |
| `mbl_number` | string | ❌ | Número MBL |
| `mode` | string | ❌ | Modo de transporte |
| `tracking_id` | string | ❌ | ID de seguimiento |

### 📅 Fechas Importantes
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `emision_date_po` | date | ❌ | Fecha emisión PO |
| `order_date` | date | ❌ | Fecha de orden |
| `date_theorical_load` | date | ✅ | Fecha carga teórica |
| `date_etd` | date | ❌ | Fecha estimada salida |
| `date_eta` | date | ❌ | Fecha estimada llegada |
| `bonded_warehouse_enter` | date | ✅ | Ingreso almacén fiscal |
| `bonded_warehouse_exit` | date | ✅ | Salida almacén fiscal |

### 📦 Dimensiones y Cantidades
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `cbm` | decimal | ❌ | Metros cúbicos |
| `peso_kg` | decimal | ❌ | Peso en kg |
| `peso_lb` | decimal | ❌ | Peso en libras |
| `pallet_quantity` | integer | ❌ | Cantidad de pallets |
| `pallet_quantity_real` | integer | ❌ | Cantidad real pallets |

### 💵 Costos y Montos
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `Invoice_amount` | decimal | ❌ | Monto factura |
| `freight_amount` | decimal | ❌ | Monto flete |
| `other_expenses` | decimal | ❌ | Otros gastos |
| `estimated_pallet_cost` | decimal | ❌ | Costo estimado pallets |
| `real_cost_estimated_po` | decimal | ❌ | Costo total estimado |
| `real_cost_real_po` | decimal | ❌ | Costo real |

### ✅ Flags y Estados
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `applies_tlc` | boolean | ❌ | Aplica TLC |
| `applies_af` | boolean | ❌ | Aplica AF |
| `has_facture_merca` | boolean | ❌ | Tiene factura mercancía |
| `used_rate_ok` | boolean | ❌ | Tarifa utilizada OK |
| `uses_bonded_warehouse` | boolean | ❌ | Usa almacén fiscal |
| `apply_technical_note` | boolean | ❌ | Aplica nota técnica |

---

## ⚠️ Validaciones y Restricciones

### 🔒 Campos Obligatorios
Los siguientes campos son **obligatorios** y deben incluirse en todas las peticiones:

- `order_number` - Debe ser único
- `vendor_id` - Debe existir en la base de datos
- `incoterms` - Valores permitidos: CIF, CIP, CFR, CPT, DAT, DAP, DDP, DEQ, DES, EXD, EXQ, EXW, FCA, FOB
- `price_incoterm` - Mismos valores que incoterms
- `logistics_incoterm` - Mismos valores que incoterms
- `category` - Categoría válida del sistema
- `reason` - Motivo de la orden
- `factory_proforma_number` - Número único de proforma
- `route_label` - Etiqueta de ruta válida
- `date_theorical_load` - Fecha válida
- `bonded_warehouse_enter` - Fecha válida
- `bonded_warehouse_exit` - Fecha válida
- `items` - Array con al menos un producto

### 📝 Validaciones de Items
Cada item en el array `items` debe contener:

- `product_code` - Código único del producto
- `product_name` - Nombre del producto
- `quantity` - Cantidad mayor a 0
- `unit_price` - Precio unitario mayor a 0
- `total_price` - Precio total (quantity × unit_price)

### 🌍 Valores Permitidos

#### Incoterms
```
CIF, CIP, CFR, CPT, DAT, DAP, DDP, DEQ, DES, EXD, EXQ, EXW, FCA, FOB
```

#### Monedas
```
USD, EUR, CLP, CNY, JPY, GBP
```

#### Modos de Transporte
```
Marítimo, Aéreo, Terrestre, Multimodal
```

#### Tipos de Contenedor
```
20FT, 40FT, 45FT, 20RF, 40RF, 45RF
```

#### Estados de Llegada
```
On Time, Delayed, Early, Cancelled
```

---

**Nota:** Este documento se actualiza automáticamente cuando se modifican los campos de la API. Para la versión más reciente, consulta la documentación oficial en `/docs/api/REFERENCE.md`.
