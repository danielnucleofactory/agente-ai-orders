<?php

namespace App\Observers;

use App\Models\ShippingDocument;
use App\Models\Comment;
use App\Helpers\ChangeDescriptionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShippingDocumentObserver
{
    /**
     * Campos críticos que se deben trackear para auditoría
     */
    protected array $trackedFields = [
        // Fechas
        'creation_date',
        'estimated_departure_date',
        'estimated_arrival_date',
        'actual_departure_date',
        'actual_arrival_date',
        'release_date',
        'date_theorical_load',
        'date_variable_date',
        'date_booking_request',
        'date_booking_authorized',
        'date_etd_updated',
        'date_eta_updated',
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        
        // Montos
        'Invoice_amount',
        'total_weight_kg',
        
        // Estados
        'status',
        'kanban_status_id',
        'arrival_status',
        
        // Otros campos importantes
        'document_number',
        'tracking_id',
        'container_number',
        'container_type',
        'mbl_number',
        'hbl_number',
        'booking_code',
        'shipping_line',
        'arrival_port',
        'departure_port',
        'forwarder_name',
        'service_provider',
    ];

    /**
     * Handle the ShippingDocument "updating" event.
     */
    public function updating(ShippingDocument $shippingDocument): void
    {
        try {
            // Solo registrar cambios si hay un usuario autenticado
            if (!Auth::check()) {
                return;
            }

            // Obtener campos modificados
            $changes = $shippingDocument->getDirty();
            
            // Filtrar solo campos trackeados
            $trackedChanges = array_intersect_key($changes, array_flip($this->trackedFields));
            
            if (empty($trackedChanges)) {
                return;
            }

            // Obtener valores originales y filtrar cambios que realmente son diferentes
            $oldValues = [];
            $realChanges = [];
            
            foreach ($trackedChanges as $field => $newValue) {
                try {
                    $oldValue = $shippingDocument->getOriginal($field);
                    
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
                    Log::warning("Error normalizando campo {$field} en ShippingDocumentObserver: " . $e->getMessage());
                    continue;
                }
            }
            
            // Si no hay cambios reales, no registrar nada
            if (empty($realChanges)) {
                return;
            }
            
            $trackedChanges = $realChanges;

            // Determinar tipo de acción
            $isStatusChange = isset($trackedChanges['status']) || isset($trackedChanges['kanban_status_id']) || isset($trackedChanges['arrival_status']);
            $actionType = $isStatusChange ? 'status_change' : 'field_change';

            // Generar descripción
            try {
                $description = ChangeDescriptionHelper::generateForShippingDocument(
                    $shippingDocument,
                    $oldValues,
                    $trackedChanges
                );
            } catch (\Exception $e) {
                // Si falla la generación de descripción, usar una descripción básica
                Log::warning("Error generando descripción en ShippingDocumentObserver: " . $e->getMessage());
                $description = "Cambios en " . count($trackedChanges) . " campo(s)";
            }

            // Crear comentario después del commit de la transacción
            DB::afterCommit(function () use ($shippingDocument, $actionType, $oldValues, $trackedChanges, $description) {
                try {
                    Comment::create([
                        'shipping_document_id' => $shippingDocument->id,
                        'user_id' => Auth::id(),
                        'comment' => $description,
                        'action_type' => $actionType,
                        'old_values' => $oldValues,
                        'new_values' => $trackedChanges,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                } catch (\Exception $e) {
                    // Si falla la creación del comentario, solo loguear el error
                    // No interrumpir el flujo principal
                    Log::error("Error creando comentario de auditoría en ShippingDocumentObserver: " . $e->getMessage(), [
                        'shipping_document_id' => $shippingDocument->id,
                        'error' => $e->getTraceAsString()
                    ]);
                }
            });
        } catch (\Exception $e) {
            // Si hay cualquier error en el Observer, solo loguearlo
            // No interrumpir la actualización del Shipping Document
            Log::error("Error en ShippingDocumentObserver::updating: " . $e->getMessage(), [
                'shipping_document_id' => $shippingDocument->id ?? null,
                'error' => $e->getTraceAsString()
            ]);
        }
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

