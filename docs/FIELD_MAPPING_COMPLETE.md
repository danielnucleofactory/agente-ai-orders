# Mapeo Completo de Campos - Base de Datos y Frontend

Este documento mapea todos los campos de la aplicación con su nombre en la base de datos y su etiqueta/nombre en el frontend. Útil para comparativas y documentación.

---

## 1. Purchase Orders (Órdenes de Compra)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único de la orden |
| `company_id` | bigint | (No visible) | ID de la compañía |
| `order_number` | varchar | Número de Orden (PO) | Número único de orden |
| `emision_date_po` | date | Fecha emisión PO | Fecha de emisión de la PO |
| `order_date` | date | Fecha de creación en RAGA | Fecha de creación |
| `status` | varchar | Estado | Estado de la orden |
| `vendor_id` | bigint | Seleccionar Nombre del Proveedor | ID del proveedor |
| `vendor_number` | varchar | Número de Proveedor | Código del proveedor |
| `ship_to_id` | bigint | Seleccionar Ship to | ID dirección de envío |
| `bill_to_id` | bigint | Seleccionar Bill to | ID dirección de facturación |
| `currency` | varchar | Moneda | Moneda de la transacción |
| `price_incoterm` | varchar | Incoterm Precios | Incoterm de precios |
| `incoterms` | varchar | Incoterm de Compra | Incoterm de compra |
| `logistics_incoterm` | varchar | Incoterm logístico | Incoterm logístico |
| `category` | varchar | Categoría | Categoría de la orden |
| `reason` | varchar | Motivo | Motivo de la orden |
| `departure_port` | varchar | Puerto de Embarque | Puerto de salida |
| `port_of_loading_validated` | boolean | Puerto de Embarque Validado | Validación del puerto |
| `arrival_port` | varchar | Puerto de Arribo | Puerto de llegada |
| `shipping_line` | varchar | Línea Naviera | Línea de envío |
| `container_type` | varchar | Tipo de Contenedor | Tipo de contenedor |
| `container_number` | varchar | Número de Contenedor | Número del contenedor |
| `mbl_number` | varchar | MBL Number | Número MBL |
| `factory_proforma_number` | varchar | Proforma de Fábrica | Número de proforma |
| `mode` | varchar | Tipo de Transporte | Modalidad de transporte |
| `tracking_id` | varchar | Número de Booking | ID de rastreo/booking |
| `bill_of_lading` | varchar | Conocimiento de Embarque | Conocimiento de embarque |
| `material_type` | json | Tipo de Material | Tipo de material (array) |
| `is_dropship` | boolean | Dropship | Es dropship |
| `applies_tlc` | boolean | Aplica TLC | Aplica TLC |
| `applies_af` | boolean | Aplica AF | Aplica AF |
| `has_facture_merca` | boolean | Tiene Factura Mercancía | Tiene factura mercancía |
| `used_rate_ok` | boolean | Tarifa Utilizada OK | Tarifa utilizada correcta |
| `uses_bonded_warehouse` | boolean | Usa Almacén Fiscal | Usa almacén fiscal |
| `apply_technical_note` | boolean | Aplica Nota Técnica | Aplica nota técnica |
| `length` | decimal | Largo | Longitud |
| `width` | decimal | Ancho | Ancho |
| `height` | decimal | Alto | Altura |
| `volume` | decimal | Volumen | Volumen calculado |
| `cbm` | decimal | CBM (m³) | Metros cúbicos |
| `length_cm` | decimal | Largo (cm) | Longitud en cm |
| `width_cm` | decimal | Ancho (cm) | Ancho en cm |
| `height_cm` | decimal | Alto (cm) | Altura en cm |
| `weight_kg` | decimal | Peso (kg) | Peso en kilogramos |
| `weight_lb` | decimal | Peso (lb) | Peso en libras |
| `pallet_quantity` | integer | Cantidad estimada de pallets | Cantidad de pallets estimada |
| `pallet_quantity_real` | integer | Cantidad Real de Pallets | Cantidad real de pallets |
| `date_booking_request` | datetime | Solicitud de Booking | Fecha solicitud booking |
| `date_booking_authorized` | datetime | Autorización Booking | Fecha autorización booking |
| `forwader_date` | datetime | Fecha de asignación de agente de carga | Fecha del forwarder |
| `inspection_date` | datetime | Fecha Inspección | Fecha de inspección |
| `vgm_cut_date` | datetime | Fecha Corte VGM | Fecha corte VGM |
| `date_theorical_load` | datetime | Fecha Carga Lista Teórica | Fecha teórica de carga |
| `date_variable_date` | datetime | Fecha Carga Lista Variable | Fecha variable |
| `carga_lista_validada` | boolean | Carga Lista Validada | Carga lista validada |
| `date_planned_pickup` | datetime | Fecha pickup planificada | Fecha pickup planificada |
| `date_actual_pickup` | datetime | Fecha pickup real | Fecha pickup real |
| `date_consolidation` | datetime | Fecha de consolidado | Fecha de consolidación |
| `release_date` | datetime | Fecha de release | Fecha de liberación |
| `dif_load_date` | datetime | Diferencia de fecha de carga lista | Diferencia de fecha |
| `consolidator_name` | varchar | Nombre del Consolidador | Nombre del consolidador |
| `date_etd_initial` | datetime | ETD Inicial | Fecha ETD inicial |
| `etd_initial_validated` | boolean | ETD Inicial Validada | ETD inicial validada |
| `date_etd` | datetime | ETD Variable | Fecha ETD variable |
| `date_atd` | datetime | ATD | Fecha ATD |
| `date_estimated_hub_arrival` | datetime | Fecha estimada de llegada al hub | Fecha estimada hub |
| `date_actual_hub_arrival` | datetime | Fecha de llegada real al hub | Fecha real hub |
| `date_eta_initial` | datetime | ETA Inicial | Fecha ETA inicial |
| `date_eta_updated` | datetime | ETA Variable | Fecha ETA actualizada |
| `date_eta` | datetime | ETA Variable | Fecha ETA |
| `date_ata` | datetime | ATA | Fecha ATA |
| `date_required_in_destination` | datetime | Fecha requerida en destino | Fecha requerida destino |
| `bonded_warehouse_enter` | datetime | Ingreso Almacén Fiscal | Fecha ingreso almacén |
| `bonded_warehouse_exit` | datetime | Salida Almacén Fiscal | Fecha salida almacén |
| `date_received` | datetime | Fecha Recepción | Fecha de recepción |
| `receipt_note_date` | datetime | Fecha Nota de Recibo | Fecha nota de recibo |
| `estimated_dc_availability_date` | datetime | Fecha Disp. Bogeda Estimada | Fecha disponibilidad DC |
| `balance_payment_date` | datetime | Fecha Pago Balance | Fecha pago balance |
| `local_charges_payment_date` | datetime | Fecha Pago Cargos Locales | Fecha pago cargos locales |
| `container_free_days` | integer | Días Libres Contenedor | Días libres contenedor |
| `etd_dates_difference` | integer | Dif Fechas ETD (días) | Diferencia fechas ETD |
| `eta_dates_difference` | integer | Dif Fechas ETA (días) | Diferencia fechas ETA |
| `Invoice_amount` | decimal | Monto Factura | Monto de factura |
| `freight_amount` | decimal | Monto Flete | Monto de flete |
| `insurance_cost` | decimal | Costo de Seguro | Costo del seguro |
| `other_expenses` | decimal | Otros Gastos | Otros gastos |
| `net_total` | decimal | Total Neto | Total neto |
| `additional_cost` | decimal | Costo Adicional | Costo adicional |
| `total` | decimal | Total | Total |
| `planned_hub_id` | bigint | HUB Planificado | ID hub planificado |
| `actual_hub_id` | bigint | HUB Real | ID hub real |
| `kanban_status_id` | bigint | Estado Kanban | ID estado kanban |
| `trading_company` | varchar | Trading Company | Compañía comercial |
| `service_provider` | varchar | Service Provider | Proveedor de servicio |
| `forwarder_name` | varchar | Forwarder Name | Nombre del forwarder |
| `cargo_invoice_number` | varchar | Cargo Invoice Number | Número factura cargo |
| `tariff_type` | varchar | Tariff Type | Tipo de tarifa |
| `route_label` | varchar | Route Label | Etiqueta de ruta |
| `arrival_status` | varchar | Arrival Status | Estado de llegada |
| `delay_days` | integer | Delay Days | Días de retraso |
| `date_etd_updated` | datetime | ETD Updated | Fecha ETD actualizada |
| `customs_dua` | varchar | Customs DUA | DUA aduanero |
| `invoice` | varchar | Invoice | Factura |
| `factura_merca` | varchar | Factura Mercancía | Factura mercancía |
| `case_number_file` | varchar | Expediente | Número de expediente |
| `receipt_note` | varchar | Nota de Recibo | Nota de recibo |
| `visibility_notes` | text | Notas de Visibilidad | Notas de visibilidad |
| `notes` | text | Notas | Notas generales |
| `comments` | text | Comentarios | Comentarios |
| `payment_terms` | varchar | Payment Terms | Términos de pago |
| `order_place` | varchar | Order Place | Lugar de orden |
| `email_agent` | varchar | Email Agent | Email del agente |
| `ground_transport_cost_1` | decimal | Ground Transport Cost 1 | Costo transporte terrestre 1 |
| `ground_transport_cost_2` | decimal | Ground Transport Cost 2 | Costo transporte terrestre 2 |
| `cost_nationalization` | decimal | Cost Nationalization | Costo nacionalización |
| `cost_ofr_estimated` | decimal | Cost OFR Estimated | Costo OFR estimado |
| `cost_ofr_real` | decimal | Cost OFR Real | Costo OFR real |
| `estimated_pallet_cost` | decimal | Estimated Pallet Cost | Costo estimado pallet |
| `real_cost_estimated_po` | decimal | Real Cost Estimated PO | Costo real estimado PO |
| `real_cost_real_po` | decimal | Real Cost Real PO | Costo real PO |
| `other_costs` | decimal | Other Costs | Otros costos |
| `variable_calculare_weight` | decimal | Variable Calculate Weight | Variable cálculo peso |
| `savings_ofr_fcl` | decimal | Savings OFR FCL | Ahorros OFR FCL |
| `saving_pickup` | decimal | Saving Pickup | Ahorro pickup |
| `saving_executed` | decimal | Saving Executed | Ahorro ejecutado |
| `saving_not_executed` | decimal | Saving Not Executed | Ahorro no ejecutado |
| `pallets` | integer | Pallets | Pallets |
| `ensurence_type` | varchar | Seguro | Tipo de seguro |
| `insurance_type` | varchar | Insurance Type | Tipo de seguro |
| `confirmation_hash` | varchar | Confirmation Hash | Hash de confirmación |
| `hash_expires_at` | datetime | Hash Expires At | Expiración hash |
| `confirmation_email_sent` | boolean | Confirmation Email Sent | Email confirmación enviado |
| `confirmation_email_sent_at` | datetime | Confirmation Email Sent At | Fecha email confirmación |
| `last_email_type_sent` | varchar | Last Email Type Sent | Último tipo email enviado |
| `last_email_sent_at` | datetime | Last Email Sent At | Última fecha email |
| `email_sent_history` | text | Email Sent History | Historial emails |
| `update_date_po` | date | Update Date PO | Fecha actualización PO |
| `confirm_update_date_po` | boolean | Confirm Update Date PO | Confirmar actualización |
| `total_amount` | decimal | Total Amount | Monto total |
| `date_invoice_received` | datetime | Date Invoice Received | Fecha recepción factura |
| `date_vendor_document_received` | datetime | Date Vendor Document Received | Fecha recepción documento |
| `retail_group` | varchar | Retail Group | Grupo retail |
| `customer_type` | varchar | Customer Type | Tipo de cliente |
| `created_at` | timestamp | Fecha de creación en Next | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |
| `deleted_at` | timestamp | Deleted At | Fecha de eliminación |

