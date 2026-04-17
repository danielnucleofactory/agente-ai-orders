Raga OLO Adapter API
IMPORTANTE: En todos los endpoints de esta API, la combinación de los campos 'id' y 'company' constituye la llave única para evitar duplicados. El campo 'id' depende de la entidad o interfaz; por ejemplo, para procesos de orden de compra (PO), el 'id' corresponde al 'po number'.

La colección "Raga OLO Adapter API" proporciona endpoints RESTful para la integración y gestión de entidades logísticas en el middleware Raga OLO Adapter. Permite operaciones CRUD sobre Proveedores de Servicio, Tipos de Contenedor, Puertos, Líneas Navieras, Tipos de Transporte, Tipos de Tarifa y Procesos de Orden de Compra (PO).

Estructura general de las respuestas:

Las respuestas exitosas suelen incluir los campos: status (boolean o string), data (objeto o arreglo con la información solicitada), y opcionalmente message (descripción breve del resultado).
En caso de error, la respuesta puede incluir: error (descripción), code (código de error), y detalles adicionales según el endpoint.
Los parámetros de respuesta pueden variar según el recurso, por lo que se recomienda revisar los ejemplos de cada endpoint.
Carpetas principales:

Health Check: Endpoints para monitorear el estado y métricas del servicio.
PO: Gestión y consulta de procesos de órdenes de compra.
Container Types: Administración de tipos de contenedores.
Ports: Operaciones sobre puertos logísticos.
Transport Types: Gestión de tipos de transporte.
Shipping Lines: Administración de líneas navieras.
Service Providers: CRUD de proveedores de servicio.
Rate Types: Gestión de tipos de tarifa.
Test Scenarios: Ejemplos y pruebas de escenarios comunes.
Error Testing: Pruebas de manejo de errores y recursos inexistentes.
Variables:

https://olo.md.orders.raga-x.ai: URL base productiva.
http://localhost:8000: URL base para pruebas locales.
Consulte cada carpeta y solicitud para detalles de autenticación, headers, payloads y ejemplos de respuesta. Para dudas, contacte al equipo de desarrollo o revise la documentación interna.

Health Check
Incluye endpoints para monitorear el estado y métricas del servicio Raga OLO Adapter. Permite realizar health checks y obtener métricas detalladas para asegurar la disponibilidad y el rendimiento del sistema. Ejemplo: consultar el endpoint de health para integraciones de monitoreo.

GET
Health Check
https://olo.md.orders.raga-x.ai/api/v1/health
Verifica la disponibilidad y estado general del servicio Raga OLO Adapter. Respuesta típica: { status: true, data: { uptime: 123456, version: '1.0.0', ... }, message: 'Servicio disponible' }.

GET
Health Metrics
https://olo.md.orders.raga-x.ai/api/v1/health/metrics
Obtiene métricas detalladas de salud y rendimiento del servicio Raga OLO Adapter para monitoreo avanzado.

PO
Carpeta para la gestión y consulta de procesos de órdenes de compra (PO). Permite crear, consultar y listar procesos relacionados con órdenes de compra, facilitando el seguimiento y control de las mismas. Ejemplo: consultar el historial de procesamiento de una PO específica.

POST
Post pucharse order
https://olo.md.orders.raga-x.ai/api/v1/purchase-orders
Crea una nueva orden de compra (PO) en el sistema. Respuesta típica: { status: true, data: { ...po }, message: 'Orden creada' }. En caso de error: { status: false, error: 'Datos inválidos', code: 400 }.

