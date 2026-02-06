<?php

namespace App\Services;

use RagaOrders\Webhook\Services\WebhookService;

/**
 * Decorador del WebhookService del módulo que post-procesa el payload de PO
 * para ocultar date_variable_date (solo se envía date_carga_po) y current_timestamp.
 *
 * Así la app no depende de modificar internal_modules (que puede estar en .gitignore).
 */
class WebhookPayloadFilterDecorator
{
    public function __construct(
        protected WebhookService $inner
    ) {}

    /**
     * Transforma el payload y luego quita campos que no deben ir al endpoint.
     */
    public function transformPurchaseOrderPayload(array $payload): array
    {
        $transformed = $this->inner->transformPurchaseOrderPayload($payload);

        // Ocultar date_variable_date (el valor ya está en date_carga_po)
        unset($transformed['date_variable_date']);

        // Ocultar current_timestamp
        unset($transformed['current_timestamp']);

        return $transformed;
    }

    /**
     * Delegar el resto de métodos al servicio original.
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->inner->{$name}(...$arguments);
    }
}
