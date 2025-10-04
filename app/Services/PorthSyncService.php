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

        // Para ShippingDocument
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

        // Para PurchaseOrder
        if ($document instanceof PurchaseOrder) {
            if ($document->tracking_id) {
                $identifiers[] = ['type' => 'tracking_id', 'value' => $document->tracking_id];
            }
            if ($document->mbl_number) {
                $identifiers[] = ['type' => 'mbl_number', 'value' => $document->mbl_number];
            }
            if ($document->container_number) {
                $identifiers[] = ['type' => 'container_number', 'value' => $document->container_number];
            }
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

            $response = Http::withHeaders([
                'apikey' => $this->porthApiKey,
                'Accept' => 'application/json'
            ])->timeout(90)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error searching in Porth', [
                'error' => $e->getMessage(),
                'identifier' => $identifier
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

        // Enriquecer con datos del documento
        if ($document instanceof ShippingDocument) {
            $basePayload = array_merge($basePayload, [
                'masterBl' => $document->mbl_number,
                'houseBl' => $document->hbl_number,
                'etd' => $document->estimated_departure_date?->toISOString(),
                'eta' => $document->estimated_arrival_date?->toISOString(),
                'notes' => $document->notes,
            ]);
        }

        if ($document instanceof PurchaseOrder) {
            $basePayload = array_merge($basePayload, [
                'incoterm' => $document->incoterms,
                'modality' => $document->mode,
                'etd' => $document->date_etd?->toISOString(),
                'eta' => $document->date_eta?->toISOString(),
                'tags' => array_merge($basePayload['tags'], [
                    'po_number:' . $document->order_number,
                    'order_number:' . $document->order_number
                ])
            ]);
        }

        return $basePayload;
    }

    /**
     * Actualizar documento con datos de Porth
     */
    private function updateDocumentWithPorthData($document, $result)
    {
        try {
            if (isset($result['data']['id'])) {
                $document->porth_shipment_id = $result['data']['id'];
                $document->save();

                Log::info('Updated document with Porth shipment ID', [
                    'document_id' => $document->id,
                    'porth_shipment_id' => $result['data']['id']
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