Request Headers
Accept
application/json
Content-Type
application/json
Body
raw (json)
View More
json
{
    "order_number": "1103894-11",
    "vendor_id": "299",
    "vendor_name": "ASSA ABLOY AMERICAS INTERNATIONAL LOGISTIC CENTER S.A. CHIN",
    "route_label": "Directo SV - OVERSEAS LOGISTICS OPERATIONS",
    "retail_group": "81",
    "total_amount": 47757.0,
    "currency": "USD",
    "emision_date_po": "2026-01-24",
    "category": "",
    "incoterms": "DAP",
    "logistics_incoterm": "CIF",
    "price_incoterm": "FCA",
    "departure_port_id": "71",
    "arrival_port_id": "45",
    "date_theorical_load": "2026-03-20",
    "date_carga_po": "2026-03-20",
    "case_number_file": "",
    "consolidator_name": "",
    "customs_dua": "",
    "receipt_note": "",
    "receipt_note_date": null,
    "factory_proforma_number": "PI3SV Additional 3893",
    "invoice": "",
    "Invoice_amount": 0.0,
    "applies_tlc": false,
    "reason": "PROGRAMACIÓN",
    "customer_type": "EPA",
    "trading_company": "OLO3",
    "apply_technical_note": true,
    "used_rate_ok": false
}
Example
Post pucharse order
Request
View More
Postman CLI
postman request POST 'https://olo.md.orders.raga-x.ai/api/v1/po' \
  --body '{
  "order_number": "1000",
  "vendor_id": 1000,
  "route_label": "RUTA-MIA-SJO",
  "retail_group": "Grupo Retail A",
  "total_amount": 18500.00,  
  "currency": "USD",
  "emision_date_po": "2025-10-21",
  "category": "Electronics",
  "incoterms": "FOB",
  "logistics_incoterm": "FOB",
  "price_incoterm": "FOB",
  "date_theorical_load": "2025-11-15",
  "case_number_file": "EXP1000",
  "consolidator_name": "Consolidador ABC",  
  "customs_dua": "DUA-OLO-789456",
  "receipt_note": "Nota de recibo - Todo conforme",  
  "receipt_note_date": "2025-12-20",
  "factory_proforma_number": "PRF-OLO-2025-001",
  "invoice": "INV-OLO-2025-001",
  "Invoice_amount": 15000.50,
  "apply_technical_note": false,
  "reason": "Reposición de inventario",  
  "customer_type": "Premium",
  "trading_company": "OLO3"
}'
Response
Body
Headers (0)
No response body
This request doesn't return any response body.
POST
Post pucharse order bulk
https://olo.md.orders.raga-x.ai/api/v1/purchase-orders/bulk
Crea una nueva orden de compra (PO) en el sistema. Respuesta típica: { status: true, data: { ...po }, message: 'Orden creada' }. En caso de error: { status: false, error: 'Datos inválidos', code: 400 }.

