<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ShippingDocument;
use App\Models\PurchaseOrder;

class PorthSyncService
{
    protected $porthApiKey;
    protected $porthBaseUrl;

    public function __construct()
    {
        $this->porthApiKey = config('services.porth.api_key');
        $this->porthBaseUrl = config('services.porth.base_url', 'https://porth-api.fly.dev');
    }

    /**
     * Sincronizar un documento con Porth
     */
    public function syncDocument($documentId, $documentType)
    {
        try {
            // Solo procesar ShippingDocument
            if ($documentType !== ShippingDocument::class) {
                Log::info('Porth sync skipped - only ShippingDocument supported', [
                    'document_type' => $documentType,
                    'document_id' => $documentId
                ]);
                return false;
            }

            $document = $documentType::find($documentId);
            if (!$document) {
                Log::error('Document not found for sync', ['id' => $documentId, 'type' => $documentType]);
                return false;
            }

            // Buscar identificadores válidos
            $identifiers = $this->extractIdentifiers($document);
            if (empty($identifiers)) {
                Log::info('No valid identifiers found for sync', ['document_id' => $documentId]);
                return false;
            }

            // Intentar encontrar o crear en Porth
            foreach ($identifiers as $identifier) {
                $result = $this->findOrCreateShipment($identifier, $document);
                if ($result) {
                    $this->updateDocumentWithPorthData($document, $result);
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error in PorthSyncService::syncDocument', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'document_id' => $documentId,
                'document_type' => $documentType
            ]);
            return false;
        }
    }

    /**
     * Extraer identificadores válidos del documento
     */
    private function extractIdentifiers($document)
    {
        $identifiers = [];

        // Solo para ShippingDocument
        if ($document instanceof ShippingDocument) {
            if ($document->tracking_id) {
                $identifiers[] = ['type' => 'tracking_id', 'value' => $document->tracking_id];
            }
            if ($document->mbl_number) {
                $identifiers[] = ['type' => 'mbl_number', 'value' => $document->mbl_number];
            }
            if ($document->container_number) {
                $identifiers[] = ['type' => 'container_number', 'value' => $document->container_number];
            }
            if ($document->booking_code) {
                $identifiers[] = ['type' => 'booking_code', 'value' => $document->booking_code];
            }
        }

        // PurchaseOrder no se sincroniza con Porth
        if ($document instanceof PurchaseOrder) {
            Log::info('PurchaseOrder detected - skipping Porth sync (only ShippingDocument supported)');
            return [];
        }

        return $identifiers;
    }

    /**
     * Buscar o crear shipment en Porth
     */
    private function findOrCreateShipment($identifier, $document)
    {
        // Primero intentar encontrar
        $existing = $this->porthFind($identifier);
        if ($existing) {
            Log::info('Found existing shipment in Porth', [
                'identifier' => $identifier,
                'shipment_id' => $existing['id'] ?? 'unknown'
            ]);
            return ['action' => 'lookup', 'status' => 'hit', 'data' => $existing];
        }

        // Si no existe, crear
        Log::info('Shipment not found, creating new one', ['identifier' => $identifier]);
        return $this->porthCreate($identifier, $document);
    }

