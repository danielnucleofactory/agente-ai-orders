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
        // En Livewire 3, cuando se usa $dispatchTo con @js(), 
        // el array puede venir expandido como argumentos separados
        // Intentamos reconstruir el array desde los argumentos
        
        $activity = null;
        
        // Si el primer argumento es un array, usarlo directamente
        if (!empty($args) && is_array($args[0])) {
            $activity = $args[0];
        }
        // Si el primer argumento es un string JSON, decodificarlo
        elseif (!empty($args) && is_string($args[0])) {
            $decoded = json_decode($args[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $activity = $decoded;
            }
        }
        // Si hay múltiples argumentos, intentar reconstruir el array
        elseif (count($args) > 1) {
            // Los argumentos vienen expandidos: [null, id, user_name, user_role, comment, created_at, status, status_icon, operation, action_type, action_type_label, old_values, new_values, has_changes, attachment]
            try {
                // Normalizar old_values y new_values para asegurar que sean arrays o null
                $oldValuesArg = $args[11] ?? null;
                $newValuesArg = $args[12] ?? null;
                
                // Si vienen como objetos, convertirlos a arrays
                if (is_object($oldValuesArg)) {
                    $oldValuesArg = json_decode(json_encode($oldValuesArg), true);
                }
                if (is_object($newValuesArg)) {
                    $newValuesArg = json_decode(json_encode($newValuesArg), true);
                }
                
                // Si no son arrays, establecer como null
                $oldValuesArg = is_array($oldValuesArg) ? $oldValuesArg : null;
                $newValuesArg = is_array($newValuesArg) ? $newValuesArg : null;
                
                $activity = [
                    'id' => $args[1] ?? null,
                    'user_name' => $args[2] ?? null,
                    'user_role' => $args[3] ?? null,
                    'comment' => $args[4] ?? null,
                    'created_at' => $args[5] ?? null,
                    'status' => $args[6] ?? null,
                    'status_icon' => $args[7] ?? null,
                    'operation' => $args[8] ?? null,
                    'action_type' => $args[9] ?? null,
                    'action_type_label' => $args[10] ?? null,
                    'old_values' => $oldValuesArg,
                    'new_values' => $newValuesArg,
                    'has_changes' => $args[13] ?? null,
                    'attachment' => $args[14] ?? null,
                ];
                
                // Si tenemos el ID del comentario, cargar los datos completos desde la base de datos
                // Esto es más confiable que depender del orden de los argumentos
                if ($activity['id']) {
                    // Intentar cargar desde PurchaseOrderComment
                    try {
                        $comment = \App\Models\PurchaseOrderComment::with('user')->find($activity['id']);
                        if ($comment) {
                            // Sobrescribir con datos del modelo para asegurar consistencia
                            $activity['purchase_order_id'] = $comment->purchase_order_id;
                            $activity['user_name'] = $comment->user->name ?? $activity['user_name'] ?? 'Usuario';
                            $activity['comment'] = $comment->comment ?? $activity['comment'];
                            $activity['created_at'] = $comment->created_at ?? $activity['created_at'];
                            $activity['operation'] = $comment->operacion ?? $activity['operation'] ?? 'Detalle PO';
                            $activity['action_type'] = $comment->action_type ?? $activity['action_type'] ?? 'comment';
                            $activity['action_type_label'] = $comment->getActionTypeLabel();
                            
                            // Cargar old_values y new_values desde el modelo
                            if ($comment->old_values) {
                                $activity['old_values'] = is_array($comment->old_values) ? $comment->old_values : [];
                            }
                            if ($comment->new_values) {
                                $activity['new_values'] = is_array($comment->new_values) ? $comment->new_values : [];
                            }
                        }
                    } catch (\Exception $e) {
                        // Intentar cargar desde ShippingDocumentComment
                        try {
                            $comment = \App\Models\Comment::with('user')->find($activity['id']);
                            if ($comment) {
                                // Sobrescribir con datos del modelo para asegurar consistencia
                                $activity['shipping_document_id'] = $comment->shipping_document_id;
                                $activity['user_name'] = $comment->user->name ?? $activity['user_name'] ?? 'Usuario';
                                $activity['comment'] = $comment->comment ?? $activity['comment'];
                                $activity['created_at'] = $comment->created_at ?? $activity['created_at'];
                                $activity['operation'] = $comment->operacion ?? $activity['operation'] ?? 'Detalle PO';
                                $activity['action_type'] = $comment->action_type ?? $activity['action_type'] ?? 'comment';
                                $activity['action_type_label'] = $comment->getActionTypeLabel();
                                
                                // Cargar old_values y new_values desde el modelo
                                if ($comment->old_values) {
                                    $activity['old_values'] = is_array($comment->old_values) ? $comment->old_values : [];
                                }
                                if ($comment->new_values) {
                                    $activity['new_values'] = is_array($comment->new_values) ? $comment->new_values : [];
                                }
                            }
                        } catch (\Exception $e2) {
                            // Ignorar si no se encuentra
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('ActivityDetailModal: Failed to reconstruct array', ['error' => $e->getMessage(), 'args' => $args]);
                return;
            }
        }

        if (empty($activity) || !is_array($activity)) {
            \Log::warning('ActivityDetailModal: Invalid activity data', ['args' => $args, 'activity' => $activity]);
            return;
        }

        $this->activity = $activity;
        
        // Asegurar que oldValues y newValues sean arrays, incluso si vienen como null o otros tipos
        $oldValues = $activity['old_values'] ?? null;
        $newValues = $activity['new_values'] ?? null;
        
        $this->oldValues = is_array($oldValues) ? $oldValues : [];
        $this->newValues = is_array($newValues) ? $newValues : [];
        
        // Determinar el tipo de modelo para usar los labels correctos
        $this->fieldLabels = $this->determineFieldLabels();
        
        $this->show = true;
        
        \Log::info('ActivityDetailModal: Modal opened', [
            'activity' => $activity, 
            'show' => $this->show,
            'oldValues' => $this->oldValues,
            'newValues' => $this->newValues
        ]);
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

    public function formatValue($value)
    {
        if ($value === null) {
            return '<span class="text-gray-400">N/A</span>';
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