Request Headers
Accept
application/json
Body
raw (json)
View More
json
[
    {
        "order_number": "PObulk_127",
        "trading_company": "OLO1",
        "vendor_id": "380",
        "route_label": null,
        "retail_group": "85",
        "total_amount": 12500,
        "currency": "USD",
        "emision_date_po": "2025-11-13",
        "category": null,
        "reason": "CNY",
        "incoterms": "FOB",
        "logistics_incoterm": null,
        "price_incoterm": null,
        "date_theorical_load": "2026-03-01",
        "departure_port_id": "4",
        "arrival_port_id": "116",
        "receipt_note_date": null,
        "factory_proforma_number": null,
        "applies_tlc": false,
        "apply_technical_note": false,
        "Invoice_amount": null,
        "customer_type": "EPA",
        "invoice": null,
        "customs_dua": null,
        "case_number_file": null,
        "receipt_note": null,
        "consolidator_name": null
    },
    {
        "order_number": "PObulk_105",
        "trading_company": "OLO3",
        "vendor_id": "380",
        "route_label": "RUTA TEST",
        "retail_group": "85",
        "total_amount": 12500,
        "currency": "USD",
        "emision_date_po": "2025-11-13",
        "category": "CAT3",
        "reason": "CNY",
        "incoterms": "FOB",
        "logistics_incoterm": "DAP",
        "price_incoterm": "FOB",
        "date_theorical_load": "2026-03-01",
        "departure_port_id": "102",
        "arrival_port_id": "101",
        "receipt_note_date": "2025-11-13",
        "factory_proforma_number": "PROFOMTEST",
        "applies_tlc": true,
        "apply_technical_note": true,
        "Invoice_amount": 2000.03,
        "customer_type": "EPA",
        "invoice": "FACTTEST",
        "customs_dua": "duatest",
        "case_number_file": "EXP123454",
        "receipt_note": "nr999",
        "consolidator_name": "consolidator456"
    },
    {
        "order_number": "PObulk_106",
        "emision_date_po": "2025-11-01",
        "currency": "USD",
        "price_incoterm": "CIF",
        "incoterms": "CIF",
        "logistics_incoterm": "FOB",
        "category": "Import",
        "reason": "Seasonal replenishment",
        "departure_port_id": "102",
        "arrival_port_id": "100",
        "factory_proforma_number": "PF-778899",
        "vendor_id": "987",
        "consolidator_name": "LogisConsolidated",
        "receipt_note_date": "2025-12-07",
        "applies_tlc": false,
        "apply_technical_note": true,
        "Invoice_amount": "52000.50",
        "total_amount": "58051.25",
        "route_label": "Asia-Pacific Route",
        "retail_group": "Retail Group South",
        "customer_type": "Retail",
        "invoice": "INV-889900",
        "customs_dua": "DUA-334455",
        "case_number_file": "CASE-2025-001",
        "receipt_note": "RN-765432",
        "trading_company": "OLO5"
    }
]
Example
Post pucharse order
Request
View More
Postman CLI
postman request POST 'https://olo.md.orders.raga-x.ai/api/v1/po' \
  --body '{
  "order_number": "1000",
  "vendor_id": 1000,
  "route_label": "RUTA-MIA-SJO",
  "retail_group": "Grupo Retail A",
  "total_amount": 18500.00,  
  "currency": "USD",
  "emision_date_po": "2025-10-21",
  "category": "Electronics",
  "incoterms": "FOB",
  "logistics_incoterm": "FOB",
  "price_incoterm": "FOB",
  "date_theorical_load": "2025-11-15",
  "case_number_file": "EXP1000",
  "consolidator_name": "Consolidador ABC",  
  "customs_dua": "DUA-OLO-789456",
  "receipt_note": "Nota de recibo - Todo conforme",  
  "receipt_note_date": "2025-12-20",
  "factory_proforma_number": "PRF-OLO-2025-001",
  "invoice": "INV-OLO-2025-001",
  "Invoice_amount": 15000.50,
  "apply_technical_note": false,
  "reason": "Reposición de inventario",  
  "customer_type": "Premium",
  "trading_company": "OLO3"
}'
Response
Body
Headers (0)
No response body
This request doesn't return any response body.
GET
Get all po processing
https://olo.md.orders.raga-x.ai/api/v1/po-logs
Obtiene el historial de procesamiento de todas las órdenes de compra. Respuesta típica: { status: true, data: [ ...poProcesses ], message: 'Procesos encontrados' }.

