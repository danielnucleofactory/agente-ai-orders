# API Intelix / Seguimiento — Campos permitidos (payload desde RAGA/NEXT)

Fuente: “Mapeo campos definitivos enviados por evento vía webhook desde plataforma RAGA/NEXT (17/11/2025)”  [oai_citation:0‡Mapeo campos definitivos enviados por evento vía webhook desde plataforma RAGA_NEXT 17112025.pdf](sediment://file_000000003e6c720e9b326568b815524a)

> Nota: Este listado corresponde a los campos “payload RAGA definitivos” que se envían por webhook hacia Seguimiento (Intelix). Incluye además 2 campos de contexto al final.

## Campos del payload (permitidos)

- `order_number` — Número de la PO (único)
- `logistics_incoterm` — Incoterm logístico (cómo se administrará la PO en lo logístico)
- `price_incoterm` — Incoterm de precios (cómo están calculados los precios)
- `date_theorical_load` — Fecha inicial teórica de carga lista (origen: inclusión de proveedor)
- `date_carga_po` — Fecha real/validada de carga (confirmada con proveedor)
- `date_booking_request` — Fecha solicitud de autorización de booking/instrucciones (por PO)
- `date_booking_authorized` — Fecha de autorización/OK e instrucciones al FF
- `mode_id` — Tipo de transporte
- `mbl_number` — Documento de transporte (BL / carta porte / guía aérea)
- `container_type_id` — Tipo/tamaño de contenedor (incluye LCL/LTL según aplique)
- `container_number` — Número de contenedor asignado a la PO
- `date_variable_date_check` — Check de “carga lista validada” (casilla 14)
- `date_etd_initial` — ETD inicial
- `etd_initial_validated` — Check validación ETD inicial
- `date_atd` — ATD (salida real)
- `date_etd` — ETD (salida / estimado según la implementación)
- `date_eta_initial` — ETA inicial
- `date_ata` — ATA (arribo real)
- `date_eta` — ETA (arribo / estimado según la implementación)
- `departure_port_id` — Puerto de embarque (ID)
- `port_of_loading_validated` — Check validación puerto de embarque
- `arrival_port_id` — Puerto de arribo (ID)
- `cargo_invoice_number` — Número de factura de flete internacional
- `freight_amount` — Monto/valor del flete
- `service_provider_id` — Proveedor de servicio / FF (ID)
- `cbm` — Volumen CBM
- `factura_merca` — Factura del proveedor (mercancía)
- `has_facture_merca` — Check: factura de mercancía recibida
- `visibility_notes` — Notas de visibilidad (breves)
- `comments` — Comentarios (texto)
- `uses_bonded_warehouse` — Check: usa almacén fiscal (AF)
- `bonded_warehouse_enter` — Fecha ingreso a AF
- `bonded_warehouse_exit` — Fecha salida de AF
- `shipping_line_id` — Naviera (ID)
- `forwader_date` — Fecha asignación FF a proveedor
- `container_free_days` — Días libres de contenedor
- `tariff_type_id` — Tipo de tarifa (ID)
- `inspection_date` — Fecha inspección
- `vgm_cut_date` — Fecha VGM cut
- `date_invoice_received` — Fecha corte/envío de documentos (según mapeo del archivo)
- `local_charges_payment_date` — Fecha pago cargos locales
- `release_date` — Fecha liberación BL
- `trading_company` — Comercializadora (ej: OLO)
- `current_timestamp` — Timestamp actual del movimiento

## Campos de contexto (adicionales)

- `comercializadora` — Comercializadora (texto; ej: OLO)
- `fecha_movimiento` — Fecha y hora del movimiento