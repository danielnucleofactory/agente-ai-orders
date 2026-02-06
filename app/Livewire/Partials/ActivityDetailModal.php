<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use App\Helpers\ChangeDescriptionHelper;

class ActivityDetailModal extends Component
{
    public $show = false;
    public $activity = null;
    public $oldValues = [];
    public $newValues = [];
    public $fieldLabels = [];

    protected function getListeners()
    {
        return [
            'openActivityDetail',
        ];
    }

    public function mount()
    {
        // Inicializar propiedades
        $this->show = false;
        $this->activity = null;
        $this->oldValues = [];
        $this->newValues = [];
        $this->fieldLabels = [];
    }

    public function openActivityDetail(...$args)
    {
        // Estrategia: Fuente única de verdad - siempre cargar desde BD usando el ID
        // Simplificar: solo extraer el ID del primer argumento, ignorar el resto
        
        $id = null;
        
        // Intentar extraer el ID del primer argumento
        if (!empty($args)) {
            // Si es un array con 'id'
            if (is_array($args[0]) && isset($args[0]['id'])) {
                $id = $args[0]['id'];
            }
            // Si es un string JSON, decodificarlo y buscar 'id'
            elseif (is_string($args[0])) {
                $decoded = json_decode($args[0], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['id'])) {
                    $id = $decoded['id'];
                }
            }
            // Si el primer argumento es directamente el ID (numérico o string numérico)
            elseif (is_numeric($args[0])) {
                $id = (int) $args[0];
            }
            // Si hay múltiples argumentos, el segundo podría ser el ID (índice 1)
            elseif (count($args) > 1 && is_numeric($args[1])) {
                $id = (int) $args[1];
            }
        }
        
        // Validar ID: si no hay ID, rechazar y mostrar error
        if (empty($id)) {
            \Log::warning('ActivityDetailModal: No ID provided', ['args' => $args]);
            $this->show = false;
            return;
        }
        
        // Cargar TODO desde la BD usando el ID
        $loaded = $this->loadActivityFromDatabase($id);
        
        if (!$loaded) {
            \Log::warning('ActivityDetailModal: Failed to load activity from database', ['id' => $id]);
            $this->show = false;
            return;
        }
        
        // Determinar el tipo de modelo para usar los labels correctos (necesario para validación)
        $this->fieldLabels = $this->determineFieldLabels();
        
        // Validar orden de valores antes de mostrar (después de determinar labels)
        $this->validateValuesOrder();
        