GET
Get po process by po number
https://olo.md.orders.raga-x.ai/api/v1/po-logs/
Obtiene el proceso de una orden de compra por número de PO. Respuesta típica: { status: true, data: { ...poProcess }, message: 'Proceso encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

GET
Get Pos by Order number and company
https://olo.md.orders.raga-x.ai/api/v1/po?order_number=PRUEBA_670&company=OLO3
﻿

Query Params
order_number
PRUEBA_670
company
OLO3
PUT
Put PO Bulk
https://olo.md.orders.raga-x.ai/api/v1/po/bulk-orders
﻿

Request Headers
Accept
application/json
Content-Type
application/json
Body
raw (json)
json
[
    {
        "order_number": "PO2025-113",
        "trading_company": "OLO3",
        "case_number_file": "Expediente test1-ACT"
    }
]
DELETE
Delete PO
http://localhost:8000/api/v1/purchase-orders
﻿

Request Headers
Accept
application/json
Content-Type
application/json
Body
raw (json)
json
{
    "order_number": "PObulk_114",
    "trading_company": "OLO1"
}
POST
Traduccion
https://olo.md.orders.raga-x.ai/api/v1/purchase-orders/translate
﻿

Request Headers
Accept
application/json
Content-Type
application/json
Body
raw (json)
View More
json
{
    "id": 14,
    "company_id": 1,
    "order_number": "0107113",
    "status": "shipped",
    "total_amount": "0.00",
    "ensurence_type": "pending",
    "order_date": "2025-09-22T00:00:00+00:00",
    "currency": "USD",
    "incoterms": "DEFAULT",
    "mode": "aereo",
    "payment_terms": null,
    "email_agent": null,
    "tracking_id": null,
    "net_total": "16687.00",
    "additional_cost": null,
    "total": "16687.00",
    "length": null,
    "width": null,
    "height": null,
    "volume": null,
    "weight_kg": "0.00",
    "weight_lb": null,
    "pallet_quantity": null,
    "pallet_quantity_real": null,
    "bill_of_lading": null,
    "date_required_in_destination": null,
    "date_planned_pickup": null,
    "date_actual_pickup": null,
    "date_estimated_hub_arrival": null,
    "date_actual_hub_arrival": null,
    "date_etd": null,
    "date_atd": null,
    "date_eta": null,
    "date_ata": null,
    "date_consolidation": null,
    "release_date": null,
    "insurance_cost": null,
    "ground_transport_cost_1": null,
    "ground_transport_cost_2": null,
    "cost_nationalization": null,
    "cost_ofr_estimated": null,
    "cost_ofr_real": null,
    "estimated_pallet_cost": null,
    "real_cost_estimated_po": null,
    "real_cost_real_po": null,
    "other_costs": null,
    "other_expenses": null,
    "variable_calculare_weight": null,
    "savings_ofr_fcl": null,
    "saving_pickup": null,
    "saving_executed": null,
    "saving_not_executed": null,
    "notes": null,
    "comments": null,
    "created_at": "2025-09-22T21:18:20+00:00",
    "updated_at": "2025-10-06T13:22:09+00:00",
    "kanban_status_id": 7,
    "ship_to_id": 8,
    "vendor_id": 5,
    "planned_hub_id": 1,
    "actual_hub_id": null,
    "bill_to_id": 2,
    "material_type": "\"[\\\"Standard\\\"]\"",
    "length_cm": "0.00",
    "width_cm": "0.00",
    "height_cm": "0.00",
    "confirmation_hash": null,
    "hash_expires_at": null,
    "confirmation_email_sent": false,
    "confirmation_email_sent_at": null,
    "update_date_po": null,
    "confirm_update_date_po": false,
    "rejection_reason": null,
    "factory_proforma_number": "DEFAULT",
    "mbl_number": null,
    "container_type": null,
    "container_number": null,
    "shipping_line": null,
    "port_of_loading_validated": false,
    "is_dropship": false,
    "applies_tlc": false,
    "applies_af": false,
    "has_facture_merca": false,
    "used_rate_ok": false,
    "uses_bonded_warehouse": false,
    "apply_technical_note": false,
    "etd_initial_validated": false,
    "date_booking_request": null,
    "date_booking_authorized": null,
    "date_theorical_load": "1900-01-01T00:00:00+00:00",
    "date_variable_date": null,
    "date_carga_po": null,
    "date_received": null,
    "date_etd_initial": null,
    "inspection_date": null,
    "vgm_cut_date": null,
    "balance_payment_date": null,
    "local_charges_payment_date": null,
    "bonded_warehouse_enter": null,
    "bonded_warehouse_exit": null,
    "receipt_note_date": "1900-01-01T00:00:00+00:00",
    "estimated_dc_availability_date": null,
    "logistics_incoterm": "DEFAULT",
    "price_incoterm": "DEFAULT",
    "reason": "PEDIDO COMPRA",
    "category": "DEFAULT",
    "forwarder_name": null,
    "cargo_invoice_number": null,
    "tariff_type": "Ocean Freight Rate Updated",
    "route_label": "DEFAULT",
    "retail_group": "86",
    "customer_type": "EPA",
    "trading_company": "OLO1",
    "service_provider": null,
    "customs_dua": null,
    "invoice": null,
    "factura_merca": null,
    "case_number_file": null,
    "receipt_note": null,
    "visibility_notes": null,
    "departure_port": null,
    "arrival_port": null,
    "Invoice_amount": "0.00",
    "freight_amount": null,
    "arrival_status": null,
    "delay_days": null,
    "container_free_days": null,
    "etd_dates_difference": null,
    "eta_dates_difference": null,
    "date_eta_initial": null,
    "date_invoice_received": null,
    "date_vendor_document_received": null,
    "deleted_at": null,
    "cbm": null,
    "dif_load_date": null,
    "consolidator_name": null,
    "vendor_number": null,
    "emision_date_po": null,
    "forwader_date": null,
    "last_email_type_sent": null,
    "last_email_sent_at": null,
    "email_sent_history": null,
    "insurance_type": null,
    "vendor": {
        "id": 5,
        "name": "BESTWAY ENTERPRISE COMPANY LIMITED",
        "vendo_code": "85",
        "email": "soporte@raga-x.ai"
    },
    "ship_to": {
        "id": 8,
        "name": "Ship To con falta de datos"
    },
    "kanban_status": {
        "id": 7,
        "name": "Alm Fiscal",
        "slug": "po-alm-fiscal-1"
    },
    "products": []
}
Container Types
Carpeta dedicada a la gestión de Tipos de Contenedor (Container Types). Permite listar, crear, actualizar y eliminar tipos de contenedores utilizados en operaciones logísticas. Ejemplo de uso: registrar un nuevo tipo de contenedor para una línea naviera.

GET
List Container Types
https://olo.md.orders.raga-x.ai/api/v1/container-types?page=1&per_page=20&company=&active=false&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
20
company
active
false
search
sort
created_at
order
desc
POST
Get Container Type by ID
https://olo.md.orders.raga-x.ai/api/v1/container-types/show
Obtiene el tipo de contenedor por ID. Respuesta típica: { status: true, data: { ...containerType }, message: 'Tipo de contenedor encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "41",
    "company": "OLO1"
}
POST
Create Container Type
https://olo.md.orders.raga-x.ai/api/v1/container-types
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "CG_0011",
    "name": "20ft Standard Container",
    "active": true,
    "company": "TEST"
}
PUT
Update Container Type
https://olo.md.orders.raga-x.ai/api/v1/container-types
﻿

