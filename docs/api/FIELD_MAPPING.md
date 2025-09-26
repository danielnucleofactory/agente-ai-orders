# Mapeo de Campos de la API (Diccionario de Datos)

Este documento sirve como la fuente central de verdad para los campos expuestos por la API. Conecta el nombre del campo en la base de datos, su tipo, cómo se presenta en la API, cómo lo ve el usuario en el frontend, su descripción funcional, y si es obligatorio al crear un nuevo recurso.

Esta estructura puede ser utilizada como plantilla para documentar las entidades de cualquier proyecto.

---

## Entidad: Purchase Order (`purchase_orders`)

Esta es la entidad principal del sistema. A continuación se detalla el mapeo campo por campo.

| Campo en BD | Tipo de Dato (BD) | Campo en API | Campo en Frontend | Descripción | Requerido (en creación) |
|---|---|---|---|---|---|
| `id` | `bigint` / `integer` | `id` | (No visible) | Identificador único de la orden de compra. | No (Automático) |
| `company_id` | `bigint` / `integer` | `company_id` | (No visible) | ID de la compañía que emite la orden. | Sí |
| `vendor_id` | `bigint` / `integer` | `vendor_id` | Seleccionar Nombre del Proveedor | ID del proveedor asociado a la orden. | Sí |
| `vendor_number` | `varchar` / `string` | `vendor_number` | Número de Proveedor | Número único del proveedor. | Opcional |
| `ship_to_id` | `bigint` / `integer` | `ship_to_id` | (No visible) | ID de la dirección de envío. | Opcional |
| `bill_to_id` | `bigint` / `integer` | `bill_to_id` | (No visible) | ID de la dirección de facturación. | Opcional |
| `order_number` | `varchar` / `string` | `order_number` | Número de Orden (PO) | Número único y legible de la orden. | Sí |
| `emision_date_po` | `date` | `emision_date_po` | Fecha emisión PO | Fecha de emisión de la orden de compra. | Opcional |
| `order_date` | `date` | `order_date` | Fecha de creación en RAGA | Fecha en que se emitió la orden. | Opcional |
| `status` | `varchar` / `string` | `status` | (No visible) | Estado actual del ciclo de vida de la orden. | No (Default) |
| `currency` | `varchar` / `string` | `currency` | Moneda | Moneda en la que se realiza la transacción (ej: 'USD'). | Opcional (Default 'USD') |
| `price_incoterm` | `varchar` / `string` | `price_incoterm` | Incoterm Precios | Incoterm que define las condiciones de precio. | Sí |
| `incoterms` | `varchar` / `string` | `incoterms` | Incoterm de Compra | Términos Internacionales de Comercio que definen la transacción. | Sí |
| `logistics_incoterm` | `varchar` / `string` | `logistics_incoterm` | Incoterm logístico | Incoterm específico para la operación logística. | Sí |
| `payment_terms` | `varchar` / `string` | `payment_terms` | (No visible) | Condiciones de pago acordadas. | Opcional |
| `category` | `varchar` / `string` | `category` | Categoría | Categoría a la que pertenece la orden. | Sí |
| `reason` | `varchar` / `string` | `reason` | Motivo | Motivo o justificación de la orden de compra. | Sí |
### **Campos de Transporte e Identificadores**
| `departure_port` | `varchar` / `string` | `departure_port` | Puerto de Embarque | Puerto de salida del envío. | Opcional |
| `arrival_port` | `varchar` / `string` | `arrival_port` | Puerto de Arribo | Puerto de llegada del envío. | Opcional |
| `shipping_line` | `varchar` / `string` | `shipping_line` | Línea Naviera | Nombre de la línea naviera encargada del transporte. | Opcional |
| `container_type` | `varchar` / `string` | `container_type` | Tipo de Contenedor | Tipo de contenedor (ej: '20FT', '40FT'). | Opcional |
| `container_number` | `varchar` / `string` | `container_number` | Número de Contenedor | Número de identificación único del contenedor. | Opcional |
| `mbl_number` | `varchar` / `string` | `mbl_number` | MBL Number | Número del Master Bill of Lading (documento de embarque principal). | Opcional |
| `factory_proforma_number` | `varchar` / `string` | `factory_proforma_number` | Proforma de Fábrica | Número de la factura proforma de la fábrica. | Sí |

### **Campos de Dimensiones**
| `cbm` | `decimal` | `cbm` | CBM (m³) | Metros cúbicos totales de la carga. | Opcional |
| `peso_kg` | `decimal` | `peso_kg` | Peso (kg) | Peso total en kilogramos. | Opcional |
| `peso_lb` | `decimal` | `peso_lb` | Peso (lb) | Peso total en libras. | Opcional |

