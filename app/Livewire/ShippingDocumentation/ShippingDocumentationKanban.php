<?php

namespace App\Livewire\ShippingDocumentation;

use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\ShippingDocument;
use App\Models\PurchaseOrder;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ShippingDocumentComment;
use Livewire\WithFileUploads;
use App\Services\TrackingService;

class ShippingDocumentationKanban extends Component
{
    use WithFileUploads;

    public $boardId;
    public $board;
    public $columns = [];
    public $documents = [];
    public $documentsByColumn = [];
    public $currentDocumentId;
    public $newColumnId;
    public $currentDocument = null;
    public $comment;
    // Variables para el HUB
    public $actual_hub_id;
    // Añadir estas propiedades para los campos del formulario
    public $tracking_id;
    public $booking_code;
    public $container_number;
    public $mbl_number;
    public $hbl_number;
    public $release_date;
    public $instruction_date;
    public $comentario_documento;
    public $attachment = null;
    public $originalColumnId;
    public $isValidating = false;

    // Filtros activos
    public $activeFilters = [];
    public $hasActiveFilters = false;

    // Listeners
    protected $listeners = [
        'refreshKanban' => 'loadData',
        'shippingDocumentFiltersChanged' => 'applyFilters',
        'setIsValidating' => 'setIsValidating'
    ];

    // Reglas de validación
    protected function rules()
    {
        return [
            'tracking_id' => 'nullable|string|max:50',
            'mbl_number' => 'nullable|string|max:50',
            'booking_code' => 'nullable|string|max:50',
            'container_number' => 'nullable|string|max:50',
            'comment' => 'nullable|string',
            'attachment' => 'nullable|file|max:5120', // 5MB max
            'release_date' => 'nullable|date',
            'instruction_date' => 'nullable|date',
        ];
    }

    // Mensajes de validación personalizados
    protected function messages()
    {
        return [
            'tracking_id.max' => 'El ID de tracking no debe exceder los 50 caracteres',
            'mbl_number.max' => 'El Master BL no debe exceder los 50 caracteres',
            'booking_code.max' => 'El código de booking no debe exceder los 50 caracteres',
            'container_number.max' => 'El número de contenedor no debe exceder los 50 caracteres',
            'attachment.max' => 'El archivo no debe exceder los 5MB',
        ];
    }

    public function mount($boardId = null)
    {
        // If no board ID is provided, try to get the shipping documentation board
        if (!$boardId) {
            $companyId = auth()->user()->company_id ?? null;
            $this->board = KanbanBoard::where('company_id', $companyId)
                ->where('type', 'shipping_documentation')
                ->where('is_active', true)
                ->first();

            if ($this->board) {
                $this->boardId = $this->board->id;
            }
        } else {
            $this->boardId = $boardId;
            $this->board = KanbanBoard::findOrFail($boardId);
        }

        $this->loadData();
    }

    public function loadData()
    {
        $this->loadColumns();
        $this->loadDocuments();
        $this->organizeDocumentsByColumn();
    }

