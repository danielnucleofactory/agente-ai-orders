<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración del Agente IA — RAGA Orders
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada para el agente IA. Incluye modelos,
    | límites, catálogo de consultas operativas y reglas de seguridad.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Modelos de IA — Cadena de fallback
    | Orden: Groq → Gemini
    |--------------------------------------------------------------------------
    */
    'models' => [
        'primary' => [
            'provider' => 'groq',
            'model'    => 'llama-3.3-70b-versatile',
        ],
        'fallback' => [
            'provider' => 'gemini',
            'model'    => 'gemini-2.5-flash',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros de generación
    |--------------------------------------------------------------------------
    */
    'generation' => [
        'max_tokens'  => 1024,
        'temperature' => 0.3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Límites de resultados para query_operational_data
    |--------------------------------------------------------------------------
    */
    'query_limits' => [
        'default' => 30,
        'max'     => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Entidades permitidas
    |--------------------------------------------------------------------------
    */
    'entities' => [
        'purchase_orders' => [
            'label'       => 'Órdenes de compra',
            'table'       => 'purchase_orders',
            'company_col' => 'purchase_orders.company_id',
            'joins'       => [
                [
                    'table'    => 'vendors',
                    'first'    => 'purchase_orders.vendor_id',
                    'operator' => '=',
                    'second'   => 'vendors.id',
                    'type'     => 'left',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Métricas permitidas
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'count_orders'   => [
            'label' => 'Cantidad de órdenes',
            'sql'   => 'COUNT(*)',
            'alias' => 'total',
        ],
        'sum_teus'       => [
            'label' => 'Total TEUs',
            'sql'   => "SUM(CASE WHEN purchase_orders.container_type IN ('40ft','40HC','40HQ') THEN 2 ELSE 1 END)",
            'alias' => 'total_teus',
        ],
        'avg_delay_days' => [
            'label' => 'Promedio días de retraso',
            'sql'   => 'ROUND(AVG(purchase_orders.delay_days), 1)',
            'alias' => 'avg_delay',
        ],
        'max_delay_days' => [
            'label' => 'Máximo días de retraso',
            'sql'   => 'MAX(purchase_orders.delay_days)',
            'alias' => 'max_delay',
        ],
        'sum_delay_days' => [
            'label' => 'Total días de retraso',
            'sql'   => 'SUM(purchase_orders.delay_days)',
            'alias' => 'total_delay',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agrupaciones permitidas
    |--------------------------------------------------------------------------
    */
    'group_by' => [
        'date_ata_week'   => [
            'label' => 'Semana de ATA',
            'sql'   => "TO_CHAR(purchase_orders.date_ata, 'IYYY-\"W\"IW')",
            'alias' => 'week_ata',
        ],
        'date_eta_week'   => [
            'label' => 'Semana de ETA',
            'sql'   => "TO_CHAR(purchase_orders.porth_first_eta, 'IYYY-\"W\"IW')",
            'alias' => 'week_eta',
        ],
        'date_atd_week'   => [
            'label' => 'Semana de ATD',
            'sql'   => "TO_CHAR(purchase_orders.date_atd, 'IYYY-\"W\"IW')",
            'alias' => 'week_atd',
        ],
        'shipping_line'   => [
            'label' => 'Naviera',
            'sql'   => 'purchase_orders.shipping_line',
            'alias' => 'shipping_line',
        ],
        'vendor'          => [
            'label' => 'Proveedor',
            'sql'   => 'vendors.name',
            'alias' => 'vendor',
        ],
        'trading_company' => [
            'label' => 'Cliente / Trading company',
            'sql'   => 'purchase_orders.trading_company',
            'alias' => 'trading_company',
        ],
        'route_label'     => [
            'label' => 'Ruta',
            'sql'   => 'purchase_orders.route_label',
            'alias' => 'route_label',
        ],
        'arrival_status'  => [
            'label' => 'Estado de llegada',
            'sql'   => 'purchase_orders.arrival_status',
            'alias' => 'arrival_status',
        ],
        'porth_phase'     => [
            'label' => 'Fase logística',
            'sql'   => 'purchase_orders.porth_phase',
            'alias' => 'porth_phase',
        ],
        'container_type'  => [
            'label' => 'Tipo de contenedor',
            'sql'   => 'purchase_orders.container_type',
            'alias' => 'container_type',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtros permitidos
    |--------------------------------------------------------------------------
    */
    'filters' => [
        'date_ata' => [
            'label'     => 'Fecha ATA',
            'col'       => 'purchase_orders.date_ata',
            'type'      => 'date',
            'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
        ],
        'date_eta' => [
            'label'     => 'Fecha ETA',
            'col'       => 'purchase_orders.porth_first_eta',
            'type'      => 'date',
            'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
        ],
        'date_atd' => [
            'label'     => 'Fecha ATD',
            'col'       => 'purchase_orders.date_atd',
            'type'      => 'date',
            'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
        ],
        'shipping_line' => [
            'label'     => 'Naviera',
            'col'       => 'purchase_orders.shipping_line',
            'type'      => 'string',
            'operators' => ['eq', 'like'],
        ],
        'vendor_id' => [
            'label'     => 'ID de proveedor',
            'col'       => 'purchase_orders.vendor_id',
            'type'      => 'integer',
            'operators' => ['eq'],
        ],
        'vendor_name' => [
            'label'     => 'Nombre de proveedor',
            'col'       => 'vendors.name',
            'type'      => 'string',
            'operators' => ['eq', 'like'],
        ],
        'trading_company' => [
            'label'     => 'Cliente / Trading company',
            'col'       => 'purchase_orders.trading_company',
            'type'      => 'string',
            'operators' => ['eq', 'like'],
        ],
        'route_label' => [
            'label'     => 'Ruta',
            'col'       => 'purchase_orders.route_label',
            'type'      => 'string',
            'operators' => ['eq', 'like'],
        ],
        'arrival_status' => [
            'label'     => 'Estado de llegada',
            'col'       => 'purchase_orders.arrival_status',
            'type'      => 'enum',
            'allowed'   => ['delayed', 'Atrasado', 'on_time', 'arrived'],
            'operators' => ['eq'],
        ],
        'delay_days' => [
            'label'     => 'Días de retraso',
            'col'       => 'purchase_orders.delay_days',
            'type'      => 'integer',
            'operators' => ['eq', 'gte', 'lte', 'gt', 'lt'],
        ],
        'porth_phase' => [
            'label'     => 'Fase logística',
            'col'       => 'purchase_orders.porth_phase',
            'type'      => 'enum',
            'allowed'   => [
                '40_in_transit',
                '20_transshipment',
                '50_at_destination_port',
                '60_to_final_destination',
                '70_delivered',
            ],
            'operators' => ['eq'],
        ],
        'container_type' => [
            'label'     => 'Tipo de contenedor',
            'col'       => 'purchase_orders.container_type',
            'type'      => 'string',
            'operators' => ['eq'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Operadores disponibles
    |--------------------------------------------------------------------------
    */
    'operators' => [
        'eq'       => '=',
        'neq'      => '!=',
        'gt'       => '>',
        'lt'       => '<',
        'gte'      => '>=',
        'lte'      => '<=',
        'like'     => 'LIKE',
        'not_null' => 'IS NOT NULL',
        'is_null'  => 'IS NULL',
        'between'  => 'BETWEEN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mensajes de error amigables
    |--------------------------------------------------------------------------
    */
    'error_messages' => [
        'rate_limit_groq'   => null,
        'rate_limit_gemini' => 'Estoy experimentando alta demanda en este momento. Por favor espera un momento e intenta de nuevo.',
        'both_unavailable'  => 'El servicio de IA no está disponible temporalmente. Por favor intenta en unos minutos.',
        'query_error'       => 'Ocurrió un error al procesar tu consulta. Por favor intenta de nuevo.',
    ],

];