Body
raw (json)
json
{
    "olo_id": "CG_0012",
    "company": "TEST",
    "name": "20ft Standard Container Updated",
    "active": false
}
DELETE
Delete Container Type
https://olo.md.orders.raga-x.ai/api/v1/container-types/
﻿

Body
raw (json)
json
{
    "olo_id": "CG_0012",
    "company": "TEST"
}
POST
Bulk Create Container Types
https://olo.md.orders.raga-x.ai/api/v1/container-types/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "container_types": [
        {
            "olo_id": "CT_0011",
            "name": "20ft Standard Container",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "CT_0012",
            "name": "40ft High Cube Container",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "CT_0013",
            "name": "45ft High Cube Container",
            "active": false,
            "company": "TEST"
        }
    ]
}
Ports
Carpeta dedicada a la gestión de Puertos (Ports). Permite listar, crear, actualizar y eliminar puertos logísticos en el sistema. Ejemplo de uso: agregar un nuevo puerto para operaciones de importación/exportación.

GET
List Ports
https://olo.md.orders.raga-x.ai/api/v1/ports?page=1&per_page=15&company=OLO1&active=true&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
15
company
OLO1
active
true
search
sort
created_at
order
desc
POST
Get Port by ID
https://olo.md.orders.raga-x.ai/api/v1/ports/show
Obtiene el puerto por ID. Respuesta típica: { status: true, data: { ...port }, message: 'Puerto encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "122",
    "company": "OLO1"
}
POST
Create Port
http://localhost:8000/api/v1/ports
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "1232",
    "name": "Port of Los Angeles",
    "active": true,
    "company": "OLO1"
}
PUT
Update Port
https://olo.md.orders.raga-x.ai/api/v1/ports
﻿