---

## 2. Shipping Documents (Documentos de Embarque)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del documento |
| `company_id` | bigint | (No visible) | ID de la compañía |
| `document_number` | varchar | Número de Documento | Número único del documento |
| `status` | varchar | Estado | Estado del documento |
| `creation_date` | date | Fecha de Creación | Fecha de creación |
| `porth_shipment_id` | varchar | Porth Shipment ID | ID de envío Porth |
| `estimated_departure_date` | date | Fecha Estimada de Salida | Fecha estimada salida |
| `estimated_arrival_date` | date | Fecha Estimada de Llegada | Fecha estimada llegada |
| `actual_departure_date` | date | Fecha Real de Salida | Fecha real salida |
| `actual_arrival_date` | date | Fecha Real de Llegada | Fecha real llegada |
| `hub_location` | varchar | Hub Location | Ubicación del hub |
| `total_weight_kg` | integer | Peso Total (kg) | Peso total en kg |
| `notes` | text | Notas | Notas |
| `release_date` | date | Fecha de Liberación | Fecha de liberación |
| `booking_code` | varchar | Código de Booking | Código de booking |
| `container_number` | varchar | Número de Contenedor | Número del contenedor |
| `mbl_number` | varchar | Número MBL | Número MBL |
| `hbl_number` | varchar | Número HBL | Número HBL |
| `date_theorical_load` | datetime | Fecha Teórica de Carga | Fecha teórica carga |
| `date_variable_date` | datetime | Fecha Variable | Fecha variable |
| `service_provider` | varchar | Proveedor de Servicio | Proveedor de servicio |
| `forwarder_name` | varchar | Nombre del Forwarder | Nombre del forwarder |
| `date_booking_request` | datetime | Fecha de Solicitud de Booking | Fecha solicitud booking |
| `date_booking_authorized` | datetime | Fecha de Autorización de Booking | Fecha autorización booking |
| `date_etd_updated` | datetime | Fecha ETD Actualizada | Fecha ETD actualizada |
| `container_type` | varchar | Tipo de Contenedor | Tipo de contenedor |
| `mode` | varchar | Tipo de Transporte | Modalidad de transporte |
| `date_eta_updated` | datetime | Fecha ETA Actualizada | Fecha ETA actualizada |
| `shipping_line` | varchar | Línea de Envío | Línea de envío |
| `arrival_status` | varchar | Estado de Llegada | Estado de llegada |
| `factura_merca` | varchar | Factura Mercancía | Factura mercancía |
| `tracking_id` | varchar | ID de Rastreo | ID de rastreo |
| `departure_port` | varchar | Puerto de Salida | Puerto de salida |
| `arrival_port` | varchar | Puerto de Llegada | Puerto de llegada |
| `Invoice_amount` | decimal | Monto de Factura | Monto de factura |
| `bill_of_lading` | varchar | Bill of Lading | Conocimiento de embarque |
| `bonded_warehouse_enter` | datetime | Fecha de Ingreso a Almacén Fiscal | Fecha ingreso almacén |
| `bonded_warehouse_exit` | datetime | Fecha de Salida de Almacén Fiscal | Fecha salida almacén |
| `receipt_note` | varchar | Nota de Recibo | Nota de recibo |
| `kanban_status_id` | bigint | Estado Kanban | ID estado kanban |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 3. Users (Usuarios)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del usuario |
| `name` | varchar | Nombre | Nombre del usuario |
| `email` | varchar | Email | Correo electrónico |
| `password` | varchar | Contraseña | Contraseña (hasheada) |
| `company_id` | bigint | Compañía | ID de la compañía |
| `email_verified_at` | timestamp | Email Verificado | Fecha verificación email |
| `language` | varchar | Idioma | Idioma preferido |
| `time_zone` | varchar | Zona Horaria | Zona horaria |
| `date_format` | varchar | Formato de Fecha | Formato de fecha |
| `time_format` | varchar | Formato de Hora | Formato de hora |
| `description` | text | Descripción | Descripción del usuario |
| `phone` | varchar | Teléfono | Teléfono |
| `remember_token` | varchar | Remember Token | Token de recordar |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 4. Companies (Compañías)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único de la compañía |
| `name` | varchar | Nombre | Nombre de la compañía |
| `address` | varchar | Dirección | Dirección |
| `country` | varchar | País | País |
| `city` | varchar | Ciudad | Ciudad |
| `zip` | varchar | Código Postal | Código postal |
| `phone` | varchar | Teléfono | Teléfono |
| `description` | text | Descripción | Descripción |
| `website` | varchar | Sitio Web | Sitio web |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 5. Products (Productos)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del producto |
| `material_id` | varchar | Material ID | ID del material |
| `short_text` | varchar | Descripción | Descripción corta |
| `supplying_plant` | varchar | Supplying Plant | Planta suministradora |
| `unit_of_measure` | varchar | Unit of Measure | Unidad de medida |
| `plant` | varchar | Plant | Planta |
| `vendor_name` | varchar | Vendor Name | Nombre del proveedor |
| `vendo_code` | varchar | Vendor Code | Código del proveedor |
| `price_per_unit` | decimal | Price Per Unit | Precio por unidad |
| `vendor_id` | bigint | Vendor ID | ID del proveedor |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 6. Vendors (Proveedores)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del proveedor |
| `company_id` | bigint | Compañía | ID de la compañía |
| `name` | varchar | Nombre | Nombre del proveedor |
| `vendo_code` | varchar | Código | Código del proveedor |
| `email` | varchar | Email | Correo electrónico |
| `contact_person` | varchar | Contact Person | Persona de contacto |
| `address` | varchar | Dirección | Dirección |
| `postal_code` | varchar | Código Postal | Código postal |
| `country` | varchar | País | País |
| `state` | varchar | Estado | Estado |
| `phone` | varchar | Teléfono | Teléfono |
| `status` | varchar | Estado | Estado del proveedor |
| `notes` | text | Notas | Notas |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 7. Ship To (Direcciones de Envío)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `company_id` | bigint | Compañía | ID de la compañía |
| `name` | varchar | Nombre | Nombre de la dirección |
| `email` | varchar | Email | Correo electrónico |
| `contact_person` | varchar | Contact Person | Persona de contacto |
| `address` | varchar | Dirección | Dirección |
| `postal_code` | varchar | Código Postal | Código postal |
| `country` | varchar | País | País |
| `state` | varchar | Estado | Estado |
| `phone` | varchar | Teléfono | Teléfono |
| `status` | varchar | Estado | Estado |
| `notes` | text | Notas | Notas |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 8. Bill To (Direcciones de Facturación)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `name` | varchar | Nombre | Nombre |
| `email` | varchar | Email | Correo electrónico |
| `contact_person` | varchar | Contact Person | Persona de contacto |
| `address` | varchar | Dirección | Dirección |
| `postal_code` | varchar | Código Postal | Código postal |
| `country` | varchar | País | País |
| `state` | varchar | Estado | Estado |
| `phone` | varchar | Teléfono | Teléfono |
| `company_id` | bigint | Compañía | ID de la compañía |
| `notes` | text | Notas | Notas |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 9. Hubs (Hubs)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del hub |
| `name` | varchar | Nombre | Nombre del hub |
| `code` | varchar | Código | Código del hub |
| `country` | varchar | País | País |
| `documentary_cut` | integer | Documentary Cut | Corte documental |
| `zarpe` | integer | Zarpe | Zarpe |
| `operation_days` | integer | Operation Days | Días de operación |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 10. Kanban Boards (Tableros Kanban)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del tablero |
| `name` | varchar | Nombre | Nombre del tablero |
| `description` | text | Descripción | Descripción |
| `company_id` | bigint | Compañía | ID de la compañía |
| `type` | varchar | Tipo | Tipo de tablero |
| `is_active` | boolean | Activo | Está activo |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 11. Kanban Statuses (Estados Kanban)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único del estado |
| `name` | varchar | Nombre | Nombre del estado |
| `slug` | varchar | Slug | Slug del estado |
| `description` | text | Descripción | Descripción |
| `kanban_board_id` | bigint | Tablero Kanban | ID del tablero |
| `position` | integer | Posición | Posición en el tablero |
| `color` | varchar | Color | Color del estado |
| `is_default` | boolean | Es Por Defecto | Es estado por defecto |
| `is_final` | boolean | Es Final | Es estado final |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 12. Purchase Order Products (Relación PO-Producto)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `purchase_order_id` | bigint | Purchase Order | ID de la orden |
| `product_id` | bigint | Product | ID del producto |
| `quantity` | integer | Cantidad | Cantidad |
| `unit_price` | decimal | Precio Unitario | Precio por unidad |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |
| `deleted_at` | timestamp | Deleted At | Fecha de eliminación |

