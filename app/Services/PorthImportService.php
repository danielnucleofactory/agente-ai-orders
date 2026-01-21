<?php

namespace App\Services;

use App\Helpers\PorthImportHelper;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PorthImportService
{
    /**
     * Email del usuario sistema para sincronizaciones automáticas
     */
    protected const SYSTEM_USER_EMAIL = 'apps@raga-x.ai';

    public function __construct(
        protected PorthImportHelper $helper
    ) {
    }

    /**
     * Importa un detalle de envío de Porth y lo guarda en las PurchaseOrders.
     * Actualiza TODAS las POs que tienen el mismo porth_id.
     * 
     * Autentica temporalmente al usuario sistema "Next Orders" para que
     * los cambios se registren en el historial de auditoría.
     * 
     * @param array $data Datos del shipment de Porth
     * @return PurchaseOrder|null Primera PO actualizada o null si no hay match
     */
    public function importShipment(array $data): ?PurchaseOrder
    {
        // Guardar usuario actual (si hay uno)
        $previousUser = Auth::user();
        
        // Autenticar usuario sistema para que el Observer registre los cambios
        $this->authenticateSystemUser();

        try {
            return DB::transaction(function () use ($data) {
                $purchaseOrders = $this->resolvePurchaseOrders($data);

                if ($purchaseOrders->isEmpty()) {
                    Log::warning('porth_import:no_purchase_order_match', [
                        'porth_id' => $data['id'] ?? null,
                    ]);
                    return null;
                }

                $firstPO = null;
                $updatedCount = 0;

                foreach ($purchaseOrders as $po) {
                    $this->updatePurchaseOrder($po, $data);

                    if (!$firstPO) {
                        $firstPO = $po->fresh();
                    }
                    $updatedCount++;
                }

                Log::info('porth_import:completed', [
                    'porth_id' => $data['id'] ?? null,
                    'updated_count' => $updatedCount,
                ]);

                return $firstPO;
            });
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
                Log::debug('porth_import:system_user_authenticated', [
                    'user_id' => $systemUser->id,
                    'user_name' => $systemUser->name,
                ]);
            } else {
                Log::warning('porth_import:system_user_not_found', [
                    'email' => self::SYSTEM_USER_EMAIL,
                    'message' => 'Los cambios no se registrarán en el historial de auditoría',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('porth_import:auth_error', [
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
            Log::debug('porth_import:restore_user_skipped', [
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Busca PurchaseOrders por porth_id
     */
    protected function resolvePurchaseOrders(array $data)
    {
        $porthId = $data['id'] ?? null;
        if (!$porthId) {
            return collect();
        }

        return PurchaseOrder::where('porth_id', $porthId)->get();
    }

    /**
     * Actualiza una PurchaseOrder con datos de Porth
     * IMPORTANTE: Los campos existentes solo se actualizan si están vacíos localmente
     */
    protected function updatePurchaseOrder(PurchaseOrder $po, array $data): void
    {
        $fieldsToUpdate = [];
        
        // BL fields - SOLO si están vacíos localmente
        $blFields = $this->helper->getBlFields($data);
        foreach ($blFields as $field => $value) {
            if (empty($po->$field)) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Container - SOLO si vacío
        if (empty($po->container_number)) {
            $containerFields = $this->helper->getContainerFields($data);
            $fieldsToUpdate = array_merge($fieldsToUpdate, $containerFields);
        }
        
        // Fechas principales desde payload
        $payloadValues = $this->helper->buildPurchaseOrderPayloadValues($data);
        $fieldsMap = $this->helper->getPurchaseOrderFieldsMap();
        
        foreach ($fieldsMap as $payloadKey => $poField) {
            $value = $payloadValues[$payloadKey] ?? null;
            // Solo actualizar si el campo local está vacío
            if (empty($po->$poField) && $value !== null) {
                $fieldsToUpdate[$poField] = $value;
            }
        }
        
        // Campos de maestros traducidos - SOLO si están vacíos
        $maestrosFields = $this->helper->getMaestrosFields($data);
        foreach ($maestrosFields as $field => $value) {
            if (empty($po->$field) && $value !== null) {
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Campos de Porth - SIEMPRE se actualizan (son campos de tracking)
        $porthFields = $this->buildPorthFieldsForPO($data);
        $fieldsToUpdate = array_merge($fieldsToUpdate, $porthFields);

        if (!empty($fieldsToUpdate)) {
            $po->fill($fieldsToUpdate);
            $po->save();

            Log::info('porth_import:po_updated', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'fields_updated' => array_keys($fieldsToUpdate),
            ]);
        }
    }

    /**
     * Construye campos Porth para PurchaseOrder
     */
    protected function buildPorthFieldsForPO(array $data): array
    {
        return [
            'porth_id' => $data['id'] ?? null,
            'porth_shipment_number' => $data['shipmentNumber'] ?? ($data['porthShipmentNumber'] ?? null),
            'porth_carrier_code' => $data['carrierCode'] ?? null,
            'porth_pol' => $this->normalize($data['pol'] ?? null),
            'porth_pod' => $this->normalize($data['pod'] ?? null),
            'porth_pol_name' => $data['polName'] ?? null,
            'porth_pod_name' => $data['podName'] ?? null,
            'porth_phase' => $data['phase'] ?? null,
            'porth_priority' => $data['priority'] ?? null,
            'porth_modality' => $data['modality'] ?? null,
            'porth_vessel_voyage' => $data['vesselVoyage'] ?? null,
            'porth_origin' => $data['origin'] ?? null,
            'porth_final_destination' => $data['finalDestination'] ?? null,
            'porth_first_eta' => $this->parseDateTime($data['firstEta'] ?? null),
            'porth_first_etd' => $this->parseDateTime($data['firstEtd'] ?? null),
            'porth_ready' => $this->parseDateTime($data['ready'] ?? null),
            'porth_to_origin_port' => $this->parseDateTime($data['toOriginPort'] ?? null),
            'porth_at_origin_port' => $this->parseDateTime($data['atOriginPort'] ?? null),
            'porth_in_transit' => $this->parseDateTime($data['inTransit'] ?? null),
            'porth_at_destination_port' => $this->parseDateTime($data['atDestinationPort'] ?? null),
            'porth_to_final_destination' => $this->parseDateTime($data['toFinalDestination'] ?? null),
            'porth_delivered' => $this->parseDateTime($data['delivered'] ?? null),
            'porth_free_time_at_destination' => $data['freeTimeAtDestination'] ?? null,
            'porth_manual_tracking' => isset($data['manualTracking']) ? (bool) $data['manualTracking'] : null,
            'last_porth_sync_at' => now(),
            'freight_type' => !empty($data['freightType']) ? strtolower(trim($data['freightType'])) : null,
        ];
    }

    /**
     * Normaliza strings: trim + uppercase
     */
    private function normalize(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        return strtoupper(trim($value));
    }

    /**
     * Parsea fecha a Carbon datetime
     */
    private function parseDateTime(?string $value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
