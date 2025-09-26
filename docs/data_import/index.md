# Documento de Mapeo de Datos para Carga de Datos Históricos

## Introducción

Este documento especifica la estructura y el formato de los datos históricos que deben ser recopilados para la carga inicial de información en el sistema. El objetivo es asegurar que la información sea completa, precisa y consistente.

A continuación, se detallan las tablas de mapeo para cada entidad principal: **Órdenes de Compra** y **Datos Maestros** (Productos, Proveedores, etc.), junto con las instrucciones detalladas para la correcta preparación y entrega de los archivos.

---

## 1. Mapeo de Datos: Órdenes de Compra (Purchase Orders)

| Nombre Frontend | Tabla | Nombre BD (Campo) | Tipo de Dato | Obligatorio |
| :--- | :--- | :--- | :--- | :--- |
| **Identificación de la OC** | | | | |
| (No aplica) | `purchase_orders` | `id` | `ID Autonumérico` | (Automático) |
| (No aplica) | `purchase_orders` | `company_id` | `ID Numérico` | Sí |
| Número de Orden (PO) | `purchase_orders` | `order_number` | `Texto` | Sí |
| Fecha emisión PO | `purchase_orders` | `emision_date_po` | `Fecha` | No |
| Fecha de creación en RAGA | `purchase_orders` | `order_date` | `Fecha` | No |
| **Condiciones comerciales** | | | | |
| Moneda | `purchase_orders` | `currency` | `Texto (3)` | No |
| Incoterm Precios | `purchase_orders` | `price_incoterm` | `Texto` | No |
| Incoterm de Compra | `purchase_orders` | `incoterms` | `Texto` | No |
| **Planificación logística** | | | | |
| Incoterm logístico | `purchase_orders` | `logistics_incoterm` | `Texto` | No |
| **Clasificación** | | | | |
| Categoría | `purchase_orders` | `category` | `Texto` | No |
| **Notas / Motivo** | | | | |
| Motivo | `purchase_orders` | `reason` | `Texto` | No |
| **Itinerario** | | | | |
| Puerto de Embarque | `purchase_orders` | `departure_port` | `Texto` | No |
| Puerto de Arribo | `purchase_orders` | `arrival_port` | `Texto` | No |
| **Naviera y equipo** | | | | |
| Línea Naviera | `purchase_orders` | `shipping_line` | `Texto` | No |
| Tipo de Contenedor | `purchase_orders` | `container_type` | `Texto` | No |
| Número de Contenedor | `purchase_orders` | `container_number` | `Texto` | No |
| **Identificadores de embarque** | | | | |
| MBL Number | `purchase_orders` | `mbl_number` | `Texto` | No |
| Proforma de Fábrica | `purchase_orders` | `factory_proforma_number` | `Texto` | No |
| **Datos Proveedor** | | | | |
| Seleccionar Nombre del Proveedor | `purchase_orders` | `vendor_id` | `ID Numérico` | Sí |
| Número de Proveedor | `purchase_orders` | `vendor_number` | `Texto` | No |
| **Dimensiones** | | | | |
| CBM (m³) | `purchase_orders` | `cbm` | `Decimal` | No |
| Peso (kg) | `purchase_orders` | `peso_kg` | `Decimal` | No |
| Peso (lb) | `purchase_orders` | `peso_lb` | `Decimal` | No |
| **Booking y coordinación** | | | | |
| Solicitud de Booking | `purchase_orders` | `date_booking_request` | `Fecha` | No |
| Autorización Booking | `purchase_orders` | `date_booking_authorized` | `Fecha` | No |
| Fecha Agente de Carga | `purchase_orders` | `forwader_date` | `Fecha` | No |
| **Origen: preparación y carga** | | | | |
| Fecha Inspección | `purchase_orders` | `inspection_date` | `Fecha` | No |
| Fecha Corte VGM | `purchase_orders` | `vgm_cut_date` | `Fecha` | No |
| Fecha Carga Lista Teórica | `purchase_orders` | `date_theorical_load` | `Fecha` | No |
| Fecha Carga Lista Variable | `purchase_orders` | `date_variable_date` | `Fecha` | No |
| Fecha Carga Lista Real | `purchase_orders` | `date_carga_po` | `Fecha` | No |
| Fecha de consolidado | `purchase_orders` | `date_consolidation` | `Fecha` | No |
| Fecha de release | `purchase_orders` | `release_date` | `Fecha` | No |
| Diferencia Fecha de Carga | `purchase_orders` | `dif_load_date` | `Fecha` | No |
| Nombre del Consolidador | `purchase_orders` | `consolidator_name` | `Texto` | No |
| **Salida (origen)** | | | | |
| ETD Inicial | `purchase_orders` | `date_etd_initial` | `Fecha` | No |
| ETD | `purchase_orders` | `date_etd` | `Fecha` | No |
| ATD | `purchase_orders` | `date_atd` | `Fecha` | No |
| ETD actualizada | `purchase_orders` | `date_etd_updated` | `Fecha` | No |
| **Arribo a destino** | | | | |
| ETA | `purchase_orders` | `date_eta` | `Fecha` | No |
| ETA actualizada | `purchase_orders` | `date_eta_updated` | `Fecha` | No |
| **Almacén fiscal y recepción** | | | | |
| Ingreso Almacén Fiscal | `purchase_orders` | `bonded_warehouse_enter` | `Fecha` | No |
| Salida Almacén Fiscal | `purchase_orders` | `bonded_warehouse_exit` | `Fecha` | No |
| Fecha Nota de Recibo | `purchase_orders` | `receipt_note_date` | `Fecha` | No |
| Fecha Disp. Bogeda Estimada | `purchase_orders` | `estimated_dc_availability_date` | `Fecha` | No |
| **Pagos y cargos** | | | | |
| Fecha Pago Balance | `purchase_orders` | `balance_payment_date` | `Fecha` | No |
| Fecha Pago Cargos Locales | `purchase_orders` | `local_charges_payment_date` | `Fecha` | No |
| **Métricas y varios** | | | | |
| Días Libres Contenedor | `purchase_orders` | `container_free_days` | `Entero` | No |
| Dif Fechas ETD (días) | `purchase_orders` | `etd_dates_difference` | `Entero` | (Automático) |
| Dif Fechas ETA (días) | `purchase_orders` | `eta_dates_difference` | `Entero` | (Automático) |
| **Configuración del envío** | | | | |
| Tipo de Transporte | `purchase_orders` | `mode` | `Texto` | No |
| Número de Booking | `purchase_orders` | `tracking_id` | `Texto` | No |
| **Opciones** | | | | |
| Aplica TLC | `purchase_orders` | `applies_tlc` | `Booleano` | No |
| Aplica AF | `purchase_orders` | `applies_af` | `Booleano` | No |
| Tiene Factura Mercancía | `purchase_orders` | `has_facture_merca` | `Booleano` | No |
| Tarifa Utilizada OK | `purchase_orders` | `used_rate_ok` | `Booleano` | No |
| Usa Almacén Fiscal | `purchase_orders` | `uses_bonded_warehouse` | `Booleano` | No |
| Aplica Nota Técnica | `purchase_orders` | `apply_technical_note` | `Booleano` | No |
| ETD Inicial Validada | `purchase_orders` | `etd_initial_validated` | `Booleano` | No |
| Puerto de Embarque Validado | `purchase_orders` | `port_of_loading_validated`| `Booleano` | No |
| **Volúmenes / pallets** | | | | |
| Cantidad estimada de pallets | `purchase_orders` | `pallet_quantity` | `Entero` | No |
| Cantidad Real de Pallets | `purchase_orders` | `pallet_quantity_real`| `Entero` | No |
| **Costos base** | | | | |
| Monto Factura | `purchase_orders` | `Invoice_amount` | `Decimal` | No |
| Monto Flete | `purchase_orders` | `freight_amount` | `Decimal` | No |
| Otros Gastos | `purchase_orders` | `other_expenses` | `Decimal` | No |
| **Totales y cálculos** | | | | |
| Costo Estimado de Pallets | `purchase_orders` | `estimated_pallet_cost` | `Decimal` | No |
| Costo Total Estimado PO | `purchase_orders` | `real_cost_estimated_po`| `Decimal` | No |
| Costo Real PO | `purchase_orders` | `real_cost_real_po`| `Decimal` | No |
| **Datos de negocio** | | | | |
| Agente de Carga | `purchase_orders` | `forwarder_name` | `Texto` | No |
| Proveedor de Servicio | `purchase_orders` | `service_provider` | `Texto` | No |
| Comercializadora | `purchase_orders` | `trading_company` | `Texto` | No |
| Tipo Tarifa | `purchase_orders` | `tariff_type` | `Texto` | No |
| Ruta Logística | `purchase_orders` | `route_label` | `Texto` | No |
| Grupo Repositor | `purchase_orders` | `retail_group` | `Texto` | No |
| Tipo Cliente | `purchase_orders` | `customer_type` | `Texto` | No |
| Factura | `purchase_orders` | `invoice` | `Texto` | No |
| Fecha recepción de factura | `purchase_orders` | `date_invoice_received` | `Fecha` | No |
| Fecha recepción doc. proveedor| `purchase_orders` | `date_vendor_document_received`| `Fecha` | No |
| Factura Flete | `purchase_orders` | `cargo_invoice_number` | `Texto` | No |
| Factura Mercancía | `purchase_orders` | `factura_merca` | `Texto` | No |
| DUA Internamiento | `purchase_orders` | `customs_dua` | `Texto` | No |
| Expediente | `purchase_orders` | `case_number_file` | `Texto` | No |
| Nota de Recibo | `purchase_orders` | `receipt_note` | `Texto` | No |
| Notas de Visibilidad | `purchase_orders` | `visibility_notes` | `Texto` | No |
| **Estado de llegada** | | | | |
| Estado | `purchase_orders` | `arrival_status` | `Texto` | No |
| Días de retraso | `purchase_orders` | `delay_days` | `Entero` | No |
| **Campos sin Frontend visible** | | | | |
| (No aplica) | `purchase_orders` | `status` | `Lista` | Sí |
| (No aplica) | `purchase_orders` | `kanban_status_id` | `ID Numérico` | No |
| (No aplica) | `purchase_orders` | `notes` | `Texto Largo` | No |
| (No aplica) | `purchase_orders` | `comments` | `Texto Largo` | No |
| (No aplica) | `purchase_orders` | `net_total` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `additional_cost`| `Decimal` | No |
| (No aplica) | `purchase_orders` | `total` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `insurance_cost` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `ground_transport_cost_1` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `ground_transport_cost_2` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `cost_nationalization` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `cost_ofr_estimated` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `cost_ofr_real` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `other_costs` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `variable_calculare_weight`| `Decimal` | No |
| (No aplica) | `purchase_orders` | `savings_ofr_fcl` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `saving_pickup` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `saving_executed` | `Decimal` | No |
| (No aplica) | `purchase_orders` | `saving_not_executed` | `Decimal` | No |