    protected function loadColumns()
    {
        if (!$this->board) {
            $this->columns = [];
            return;
        }

        $this->columns = $this->board->statuses()
            ->orderBy('position')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'position' => $status->position,
                ];
            })
            ->toArray();

        \Log::info("Available columns: " . json_encode(collect($this->columns)->pluck('name', 'id')));
    }

    protected function loadDocuments()
    {
        if (!$this->board) {
            $this->documents = [];
            return;
        }

        // Get shipping documents with their associated purchase orders
        $shippingDocs = ShippingDocument::with(['purchaseOrders', 'company'])
            ->get();

        $this->documents = $shippingDocs->map(function ($doc) {
            // Intentar extraer kanban_status_id de las notas
            $kanbanStatusId = null;

            if ($doc->notes && strpos($doc->notes, 'KANBAN_STATUS_ID:') !== false) {
                preg_match('/KANBAN_STATUS_ID:(\d+)/', $doc->notes, $matches);
                if (isset($matches[1])) {
                    $kanbanStatusId = $matches[1];
                    \Log::info("Extracted kanban_status_id from notes: $kanbanStatusId for document ID: {$doc->id}");
                }
            }

            // Si no se encontró en las notas, mapear según el estado
            if (!$kanbanStatusId) {
                // Map the status from shipping document to kanban status
                $kanbanStatus = null;

                switch ($doc->status) {
                    case 'draft':
                        $kanbanStatus = $this->board->statuses()->where('name', 'like', '%gestion documental%')
                            ->first();
                        break;
                    case 'pending':
                        $kanbanStatus = $this->board->statuses()->where('name', 'like', '%coordinación de salida%')
                            ->first();
                        break;
                    case 'approved':
                        $kanbanStatus = $this->board->statuses()->where('name', 'like', '%notificación de arribo%')
                            ->first();
                        break;
                    case 'in_transit':
                        $kanbanStatus = $this->board->statuses()->where('name', 'like', '%tránsito%')
                            ->first();
                        break;
                    case 'delivered':
                        $kanbanStatus = $this->board->statuses()->where('name', 'like', '%liberación%')
                            ->first();
                        break;
                    default:
                        $kanbanStatus = $this->board->defaultStatus();
                }

                $kanbanStatusId = $kanbanStatus ? $kanbanStatus->id : null;

                // Si no se encontró ningún estado de Kanban, usar el predeterminado
                if (!$kanbanStatusId) {
                    $defaultStatus = $this->board->defaultStatus();
                    $kanbanStatusId = $defaultStatus ? $defaultStatus->id : null;
                }
            }

            return [
                'id' => 'DOC-' . $doc->id,
                'document_id' => $doc->id,
                'document_number' => $doc->document_number,
                'po_count' => $doc->purchaseOrders->count(),
                'company' => $doc->company->name ?? 'N/A',
                'weight_kg' => $doc->total_weight_kg ?? 0,
                'creation_date' => $doc->creation_date ? $doc->creation_date->format('d/m/Y') : 'N/A',
                'estimated_departure_date' => $doc->estimated_departure_date ? $doc->estimated_departure_date->format('d/m/Y') : 'N/A',
                'estimated_arrival_date' => $doc->estimated_arrival_date ? $doc->estimated_arrival_date->format('d/m/Y') : 'N/A',
                'hub_location' => $doc->hub_location ?? 'N/A',
                'status' => $doc->status,
                'kanban_status_id' => $kanbanStatusId,
                'purchase_orders' => $doc->purchaseOrders->map(function ($po) {
                    return [
                        'id' => $po->id,
                        'order_number' => $po->order_number,
                        'currency' => $po->currency,
                        'incoterms' => $po->incoterms,
                        'planned_hub_id' => $po->planned_hub_id,
                        'actual_hub_id' => $po->actual_hub_id,
                        'material_type' => $po->material_type,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    protected function organizeDocumentsByColumn()
    {
        \Log::info("organizeDocumentsByColumn: Starting method");
        $this->documentsByColumn = [];

        // Initialize empty arrays for each column
        foreach ($this->columns as $column) {
            $this->documentsByColumn[$column['id']] = [];
        }

        // Filter documents based on active filters
        $filteredDocuments = $this->filterDocuments();

        // Organize documents by column
        foreach ($filteredDocuments as $document) {
            $statusId = $document['kanban_status_id'];
            \Log::info("organizeDocumentsByColumn: Document {$document['id']} has kanban_status_id: $statusId");

            // If the document has a valid status ID and the status exists in our columns
            if ($statusId && isset($this->documentsByColumn[$statusId])) {
                $this->documentsByColumn[$statusId][] = $document;
                \Log::info("organizeDocumentsByColumn: Added document to column $statusId");
            } else {
                // If the document doesn't have a valid status, put it in the first column
                if (!empty($this->columns)) {
                    $firstColumnId = $this->columns[0]['id'];
                    $this->documentsByColumn[$firstColumnId][] = $document;
                    \Log::info("organizeDocumentsByColumn: Document has invalid status, added to first column ($firstColumnId)");
                }
            }
        }

        // Log column counts for debugging
        foreach ($this->columns as $column) {
            $count = count($this->documentsByColumn[$column['id']]);
            \Log::info("organizeDocumentsByColumn: Column {$column['id']} ({$column['name']}) has $count documents");
        }
    }

    /**
     * Filter documents based on active filters
     */
    protected function filterDocuments()
    {
        if (empty($this->activeFilters)) {
            return $this->documents;
        }

        \Log::info("Applying filters: " . json_encode($this->activeFilters));

        return array_filter($this->documents, function ($document) {
            // Obtenemos todas las órdenes de compra asociadas para verificar si alguna coincide con los filtros
            $purchaseOrders = $document['purchase_orders'] ?? [];

            if (empty($purchaseOrders)) {
                return false;
            }

            // Para cada orden de compra, verificar si cumple con los filtros
            foreach ($purchaseOrders as $po) {
                $matchesFilters = true;

                // Filtrar por moneda
                if (isset($this->activeFilters['currency']) && $po['currency'] != $this->activeFilters['currency']) {
                    $matchesFilters = false;
                    continue;
                }

                // Filtrar por incoterms
                if (isset($this->activeFilters['incoterms']) && $po['incoterms'] != $this->activeFilters['incoterms']) {
                    $matchesFilters = false;
                    continue;
                }

                // Filtrar por hub planificado
                if (isset($this->activeFilters['planned_hub_id']) && $po['planned_hub_id'] != $this->activeFilters['planned_hub_id']) {
                    $matchesFilters = false;
                    continue;
                }

                // Filtrar por hub real
                if (isset($this->activeFilters['actual_hub_id']) && $po['actual_hub_id'] != $this->activeFilters['actual_hub_id']) {
                    $matchesFilters = false;
                    continue;
                }

                // Filtrar por tipo de material
                if (isset($this->activeFilters['material_type'])) {
                    $materialType = $this->activeFilters['material_type'];

                    // Verificar si el material_type es un array o un string
                    if (is_array($po['material_type'])) {
                        if (!in_array($materialType, $po['material_type'])) {
                            $matchesFilters = false;
                            continue;
                        }
                    } else {
                        if ($po['material_type'] != $materialType) {
                            $matchesFilters = false;
                            continue;
                        }
                    }
                }

                // Si una orden de compra coincide con todos los filtros, incluir el documento
                if ($matchesFilters) {
                    return true;
                }
            }

            // Si ninguna orden de compra coincide con todos los filtros, no incluir el documento
            return false;
        });
    }

    /**
     * Apply filters received from the filter component
     */
    public function applyFilters($filters = [])
    {
        \Log::info("applyFilters called with: " . json_encode($filters));

        $this->activeFilters = $filters;
        $this->hasActiveFilters = !empty($filters);

        // Reorganizar documentos por columna con los nuevos filtros aplicados
        $this->organizeDocumentsByColumn();
    }

    public function moveDocument($documentId, $newColumnId)
    {
        // Log detallado
        \Log::info("Moving document $documentId to column $newColumnId");

        // Validar los datos de entrada
        if (empty($documentId) || empty($newColumnId)) {
            \Log::error("Invalid parameters: documentId=$documentId, newColumnId=$newColumnId");
            return;
        }

        // Extract the document ID from the document ID string
        $shippingDocId = str_replace('DOC-', '', $documentId);

        // Find the shipping document
        $shippingDoc = ShippingDocument::find($shippingDocId);

        if (!$shippingDoc) {
            \Log::error("Shipping document not found: $shippingDocId");
            return;
        }

        // Find the kanban status
        $kanbanStatus = KanbanStatus::find($newColumnId);

        if (!$kanbanStatus) {
            \Log::error("Kanban status not found: $newColumnId");
            return;
        }

        // Log the actual status name for debugging
        \Log::info("Kanban status name: " . $kanbanStatus->name);

        // Map the kanban status back to a shipping document status
        $newStatus = 'draft'; // Default
        $statusName = strtolower($kanbanStatus->name);

        if (str_contains($statusName, 'gestion documental')) {
            $newStatus = 'draft';
        } elseif (str_contains($statusName, 'coordinación de salida') || str_contains($statusName, 'coordinacion de salida') || str_contains($statusName, 'zarpe')) {
            $newStatus = 'pending';
        } elseif (str_contains($statusName, 'en tránsito') || str_contains($statusName, 'en transito') || str_contains($statusName, 'seguimiento')) {
            $newStatus = 'in_transit';
        } elseif (str_contains($statusName, 'entrega') || str_contains($statusName, 'liberación') || str_contains($statusName, 'liberacion') || str_contains($statusName, 'facturación')) {
            $newStatus = 'delivered';
        } elseif (str_contains($statusName, 'notificación de arribo') || str_contains($statusName, 'notificacion de arribo')) {
            $newStatus = 'approved';
        } elseif (str_contains($statusName, 'digitaciones')) {
            $newStatus = 'approved'; // O podrías usar otro estado apropiado
        } elseif (str_contains($statusName, 'transito interno destino')) {
            $newStatus = 'in_transit';
        } elseif (str_contains($statusName, 'archivado')) {
            $newStatus = 'delivered';
        }

        // Log para verificar el mapeo
        \Log::info("Mapping status from '$statusName' to '$newStatus'");

        // Guardar la información del kanban en el campo notes
        try {
            $shippingDoc->status = $newStatus;

            // Almacenar el kanban_status_id en las notas
            $notes = $shippingDoc->notes ?? '';
            $kanbanInfo = "KANBAN_STATUS_ID:" . $newColumnId;

            // Eliminar cualquier información anterior del kanban
            if (strpos($notes, 'KANBAN_STATUS_ID:') !== false) {
                $notes = preg_replace('/KANBAN_STATUS_ID:\d+/', $kanbanInfo, $notes);
            } else {
                // Añadir al principio o al final de las notas
                $notes = $notes ? ($notes . "\n" . $kanbanInfo) : $kanbanInfo;
            }

            $shippingDoc->notes = $notes;
            $result = $shippingDoc->save();

            \Log::info("Document updated with new status and kanban info in notes. Result: " . ($result ? 'success' : 'failed'));
        } catch (\Exception $e) {
            \Log::error("Error updating document: " . $e->getMessage());
        }

        // Actualizar en memoria para esta sesión
        foreach ($this->documents as &$document) {
            if ($document['id'] === 'DOC-' . $shippingDocId) {
                $document['kanban_status_id'] = $newColumnId;
                $document['status'] = $newStatus;
                \Log::info("Updated document in memory: ID={$document['id']}, kanban_status_id=$newColumnId");
                break;
            }
        }

        // Reorganizar los documentos por columna
        $this->organizeDocumentsByColumn();
    }

    public function setCurrentDocument($documentId, $newColumnId)
    {
        \Log::info("setCurrentDocument called", [
            'documentId' => $documentId,
            'newColumnId' => $newColumnId
        ]);

        $this->currentDocumentId = $documentId;
        $this->newColumnId = $newColumnId;
        $this->originalColumnId = null;

        // Find the current document from the loaded documents
        foreach ($this->documents as $document) {
            if ($document['id'] == $documentId) {
                $this->currentDocument = $document;
                $this->originalColumnId = $document['kanban_status_id'];
                \Log::info("Document found", ['document' => $this->currentDocument]);
                break;
            }
        }
    }

    public function setComments($documentId, $comment)
    {
        \Log::info("Setting comments for document $documentId: " . $comment);

        if (empty($comment)) {
            \Log::info("Empty comment, not saving anything");
            return;
        }

        try {
            // Extraer el ID del documento
            $shippingDocId = str_replace('DOC-', '', $documentId);

            // Buscar el documento
            $doc = ShippingDocument::find($shippingDocId);

            if (!$doc) {
                \Log::error("Document not found for comment: $shippingDocId");
                return;
            }

            // Usar el modelo ShippingDocumentComment
            $commentModel = new ShippingDocumentComment();
            $commentModel->shipping_document_id = $shippingDocId;
            $commentModel->user_id = auth()->id();
            $commentModel->comment = $comment;
            $commentModel->save();

            \Log::info("Comment saved with ID: " . $commentModel->id);

            // También podemos actualizar el campo notes del documento si existe
            if (Schema::hasColumn('shipping_documents', 'notes')) {
                $doc->notes = ($doc->notes ? $doc->notes . "\n" : '') . $comment;
                $doc->save();
                \Log::info("Comment also saved to shipping document notes field");
            }

            // Reset the comment in the component after saving
            $this->comment = '';

        } catch (\Exception $e) {
            \Log::error("Error saving comment: " . $e->getMessage());
        }
    }

    public function addDataToDocument($documentId) {
        \Log::info("addDataToDocument: Processing document $documentId");

        // Extract the document ID from the document ID string
        $shippingDocId = str_replace('DOC-', '', $documentId);

        // Find the shipping document
        $shippingDoc = ShippingDocument::find($shippingDocId);

        if (!$shippingDoc) {
            \Log::error("Shipping document not found: $shippingDocId");
            return;
        }

        try {
            // Update shipping document fields based on column ID
            $this->updateDocumentFields($shippingDoc);

            // Save the document
            $shippingDoc->save();

            // Process file upload if a file exists
            if ($this->attachment) {
                // Add file to media library
                $media = $shippingDoc->addMedia($this->attachment->getRealPath())
                    ->usingName($this->attachment->getClientOriginalName())
                    ->withCustomProperties([
                        'stage' => 'attachment',
                        'comment' => $this->comment ?? null,
                        'uploaded_by' => auth()->id() ?: 'system'
                    ])
                    ->toMediaCollection('shipping_documents');

                \Log::info("File uploaded with ID: " . $media->id);

                // Reset the file upload field
                $this->attachment = null;
            }

        } catch (\Exception $e) {
            \Log::error("Error adding data to document: " . $e->getMessage());
        }
    }

    // Helper method to update document fields based on column
    private function updateDocumentFields($shippingDoc)
    {
        // Check which column we're updating for and apply specific field updates
        if ($this->newColumnId == $this->columns[0]['id'] && $this->release_date) {
            $shippingDoc->release_date = $this->release_date;
        } elseif ($this->newColumnId == $this->columns[1]['id']) {
            // Update fields for column 2
            if ($this->tracking_id) {
                $shippingDoc->tracking_id = $this->tracking_id;
            }
            if ($this->booking_code) {
                $shippingDoc->booking_code = $this->booking_code;
            }
            if ($this->container_number) {
                $shippingDoc->container_number = $this->container_number;
            }
            if ($this->mbl_number) {
                $shippingDoc->mbl_number = $this->mbl_number;
            }

            if ($this->container_number) {
                $shippingDoc->container_number = $this->container_number;
            }

            // Validate that at least one tracking field is provided when needed
            if ($this->newColumnId == $this->columns[1]['id'] && !$this->tracking_id && !$this->mbl_number && !$this->container_number) {
                throw new \Exception('Debe proporcionar al menos un código de seguimiento (ID o Master BL)');
            }
        } elseif ($this->newColumnId == 14 && $this->instruction_date) { // Column "Digitaciones" (ID 14)
            $shippingDoc->instruction_date = $this->instruction_date;
        }

        return $shippingDoc;
    }

    /**
     * Valida los códigos de tracking antes de mover el documento
     *
     * @return array|false Retorna los datos de tracking si son válidos, false en caso contrario
     */
    private function validateTrackingCodes()
    {
        try {
            $this->isValidating = true;
            $this->dispatch('validating-state-changed', isValidating: true);

            // Verificamos que estamos en la columna que requiere validación
            if ($this->newColumnId != $this->columns[1]['id']) {
                $this->isValidating = false;
                $this->dispatch('validating-state-changed', isValidating: false);
                return true; // No se requiere validación para otras columnas
            }

            // Validamos los formatos de los campos
            $this->validate([
                'tracking_id' => 'nullable|string|max:50',
                'mbl_number' => 'nullable|string|max:50',
                'container_number' => 'nullable|string|max:50',
            ]);

            // Verificamos que hay al menos un código de tracking
            if (!$this->tracking_id && !$this->mbl_number && !$this->container_number) {
                $this->isValidating = false;
                $this->dispatch('validating-state-changed', isValidating: false);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Debe proporcionar al menos un código de seguimiento (ID o Master BL)'
                ]);
                return false;
            }

            $trackingService = new TrackingService();
            $trackingData = null;

            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Validando códigos de seguimiento...'
            ]);

            // Si tenemos un tracking_id, intentamos validarlo primero
            if ($this->tracking_id) {
                \Log::info('Validando tracking_id', ['id' => $this->tracking_id]);
                $trackingData = $trackingService->getPorthTracking($this->tracking_id);

                if ($trackingData) {
                    \Log::info('Tracking ID válido', ['id' => $this->tracking_id]);
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'ID de tracking validado correctamente'
                    ]);
                    $this->isValidating = false;
                    $this->dispatch('validating-state-changed', isValidating: false);
                    return $trackingData;
                } else {
                    \Log::warning('Tracking ID inválido', ['id' => $this->tracking_id]);
                }
            }

            // Si no se validó por tracking_id o no se proporcionó, intentamos con mbl_number
            if ($this->mbl_number) {
                \Log::info('Validando mbl_number', ['mbl' => $this->mbl_number]);
                $trackingData = $trackingService->getPorthTrackingByMasterBl($this->mbl_number);

                if ($trackingData) {
                    \Log::info('Master BL válido', ['mbl' => $this->mbl_number]);
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Master BL validado correctamente'
                    ]);
                    $this->isValidating = false;
                    $this->dispatch('validating-state-changed', isValidating: false);
                    return $trackingData;
                } else {
                    \Log::warning('Master BL inválido', ['mbl' => $this->mbl_number]);
                }
            }

            // Si no se validó por tracking_id o mbl_number, intentamos con container_number
            if ($this->container_number) {
                \Log::info('Validando container_number', ['container' => $this->container_number]);
                $trackingData = $trackingService->getPorthTrackingByContainerNumber($this->container_number);

                if ($trackingData) {
                    \Log::info('Container válido', ['container' => $this->container_number]);
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Contenedor válido'
                    ]);
                    $this->isValidating = false;
                    $this->dispatch('validating-state-changed', isValidating: false);
                    return $trackingData;
                } else {
                    \Log::warning('Container inválido', ['container' => $this->container_number]);
                }
            }

            // Si llegamos aquí, ninguno de los códigos es válido
            $errorMessage = '';
            if ($this->tracking_id && $this->mbl_number) {
                $errorMessage = 'Ninguno de los códigos proporcionados es válido. Verifique e intente nuevamente.';
            } elseif ($this->tracking_id) {
                $errorMessage = 'El ID de tracking proporcionado no es válido. Verifique e intente nuevamente.';
            } elseif ($this->mbl_number) {
                $errorMessage = 'El Master BL proporcionado no es válido. Verifique e intente nuevamente.';
            } elseif ($this->container_number) {
                $errorMessage = 'El contendor proporcionado no es válido. Verifique e intente nuevamente.';
            }

            $this->isValidating = false;
            $this->dispatch('validating-state-changed', isValidating: false);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $errorMessage
            ]);
            return false;

        } catch (\Exception $e) {
            \Log::error('Error validando códigos de tracking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->isValidating = false;
            $this->dispatch('validating-state-changed', isValidating: false);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al validar los códigos: ' . $e->getMessage()
            ]);
            return false;
        } finally {
            // Asegurarnos de que isValidating se resetea al final de la función
            $this->isValidating = false;
            $this->dispatch('validating-state-changed', isValidating: false);
        }
    }

    // First, add a method that handles everything in one go
    public function saveAndMoveDocument()
    {
        try {
            // Primero validamos los códigos de tracking si es necesario
            if ($this->newColumnId == $this->columns[1]['id']) {
                // Activar indicador de validación
                $this->isValidating = true;
                $this->dispatch('validating-state-changed', isValidating: true);

                $validationResult = $this->validateTrackingCodes();
                if ($validationResult === false) {
                    // La validación falló, no proceder con el guardado
                    $this->isValidating = false;
                    $this->dispatch('validating-state-changed', isValidating: false);
                    return;
                }
            }

            DB::beginTransaction();

            $shippingDocId = str_replace('DOC-', '', $this->currentDocumentId);
            $shippingDoc = ShippingDocument::findOrFail($shippingDocId);

            // 1. Obtener el nombre de la columna actual
            $kanbanStatus = KanbanStatus::findOrFail($this->newColumnId);
            $statusName = strtolower($kanbanStatus->name);
            $newStatus = $this->mapKanbanStatusToDocumentStatus($statusName);

            // Actualizar el documento
            $shippingDoc->status = $newStatus;
            $shippingDoc->notes = $this->updateKanbanNotes($shippingDoc->notes, $this->newColumnId);

            try {
                $this->updateDocumentFields($shippingDoc);
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('error', [
                    'message' => $e->getMessage()
                ]);
                return;
            }

            $shippingDoc->save();

            // 2. Crear comentario si existe
            if (!empty($this->comment)) {
                // Crear el comentario
                $comment = $shippingDoc->comments()->create([
                    'comment' => $this->comment,
                    'user_id' => auth()->id(),
                    'stage' => $kanbanStatus->name,
                    'shipping_document_id' => $shippingDocId
                ]);

                // Si hay archivo adjunto, procesarlo
                if ($this->attachment) {
                    $media = $comment->addMedia($this->attachment->getRealPath())
                        ->preservingOriginal()
                        ->usingFileName($this->attachment->getClientOriginalName())
                        ->withCustomProperties([
                            'uploaded_by' => auth()->id(),
                            'stage' => $kanbanStatus->name,
                            'comment_id' => $comment->id
                        ])
                        ->toMediaCollection('comment_attachments');
                }
            }

            DB::commit();

            // 3. Recargar datos y actualizar UI
            $this->loadData();

            // 4. Notificar éxito
            $this->dispatch('document-moved-successfully');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Documento actualizado exitosamente'
            ]);

            // 5. Limpiar el formulario y cerrar modal
            $this->resetFormFields();
            $this->dispatch('close-modal', 'modal-document-move');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in saveAndMoveDocument: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('error', [
                'message' => 'Error al actualizar el documento: ' . $e->getMessage()
            ]);
        } finally {
            // Asegurarnos de que isValidating se resetea al final de la función
            $this->isValidating = false;
            $this->dispatch('validating-state-changed', isValidating: false);
        }
    }

    private function updateKanbanNotes($currentNotes, $newStatusId)
    {
        $kanbanInfo = "KANBAN_STATUS_ID:" . $newStatusId;
        $currentNotes = $currentNotes ?? '';

        if (strpos($currentNotes, 'KANBAN_STATUS_ID:') !== false) {
            return preg_replace('/KANBAN_STATUS_ID:\d+/', $kanbanInfo, $currentNotes);
        }

        return $currentNotes ? ($currentNotes . "\n" . $kanbanInfo) : $kanbanInfo;
    }

    // Agregar este nuevo método helper
    private function mapKanbanStatusToDocumentStatus($statusName)
    {
        if (str_contains($statusName, 'gestion documental')) {
            return 'draft';
        } elseif (str_contains($statusName, 'coordinación de salida') || str_contains($statusName, 'coordinacion de salida') || str_contains($statusName, 'zarpe')) {
            return 'pending';
        } elseif (str_contains($statusName, 'en tránsito') || str_contains($statusName, 'en transito') || str_contains($statusName, 'seguimiento')) {
            return 'in_transit';
        } elseif (str_contains($statusName, 'entrega') || str_contains($statusName, 'liberación') || str_contains($statusName, 'liberacion') || str_contains($statusName, 'facturación')) {
            return 'delivered';
        } elseif (str_contains($statusName, 'notificación de arribo') || str_contains($statusName, 'notificacion de arribo')) {
            return 'approved';
        } elseif (str_contains($statusName, 'digitaciones')) {
            return 'approved';
        } elseif (str_contains($statusName, 'transito interno destino')) {
            return 'in_transit';
        } elseif (str_contains($statusName, 'archivado')) {
            return 'delivered';
        }

        return 'draft'; // estado por defecto
    }

    // Add this new method to reset all form fields
    private function resetFormFields()
    {
        $this->currentDocumentId = null;
        $this->newColumnId = null;
        $this->currentDocument = null;
        $this->comment = '';
        $this->tracking_id = null;
        $this->booking_code = null;
        $this->container_number = null;
        $this->mbl_number = null;
        $this->release_date = null;
        $this->instruction_date = null;
        $this->attachment = null;
        $this->isValidating = false;
    }

    // Este método se ejecuta después de cada actualización de Livewire
    public function hydrate()
    {
        // Aseguramos que el estado de validación se mantiene controlado
        if ($this->isValidating && !$this->newColumnId == $this->columns[1]['id']) {
            $this->isValidating = false;
        }
    }

    // Método para actualizar la propiedad isValidating desde JavaScript
    public function setIsValidating($value)
    {
        $this->isValidating = $value;
    }

    public function render()
    {
        return view('livewire.shipping-documentation.shipping-documentation-kanban');
    }
}