---

## 13. Purchase Order Comments (Comentarios de PO)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `purchase_order_id` | bigint | Purchase Order | ID de la orden |
| `user_id` | bigint | Usuario | ID del usuario |
| `comment` | text | Comentario | Texto del comentario |
| `operacion` | varchar | Operación | Tipo de operación |
| `action_type` | varchar | Tipo de Acción | Tipo de acción |
| `old_values` | json | Valores Anteriores | Valores anteriores (JSON) |
| `new_values` | json | Valores Nuevos | Valores nuevos (JSON) |
| `ip_address` | varchar | IP Address | Dirección IP |
| `user_agent` | text | User Agent | User agent |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |
| `deleted_at` | timestamp | Deleted At | Fecha de eliminación |

---

## 14. Shipping Document Comments (Comentarios de Documento)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `shipping_document_id` | bigint | Shipping Document | ID del documento |
| `user_id` | bigint | Usuario | ID del usuario |
| `comment` | text | Comentario | Texto del comentario |
| `stage` | varchar | Etapa | Etapa del proceso |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 15. Boarding Documents (Documentos de Embarque)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `purchase_order_id` | bigint | Purchase Order | ID de la orden |
| `document_path` | varchar | Document Path | Ruta del documento |
| `document_type` | varchar | Document Type | Tipo de documento |
| `status` | varchar | Estado | Estado |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |
| `deleted_at` | timestamp | Deleted At | Fecha de eliminación |