---

## 2. Mapeo de Datos: Maestros

A continuación, se presentan las tablas para los datos maestros principales.

### 2.1 Productos (`Product`)

| Nombre Frontend | Tabla | Nombre BD (Campo) | Tipo de Dato | Descripción | Ejemplo | Obligatorio |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| (No aplica) | `products` | `id` | `ID Autonumérico` | ID único del producto. | `1` | (Automático) |
| Compañía | `products` | `company_id` | `ID Numérico` | ID de la compañía a la que pertenece. | `1` | Sí |
| SKU | `products` | `sku` | `Texto` | Stock Keeping Unit, código único del producto.| `PROD-001` | Sí |
| Nombre | `products` | `name` | `Texto` | Nombre del producto. | `Tornillo de Acero 1/4"` | Sí |
| Descripción | `products` | `description` | `Texto Largo` | Descripción detallada del producto. | `Tornillo de acero inoxidable cabeza hexagonal.` | No |
| Precio | `products` | `price` | `Decimal` | Precio unitario del producto. | `1.25` | Sí |
| Stock | `products` | `stock` | `Entero` | Cantidad de unidades en inventario. | `10000` | Sí |

### 2.2 Proveedores (`Vendor`)

| Nombre Frontend | Tabla | Nombre BD (Campo) | Tipo de Dato | Descripción | Ejemplo | Obligatorio |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| (No aplica) | `vendors` | `id` | `ID Autonumérico` | ID único del proveedor. | `1` | (Automático) |
| Compañía | `vendors` | `company_id` | `ID Numérico` | ID de la compañía a la que está asociado. | `1` | Sí |
| Nombre | `vendors` | `name` | `Texto` | Nombre comercial del proveedor. | `Aceros del Norte S.A.` | Sí |
| Contacto | `vendors` | `contact_name` | `Texto` | Nombre de la persona de contacto. | `Juan Pérez` | No |
| Email | `vendors` | `contact_email`| `Email` | Correo del contacto. | `juan.perez@aceros.com`| No |
| Teléfono | `vendors` | `phone_number` | `Texto` | Teléfono del contacto. | `+52 81 1234 5678` | No |
| Dirección | `vendors` | `address` | `Texto` | Dirección física del proveedor. | `Av. Industrial 100, Monterrey, MX` | No |

