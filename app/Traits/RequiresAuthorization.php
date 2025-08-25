<?php

namespace App\Traits;

use App\Models\AuthorizationRequest;
use App\Services\AuthorizationService;

trait RequiresAuthorization
{
    /**
     * Solicita autorización para realizar una acción en este modelo
     */
    public function requestAuthorization(
        string $operationType,
        array $data = [],
        string $notes = null
    ): AuthorizationRequest {
        $service = app(AuthorizationService::class);
        return $service->requestAuthorization($this, $operationType, $data, $notes);
    }

    /**
     * Relación con las solicitudes de autorización para este modelo
     */
    public function authorizationRequests()
    {
        return $this->morphMany(AuthorizationRequest::class, 'authorizable');
    }

    /**
     * Verifica si hay alguna solicitud pendiente para una operación específica
     */
    public function hasPendingAuthorization(string $operationType): bool
    {
        return $this->authorizationRequests()
            ->where('operation_type', $operationType)
            ->where('status', AuthorizationRequest::STATUS_PENDING)
            ->exists();
    }
}
