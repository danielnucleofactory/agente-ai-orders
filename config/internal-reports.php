<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dominios internos autorizados para reportes restringidos
    |--------------------------------------------------------------------------
    |
    | Los reportes internos de Raga requieren dos condiciones:
    | 1. Un permiso explícito asignado al usuario.
    | 2. Que el email pertenezca a uno de estos dominios internos.
    |
    */
    'allowed_email_domains' => [
        'raga-x.ai',
        'raga-orders.com',
    ],
];