### 2.3 Direcciones de Envío (`ShipTo`)

| Nombre Frontend | Tabla | Nombre BD (Campo) | Tipo de Dato | Descripción | Ejemplo | Obligatorio |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| (No aplica) | `ship_tos` | `id` | `ID Autonumérico` | ID único de la dirección. | `1` | (Automático) |
| Compañía | `ship_tos` | `company_id` | `ID Numérico` | ID de la compañía a la que pertenece. | `1` | Sí |
| Nombre | `ship_tos` | `name` | `Texto` | Nombre identificador de la dirección (ej: "Almacén Principal"). | `Almacén Principal` | Sí |
| Dirección | `ship_tos` | `address` | `Texto` | Dirección completa. | `Calle Falsa 123, Sprinfield` | Sí |
| Ciudad | `ship_tos` | `city` | `Texto` | Ciudad. | `Springfield` | Sí |
| Estado | `ship_tos` | `state` | `Texto` | Estado o provincia. | `Illinois` | Sí |
| País | `ship_tos` | `country` | `Texto` | País. | `USA` | Sí |
| Código Postal | `ship_tos` | `zip_code` | `Texto` | Código Postal. | `62704` | Sí |

### 2.4 Entidades de Facturación (`BillTo`)

| Nombre Frontend | Tabla | Nombre BD (Campo) | Tipo de Dato | Descripción | Ejemplo | Obligatorio |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| (No aplica) | `bill_tos` | `id` | `ID Autonumérico` | ID único de la entidad. | `1` | (Automático) |
| Compañía | `bill_tos` | `company_id` | `ID Numérico` | ID de la compañía a la que pertenece. | `1` | Sí |
| Nombre | `bill_tos` | `name` | `Texto` | Razón Social o nombre fiscal. | `Mi Empresa S.A. de C.V.` | Sí |
| ID Fiscal (RFC/TAX ID) | `bill_tos` | `tax_id` | `Texto` | Número de identificación fiscal. | `MEM840101ABC` | Sí |
| Dirección | `bill_tos` | `address` | `Texto` | Dirección fiscal completa. | `Av. de la Reforma 222, CDMX` | Sí |