        $this->show = true;
    }

    /**
     * Carga la actividad desde la base de datos usando el ID
     * 
     * @param int $id ID del comentario
     * @return bool true si se cargó correctamente, false en caso contrario
     */
    protected function loadActivityFromDatabase($id): bool
    {
        try {
            // Intentar cargar como PurchaseOrderComment primero
            $comment = \App\Models\PurchaseOrderComment::with('user')->find($id);
            
            if ($comment) {
                \Log::info('ActivityDetailModal: Loading PurchaseOrderComment from DB', ['id' => $id]);
                
                // Asegurar que created_at sea Carbon
                $createdAt = $comment->created_at;
                if (!$createdAt instanceof \Carbon\Carbon) {
                    $createdAt = \Carbon\Carbon::parse($createdAt);
                }
                
                // Cargar old_values y new_values y convertir a arrays de forma segura
                $oldVals = $comment->old_values;
                $newVals = $comment->new_values;
                
                // Convertir objetos stdClass a arrays si es necesario
                if (is_object($oldVals)) {
                    $oldVals = json_decode(json_encode($oldVals), true);
                }
                if (is_object($newVals)) {
                    $newVals = json_decode(json_encode($newVals), true);
                }
                
                // Asegurar que sean arrays válidos
                $oldVals = is_array($oldVals) ? $oldVals : [];
                $newVals = is_array($newVals) ? $newVals : [];
                
                // Construir el array de actividad con todos los campos necesarios
                $this->activity = [
                    'id' => $comment->id,
                    'purchase_order_id' => $comment->purchase_order_id,
                    'user_name' => $comment->user->name ?? 'Usuario desconocido',
                    'user_role' => $comment->getRole() ?? 'Sin rol',
                    'comment' => $comment->comment ?? '',
                    'created_at' => $createdAt,
                    'operation' => $comment->operacion ?? 'Detalle PO',
                    'action_type' => $comment->action_type ?? 'comment',
                    'action_type_label' => $comment->getActionTypeLabel(),
                ];
                
                // Asignar valores a las propiedades del componente
                $this->oldValues = $oldVals;
                $this->newValues = $newVals;
                
                \Log::info('ActivityDetailModal: Successfully loaded PurchaseOrderComment', [
                    'comment_id' => $comment->id,
                    'old_values_count' => count($this->oldValues),
                    'new_values_count' => count($this->newValues),
                    'user_name' => $this->activity['user_name'],
                    'created_at' => $createdAt->toDateTimeString(),
                ]);
                
                return true;
            }
            
            // Si no es PurchaseOrderComment, intentar como Comment (ShippingDocument)
            $comment = \App\Models\Comment::with('user')->find($id);
            
            if ($comment) {
                \Log::info('ActivityDetailModal: Loading Comment (ShippingDocument) from DB', ['id' => $id]);
                
                // Asegurar que created_at sea Carbon
                $createdAt = $comment->created_at;
                if (!$createdAt instanceof \Carbon\Carbon) {
                    $createdAt = \Carbon\Carbon::parse($createdAt);
                }
                
                // Cargar old_values y new_values y convertir a arrays de forma segura
                $oldVals = $comment->old_values;
                $newVals = $comment->new_values;
                
                // Convertir objetos stdClass a arrays si es necesario
                if (is_object($oldVals)) {
                    $oldVals = json_decode(json_encode($oldVals), true);
                }
                if (is_object($newVals)) {
                    $newVals = json_decode(json_encode($newVals), true);
                }
                
                // Asegurar que sean arrays válidos
                $oldVals = is_array($oldVals) ? $oldVals : [];
                $newVals = is_array($newVals) ? $newVals : [];
                
                // Construir el array de actividad con todos los campos necesarios
                $this->activity = [
                    'id' => $comment->id,
                    'shipping_document_id' => $comment->shipping_document_id,
                    'user_name' => $comment->user->name ?? 'Usuario desconocido',
                    'user_role' => $comment->getRole() ?? 'Sin rol',
                    'comment' => $comment->comment ?? '',
                    'created_at' => $createdAt,
                    'operation' => $comment->operacion ?? 'Detalle SD',
                    'action_type' => $comment->action_type ?? 'comment',
                    'action_type_label' => $comment->getActionTypeLabel(),
                ];
                
                // Asignar valores a las propiedades del componente
                $this->oldValues = $oldVals;
                $this->newValues = $newVals;
                
                \Log::info('ActivityDetailModal: Successfully loaded Comment (ShippingDocument)', [
                    'comment_id' => $comment->id,
                    'old_values_count' => count($this->oldValues),
                    'new_values_count' => count($this->newValues),
                    'user_name' => $this->activity['user_name'],
                    'created_at' => $createdAt->toDateTimeString(),
                ]);
                
                return true;
            }
            
            // Si no se encontró ningún comentario
            \Log::warning('ActivityDetailModal: Comment not found in database', ['id' => $id]);
            return false;
            
        } catch (\Exception $e) {
            \Log::error('ActivityDetailModal: Error loading activity from database', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    
    /**
     * Valida el orden de old_values y new_values comparando con la descripción del comentario
     * Si detecta un intercambio, lo corrige automáticamente
     */
    protected function validateValuesOrder(): void
    {
        // Solo validar si hay valores para comparar
        if (empty($this->oldValues) && empty($this->newValues)) {
            return;
        }
        
        // Solo validar si tenemos la descripción del comentario
        $description = $this->activity['comment'] ?? '';
        if (empty($description)) {
            return;
        }
        
        // Obtener el primer campo cambiado de la descripción para comparar
        // La descripción tiene formato: "Campo: valor_anterior → valor_nuevo"
        $descriptionParts = explode(':', $description);
        if (count($descriptionParts) < 2) {
            return;
        }
        
        $fieldPart = trim($descriptionParts[0]);
        $valuePart = trim($descriptionParts[1]);
        
        // Buscar el campo en los labels para obtener el nombre técnico
        $fieldName = null;
        foreach ($this->fieldLabels as $techField => $label) {
            if ($label === $fieldPart) {
                $fieldName = $techField;
                break;
            }
        }
        
        // Si no encontramos el campo, intentar usar el primer campo disponible
        if (!$fieldName && !empty($this->oldValues)) {
            $fieldName = array_key_first($this->oldValues);
        }
        if (!$fieldName && !empty($this->newValues)) {
            $fieldName = array_key_first($this->newValues);
        }
        
        if (!$fieldName) {
            return;
        }
        
        // Obtener valores actuales
        $currentOldValue = $this->oldValues[$fieldName] ?? null;
        $currentNewValue = $this->newValues[$fieldName] ?? null;
        
        // Parsear la descripción para extraer valores esperados
        // Formato: "Campo: valor_anterior → valor_nuevo"
        if (strpos($valuePart, '→') !== false) {
            $valueParts = explode('→', $valuePart);
            $expectedOldValue = trim($valueParts[0] ?? '');
            $expectedNewValue = trim($valueParts[1] ?? '');
            
            // Normalizar valores para comparación (convertir a string y trim)
            $normalizedCurrentOld = $this->normalizeValueForComparison($currentOldValue);
            $normalizedCurrentNew = $this->normalizeValueForComparison($currentNewValue);
            $normalizedExpectedOld = trim((string) $expectedOldValue);
            $normalizedExpectedNew = trim((string) $expectedNewValue);
            
            // Detectar si están intercambiados
            // Si el valor actual en oldValues coincide con el esperado en newValues
            // y el valor actual en newValues coincide con el esperado en oldValues
            $oldMatchesNew = $normalizedCurrentOld === $normalizedExpectedNew;
            $newMatchesOld = $normalizedCurrentNew === $normalizedExpectedOld;
            
            if ($oldMatchesNew && $newMatchesOld) {
                // Los valores están intercambiados, corregirlos
                \Log::warning('ActivityDetailModal: Detected swapped old/new values, correcting', [
                    'field' => $fieldName,
                    'current_old' => $currentOldValue,
                    'current_new' => $currentNewValue,
                    'expected_old' => $expectedOldValue,
                    'expected_new' => $expectedNewValue,
                ]);
                
                // Intercambiar los valores
                $temp = $this->oldValues;
                $this->oldValues = $this->newValues;
                $this->newValues = $temp;
                
                \Log::info('ActivityDetailModal: Corrected swapped values', [
                    'field' => $fieldName,
                    'corrected_old' => $this->oldValues[$fieldName] ?? null,
                    'corrected_new' => $this->newValues[$fieldName] ?? null,
                ]);
            }
        }
    }
    
    /**
     * Normaliza un valor para comparación (convierte a string y limpia)
     */
    protected function normalizeValueForComparison($value): string
    {
        if ($value === null) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }
        
        return trim((string) $value);
    }

    public function close()
    {
        $this->show = false;
        $this->activity = null;
        $this->oldValues = [];
        $this->newValues = [];
        $this->fieldLabels = [];
    }

    protected function determineFieldLabels()
    {
        // Asegurar que oldValues y newValues sean arrays
        $oldValues = is_array($this->oldValues) ? $this->oldValues : [];
        $newValues = is_array($this->newValues) ? $this->newValues : [];
        
        $allFields = array_merge(
            array_keys($oldValues),
            array_keys($newValues)
        );
        
        $labels = [];
        $isPurchaseOrder = isset($this->activity['purchase_order_id']);
        
        foreach ($allFields as $field) {
            if ($isPurchaseOrder) {
                // Usar labels de Purchase Order
                $label = ChangeDescriptionHelper::getPurchaseOrderFieldLabel($field);
            } else {
                // Usar labels de Shipping Document
                $label = ChangeDescriptionHelper::getShippingDocumentFieldLabel($field);
                // Si no se encontró, intentar con Purchase Order como fallback
                if ($label === $field) {
                    $label = ChangeDescriptionHelper::getPurchaseOrderFieldLabel($field);
                }
            }
            $labels[$field] = $label;
        }
        
        return $labels;
    }

    public function getFieldLabel($field)
    {
        return $this->fieldLabels[$field] ?? $field;
    }

    public function formatValue($value, $field = null)
    {
        // Si es null, mostrar como "(sin valor)"
        if ($value === null) {
            return '<span class="text-gray-400 italic">(sin valor)</span>';
        }
        
        // Si es una cadena vacía, mostrar como "(vacío)"
        if (is_string($value) && trim($value) === '') {
            return '<span class="text-gray-400 italic">(vacío)</span>';
        }
        
        // Si es el string "N/A" literal, mantenerlo pero con estilo
        if (is_string($value) && strtoupper(trim($value)) === 'N/A') {
            return '<span class="text-gray-400">N/A</span>';
        }
        
        // Formatear kanban_status_id usando el slug en lugar del ID
        if ($field === 'kanban_status_id' && is_numeric($value)) {
            $status = \App\Models\KanbanStatus::find($value);
            if ($status) {
                $displayValue = $status->slug ?? $status->name ?? "ID: {$value}";
                return htmlspecialchars((string) $displayValue);
            }
        }
        
        if (is_array($value)) {
            return '<pre class="text-sm">' . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
        }
        
        return htmlspecialchars((string) $value);
    }

    public function render()
    {
        return view('livewire.partials.activity-detail-modal');
    }
}
