<?php

namespace App\Services;

use App\Exceptions\PorthTemporarilyBlockedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PorthApiService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $authHeader;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.porth.api_key', '');
        $this->apiUrl = rtrim(config('services.porth.api_url', 'https://api.porth.app'), '/');
        $this->authHeader = config('services.porth.auth_header', 'apikey');
        $this->timeout = (int) config('services.porth.timeout', 90);
    }

    /**
     * Verifica si la API de Porth está habilitada
     */
    public function isEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Lista shipments actualizados en un rango de fechas (ISO).
     * 
     * @param string $startIso Fecha inicio en formato ISO8601
     * @param string $endIso Fecha fin en formato ISO8601
     * @return array Lista de IDs de shipments actualizados
     */
    public function listLastUpdated(string $startIso, string $endIso): array
    {
        $data = [];

        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return $data;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/v2/shipments/lastUpdated";

            Log::info('porth_api:listLastUpdated_request', [
                'endpoint' => $endpoint,
                'start' => $startIso,
                'end' => $endIso,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint, [
                    'startdate' => $startIso,
                    'endDate' => $endIso,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (is_array($responseData)) {
                    $data = $responseData;
                    Log::info('porth_api:listLastUpdated_success', [
                        'count' => count($data),
                    ]);
                }
            } else {
                Log::error('porth_api:lastUpdated_failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('porth_api:listLastUpdated_exception', [
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    /**
     * Obtiene detalle de un shipment por ID.
     * 
     * @param string $id ID del shipment en Porth
     * @return array|null Datos del shipment o null si no se encontró
     */
    public function getShipmentById(string $id): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/shipment/byId/{$id}";

            Log::info('porth_api:getShipmentById_request', [
                'id' => $id,
                'endpoint' => $endpoint,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('porth_api:getShipmentById_success', [
                    'id' => $id,
                    'has_data' => !empty($data),
                ]);
                return $data;
            }

            $body = $response->json();
            if (
                $response->status() === 403
                && is_array($body)
                && ($body['error'] ?? null) === 'temporarily_blocked'
            ) {
                $retryAfterSeconds = isset($body['retryAfterSeconds']) ? (int) $body['retryAfterSeconds'] : null;

                Log::warning('porth_api:temporarily_blocked', [
                    'id' => $id,
                    'retry_after_seconds' => $retryAfterSeconds,
                ]);

                throw new PorthTemporarilyBlockedException($retryAfterSeconds);
            }

            Log::error('porth_api:get_by_id_failed', [
                'id' => $id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;

        } catch (PorthTemporarilyBlockedException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('porth_api:getShipmentById_exception', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Busca un shipment por Master BL
     * 
     * @param string $mbl Número de Master BL
     * @return array|null Datos del shipment o null si no se encontró
     */
    public function getShipmentByMasterBl(string $mbl): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/shipment/byMasterBl/{$mbl}";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            Log::info('porth_api:getShipmentByMasterBl_not_found', [
                'mbl' => $mbl,
                'status' => $response->status(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('porth_api:getShipmentByMasterBl_exception', [
                'mbl' => $mbl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Busca un shipment por número de contenedor
     * 
     * @param string $containerNumber Número de contenedor
     * @return array|null Datos del shipment o null si no se encontró
     */
    public function getShipmentByContainer(string $containerNumber): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/shipment/byContainer/{$containerNumber}";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            Log::info('porth_api:getShipmentByContainer_not_found', [
                'container' => $containerNumber,
                'status' => $response->status(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('porth_api:getShipmentByContainer_exception', [
                'container' => $containerNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Actualiza un shipment existente en Porth.
     * Solo envía los campos proporcionados (partial update).
     * 
     * Campos soportados: masterBl, bookingNumber, carrierCode, cargo, etc.
     * 
     * @param string $shipmentId ID del shipment en Porth
     * @param array $data Campos a actualizar
     * @return array|null Datos del shipment actualizado o null si falló
     */
    public function updateShipment(string $shipmentId, array $data): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/shipment/update/{$shipmentId}";

            Log::info('porth_api:updateShipment_request', [
                'shipment_id' => $shipmentId,
                'endpoint' => $endpoint,
                'fields' => array_keys($data),
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->put($endpoint, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('porth_api:updateShipment_success', [
                    'shipment_id' => $shipmentId,
                    'updated_fields' => array_keys($data),
                ]);
                return $responseData;
            }

            Log::error('porth_api:updateShipment_failed', [
                'shipment_id' => $shipmentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('porth_api:updateShipment_exception', [
                'shipment_id' => $shipmentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Actualiza un cargo/contenedor existente en Porth.
     *
     * @param string $cargoId ID del cargo en Porth
     * @param array $data Campos a actualizar
     * @return array|null Respuesta de Porth o null si falló
     */
    public function updateShipmentCargo(string $cargoId, array $data): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/cargo/shipment-cargo/update";
            $payload = array_merge(['id' => $cargoId], $data);

            Log::info('porth_api:updateShipmentCargo_request', [
                'cargo_id' => $cargoId,
                'endpoint' => $endpoint,
                'fields' => array_keys($payload),
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->put($endpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('porth_api:updateShipmentCargo_success', [
                    'cargo_id' => $cargoId,
                    'updated_fields' => array_keys($payload),
                ]);
                return $responseData;
            }

            Log::error('porth_api:updateShipmentCargo_failed', [
                'cargo_id' => $cargoId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('porth_api:updateShipmentCargo_exception', [
                'cargo_id' => $cargoId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Sincroniza cambios de campos de negocio desde Orders hacia Porth.
     * 
     * Dos comportamientos según el tipo de campo:
     * 
     * A) Contenedor agregado o identificadores que cambian el embarque → re-vinculación.
     *    Se limpia porth_id y se despacha PorthSyncJob para:
     *    1. buscar el shipment por el nuevo identificador en Porth
     *    2. si existe, vincularlo a la PO
     *    3. si no existe, crearlo y luego vincularlo
     * 
     * B) Naviera (shipping_line) → Corrección al embarque actual.
     *    Se actualiza el carrierCode en el embarque existente de Porth.
     * 
     * @param \App\Models\PurchaseOrder $po La PO con datos actualizados
     * @param array $changedFields Array asociativo campo => nuevo_valor
     * @param array $previousValues Array asociativo campo => valor_anterior
     */
    public function pushChangesToPorth(\App\Models\PurchaseOrder $po, array $changedFields, array $previousValues = []): void
    {
        // Campos que son correcciones al embarque actual → update en Porth
        $updateFields = ['shipping_line', 'container_number'];
        $updateChanges = array_intersect_key($changedFields, array_flip($updateFields));

        // Si no hay cambios soportados o no hay shipment actual, no hacemos nada.
        if (empty($updateChanges) || empty($po->porth_id)) {
            return;
        }

        $porthId = $po->porth_id;
        $shipmentUpdated = false;

        // A) Si cambió contenedor:
        // - si antes no había contenedor, es un alta: buscar/vincular o crear shipment vía PorthSyncJob
        // - si sí había, intentar update directo del cargo actual por cargoId
        if (array_key_exists('container_number', $updateChanges) && !empty($updateChanges['container_number'])) {
            $previousContainerNumber = $this->normalizeContainerNumber($previousValues['container_number'] ?? null);

            if (empty($previousContainerNumber)) {
                Log::info('porth_api:container_added_trigger_shipment_relink', [
                    'purchase_order_id' => $po->id,
                    'order_number' => $po->order_number,
                    'porth_id' => $porthId,
                    'new_container_number' => $updateChanges['container_number'],
                ]);

                $this->relinkShipmentAfterIdentifierChange($po, [
                    'container_number' => $updateChanges['container_number'],
                ]);

                return;
            }

            $cargoId = $this->resolveShipmentCargoIdForContainerUpdate(
                $porthId,
                $previousContainerNumber
            );

            if ($cargoId) {
                try {
                    $this->updateShipmentCargo($cargoId, [
                        'number' => $updateChanges['container_number'],
                    ]);

                    Log::info('porth_api:container_updated_by_cargo_id', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'porth_id' => $porthId,
                        'cargo_id' => $cargoId,
                        'old_container_number' => $previousContainerNumber,
                        'new_container_number' => $updateChanges['container_number'],
                    ]);
                } catch (\Throwable $e) {
                    Log::error('porth_api:container_update_by_cargo_id_failed', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'porth_id' => $porthId,
                        'cargo_id' => $cargoId,
                        'error' => $e->getMessage(),
                    ]);

                    $this->relinkShipmentAfterIdentifierChange($po, [
                        'container_number' => $updateChanges['container_number'],
                    ]);

                    return;
                }
            } else {
                Log::warning('porth_api:container_update_missing_cargo_id_fallback_relink', [
                    'purchase_order_id' => $po->id,
                    'order_number' => $po->order_number,
                    'porth_id' => $porthId,
                    'old_container_number' => $previousContainerNumber,
                    'new_container_number' => $updateChanges['container_number'],
                ]);

                $this->relinkShipmentAfterIdentifierChange($po, [
                    'container_number' => $updateChanges['container_number'],
                ]);

                return;
            }
        }

        // B) Si cambió naviera: actualizar el embarque existente en Porth
        if (!empty($updateChanges) && !empty($po->porth_id)) {
            $porthPayload = [];

            if (isset($updateChanges['shipping_line']) && !empty($updateChanges['shipping_line'])) {
                $translationService = app(PorthTranslationService::class);
                $carrierCode = $translationService->getCarrierCodeFromShippingLineName($updateChanges['shipping_line']);
                if ($carrierCode) {
                    $porthPayload['carrierCode'] = $carrierCode;
                }
            }

            if (empty($porthPayload)) {
                return;
            }

            Log::info('porth_api:updating_existing_shipment', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'porth_id' => $porthId,
                'porth_payload' => $porthPayload,
            ]);

            try {
                $this->updateShipment($porthId, $porthPayload);
                $shipmentUpdated = true;
            } catch (\Throwable $e) {
                Log::error('porth_api:push_changes_error', [
                    'purchase_order_id' => $po->id,
                    'porth_id' => $porthId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($shipmentUpdated) {
            Log::info('porth_api:shipment_updated_from_orders', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'porth_id' => $porthId,
                'updated_fields' => array_keys($updateChanges),
            ]);
        }
    }

    private function resolveShipmentCargoIdForContainerUpdate(string $shipmentId, ?string $previousContainerNumber): ?string
    {
        $shipment = $this->getShipmentById($shipmentId);
        $cargoList = $shipment['cargo'] ?? [];

        if (!is_array($cargoList) || empty($cargoList)) {
            return null;
        }

        if (count($cargoList) === 1) {
            return $cargoList[0]['id'] ?? null;
        }

        $normalizedPreviousContainer = $this->normalizeContainerNumber($previousContainerNumber);
        if ($normalizedPreviousContainer === null) {
            return null;
        }

        foreach ($cargoList as $cargo) {
            $cargoNumber = $cargo['number'] ?? ($cargo['name'] ?? null);
            if ($this->normalizeContainerNumber($cargoNumber) === $normalizedPreviousContainer) {
                return $cargo['id'] ?? null;
            }
        }

        return null;
    }

    private function relinkShipmentAfterIdentifierChange(\App\Models\PurchaseOrder $po, array $identifierChanges): void
    {
        $oldPorthId = $po->porth_id;

        Log::info('porth_api:shipment_identifier_change_triggering_relink', [
            'purchase_order_id' => $po->id,
            'order_number' => $po->order_number,
            'old_porth_id' => $oldPorthId,
            'changed_identifiers' => array_keys($identifierChanges),
        ]);

        \Illuminate\Support\Facades\DB::table('purchase_orders')
            ->where('id', $po->id)
            ->update([
                'porth_id' => null,
                'last_porth_sync_at' => null,
                'porth_shipment_number' => null,
            ]);

        $po->refresh();

        \App\Jobs\PorthSyncJob::dispatch($po->id, get_class($po))
            ->onQueue('porth-sync')
            ->delay(now()->addSeconds(5))
            ->afterResponse();

        Log::info('porth_api:porth_sync_job_dispatched_for_relookup_or_create', [
            'purchase_order_id' => $po->id,
            'order_number' => $po->order_number,
            'new_identifiers' => $identifierChanges,
            'after_response' => true,
        ]);
    }

    private function normalizeContainerNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return $normalized === '' ? null : $normalized;
    }

    /**
     * Busca un shipment por número de booking
     * 
     * @param string $bookingNumber Número de booking
     * @return array|null Datos del shipment o null si no se encontró
     */
    public function getShipmentByBooking(string $bookingNumber): ?array
    {
        if (!$this->isEnabled()) {
            Log::warning('porth_api:disabled_missing_key');
            return null;
        }

        try {
            $endpoint = "{$this->apiUrl}/api/shipment/byBooking/{$bookingNumber}";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    $this->authHeader => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json();
            }

            Log::info('porth_api:getShipmentByBooking_not_found', [
                'booking' => $bookingNumber,
                'status' => $response->status(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('porth_api:getShipmentByBooking_exception', [
                'booking' => $bookingNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