---

## 3. Instrucciones para la Contraparte

### 3.1 Archivos Incluidos

Para la carga de datos, se proporcionan las siguientes plantillas CSV:

- `templates/purchase_orders_mapping.csv`
- `templates/products_mapping.csv`
- `templates/vendors_mapping.csv`
- `templates/ship_tos_mapping.csv`
- `templates/bill_tos_mapping.csv`

### 3.2 Proceso de Preparación de Datos

1.  **Utilice las plantillas CSV**: Abra los archivos en un editor de hojas de cálculo (como Excel, Google Sheets).
2.  **Complete los datos**: Llene las columnas correspondientes a los campos de la base de datos.
3.  **No altere la estructura**: No modifique los nombres de las columnas ni su orden.

### 3.3 Formatos de Datos Requeridos

#### Fechas
-   **Fechas simples**: `YYYY-MM-DD` (ej: `2025-09-12`)
-   **Fechas con hora**: `YYYY-MM-DD HH:MM:SS` (ej: `2025-10-30 17:00:00`)

#### Números
-   **Decimales**: Utilice un punto `.` como separador (ej: `123.45`).
-   **Enteros**: Números sin decimales (ej: `100`).

#### Texto
-   **Comillas**: Si un campo de texto contiene una coma, debe ir entre comillas dobles (ej: `"Valor con, coma"`).
-   **Caracteres especiales**: Evite el uso de caracteres que puedan interferir con el formato CSV.

### 3.4 Campos Obligatorios y Relaciones

-   **Campos Obligatorios**: Todos los campos marcados como "Sí" en las tablas de mapeo deben ser completados.
-   **IDs de Relación**: Campos como `company_id`, `vendor_id`, etc., deben contener un ID que ya exista en la tabla maestra correspondiente.

### 3.5 Orden de Carga Recomendado

1.  **Datos Maestros**: Cargue primero los archivos de Productos, Proveedores, ShipTo y BillTo.
2.  **Órdenes de Compra**: Una vez cargados los maestros, proceda con el archivo de Órdenes de Compra.

### 3.6 Proceso de Entrega y Validación

1.  **Revisión**: Antes de entregar, valide que todos los campos obligatorios estén completos y los formatos sean correctos.
2.  **Entrega**: Envíe todos los archivos CSV completados.
3.  **Soporte**: Para cualquier duda sobre el formato o contenido, contacte al equipo de desarrollo.

---

*Documento generado el: 2025-09-15*
*Versión: 3.0*
