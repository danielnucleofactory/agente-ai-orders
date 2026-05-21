<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    RagaOrders\Webhook\WebhookServiceProvider::class,
    App\Providers\ModuleServiceProvider::class,
    App\Providers\WebhookSettingsRouteFallbackServiceProvider::class,
];