### **Campos de Fechas**
| `date_booking_request` | `date` | `date_booking_request` | Solicitud de Booking | Fecha en que se solicitó el booking (reserva de espacio). | Opcional |
| `date_booking_authorized` | `date` | `date_booking_authorized` | Autorización Booking | Fecha en que se autorizó el booking. | Opcional |
| `forwader_date` | `date` | `forwader_date` | Fecha Agente de Carga | Fecha del agente de carga. | Opcional |
| `inspection_date` | `date` | `inspection_date` | Fecha Inspección | Fecha de inspección. | Opcional |
| `vgm_cut_date` | `date` | `vgm_cut_date` | Fecha Corte VGM | Fecha de corte VGM. | Opcional |
| `date_theorical_load` | `date` | `date_theorical_load` | Fecha Carga Lista Teórica | Fecha teórica en la que la carga debería estar lista. | Sí |
| `date_variable_date` | `date` | `date_variable_date` | Fecha Carga Lista Variable | Fecha variable de carga lista. | Opcional |
| `date_carga_po` | `date` | `date_carga_po` | Fecha Carga Lista Real | Fecha real de carga lista. | Opcional |
| `date_consolidation` | `date` | `date_consolidation` | Fecha de consolidado | Fecha de consolidación. | Opcional |
| `release_date` | `date` | `release_date` | Fecha de release | Fecha de liberación. | Opcional |
| `dif_load_date` | `date` | `dif_load_date` | Diferencia Fecha de Carga | Diferencia en fecha de carga. | Opcional |
| `consolidator_name` | `varchar` / `string` | `consolidator_name` | Nombre del Consolidador | Nombre del consolidador. | Opcional |
| `date_etd_initial` | `date` | `date_etd_initial` | ETD Inicial | Fecha estimada de salida inicial. | Opcional |
| `date_etd` | `date` | `date_etd` | ETD | Fecha estimada de salida. | Opcional |
| `date_atd` | `date` | `date_atd` | ATD | Fecha actual de salida. | Opcional |
| `date_eta` | `date` | `date_eta` | ETA | Fecha estimada de llegada. | Opcional |
| `date_eta_updated` | `date` | `date_eta_updated` | ETA Inicial | Fecha estimada de llegada inicial. | Opcional |
| `date_ata` | `date` | `date_ata` | ATA | Fecha actual de llegada. | Opcional |
| `bonded_warehouse_enter` | `date` | `bonded_warehouse_enter` | Ingreso Almacén Fiscal | Fecha de ingreso al Almacén Fiscal. | Sí |
| `bonded_warehouse_exit` | `date` | `bonded_warehouse_exit` | Salida Almacén Fiscal | Fecha de salida del Almacén Fiscal. | Sí |
| `receipt_note_date` | `date` | `receipt_note_date` | Fecha Nota de Recibo | Fecha de la nota de recibo. | Opcional |
| `estimated_dc_availability_date` | `date` | `estimated_dc_availability_date` | Fecha Disp. Bodega Estimada | Fecha estimada de disponibilidad en bodega. | Opcional |
| `balance_payment_date` | `date` | `balance_payment_date` | Fecha Pago Balance | Fecha de pago del balance. | Opcional |
| `local_charges_payment_date` | `date` | `local_charges_payment_date` | Fecha Pago Cargos Locales | Fecha de pago de cargos locales. | Opcional |

### **Campos de Información Adicional**
| `mode` | `varchar` / `string` | `mode` | Tipo de Transporte | Modo de transporte (Marítimo, Aéreo, Terrestre). | Opcional |
| `tracking_id` | `varchar` / `string` | `tracking_id` | Número de Booking | Número de booking. | Opcional |
| `pallet_quantity` | `integer` | `pallet_quantity` | Cantidad estimada de pallets | Cantidad estimada de pallets. | Opcional |
| `pallet_quantity_real` | `integer` | `pallet_quantity_real` | Cantidad Real de Pallets | Cantidad real de pallets. | Opcional |
| `Invoice_amount` | `decimal` | `Invoice_amount` | Monto Factura | Monto de la factura. | Opcional |
| `freight_amount` | `decimal` | `freight_amount` | Monto Flete | Monto del flete. | Opcional |
| `other_expenses` | `decimal` | `other_expenses` | Otros Gastos | Otros gastos adicionales. | Opcional |
| `estimated_pallet_cost` | `decimal` | `estimated_pallet_cost` | Costo Estimado de Pallets | Costo estimado por pallet. | Opcional |
| `real_cost_estimated_po` | `decimal` | `real_cost_estimated_po` | Costo Total Estimado PO | Costo total estimado de la PO. | Opcional |
| `real_cost_real_po` | `decimal` | `real_cost_real_po` | Costo Real PO | Costo real de la PO. | Opcional |

