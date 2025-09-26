# Mapeo de Campos de la API (Diccionario de Datos)

Este documento sirve como la fuente central de verdad para los campos expuestos por la API. Conecta el nombre del campo en la base de datos, su tipo, cómo se presenta en la API (y por tanto, cómo lo consume el frontend), su descripción funcional, y si es obligatorio al crear un nuevo recurso.

Esta estructura puede ser utilizada como plantilla para documentar las entidades de cualquier proyecto.

---

## Entidad: Purchase Order (`purchase_orders`)

Esta es la entidad principal del sistema. A continuación se detalla el mapeo campo por campo.

| Campo en BD | Tipo de Dato (BD) | Campo en API/Frontend | Descripción | Requerido (en creación) |
|---|---|---|---|---|
| `id` | `bigint` / `integer` | `id` | Identificador único de la orden de compra. | No (Automático) |
| `company_id` | `bigint` / `integer` | `company_id` | ID de la compañía que emite la orden. | Sí |
| `vendor_id` | `bigint` / `integer` | `vendor_id` | ID del proveedor asociado a la orden. | Sí (o `vendor_name`) |
| `ship_to_id` | `bigint` / `integer` | `ship_to_id` | ID de la dirección de envío. | Opcional |
| `bill_to_id` | `bigint` / `integer` | `bill_to_id` | ID de la dirección de facturación. | Opcional |
| `order_number` | `varchar` / `string` | `order_number` | Número único y legible de la orden. | Sí |
| `order_date` | `date` | `order_date` | Fecha en que se emitió la orden. | Opcional |
| `status` | `varchar` / `string` | `status` | Estado actual del ciclo de vida de la orden. | No (Default) |
| `currency` | `varchar` / `string` | `currency` | Moneda en la que se realiza la transacción (ej: 'USD'). | Opcional (Default 'USD') |
| `incoterms` | `varchar` / `string` | `incoterms` | Términos Internacionales de Comercio que definen la transacción. | Sí |
| `payment_terms` | `varchar` / `string` | `payment_terms` | Condiciones de pago acordadas. | Opcional |
| `net_total` | `decimal` | `net_total` | Monto total de los productos sin incluir costos adicionales. | Opcional |
| `total` | `decimal` | `total` | Monto final de la orden, incluyendo todos los costos. | Opcional |
| `kanban_status_id` | `bigint` / `integer` | `kanban_status_id` | ID del estado actual en el tablero Kanban visual. | No (Automático) |
| `priority` | `varchar` / `string` | `priority` | Nivel de urgencia de la orden (ej: 'Baja', 'Media', 'Alta'). | Opcional |
| `factory_proforma_number` | `varchar` / `string` | `factory_proforma_number` | Número de la factura proforma de la fábrica. | Sí |
| `mbl_number` | `varchar` / `string` | `mbl_number` | Número del Master Bill of Lading (documento de embarque principal). | Opcional |
| `container_type` | `varchar` / `string` | `container_type` | Tipo de contenedor (ej: '20FT', '40FT'). | Opcional |
| `container_number` | `varchar` / `string` | `container_number` | Número de identificación único del contenedor. | Opcional |
| `shipping_line` | `varchar` / `string` | `shipping_line` | Nombre de la línea naviera encargada del transporte. | Opcional |
| `is_dropship` | `boolean` | `is_dropship` | Indica si es un envío directo al cliente final. | Opcional (Default `false`) |
| `applies_tlc` | `boolean` | `applies_tlc` | Indica si aplica un Tratado de Libre Comercio. | Opcional (Default `false`) |
| `applies_af` | `boolean` | `applies_af` | Indica si aplica Almacén Fiscal. | Opcional (Default `false`) |
| `date_booking_request` | `datetime` | `date_booking_request` | Fecha y hora en que se solicitó el booking (reserva de espacio). | Opcional |
| `date_booking_authorized` | `datetime` | `date_booking_authorized` | Fecha y hora en que se autorizó el booking. | Opcional |
| `date_theorical_load` | `datetime` | `date_theorical_load` | Fecha teórica en la que la carga debería estar lista. | Sí |
| `logistics_incoterm` | `varchar` / `string` | `logistics_incoterm` | Incoterm específico para la operación logística. | Sí |
| `reason` | `varchar` / `string` | `reason` | Motivo o justificación de la orden de compra. | Sí |
| `category` | `varchar` / `string` | `category` | Categoría a la que pertenece la orden (ej: 'Electrónicos'). | Sí |
| `forwarder_name` | `varchar` / `string` | `forwarder_name` | Nombre del agente de carga (Freight Forwarder). | Opcional |
| `cargo_invoice_number` | `varchar` / `string` | `cargo_invoice_number` | Número de la factura de la carga. | Opcional |
| `route_label` | `varchar` / `string` | `route_label` | Etiqueta o nombre de la ruta logística asignada. | Sí |
| `arrival_status` | `varchar` / `string` | `arrival_status` | Estado de la llegada (ej: 'On Time', 'Delayed'). | Opcional |
| `delay_days` | `integer` | `delay_days` | Número de días de retraso. | Opcional |
| `date_eta_updated` | `datetime` | `date_eta_updated` | Última fecha estimada de llegada actualizada. | Opcional |
| `date_etd_updated` | `datetime` | `date_etd_updated` | Última fecha estimada de salida actualizada. | Opcional |
| `arrival_port` | `varchar` / `string` | `arrival_port` | Puerto de llegada. | Opcional |
| `departure_port` | `varchar` / `string` | `departure_port` | Puerto de salida. | Opcional |
| `bonded_warehouse_enter` | `datetime` | `bonded_warehouse_enter` | Fecha de ingreso al Almacén Fiscal. | Sí |
| `bonded_warehouse_exit` | `datetime` | `bonded_warehouse_exit` | Fecha de salida del Almacén Fiscal. | Sí |
| `price_incoterm` | `varchar` / `string` | `price_incoterm` | Incoterm que define las condiciones de precio. | Sí |
| `cbm` | `decimal` | `cbm` | Metros cúbicos totales de la carga. | Opcional |
| `items` | `array` | `items` | Array de objetos, cada uno representando un producto en la orden. | Sí |

*Nota: La columna "Tipo de Dato (BD)" es una inferencia basada en los `$casts` del modelo de Laravel y convenciones comunes. Puede variar ligeramente de la implementación exacta en la base de datos.*
