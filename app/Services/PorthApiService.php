<?php

namespace App\Services;

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

            Log::error('porth_api:get_by_id_failed', [
                'id' => $id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;

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
