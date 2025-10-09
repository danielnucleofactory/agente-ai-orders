<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PO Confirmation Module Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar todas las opciones del módulo de confirmación
    | de órdenes de compra.
    |
    */

    'enabled' => env('PO_CONFIRMATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Hash Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para los hashes de confirmación.
    |
    */
    'hash_expiry_hours' => env('PO_CONFIRMATION_HASH_EXPIRY', 72),

    /*
    |--------------------------------------------------------------------------
    | Email Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para los emails de confirmación.
    |
    */
    'email_template' => 'po-confirmation::emails.confirmation',
    'email_from_name' => env('PO_CONFIRMATION_FROM_NAME', 'Raga Orders'),
    'email_from_address' => env('PO_CONFIRMATION_FROM_ADDRESS', 'noreply@ragaorders.com'),

    /*
    |--------------------------------------------------------------------------
    | Automation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la automatización del envío de emails.
    |
    */
    'auto_send' => env('PO_CONFIRMATION_AUTO_SEND', true),
    'check_interval' => env('PO_CONFIRMATION_CHECK_INTERVAL', 'hourly'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para las rutas del módulo.
    |
    */
    'route_prefix' => env('PO_CONFIRMATION_ROUTE_PREFIX', 'po'),
    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la base de datos.
    |
    */
    'purchase_order_model' => env('PO_CONFIRMATION_PO_MODEL', 'App\Models\PurchaseOrder'),
    'vendor_model' => env('PO_CONFIRMATION_VENDOR_MODEL', 'App\Models\Vendor'),

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para las notificaciones.
    |
    */
    'notify_admin_on_confirmation' => env('PO_CONFIRMATION_NOTIFY_ADMIN', true),
    'admin_email' => env('PO_CONFIRMATION_ADMIN_EMAIL', 'admin@ragaorders.com'),
];