Body
raw (json)
View More
json
[
    {
        "olo_id": "4",
        "company": "OLO1",
        "name": "Port of Los Angeles Updated",
        "active": false
    },
    {
        "olo_id": "999",
        "company": "OLO1",
        "name": "Port of Los Angeles Updated",
        "active": false
    }    
]
DELETE
Delete Port
https://olo.md.orders.raga-x.ai/api/v1/ports/
﻿

Body
raw (json)
json
{
    "olo_id": "123",
    "company": "OLO1"    
}
POST
Bulk Create Ports
https://olo.md.orders.raga-x.ai/api/v1/ports/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "ports": [
        {
            "olo_id": "PORT_001",
            "name": "Port of Los Angeles",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "PORT_002",
            "name": "Port of Long Beach",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "PORT_003",
            "name": "Port of Oakland",
            "active": false,
            "company": "TEST"
        }
    ]
}
Transport Types
Contiene los endpoints para la administración de Tipos de Transporte (Transport Types). Permite listar, crear, actualizar y eliminar tipos de transporte utilizados en operaciones logísticas, como terrestre, marítimo o aéreo. Ejemplo de uso: agregar un nuevo tipo de transporte para rutas específicas.

GET
List Transport Types
https://olo.md.orders.raga-x.ai/api/v1/transport-types?page=1&per_page=15&company=TEST&active=true&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
15
company
TEST
active
true
search
sort
created_at
order
desc
POST
Get Transport Type by ID
https://olo.md.orders.raga-x.ai/api/v1/transport-types/show
Obtiene el tipo de transporte por ID. Respuesta típica: { status: true, data: { ...transportType }, message: 'Tipo de transporte encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "2",
    "company": "OLO1"
}
POST
Create Transport Type
https://olo.md.orders.raga-x.ai/api/v1/transport-types
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "TG_003",
    "name": "FCL",
    "active": true,
    "company": "TEST"
}
PUT
Update Transport Type
https://olo.md.orders.raga-x.ai/api/v1/transport-types
﻿

Body
raw (json)
View More
json
[
    {
        "olo_id": "1",
        "company": "OLO1",
        "name": "First Update meet",
        "active": false
    },
    {
        "olo_id": "2",
        "company": "OLO1",
        "name": "Second Update",
        "active": true
    }
]
DELETE
Delete Transport Type
https://olo.md.orders.raga-x.ai/api/v1/transport-types
﻿

Body
raw (json)
json
{
    "olo_id": "TT_002",
    "company": "TEST"
}
POST
Bulk Create Transport Types
https://olo.md.orders.raga-x.ai/api/v1/transport-types/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "transport_types": [
        {
            "olo_id": "TT_001",
            "name": "FCL",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "TT_002",
            "name": "LCL",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "TT_003",
            "name": "BULK",
            "active": false,
            "company": "TEST"
        }
    ]
}
Shipping Lines
Incluye endpoints para la administración de Líneas Navieras (Shipping Lines). Permite listar, crear, actualizar y eliminar líneas navieras utilizadas en operaciones logísticas. Ejemplo de uso: registrar una nueva línea naviera para asociarla a rutas de transporte.