    /**
     * Buscar shipment en Porth
     */
    private function porthFind($identifier)
    {
        try {
            $endpoint = $this->getSearchEndpoint($identifier['type']);
            $url = "{$this->porthBaseUrl}{$endpoint}/{$identifier['value']}";

            Log::info('PorthSyncService: Searching in Porth', [
                'identifier' => $identifier,
                'endpoint' => $endpoint,
                'url' => $url,
                'api_key_configured' => !empty($this->porthApiKey)
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json'
            ])->timeout(90)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('PorthSyncService: Found data in Porth', [
                    'identifier' => $identifier,
                    'response_status' => $response->status(),
                    'response_data' => $data,
                    'has_master_bl' => isset($data['masterBl']),
                    'has_container' => isset($data['container']),
                    'master_bl_value' => $data['masterBl'] ?? null,
                    'container_value' => $data['container'] ?? null
                ]);
                return $data;
            } else {
                Log::info('PorthSyncService: No data found in Porth', [
                    'identifier' => $identifier,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error searching in Porth', [
                'error' => $e->getMessage(),
                'identifier' => $identifier,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Crear shipment en Porth
     */
    private function porthCreate($identifier, $document)
    {
        try {
            $payload = $this->buildCreatePayload($identifier, $document);

            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(90)->post("{$this->porthBaseUrl}/api/shipment/add", $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Successfully created shipment in Porth', [
                    'identifier' => $identifier,
                    'shipment_id' => $data['id'] ?? 'unknown'
                ]);
                return ['action' => 'create', 'status' => 'success', 'data' => $data];
            }

            Log::error('Failed to create shipment in Porth', [
                'status' => $response->status(),
                'body' => $response->body(),
                'identifier' => $identifier
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error creating shipment in Porth', [
                'error' => $e->getMessage(),
                'identifier' => $identifier
            ]);
            return null;
        }
    }

    /**
     * Construir payload para creación
     */
    private function buildCreatePayload($identifier, $document)
    {
        $basePayload = [
            'name' => 'GLF-' . $identifier['type'] . '-' . $identifier['value'] . '-' . time(),
            'freightType' => 'ocean',
            'incoterm' => 'FCA',
            'manualTracking' => false,
            'trackCargo' => true,
            'priority' => 'normal',
            'tags' => ['GLF', 'AUTO-CREATED', $identifier['type'] . ':' . $identifier['value']],
            'meta' => [
                'source' => 'GLF-auto-sync',
                'created_at' => now()->toISOString(),
                'document_id' => $document->id,
                'document_type' => get_class($document)
            ]
        ];

        // Enriquecer con datos del ShippingDocument
        if ($document instanceof ShippingDocument) {
            $basePayload = array_merge($basePayload, [
                'masterBl' => $document->mbl_number,
                'houseBl' => $document->hbl_number,
                'etd' => $document->estimated_departure_date?->toISOString(),
                'eta' => $document->estimated_arrival_date?->toISOString(),
                'notes' => $document->notes,
            ]);
        }

        // PurchaseOrder no se sincroniza con Porth
        if ($document instanceof PurchaseOrder) {
            Log::warning('Attempted to build Porth payload for PurchaseOrder - this should not happen');
            return null;
        }

        return $basePayload;
    }

    /**
     * Actualizar documento con datos de Porth
     */
    private function updateDocumentWithPorthData($document, $result)
    {
        try {
            // Log detallado de los datos que vienen de Porth
            Log::info('PorthSyncService: updateDocumentWithPorthData called', [
                'document_id' => $document->id,
                'document_mbl_number' => $document->mbl_number,
                'document_container_number' => $document->container_number,
                'result_data' => $result['data'] ?? null,
                'result_action' => $result['action'] ?? null,
                'result_status' => $result['status'] ?? null
            ]);

            if (isset($result['data']['id'])) {
                // Guardar el estado original antes de la actualización
                $originalMbl = $document->mbl_number;
                $originalContainer = $document->container_number;
                
                $document->porth_shipment_id = $result['data']['id'];
                
                // Verificar si Porth está devolviendo datos que puedan sobrescribir nuestros campos
                if (isset($result['data']['masterBl']) && $result['data']['masterBl'] !== $originalMbl) {
                    Log::warning('Porth returned different MBL number', [
                        'document_id' => $document->id,
                        'local_mbl' => $originalMbl,
                        'porth_mbl' => $result['data']['masterBl'],
                        'action' => 'keeping_local_value'
                    ]);
                    // NO sobrescribir el mbl_number local
                }
                
                if (isset($result['data']['container']) && $result['data']['container'] !== $originalContainer) {
                    Log::warning('Porth returned different container number', [
                        'document_id' => $document->id,
                        'local_container' => $originalContainer,
                        'porth_container' => $result['data']['container'],
                        'action' => 'keeping_local_value'
                    ]);
                    // NO sobrescribir el container_number local
                }
                
                $document->save();

                Log::info('Updated document with Porth shipment ID (preserving local values)', [
                    'document_id' => $document->id,
                    'porth_shipment_id' => $result['data']['id'],
                    'final_mbl_number' => $document->mbl_number,
                    'final_container_number' => $document->container_number
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating document with Porth data', [
                'error' => $e->getMessage(),
                'document_id' => $document->id
            ]);
        }
    }

    /**
     * Obtener endpoint de búsqueda según el tipo
     */
    private function getSearchEndpoint($type)
    {
        $endpoints = [
            'tracking_id' => '/api/shipment/byId',
            'mbl_number' => '/api/shipment/byMasterBl',
            'container_number' => '/api/shipment/byContainer',
            'booking_code' => '/api/shipment/byBooking'
        ];

        return $endpoints[$type] ?? '/api/shipment/byId';
    }
}