### **Campos de Datos de Negocio**
| `forwarder_name` | `varchar` / `string` | `forwarder_name` | Agente de Carga | Nombre del agente de carga (Freight Forwarder). | Opcional |
| `service_provider` | `varchar` / `string` | `service_provider` | Proveedor de Servicio | Proveedor de servicio. | Opcional |
| `trading_company` | `varchar` / `string` | `trading_company` | Comercializadora | Comercializadora. | Opcional |
| `tariff_type` | `varchar` / `string` | `tariff_type` | Tipo Tarifa | Tipo de tarifa. | Opcional |
| `route_label` | `varchar` / `string` | `route_label` | Ruta Logística | Etiqueta o nombre de la ruta logística asignada. | Sí |
| `retail_group` | `varchar` / `string` | `retail_group` | Grupo Repositor | Grupo repositor. | Opcional |
| `customer_type` | `varchar` / `string` | `customer_type` | Tipo Cliente | Tipo de cliente. | Opcional |
| `invoice` | `varchar` / `string` | `invoice` | Factura | Número de factura. | Opcional |
| `date_invoice_received` | `date` | `date_invoice_received` | Fecha recepción de factura | Fecha de recepción de factura. | Opcional |
| `date_vendor_document_received` | `date` | `date_vendor_document_received` | Fecha recepción doc. proveedor | Fecha de recepción de documentos del proveedor. | Opcional |
| `cargo_invoice_number` | `varchar` / `string` | `cargo_invoice_number` | Factura Flete | Número de la factura del flete. | Opcional |
| `factura_merca` | `varchar` / `string` | `factura_merca` | Factura Mercancía | Número de factura de mercancía. | Opcional |
| `customs_dua` | `varchar` / `string` | `customs_dua` | DUA Internamiento | DUA de internamiento. | Opcional |
| `case_number_file` | `varchar` / `string` | `case_number_file` | Expediente | Número de expediente. | Opcional |
| `receipt_note` | `varchar` / `string` | `receipt_note` | Nota de Recibo | Nota de recibo. | Opcional |
| `visibility_notes` | `varchar` / `string` | `visibility_notes` | Notas de Visibilidad | Notas de visibilidad. | Opcional |

### **Campos de Estado de Llegada**
| `arrival_status` | `varchar` / `string` | `arrival_status` | Estado | Estado de la llegada (ej: 'On Time', 'Delayed'). | Opcional |
| `delay_days` | `integer` | `delay_days` | Días de retraso | Número de días de retraso. | Opcional |

### **Campos de Opciones/Flags**
| `applies_tlc` | `boolean` | `applies_tlc` | Aplica TLC | Indica si aplica un Tratado de Libre Comercio. | Opcional (Default `false`) |
| `applies_af` | `boolean` | `applies_af` | Aplica AF | Indica si aplica Almacén Fiscal. | Opcional (Default `false`) |
| `has_facture_merca` | `boolean` | `has_facture_merca` | Tiene Factura Mercancía | Indica si tiene factura de mercancía. | Opcional (Default `false`) |
| `used_rate_ok` | `boolean` | `used_rate_ok` | Tarifa Utilizada OK | Indica si la tarifa utilizada está OK. | Opcional (Default `false`) |
| `uses_bonded_warehouse` | `boolean` | `uses_bonded_warehouse` | Usa Almacén Fiscal | Indica si usa almacén fiscal. | Opcional (Default `false`) |
| `apply_technical_note` | `boolean` | `apply_technical_note` | Aplica Nota Técnica | Indica si aplica nota técnica. | Opcional (Default `false`) |
| `etd_initial_validated` | `boolean` | `etd_initial_validated` | ETD Inicial Validada | Indica si ETD inicial está validada. | Opcional (Default `false`) |
| `port_of_loading_validated` | `boolean` | `port_of_loading_validated` | Puerto de Embarque Validado | Indica si puerto de embarque está validado. | Opcional (Default `false`) |

### **Campos de Métricas**
| `container_free_days` | `integer` | `container_free_days` | Días Libres Contenedor | Días libres del contenedor. | Opcional |
| `etd_dates_difference` | `integer` | `etd_dates_difference` | Dif Fechas ETD (días) | Diferencia en días de fechas ETD (calculado automáticamente). | No (Automático) |
| `eta_dates_difference` | `integer` | `eta_dates_difference` | Dif Fechas ETA (días) | Diferencia en días de fechas ETA (calculado automáticamente). | No (Automático) |

### **Campos del Sistema**
| `net_total` | `decimal` | `net_total` | (Calculado) | Monto total de los productos sin incluir costos adicionales. | No (Automático) |
| `total` | `decimal` | `total` | (Calculado) | Monto final de la orden, incluyendo todos los costos. | No (Automático) |
| `kanban_status_id` | `bigint` / `integer` | `kanban_status_id` | (No visible) | ID del estado actual en el tablero Kanban visual. | No (Automático) |
| `items` | `array` | `items` | (Tabla de productos) | Array de objetos, cada uno representando un producto en la orden. | Sí |

*Nota: La columna "Tipo de Dato (BD)" es una inferencia basada en los `$casts` del modelo de Laravel y convenciones comunes. Puede variar ligeramente de la implementación exacta en la base de datos.*