GET
List Shipping Lines
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines?page=1&per_page=15&company=TEST&active=true&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
15
company
TEST
active
true
search
sort
created_at
order
desc
POST
Get Shipping Line by ID
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines/show
Obtiene la línea naviera por ID. Respuesta típica: { status: true, data: { ...shippingLine }, message: 'Línea naviera encontrada' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "4",
    "company": "OLO6"
}
POST
Create Shipping Line
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "SL_001",
    "name": "Maersk Line",
    "active": true,
    "company": "TEST"
}
PUT
Update Shipping Line
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines/
﻿

Body
raw (json)
json
{
    "olo_id": "1",
    "company": "OLO1",
    "name": "Maersk Line Updated",
    "active": true
}
DELETE
Delete Shipping Line
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines/
﻿

Body
raw (json)
json
{
    "olo_id": "3",
    "company": "OLO1"
}
POST
Bulk Create Shipping Lines
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "shipping_lines": [
        {
            "olo_id": "SL_001",
            "name": "Maersk Line",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "SL_002",
            "name": "MSC",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "SL_003",
            "name": "CMA CGM",
            "active": false,
            "company": "TEST"
        }
    ]
}
Service Providers
Incluye endpoints para la administración de Proveedores de Servicio (Service Providers). Permite listar, crear, actualizar y eliminar proveedores utilizados en la cadena logística. Ejemplo de uso: registrar un nuevo proveedor de transporte o almacenamiento.

GET
List Service Providers
https://olo.md.orders.raga-x.ai/api/v1/service-providers?page=1&per_page=15&company=TEST&active=true&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
15
company
TEST
active
true
search
sort
created_at
order
desc
POST
Get Service Provider by ID
https://olo.md.orders.raga-x.ai/api/v1/service-providers/show
Obtiene la información detallada de un proveedor de servicio específico a partir de su ID. Respuesta típica: { status: true, data: { ...proveedor }, message: 'Proveedor encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "1000001",
    "company": "OLO1"
}
POST
Create Service Provider
https://olo.md.orders.raga-x.ai/api/v1/service-providers
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "SP_001",
    "name": "Logistics Provider Inc",
    "active": true,
    "company": "TEST"
}
PUT
Update Service Provider
https://olo.md.orders.raga-x.ai/api/v1/service-providers/
﻿

Body
raw (json)
json
{
    "olo_id": "1000001",
    "company": "OLO1",
    "name": "Logistics Provider Inc Updated",
    "active": true
}
DELETE
Delete Service Provider
https://olo.md.orders.raga-x.ai/api/v1/service-providers/
﻿

Body
raw (json)
json
{
    "olo_id": "1000001",
    "company": "OLO1"
}
POST
Bulk Create Service Providers
https://olo.md.orders.raga-x.ai/api/v1/service-providers/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "service_providers": [
        {
            "olo_id": "SP_001",
            "name": "Logistics Provider Inc",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "SP_002",
            "name": "Transport Solutions",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "SP_003",
            "name": "Cargo Services Ltd",
            "active": false,
            "company": "TEST"
        }
    ]
}
Rate Types
Esta carpeta agrupa los endpoints para la gestión de Tipos de Tarifa (Rate Types). Permite listar, crear, actualizar y eliminar tipos de tarifa utilizados en la configuración de servicios logísticos. Útil para mantener la estructura de tarifas y sus variantes en el sistema. Ejemplo de uso: crear un nuevo tipo de tarifa para un servicio específico.

GET
List Rate Types
https://olo.md.orders.raga-x.ai/api/v1/rate-types?page=1&per_page=20&company=&active=true&search=&sort=created_at&order=desc
﻿

