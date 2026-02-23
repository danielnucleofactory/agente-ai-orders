<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use App\Helpers\ChangeDescriptionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderObserver
{
    /** Email del usuario sistema (sync Porth) para registrar sus actualizaciones en el histórico */
    protected const PORTH_SYSTEM_USER_EMAIL = 'apps@raga-x.ai';

    /**
     * Campos críticos que se deben trackear para auditoría
     */
    protected array $trackedFields = [
        // Fechas
        'order_date',
        'date_required_in_destination',
        'date_planned_pickup',
        'date_actual_pickup',
        'date_estimated_hub_arrival',
        'date_actual_hub_arrival',
        'date_etd',
        'date_atd',
        'date_eta',
        'date_ata',
        'date_consolidation',
        'release_date',
        'date_booking_request',
        'date_booking_authorized',
        'date_theorical_load',
        'date_variable_date',
        'carga_lista_validada',
        'date_received',
        'date_eta_updated',
        'date_etd_updated',
        'date_etd_initial',
        'date_eta_initial',
        'inspection_date',
        'vgm_cut_date',
        'balance_payment_date',
        'local_charges_payment_date',
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        'receipt_note_date',
        'estimated_dc_availability_date',
        'date_invoice_received',
        'date_vendor_document_received',
        'forwader_date',
        'dif_load_date',
        'emision_date_po',
        'update_date_po',

        // Montos
        'net_total',
        'total',
        'additional_cost',
        'insurance_cost',
        'ground_transport_cost_1',
        'ground_transport_cost_2',
        'cost_nationalization',
        'cost_ofr_estimated',
        'cost_ofr_real',
        'estimated_pallet_cost',
        'real_cost_estimated_po',
        'real_cost_real_po',
        'other_costs',
        'other_expenses',
        'savings_ofr_fcl',
        'saving_pickup',
        'saving_executed',
        'saving_not_executed',
        'Invoice_amount',
        'freight_amount',
        'total_amount',

        // Estados
        'status',
        'kanban_status_id',

        // Campos comerciales
        'currency',
        'incoterms',
        'logistics_incoterm',
        'price_incoterm',
        'payment_terms',
        'order_place',

        // Campos de transporte/logística
        'departure_port',
        'arrival_port',
        'shipping_line',
        'container_type',
        'container_number',
        'tariff_type',
        'mode',
        'route_label',
        'forwarder_name',
        'tracking_id',
        'mbl_number',
        'factory_proforma_number',
        'bill_of_lading',
        'consolidator_name',

        // Otros campos importantes
        'reason',
        'category',
        'notes',
    ];

    /**
     * Handle the PurchaseOrder "updating" event.
     */
    public function updating(PurchaseOrder $purchaseOrder): void
    {
        try {
            // Solo registrar cambios si hay un usuario autenticado
            if (!Auth::check()) {
                return;
            }

            // Obtener campos modificados
            $changes = $purchaseOrder->getDirty();

            // Filtrar solo campos trackeados
            $trackedChanges = array_intersect_key($changes, array_flip($this->trackedFields));

            // Si no hay cambios en campos trackeados pero el usuario es el sync de Porth,
            // registrar entrada solo si hay cambios de negocio (excluir last_porth_sync_at y porth_*)
            if (empty($trackedChanges)) {
                if ($this->isPorthSyncUser() && !empty($changes)) {
                    $meaningfulChanges = array_filter(array_keys($changes), function ($field) {
                        return $field !== 'last_porth_sync_at' && !str_starts_with($field, 'porth_');
                    });
                    if (!empty($meaningfulChanges)) {
                        $filteredChanges = array_intersect_key($changes, array_flip($meaningfulChanges));
                        $this->registerPorthSyncAudit($purchaseOrder, $filteredChanges);
                    }
                }
                return;
            }

            // Obtener valores originales y filtrar cambios que realmente son diferentes
            $oldValues = [];
            $realChanges = [];

            foreach ($trackedChanges as $field => $newValue) {
                try {
                    $oldValue = $purchaseOrder->getOriginal($field);

                    // Normalizar valores para comparación
                    $normalizedOld = $this->normalizeValue($oldValue);
                    $normalizedNew = $this->normalizeValue($newValue);

                    // Solo registrar si realmente cambió
                    if ($normalizedOld !== $normalizedNew) {
                        $oldValues[$field] = $oldValue;
                        $realChanges[$field] = $newValue;
                    }
                } catch (\Exception $e) {
                    // Si hay un error normalizando un campo, continuar con el siguiente
                    Log::warning("Error normalizando campo {$field} en PurchaseOrderObserver: " . $e->getMessage());
                    continue;
                }
            }

            // Si no hay cambios reales, no registrar nada
            if (empty($realChanges)) {
                return;
            }

            $trackedChanges = $realChanges;

            // Determinar tipo de acción
            $isStatusChange = isset($trackedChanges['status']) || isset($trackedChanges['kanban_status_id']);
            $actionType = $isStatusChange ? 'status_change' : 'field_change';

            // Generar descripción
            try {
                $description = ChangeDescriptionHelper::generateForPurchaseOrder(
                    $purchaseOrder,
                    $oldValues,
                    $trackedChanges
                );
            } catch (\Exception $e) {
                // Si falla la generación de descripción, usar una descripción básica
                Log::warning("Error generando descripción en PurchaseOrderObserver: " . $e->getMessage());
                $description = "Cambios en " . count($trackedChanges) . " campo(s)";
            }

            // Capturar datos de Auth AHORA, antes del afterCommit
            // (en contexto de PorthSync, Auth::logout() ocurre antes de que afterCommit se ejecute)
            $currentUserId = Auth::id();

            // Crear comentario después del commit de la transacción
            DB::afterCommit(function () use ($purchaseOrder, $actionType, $oldValues, $trackedChanges, $description, $isStatusChange, $currentUserId) {
                try {
                    PurchaseOrderComment::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'user_id' => $currentUserId,
                        'comment' => $description,
                        'action_type' => $actionType,
                        'old_values' => $oldValues,
                        'new_values' => $trackedChanges,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    // Dispatch webhook event if status changed
                    // NOTE: We do NOT dispatch purchase_order.updated here to avoid duplicates
                    // The controllers/Livewire components already dispatch purchase_order.updated
                    // This observer only handles audit comments and status_changed events
                    if ($isStatusChange && function_exists('dispatch_webhook')) {
                        $purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        dispatch_webhook('purchase_order.status_changed', [
                            'purchase_order_id' => $purchaseOrder->id,
                            'order_number' => $purchaseOrder->order_number,
                            'old_status' => $oldValues['status'] ?? $oldValues['kanban_status_id'] ?? null,
                            'new_status' => $trackedChanges['status'] ?? $trackedChanges['kanban_status_id'] ?? null,
                            'data' => $purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user'])->toArray(), // Incluye todos los campos (143 campos)
                        ]);
                    }
                    // NOTE: purchase_order.updated is NOT dispatched here to avoid duplicate webhooks
                    // Controllers and Livewire components handle purchase_order.updated events

                    // Push cambios relevantes a Porth (solo container_number y shipping_line)
                    // Solo si la PO tiene porth_id y los campos relevantes cambiaron
                    $porthRelevantFields = ['container_number', 'shipping_line'];
                    $porthChanges = array_intersect_key($trackedChanges, array_flip($porthRelevantFields));
                    if (!empty($porthChanges) && !empty($purchaseOrder->porth_id)) {
                        try {
                            $porthApi = app(\App\Services\PorthApiService::class);
                            $porthApi->pushChangesToPorth($purchaseOrder, $porthChanges);
                        } catch (\Throwable $e) {
                            Log::error('observer:porth_push_error', [
                                'purchase_order_id' => $purchaseOrder->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Si falla la creación del comentario, solo loguear el error
                    // No interrumpir el flujo principal
                    Log::error("Error creando comentario de auditoría en PurchaseOrderObserver: " . $e->getMessage(), [
                        'purchase_order_id' => $purchaseOrder->id,
                        'error' => $e->getTraceAsString()
                    ]);
                }
            });
        } catch (\Exception $e) {
            // Si hay cualquier error en el Observer, solo loguearlo
            // No interrumpir la actualización de la Purchase Order
            Log::error("Error en PurchaseOrderObserver::updating: " . $e->getMessage(), [
                'purchase_order_id' => $purchaseOrder->id ?? null,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Indica si el usuario actual es el de sincronización Porth (sistema).
     */
    protected function isPorthSyncUser(): bool
    {
        $user = Auth::user();
        return $user && $user->email === self::PORTH_SYSTEM_USER_EMAIL;
    }

    /**
     * Registra en el histórico una actualización desde Porth cuando solo cambiaron campos no trackeados
     * (porth_*, last_porth_sync_at, etc.), para que el usuario vea que hubo sync.
     */
    protected function registerPorthSyncAudit(PurchaseOrder $purchaseOrder, array $changes): void
    {
        // Guardar valores anteriores y nuevos para que se vean en el modal de detalle
        $oldValues = [];
        $newValues = [];
        foreach ($changes as $field => $newValue) {
            $original = $purchaseOrder->getOriginal($field);
            // Serializar Carbon/DateTime a string
            $oldValues[$field] = ($original instanceof \DateTimeInterface) ? $original->format('Y-m-d H:i:s') : $original;
            $newValues[$field] = ($newValue instanceof \DateTimeInterface) ? $newValue->format('Y-m-d H:i:s') : $newValue;
        }

        $changedKeys = array_keys($changes);
        $description = 'Actualización desde Porth (tracking). Campos: ' . implode(', ', $changedKeys);

        // Capturar Auth::id() AHORA, antes de que DB::afterCommit se ejecute
        // (PorthImportService hace Auth::logout() en finally, que ocurre antes del afterCommit
        // cuando save() está dentro de DB::transaction())
        $userId = Auth::id();
        $poId = $purchaseOrder->id;

        DB::afterCommit(function () use ($poId, $description, $oldValues, $newValues, $userId) {
            try {
                PurchaseOrderComment::create([
                    'purchase_order_id' => $poId,
                    'user_id' => $userId,
                    'comment' => $description,
                    'action_type' => 'porth_sync',
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'ip_address' => request()->ip(),
                    'user_agent' => 'PorthSync',
                ]);
            } catch (\Exception $e) {
                Log::error('Error creando comentario de auditoría Porth en PurchaseOrderObserver: ' . $e->getMessage(), [
                    'purchase_order_id' => $poId,
                    'error' => $e->getTraceAsString(),
                ]);
            }
        });
    }

    /**
     * Normaliza un valor para comparación
     */
    protected function normalizeValue($value)
    {
        // Si es null, convertir a string vacío
        if ($value === null) {
            return '';
        }

        // Si es una fecha/Carbon, convertir a string ISO
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // Si es string numérico o numérico, convertir a float para comparación
        // Esto maneja casos como "0.00" vs 0 vs "0"
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            // Si después de trim es numérico, convertir a float
            if (is_numeric($trimmed)) {
                return (float) $trimmed;
            }
            // Si no, devolver el string trimmed
            return $trimmed;
        }

        // Si es boolean, convertir a int
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        // Si es array, convertir a JSON string para comparación
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // Para otros tipos, convertir a string
        return (string) $value;
    }
}

