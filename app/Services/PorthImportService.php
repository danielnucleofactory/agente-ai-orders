<?php

namespace App\Services;

use App\Helpers\PorthImportHelper;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\PorthCargo;
use App\Models\PorthItinerary;
use App\Models\PorthPhase;
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
                    $this->syncPorthStructures($po->fresh(), $data);

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
     * Actualiza una PurchaseOrder con datos de Porth.
     * Porth sobrescribe los campos de negocio (fechas, puertos, naviera, etc.),
     * salvo date_eta_initial: solo se asigna desde firstEta si aún está vacía en la PO.
     * Los cambios quedan registrados en el historial con el usuario "Next Orders".
     */
    protected function updatePurchaseOrder(PurchaseOrder $po, array $data): void
    {
        if ((bool) $po->tracking_not_applicable) {
            Log::info('porth_import:po_skipped_tracking_not_applicable', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'porth_id' => $po->porth_id,
            ]);

            return;
        }

        // Guardar valores originales antes de actualizar para detectar cambios reales
        $originalValues = [];
        $fieldsToUpdate = [];
        
        // BL fields - siempre sobrescribir si Porth trae valor
        $blFields = $this->helper->getBlFields($data);
        foreach ($blFields as $field => $value) {
            $originalValues[$field] = $po->$field;
            $fieldsToUpdate[$field] = $value;
        }
        
        // Container - siempre sobrescribir si Porth trae valor
        $containerFields = $this->helper->getContainerFields($data);
        foreach ($containerFields as $field => $value) {
            $originalValues[$field] = $po->$field;
            $fieldsToUpdate[$field] = $value;
        }
        
        // Fechas principales desde payload. Las fechas iniciales son baseline:
        // Porth solo las llena si aún no existen.
        $payloadValues = $this->helper->buildPurchaseOrderPayloadValues($data);
        $fieldsMap = $this->helper->getPurchaseOrderFieldsMap();

        foreach ($fieldsMap as $payloadKey => $poField) {
            $value = $payloadValues[$payloadKey] ?? null;
            if ($value === null) {
                continue;
            }
            if ($this->isInitialDateFieldAlreadySet($po, $poField)) {
                continue;
            }
            $originalValues[$poField] = $po->$poField;
            $fieldsToUpdate[$poField] = $value;
        }
        
        // Campos de maestros traducidos - siempre sobrescribir
        $maestrosFields = $this->helper->getMaestrosFields($data, $po);
        foreach ($maestrosFields as $field => $value) {
            if ($value !== null) {
                $originalValues[$field] = $po->$field;
                $fieldsToUpdate[$field] = $value;
            }
        }
        
        // Campos de Porth - SIEMPRE se actualizan (son campos de tracking internos)
        $porthFields = $this->buildPorthFieldsForPO($data);
        foreach ($porthFields as $field => $value) {
            $originalValues[$field] = $po->$field;
        }
        $fieldsToUpdate = array_merge($fieldsToUpdate, $porthFields);

        // Reglas de booleanos según datos de Porth
        $this->applyPorthBooleanRules($po, $data, $fieldsToUpdate, $originalValues);

        if (array_key_exists('date_ata', $fieldsToUpdate)
            && $this->normalizeValueForComparison($po->date_ata, 'date_ata') !== $this->normalizeValueForComparison($fieldsToUpdate['date_ata'], 'date_ata')) {
            $compliance = PurchaseOrder::calculateDeliveryCompliance(
                $fieldsToUpdate['date_eta_initial'] ?? $po->date_eta_initial,
                $fieldsToUpdate['date_ata']
            );

            $originalValues['arrival_status'] = $po->arrival_status;
            $originalValues['delay_days'] = $po->delay_days;
            $fieldsToUpdate['arrival_status'] = $compliance['arrival_status'];
            $fieldsToUpdate['delay_days'] = $compliance['delay_days'];
        }

        if (!empty($fieldsToUpdate)) {
            // Detectar cambios reales comparando valores originales con nuevos
            $actualChanges = [];
            foreach ($fieldsToUpdate as $field => $newValue) {
                $oldValue = $originalValues[$field] ?? null;
                
                // Normalizar valores para comparación (puertos: sin acentos para evitar falsos cambios)
                $normalizedOld = $this->normalizeValueForComparison($oldValue, $field);
                $normalizedNew = $this->normalizeValueForComparison($newValue, $field);
                
                // Solo incluir si realmente cambió
                if ($normalizedOld !== $normalizedNew) {
                    $actualChanges[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }

            $po->fill($fieldsToUpdate);
            $po->save();

            Log::info('porth_import:po_updated', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'fields_updated' => array_keys($fieldsToUpdate),
                'actual_changes' => array_keys($actualChanges),
            ]);

            // Automatización: transiciones Kanban según estado Porth
            $this->applyPorthKanbanTransitions($po->fresh(), $data);

            // Disparar webhook solo si hay cambios reales
            if (!empty($actualChanges)) {
                if (function_exists('dispatch_webhook')) {
                    $this->dispatchWebhookForPorthUpdate($po, $actualChanges);
                } else {
                    Log::warning('porth_import:webhook_function_missing', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'actual_changes_count' => count($actualChanges),
                        'actual_changes_keys' => array_keys($actualChanges),
                    ]);
                }
            }
        }
    }

    protected function syncPorthStructures(PurchaseOrder $po, array $data): void
    {
        $shippingDocumentId = $po->shippingDocuments()->value('shipping_documents.id');

        $cargoPayloads = collect($this->helper->getCargoItems($data))
            ->filter(fn ($cargo) => is_array($cargo))
            ->map(function (array $cargo) use ($po, $shippingDocumentId) {
                return array_merge(
                    $this->helper->buildCargoPayload($cargo),
                    [
                        'purchase_order_id' => $po->id,
                        'shipping_document_id' => $shippingDocumentId,
                    ]
                );
            })
            ->values()
            ->all();

        $phasePayloads = collect($this->helper->getPhaseItems($data))
            ->filter(fn ($phase) => is_array($phase))
            ->map(function (array $phase) use ($po, $shippingDocumentId) {
                return array_merge(
                    $this->helper->buildPhasePayload($phase),
                    [
                        'purchase_order_id' => $po->id,
                        'shipping_document_id' => $shippingDocumentId,
                    ]
                );
            })
            ->values()
            ->all();

        $itineraryPayloads = collect($this->helper->getItineraryItems($data))
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) use ($po, $shippingDocumentId, $data) {
                return array_merge(
                    $this->helper->buildItineraryPayload($item, $data['id'] ?? null),
                    [
                        'purchase_order_id' => $po->id,
                        'shipping_document_id' => $shippingDocumentId,
                    ]
                );
            })
            ->values()
            ->all();

        PorthCargo::where('purchase_order_id', $po->id)->delete();
        PorthPhase::where('purchase_order_id', $po->id)->delete();
        PorthItinerary::where('purchase_order_id', $po->id)->delete();

        if (!empty($cargoPayloads)) {
            $po->porthCargos()->createMany($cargoPayloads);
        }

        if (!empty($phasePayloads)) {
            $po->porthPhases()->createMany($phasePayloads);
        }

        if (!empty($itineraryPayloads)) {
            $po->porthItineraries()->createMany($itineraryPayloads);
        }

        Log::info('porth_import:structures_synced', [
            'purchase_order_id' => $po->id,
            'order_number' => $po->order_number,
            'shipping_document_id' => $shippingDocumentId,
            'cargo_count' => count($cargoPayloads),
            'phase_count' => count($phasePayloads),
            'itinerary_count' => count($itineraryPayloads),
        ]);
    }

    /**
     * Automatización: transiciones de Kanban según phase de Porth (solo phase, no fechas).
     * - Desde Producción/Booking/Consolidador, 40_in_transit mueve a En tránsito.
     * - Desde Producción en adelante, cualquier fase desde puerto destino mueve a Puerto.
     */
    protected function applyPorthKanbanTransitions(PurchaseOrder $po, array $data): void
    {
        if (!config('services.porth.kanban_auto_transition', true)) {
            return;
        }

        $stages = config('services.porth.kanban_stages', []);
        if (empty($stages['produccion']) || empty($stages['booking']) || empty($stages['consolidador']) || empty($stages['en_transito']) || empty($stages['puerto'])) {
            return;
        }

        $board = KanbanBoard::where('company_id', $po->company_id)
            ->where('type', 'po_stages')
            ->first();

        if (!$board) {
            return;
        }

        $currentStatus = $po->kanbanStatus;
        if (!$currentStatus) {
            return;
        }

        $currentName = $currentStatus->name ?? '';
        $phase = strtolower(trim($data['phase'] ?? ''));

        $hasInTransit = in_array($phase, ['in_transit', '40_in_transit'], true);
        $hasReachedDestinationPort = in_array($phase, [
            'at_destination_port',
            '50_at_destination_port',
            'to_final_destination',
            '60_to_final_destination',
            'delivered',
            '70_delivered',
        ], true);

        $automationSourceStages = array_merge(
            $stages['produccion'],
            $stages['booking'],
            $stages['consolidador'],
            $stages['en_transito'],
            $stages['puerto'],
        );

        if (! $this->statusMatchesNames($currentName, $automationSourceStages)) {
            return;
        }

        if ($hasReachedDestinationPort) {
            $this->movePurchaseOrderToKanbanStage($po, $board, $stages['puerto'], $currentName, 'porth_phase_destination_port_or_later');
            return;
        }

        if ($hasInTransit && $this->statusMatchesNames($currentName, array_merge($stages['produccion'], $stages['booking'], $stages['consolidador']))) {
            $this->movePurchaseOrderToKanbanStage($po, $board, $stages['en_transito'], $currentName, 'porth_phase_40_in_transit');
        }
    }

    private function movePurchaseOrderToKanbanStage(PurchaseOrder $po, KanbanBoard $board, array $targetStageNames, string $currentName, string $trigger): void
    {
        $targetStatus = $this->findStatusByName($board, $targetStageNames);

        if (! $targetStatus || (int) $po->kanban_status_id === (int) $targetStatus->id) {
            return;
        }

        $po->update(['kanban_status_id' => $targetStatus->id]);
        Log::info('porth_import:kanban_auto_transition', [
            'purchase_order_id' => $po->id,
            'order_number' => $po->order_number,
            'from' => $currentName,
            'to' => $targetStatus->name,
            'trigger' => $trigger,
        ]);
    }

    /**
     * Aplica reglas de booleanos según datos de Porth:
     * - ATD recibido → etd_initial_validated = true
     * - Puerto recibido desde Porth → port_of_loading_validated = true (sin importar si coincide con la PO)
     */
    protected function applyPorthBooleanRules(PurchaseOrder $po, array $data, array &$fieldsToUpdate, array &$originalValues): void
    {
        // Si llega ATD (Actual Time of Departure), marcar ETD inicial validada
        if (!empty($data['atd'])) {
            $fieldsToUpdate['etd_initial_validated'] = true;
            $originalValues['etd_initial_validated'] = $po->etd_initial_validated;
        }

        // Si recibimos puerto de embarque desde Porth, marcar validado (sin importar si coincide con la PO)
        $hasPortFromPorth = !empty($data['pol']) || !empty($data['polName']);
        if ($hasPortFromPorth) {
            $fieldsToUpdate['port_of_loading_validated'] = true;
            $originalValues['port_of_loading_validated'] = $po->port_of_loading_validated;
        }
    }

    private function statusMatchesNames(string $statusName, array $allowedNames): bool
    {
        $normalized = strtolower(trim($statusName));
        foreach ($allowedNames as $name) {
            if ($normalized === strtolower(trim($name))) {
                return true;
            }
        }
        return false;
    }

    private function findStatusByName(KanbanBoard $board, array $names): ?KanbanStatus
    {
        foreach ($names as $name) {
            $status = $board->statuses()
                ->where('name', $name)
                ->where('is_hidden', false)
                ->first();
            if ($status) {
                return $status;
            }
        }
        return null;
    }

    /**
     * Normaliza un valor para comparación.
     * Para departure_port y arrival_port: quita acentos y unifica mayúsculas
     * para que "MOÍN, COSTA RICA" y "MOIN, COSTA RICA" se consideren iguales.
     */
    private function normalizeValueForComparison($value, ?string $field = null)
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($field && in_array($field, ['departure_port', 'arrival_port'], true)) {
                return $this->normalizePortForComparison($value);
            }
            return $value;
        }

        return $value;
    }

    /**
     * Normaliza nombre de puerto para comparación: quita acentos y unifica mayúsculas.
     * Evita falsos positivos cuando "MOÍN, COSTA RICA" vs "MOIN, COSTA RICA".
     */
    private function normalizePortForComparison(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N',
        ];
        return strtoupper(strtr($value, $map));
    }

    /**
     * Dispara webhook para actualización desde Porth.
     * Solo envía campos editables en el front (no campos porth_* internos).
     * Envía la PO completa en 'data' para que transformPurchaseOrderPayload
     * pueda traducir y filtrar correctamente.
     */
    private function dispatchWebhookForPorthUpdate(PurchaseOrder $po, array $changes): void
    {
        try {
            // Filtrar: solo enviar campos de negocio editables en el front,
            // no campos internos de Porth (porth_*, last_porth_sync_at)
            $businessChanges = [];
            foreach ($changes as $field => $changeData) {
                if (str_starts_with($field, 'porth_') || $field === 'last_porth_sync_at') {
                    continue;
                }
                $businessChanges[$field] = $changeData;
            }

            Log::info('porth_import:webhook_evaluation', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'total_changes' => count($changes),
                'business_changes_count' => count($businessChanges),
                'business_changes_keys' => array_keys($businessChanges),
                'filtered_out_porth_fields' => count($changes) - count($businessChanges),
            ]);

            // Si no hay cambios de negocio, no enviar webhook
            if (empty($businessChanges)) {
                Log::info('porth_import:webhook_skipped_no_business_changes', [
                    'purchase_order_id' => $po->id,
                    'order_number' => $po->order_number,
                ]);
                return;
            }

            // Construir payload con solo los datos actualizados (no toda la PO)
            // Siempre incluir identificadores core + current_timestamp para el endpoint
            $updatedData = [
                'id' => $po->id,
                'order_number' => $po->order_number,
                'trading_company' => $po->trading_company,
                'company_id' => $po->company_id,
                'current_timestamp' => function_exists('format_webhook_date') ? format_webhook_date(now()) : now()->utc()->format('Y-m-d\TH:i:s.v\Z'),
            ];
            foreach ($businessChanges as $field => $changeData) {
                $updatedData[$field] = $changeData['new'];
            }

            dispatch_webhook('purchase_order.updated', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'source' => 'porth_sync',
                'changes' => $businessChanges,
                'data' => $updatedData,
            ]);

            Log::info('porth_import:webhook_dispatched', [
                'purchase_order_id' => $po->id,
                'changes_keys' => array_keys($businessChanges),
            ]);
        } catch (\Throwable $e) {
            Log::error('porth_import:webhook_error', [
                'purchase_order_id' => $po->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // No lanzar la excepción para no interrumpir el flujo principal
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
            'porth_manual_tracking' => isset($data['manualTracking']) ? (bool) $data['manualTracking'] : false,
            'porth_itinerary' => $data['itinerary'] ?? null,
            'last_porth_sync_at' => now(),
            'freight_type' => $this->helper->getTranslatedFreightType($data['freightType'] ?? null),
        ];
    }

    /**
     * Si la PO ya tiene una fecha inicial, Porth no debe volver a escribir el baseline.
     */
    private function isInitialDateFieldAlreadySet(PurchaseOrder $po, string $field): bool
    {
        return in_array($field, ['date_etd_initial', 'date_eta_initial'], true) && filled($po->{$field});
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
