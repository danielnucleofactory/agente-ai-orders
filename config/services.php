<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ship24' => [
        'api_key' => env('SHIP24_API_KEY'),
    ],

    'po_bulk_update' => [
        'api_url'   => env('PO_BULK_UPDATE_API_URL', 'https://olo.md.orders.raga-x.ai/api/v1/po/bulk-orders'),
        'api_token' => env('PO_BULK_UPDATE_API_TOKEN'),
    ],

    'porth' => [
        'api_key'                         => env('PORTH_API_KEY'),
        'api_url'                         => env('PORTH_API_URL', 'https://api.porth.app'),
        'base_url'                        => env('PORTH_BASE_URL', 'https://porth-api.fly.dev'),
        'auth_header'                     => env('PORTH_AUTH_HEADER', 'apikey'),
        'enabled'                         => env('PORTH_SYNC_ENABLED', true),
        'sync_enabled'                    => env('PORTH_SYNC_ENABLED', true),
        'max_retries'                     => env('PORTH_MAX_RETRIES', 5),
        'timeout'                         => env('PORTH_TIMEOUT', 90),
        'sync_dry_run'                    => env('PORTH_SYNC_DRY_RUN', false),
        'sync_lookback_hours'             => env('PORTH_SYNC_LOOKBACK_HOURS', 2),
        'sync_max_shipments_per_run'      => env('PORTH_SYNC_MAX_SHIPMENTS_PER_RUN', 100),
        'sync_processing_timeout_minutes' => env('PORTH_SYNC_PROCESSING_TIMEOUT_MINUTES', 30),
        'notification_user_ids'           => env('PORTH_SYNC_NOTIFICATION_USER_IDS', ''),
        'failed_retry_enabled'            => env('PORTH_FAILED_RETRY_ENABLED', true),
        'failed_retry_max_attempts'       => env('PORTH_FAILED_RETRY_MAX_ATTEMPTS', 5),
        'failed_retry_base_minutes'       => env('PORTH_FAILED_RETRY_BASE_MINUTES', 60),
        'unmatched_port_alert_email'      => env('PORTH_UNMATCHED_PORT_ALERT_EMAIL'),
        'unmatched_port_alert_ttl_hours'  => env('PORTH_UNMATCHED_PORT_ALERT_TTL_HOURS', 12),
        'kanban_auto_transition'          => env('PORTH_KANBAN_AUTO_TRANSITION', true),
        'kanban_stages' => [
            'produccion'   => ['Producción', 'Produccion'],
            'booking'      => ['Booking'],
            'consolidador' => ['Consolidador', 'Consolidación', 'Consolidacion', 'Pick Up'],
            'en_transito'  => ['En Tránsito', 'En tránsito', 'Transito', 'Tránsito', 'En tránsito terrestre'],
            'puerto'       => ['Puerto', 'Llegada al hub'],
        ],
    ],

    'whatsapp' => [
        'phone' => env('WHATSAPP_PHONE', '50670715265'),
    ],

    'maestros' => [
        'base_url' => env('MAESTROS_API_BASE_URL', 'https://olo.md.orders.raga-x.ai'),
    ],

    'pricing' => [
        'base_url'         => env('PRICING_API_BASE_URL', ''),
        'margins_endpoint' => env('PRICING_MARGINS_ENDPOINT', '/api/margins'),
        'api_token'        => env('PRICING_API_TOKEN'),
        'timeout'          => env('PRICING_API_TIMEOUT', 60),
        'user_id'          => env('PRICING_USER_ID', 16),
    ],

    'transit_matrix' => [
        'csv_path' => env(
            'TRANSIT_MATRIX_CSV_PATH',
            base_path('Matriz de regiones y puertos para validar tiempo de transito.csv')
        ),
    ],

    // Modelo principal del agente IA — activo
    // Cascada de keys: cuando una se agota, pasa a la siguiente
    'groq' => [
        'api_key'   => env('GROQ_API_KEY'),
        'api_key_2' => env('GROQ_API_KEY_2'),
        'api_key_3' => env('GROQ_API_KEY_3'),
        'api_key_4' => env('GROQ_API_KEY_4'),
        'api_key_5' => env('GROQ_API_KEY_5'),
    ],

    // Modelo fallback — desactivado, pendiente aprobación del jefe
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    // Modelo terciario — desactivado, pendiente aprobación del jefe
    'cerebras' => [
        'api_key' => env('CEREBRAS_API_KEY'),
    ],

    // -------------------------------------------------------------------------
    // Agente IA — endpoints REST internos
    // AGENT_API_TOKEN: genera con php artisan tinker → Str::random(64)
    // AGENT_BASE_URL:  en Docker usar nombre del servicio, no localhost
    //                  ejemplo: http://raga-orders-app/api/agent
    // -------------------------------------------------------------------------
    'agent' => [
        'api_token' => env('AGENT_API_TOKEN'),
        'base_url'  => env('AGENT_BASE_URL', 'http://localhost:8001/api/agent'),
    ],

];