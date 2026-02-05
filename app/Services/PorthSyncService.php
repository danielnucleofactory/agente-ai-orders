<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ShippingDocument;
use App\Models\PurchaseOrder;
use App\Models\User;

class PorthSyncService
{
    /**
     * Email del usuario sistema para sincronizaciones automáticas
     */
    protected const SYSTEM_USER_EMAIL = 'apps@raga-x.ai';

    protected $porthApiKey;
    protected $porthBaseUrl;

    public function __construct(
        protected PorthTranslationService $translationService
    ) {
        $this->porthApiKey = config('services.porth.api_key');
        $this->porthBaseUrl = rtrim(config('services.porth.api_url', 'https://api.porth.app'), '/');
    }

    /**
     * Sincronizar un documento con Porth
     * Soporta ShippingDocument y PurchaseOrder
     */
    public function syncDocument($documentId, $documentType)
    {
        try {
            // Solo procesar ShippingDocument o PurchaseOrder
            if ($documentType !== ShippingDocument::class && $documentType !== PurchaseOrder::class) {
                Log::info('Porth sync skipped - only ShippingDocument and PurchaseOrder supported', [
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
     * Construir payload para creación.
     * Incluye container/BL/booking cuando existan y carrierCode (desde porth_carrier_code o nombre de naviera)
     * para que Porth traiga la información correcta.
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

        // Identificador usado: container, BL o booking (según tipo)
        if (!empty($identifier['value'])) {
            if ($identifier['type'] === 'container_number') {
                $basePayload['container'] = $identifier['value'];
                $basePayload['cargo'] = [['type' => 'container', 'number' => $identifier['value'], 'name' => $identifier['value']]];
            } elseif ($identifier['type'] === 'mbl_number') {
                $basePayload['masterBl'] = $identifier['value'];
            } elseif ($identifier['type'] === 'booking_code') {
                $basePayload['bookingNumber'] = $identifier['value'];
            }
        }

        // Enriquecer con datos del documento (sobrescriben si ya se puso por identifier)
        if ($document instanceof ShippingDocument) {
            $basePayload['masterBl'] = $document->mbl_number ?? $basePayload['masterBl'] ?? null;
            $basePayload['houseBl'] = $document->hbl_number;
            $basePayload['bookingNumber'] = $document->booking_code ?? $basePayload['bookingNumber'] ?? null;
            $basePayload['etd'] = $document->estimated_departure_date?->toISOString();
            $basePayload['eta'] = $document->estimated_arrival_date?->toISOString();
            $basePayload['notes'] = $document->notes;
            if ($document->container_number && empty($basePayload['cargo'])) {
                $basePayload['container'] = $document->container_number;
                $basePayload['cargo'] = [['type' => 'container', 'number' => $document->container_number, 'name' => $document->container_number]];
            }
        }

        if ($document instanceof PurchaseOrder) {
            $basePayload['masterBl'] = $document->mbl_number ?? $basePayload['masterBl'] ?? null;
            $basePayload['etd'] = $document->date_etd?->toISOString();
            $basePayload['eta'] = $document->date_eta?->toISOString();
            $basePayload['notes'] = $document->notes;
            if ($document->container_number && empty($basePayload['cargo'])) {
                $basePayload['container'] = $document->container_number;
                $basePayload['cargo'] = [['type' => 'container', 'number' => $document->container_number, 'name' => $document->container_number]];
            }
        }

        // carrierCode: si ya está guardado (porth_carrier_code) o se resuelve desde shipping_line (nombre Maestros)
        $carrierCode = $document->porth_carrier_code ?? null;
        if (empty($carrierCode) && !empty($document->shipping_line)) {
            $carrierCode = $this->translationService->getCarrierCodeFromShippingLineName($document->shipping_line);
        }
        if (!empty($carrierCode)) {
            $basePayload['carrierCode'] = $carrierCode;
        }

        return array_filter($basePayload, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Actualizar documento con datos de Porth
     */
    private function updateDocumentWithPorthData($document, $result)
    {
        // Guardar usuario actual (si hay uno)
        $previousUser = Auth::user();
        
        // Autenticar usuario sistema para que el Observer registre los cambios
        $this->authenticateSystemUser();

        try {
            // Log detallado de los datos que vienen de Porth
            Log::info('PorthSyncService: updateDocumentWithPorthData called', [
                'document_id' => $document->id,
                'document_type' => get_class($document),
                'document_mbl_number' => $document->mbl_number ?? null,
                'document_container_number' => $document->container_number ?? null,
                'result_data' => $result['data'] ?? null,
                'result_action' => $result['action'] ?? null,
                'result_status' => $result['status'] ?? null
            ]);

            if (isset($result['data']['id'])) {
                $porthId = $result['data']['id'];
                
                // Guardar el estado original antes de la actualización
                $originalMbl = $document->mbl_number ?? null;
                $originalContainer = $document->container_number ?? null;
                
                // Usar porth_id (campo correcto para sincronización)
                $document->porth_id = $porthId;
                
                // Verificar si Porth está devolviendo datos que puedan sobrescribir nuestros campos
                if (isset($result['data']['masterBl']) && $result['data']['masterBl'] !== $originalMbl) {
                    Log::warning('Porth returned different MBL number', [
                        'document_id' => $document->id,
                        'document_type' => get_class($document),
                        'local_mbl' => $originalMbl,
                        'porth_mbl' => $result['data']['masterBl'],
                        'action' => 'keeping_local_value'
                    ]);
                    // NO sobrescribir el mbl_number local
                }
                
                if (isset($result['data']['container']) && $result['data']['container'] !== $originalContainer) {
                    Log::warning('Porth returned different container number', [
                        'document_id' => $document->id,
                        'document_type' => get_class($document),
                        'local_container' => $originalContainer,
                        'porth_container' => $result['data']['container'],
                        'action' => 'keeping_local_value'
                    ]);
                    // NO sobrescribir el container_number local
                }
                
                $document->save();

                Log::info('Updated document with Porth ID (preserving local values)', [
                    'document_id' => $document->id,
                    'document_type' => get_class($document),
                    'porth_id' => $porthId,
                    'final_mbl_number' => $document->mbl_number ?? null,
                    'final_container_number' => $document->container_number ?? null
                ]);

                // Nota: La importación de datos completos se hace mediante el cronjob
                // 'porth:import-pending' que se ejecuta cada 5 minutos.
                // Esto evita timeouts en la request del usuario.
            }
        } catch (\Exception $e) {
            Log::error('Error updating document with Porth data', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
                'document_type' => get_class($document)
            ]);
        } finally {
            // Restaurar usuario anterior o desautenticar
            $this->restoreUser($previousUser);
        }
    }

    /**
     * Autentica el usuario sistema "Raga-X Apps" para registrar cambios en auditoría
     */
    protected function authenticateSystemUser(): void
    {
        try {
            $systemUser = User::where('email', self::SYSTEM_USER_EMAIL)->first();
            
            if ($systemUser) {
                Auth::login($systemUser);
                Log::debug('porth_sync:system_user_authenticated', [
                    'user_id' => $systemUser->id,
                    'user_name' => $systemUser->name,
                ]);
            } else {
                Log::warning('porth_sync:system_user_not_found', [
                    'email' => self::SYSTEM_USER_EMAIL,
                    'message' => 'Los cambios no se registrarán en el historial de auditoría',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('porth_sync:auth_error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Restaura el usuario anterior o desautentica
     */
    protected function restoreUser($previousUser): void
    {
        try {
            if ($previousUser) {
                Auth::login($previousUser);
            } else {
                Auth::logout();
            }
        } catch (\Exception $e) {
            // Ignorar errores de logout en contexto de consola
            Log::debug('porth_sync:restore_user_skipped', [
                'reason' => $e->getMessage(),
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