---

## 16. Tracking Data PO (Datos de Rastreo)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `purchase_order_id` | bigint | Purchase Order | ID de la orden |
| `status` | varchar | Estado | Estado del rastreo |
| `location` | varchar | Ubicación | Ubicación actual |
| `carrier` | varchar | Carrier | Transportista |
| `tracking_number` | varchar | Tracking Number | Número de rastreo |
| `estimated_delivery` | datetime | Estimated Delivery | Entrega estimada |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |
| `deleted_at` | timestamp | Deleted At | Fecha de eliminación |

---

## 17. Forecasts (Pronósticos)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `release_date` | date | Release Date | Fecha de liberación |
| `material` | varchar | Material | Material |
| `short_text` | varchar | Descripción | Descripción corta |
| `purchase_requisition` | varchar | Purchase Requisition | Requisición de compra |
| `supplying_plant` | varchar | Supplying Plant | Planta suministradora |
| `qty_real` | decimal | Quantity Real | Cantidad real |
| `uom_real` | varchar | UOM Real | Unidad de medida real |
| `quantity_requested` | decimal | Quantity Requested | Cantidad solicitada |
| `delivery_date` | date | Delivery Date | Fecha de entrega |
| `unit_of_measure` | varchar | Unit of Measure | Unidad de medida |
| `plant` | varchar | Plant | Planta |
| `planned_delivery_time` | integer | Planned Delivery Time | Tiempo de entrega planificado |
| `mrp_controller` | varchar | MRP Controller | Controlador MRP |
| `vendor_name` | varchar | Vendor Name | Nombre del proveedor |
| `vendor_code` | varchar | Vendor Code | Código del proveedor |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 18. Notifications (Notificaciones)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `type` | varchar | Tipo | Tipo de notificación |
| `user_id` | bigint | Usuario | ID del usuario |
| `title` | varchar | Título | Título de la notificación |
| `message` | text | Mensaje | Mensaje |
| `data` | json | Datos | Datos adicionales (JSON) |
| `read_at` | timestamp | Read At | Fecha de lectura |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 19. Authorizations (Autorizaciones)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `operation_id` | varchar | Operation ID | ID de la operación |
| `authorizable_id` | bigint | Authorizable ID | ID de la entidad |
| `authorizable_type` | varchar | Authorizable Type | Tipo de entidad |
| `requester_id` | bigint | Requester | ID del solicitante |
| `operation_type` | varchar | Operation Type | Tipo de operación |
| `status` | varchar | Estado | Estado (pending/approved/rejected) |
| `data` | json | Datos | Datos adicionales (JSON) |
| `authorizer_id` | bigint | Authorizer | ID del autorizador |
| `authorized_at` | datetime | Authorized At | Fecha de autorización |
| `notes` | text | Notas | Notas |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## 20. Historical Purchase Orders (Órdenes Históricas)

| Campo BD | Tipo BD | Etiqueta Frontend | Descripción |
|----------|---------|-------------------|-------------|
| `id` | bigint | (No visible) | ID único |
| `purchase_order_id` | bigint | Purchase Order ID | ID de la orden original |
| `snapshot_data` | json | Snapshot Data | Datos de snapshot (JSON) |
| `snapshot_date` | date | Snapshot Date | Fecha del snapshot |
| `created_at` | timestamp | Created At | Fecha de creación |
| `updated_at` | timestamp | Updated At | Fecha de actualización |

---

## Notas Importantes

1. **Campos Timestamps**: Todos los modelos incluyen `created_at` y `updated_at` automáticamente en Laravel.
2. **Soft Deletes**: Algunos modelos usan soft deletes y tienen `deleted_at`.
3. **Relaciones**: Los campos `*_id` son foreign keys que relacionan con otras tablas.
4. **JSON Fields**: Algunos campos almacenan datos estructurados en formato JSON.
5. **Campos Calculados**: Algunos campos son calculados y no se almacenan directamente en BD.

---

**Última actualización**: Diciembre 2024  
**Versión**: 1.0.0



