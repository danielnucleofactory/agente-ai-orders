<?php

namespace App\Services;

use RagaOrders\Webhook\Services\WebhookService;

/**
 * Decorador del WebhookService del módulo que post-procesa el payload de PO
 * para ocultar date_variable_date (solo se envía date_carga_po)
 * y freight_type (redundante con mode, que ya lleva el valor correcto).
 *
 * Extiende WebhookService para que el type-hint en WebhookSettingsController
 * siga siendo válido (evita error 500 en /webhook/settings).
 */
class WebhookPayloadFilterDecorator extends WebhookService
{
    public function __construct(
        protected WebhookService $inner
    ) {
        // No llamar a parent::__construct(); el comportamiento real está en $inner
    }

    /**
     * Transforma el payload y luego quita campos que no deben ir al endpoint.
     */
    public function transformPurchaseOrderPayload(array $payload): array
    {
        $transformed = $this->inner->transformPurchaseOrderPayload($payload);

        // Ocultar date_variable_date (el valor ya está en date_carga_po)
        unset($transformed['date_variable_date']);

        // Ocultar freight_type (redundante con mode, que ya lleva "MARITIMO", "AÉREO", etc.)
        unset($transformed['freight_type']);

        // Campos que no deben enviarse al webhook (según especificación Intelix)
        foreach (['variable_calculare_weight', 'Invoice_amount', 'freight_amount', 'vendor_number', 'tracking_id', 'vendor_id'] as $key) {
            unset($transformed[$key]);
        }

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
