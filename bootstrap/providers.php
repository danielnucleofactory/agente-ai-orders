<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    // RagaOrders\POConfirmation\POConfirmationServiceProvider::class, // Ahora se maneja desde ModuleServiceProvider
];
