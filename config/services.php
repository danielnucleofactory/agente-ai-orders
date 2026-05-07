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
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ship24' => [
        'api_key' => env('SHIP24_API_KEY'),
    ],

    'po_bulk_update' => [
        'api_url' => env('PO_BULK_UPDATE_API_URL', 'https://olo.md.orders.raga-x.ai/api/v1/po/bulk-orders'),
        'api_token' => env('PO_BULK_UPDATE_API_TOKEN'),
    ],

    'porth' => [
        'api_key' => env('PORTH_API_KEY'),
        'api_url' => env('PORTH_API_URL', 'https://api.porth.app'),
        'base_url' => env('PORTH_BASE_URL', 'https://porth-api.fly.dev'), // Legacy
        'auth_header' => env('PORTH_AUTH_HEADER', 'apikey'),
        'enabled' => env('PORTH_SYNC_ENABLED', true),
        'sync_enabled' => env('PORTH_SYNC_ENABLED', true), // Legacy
        'max_retries' => env('PORTH_MAX_RETRIES', 5),
        'timeout' => env('PORTH_TIMEOUT', 90),
        'sync_dry_run' => env('PORTH_SYNC_DRY_RUN', false),
        'sync_lookback_hours' => env('PORTH_SYNC_LOOKBACK_HOURS', 2),
        'notification_user_ids' => env('PORTH_SYNC_NOTIFICATION_USER_IDS', ''),
        // Automatización de transiciones Kanban según estado Porth
        'kanban_auto_transition' => env('PORTH_KANBAN_AUTO_TRANSITION', true),
        'kanban_stages' => [
            'produccion' => ['Producción', 'Produccion'],
            'booking' => ['Booking'],
            'consolidador' => ['Consolidador', 'Consolidación', 'Consolidacion', 'Pick Up'],
            'en_transito' => ['En Tránsito', 'En tránsito', 'Transito', 'Tránsito', 'En tránsito terrestre'],
            'puerto' => ['Puerto', 'Llegada al hub'],
        ],
    ],

    'whatsapp' => [
        'phone' => env('WHATSAPP_PHONE', '50670715265'),
    ],

    'maestros' => [
        'base_url' => env('MAESTROS_API_BASE_URL', 'https://olo.md.orders.raga-x.ai'),
    ],

    'transit_matrix' => [
        'csv_path' => env(
            'TRANSIT_MATRIX_CSV_PATH',
            base_path('Matriz de regiones y puertos para validar tiempo de transito.csv')
        ),
    ],

];