Query Params
page
1
per_page
20
company
active
true
search
sort
created_at
order
desc
POST
Get Rate Type by ID
https://olo.md.orders.raga-x.ai/api/v1/rate-types/show
Obtiene el tipo de tarifa por ID. Respuesta típica: { status: true, data: { ...rateType }, message: 'Tipo de tarifa encontrado' }. En caso de error: { status: false, error: 'No encontrado', code: 404 }.

Body
raw (json)
json
{
    "olo_id": "5",
    "company": "OLO1"
}
POST
Create Rate Type
https://olo.md.orders.raga-x.ai/api/v1/rate-types
﻿

Request Headers
Accept
application/json
Body
raw (json)
json
{
    "olo_id": "RT_0011",
    "name": "Ocean Freight Rate",
    "active": true,
    "company": "TEST"
}
PUT
Update Rate Type
https://olo.md.orders.raga-x.ai/api/v1/rate-types
﻿

Body
raw (json)
View More
json
[
    {
        "olo_id": "0",
        "company": "OLO1",
        "name": "Ocean Freight Rate Updated",
        "active": false
    },
    {
        "olo_id": "1",
        "company": "OLO1",        
        "name": "Ocean Freight Rate Updated",
        "active": false
    }        
]
DELETE
Delete Rate Type
https://olo.md.orders.raga-x.ai/api/v1/rate-types/
﻿

Body
raw (json)
json
{
   "olo_id": "RT_0011",
    "company": "TEST"
}
POST
Bulk Create Rate Types
http://localhost:8000/api/v1/rate-types/bulk
﻿

Request Headers
Accept
application/json
Body
raw (json)
View More
json
{
    "rate_types": [
        {
            "olo_id": "RT_001",
            "name": "Ocean Freight Rate",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "RT_002",
            "name": "Inland Transport Rate",
            "active": true,
            "company": "TEST"
        },
        {
            "olo_id": "RT_003",
            "name": "Terminal Handling Rate",
            "active": false,
            "company": "TEST"
        }
    ]
}
Test Scenarios
Incluye endpoints de prueba y escenarios comunes para validar funcionalidades del sistema. Aquí se pueden encontrar ejemplos de búsquedas, filtros y paginación, útiles para desarrolladores y testers al verificar el comportamiento esperado de la API bajo diferentes condiciones.

GET
Search by Name
https://olo.md.orders.raga-x.ai/api/v1/container-types?search=Standard&company=TEST
﻿

Query Params
search
Standard
company
TEST
GET
Filter by Active Status
https://olo.md.orders.raga-x.ai/api/v1/ports?active=true&company=OLO1
﻿

Query Params
active
true
company
OLO1
GET
Sort by Name Ascending
https://olo.md.orders.raga-x.ai/api/v1/shipping-lines?sort=name&order=asc&company=OLO1
﻿

Query Params
sort
name
order
asc
company
OLO1
GET
Pagination Test
https://olo.md.orders.raga-x.ai/api/v1/service-providers?page=2&per_page=5&company=TEST
﻿

Query Params
page
2
per_page
5
company
TEST
Error Testing
Contiene endpoints diseñados para probar el manejo de errores y recursos inexistentes en la API. Útil para validar respuestas ante solicitudes inválidas, recursos no encontrados o datos incorrectos. Ejemplo: intentar eliminar un recurso que no existe para verificar el mensaje de error.

POST
Invalid Container Type Creation
https://olo.md.orders.raga-x.ai/api/v1/container-types
﻿

Body
raw (json)
json
{
    "olo_id": "",
    "name": "",
    "company": ""
}
GET
Get Non-existent Resource
https://olo.md.orders.raga-x.ai/api/v1/ports/99999
﻿

PUT
Update Non-existent Resource
http://localhost:8000/api/v1/transport-types/99999
﻿

Body
raw (json)
json
{
    "name": "Updated Name"
}
DELETE
Delete Non-existent Resource
http://localhost:8000/api/v1/shipping-lines/99999