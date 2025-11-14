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

    // ==== Props nuevas para etapas ====
    // Producción
    public $date_theorical_load;
    public $date_variable_date;
    public $service_provider;
    public $forwarder_name;

    // Booking
    public $date_booking_request;
    public $date_booking_authorized;
    public $date_etd_updated;
    public $container_type;
    public $mode;
    public $estimated_departure_date;

    // Tránsito
    public $actual_departure_date;
    public $estimated_arrival_date;
    public $date_eta_updated;
    public $Invoice_amount;
    public $shipping_line;
    public $arrival_status;
    public $factura_merca;
    public $departure_port;
    public $arrival_port;
    public $bill_of_lading;

    // Puerto
    public $actual_arrival_date;

    // Almacén Fiscal
    public $bonded_warehouse_enter;
    public $bonded_warehouse_exit;

    // Ingresada
    public $receipt_note;


    // Reglas de validación
    public function getRules()
    {
        // Reglas comunes para cualquier etapa
        $common = [
            'comment'    => 'nullable|string',
            'attachment' => 'nullable|file|max:5120', // 5MB
        ];

        // Columna 0: Consolidador (Nueva)
        if ($this->newColumnId == $this->columns[0]['id']) {
            return $common + [
                    'release_date' => 'nullable|date',
                ];
        }

        // Columna 1: Producción
        if ($this->newColumnId == $this->columns[1]['id']) {
            return $common + [
                    // Necesarios (no obligatorios): fecha teórica
                    'date_theorical_load' => [
                        'nullable',
                        'date',
                        function ($attribute, $value, $fail) {
                            if ($value) {
                                // Obtener la fecha de emisión de la PO relacionada
                                $shippingDoc = \App\Models\ShippingDocument::find($this->shippingDocumentId);
                                $emisionDate = $shippingDoc && $shippingDoc->purchaseOrder ? 
                                    $shippingDoc->purchaseOrder->emision_date_po : 
                                    null;
                                
                                if ($emisionDate && $value < $emisionDate) {
                                    $fail('La fecha de carga lista teórica no puede ser anterior a la fecha de emisión de la PO (' . formatDate($emisionDate) . ')');
                                }
                            }
                        }
                    ],
                    // Requeridos para pasar de etapa:
                    'date_variable_date'  => [
                        'required',
                        'date',
                        function ($attribute, $value, $fail) {
                            // Obtener la fecha de emisión de la PO relacionada
                            $shippingDoc = \App\Models\ShippingDocument::find($this->shippingDocumentId);
                            $emisionDate = $shippingDoc && $shippingDoc->purchaseOrder ? 
                                $shippingDoc->purchaseOrder->emision_date_po : 
                                null;
                            
                            if ($emisionDate && $value < $emisionDate) {
                                $fail('La fecha de carga lista variable no puede ser anterior a la fecha de emisión de la PO (' . formatDate($emisionDate) . ')');
                            }
                        }
                    ],
                    'service_provider'    => 'required|string|max:100',
                    'forwarder_name'      => 'required|string|max:100',
                ];
        }

        // Columna 2: Booking
        if ($this->newColumnId == $this->columns[2]['id']) {
            return $common + [
                    // Requeridos
                    'date_booking_request'     => 'required|date',
                    'date_booking_authorized'  => 'required|date',
                    'estimated_departure_date' => 'required|date', // ETD inicial (reusado)
                    'date_etd_updated'         => 'required|date', // ETD variable
                    // Necesarios/optativos
                    'mode'           => 'nullable|string|max:100',
                ];
        }

        // Columna 3: Consolidador
        if ($this->newColumnId == $this->columns[3]['id']) {
            return $common; // Sin campos específicos
        }

        // Columna 4: Tránsito
        if ($this->newColumnId == $this->columns[4]['id']) {
            return $common + [
                    // Requeridos
                    'actual_departure_date'  => 'required|date', // ETD real
                    'estimated_arrival_date' => 'required|date', // ETA inicial
                    'date_eta_updated'       => 'required|date', // ETA variable
                    // Al menos uno de estos tres debe estar presente
                    'container_number'       => 'nullable|required_without_all:tracking_id,bill_of_lading|string|max:50',
                    'tracking_id'            => 'nullable|required_without_all:container_number,bill_of_lading|string|max:50',
                    'bill_of_lading'         => 'nullable|required_without_all:tracking_id,container_number|string|max:100',
                    // Otros campos requeridos
                    'shipping_line'          => 'required|string|max:100',
                    'departure_port'         => 'required|string|max:100',
                    'arrival_port'           => 'required|string|max:100',
                    // Necesarios/optativos según hoja
                    'Invoice_amount' => 'nullable|numeric|min:0',
                    'arrival_status' => 'nullable|string|max:100',
                    'factura_merca'  => 'nullable|string|max:100',
                    'container_type' => 'nullable|string|max:100',
                ];
        }

        // Columna 5: Puerto
        if ($this->newColumnId == $this->columns[5]['id']) {
            return $common + [
                    'actual_arrival_date' => 'required|date', // ETA real
                ];
        }

        // Columna 6: Almacén Fiscal
        if ($this->newColumnId == $this->columns[6]['id']) {
            return $common + [
                    'bonded_warehouse_enter' => 'required|date',
                    'bonded_warehouse_exit'  => 'required|date',
                ];
        }

        // Columna 9: Ingresada
        if ($this->newColumnId == $this->columns[9]['id']) {
            return $common + [
                    'receipt_note' => 'nullable|string|max:255',
                ];
        }
        return $common;
    }

    // Mensajes de validación personalizados
    protected function messages()
    {
        return [
            // comunes
            'attachment.max' => 'El archivo no debe exceder los 5MB',

            // Producción
            'date_variable_date.required' => 'El campo es obligatorio.',
            'service_provider.required'   => 'El campo es obligatorio.',
            'forwarder_name.required'     => 'El campo es obligatorio.',

            // Booking
            'date_booking_request.required'     => 'El campo es obligatorio.',
            'date_booking_authorized.required'  => 'El campo es obligatorio.',
            'estimated_departure_date.required' => 'El campo es obligatorio.',
            'date_etd_updated.required'         => 'El campo es obligatorio.',

            // Tránsito
            'actual_departure_date.required'  => 'El campo es obligatorio.',
            'estimated_arrival_date.required' => 'El campo es obligatorio.',
            'date_eta_updated.required'       => 'El campo es obligatorio.',
            'container_number.required_without_all' => 'Debe proporcionar al menos uno: Número de Booking, MBL o Número de Contenedor.',
            'tracking_id.required_without_all'      => 'Debe proporcionar al menos uno: Número de Booking, MBL o Número de Contenedor.',
            'bill_of_lading.required_without_all'   => 'Debe proporcionar al menos uno: Número de Booking, MBL o Número de Contenedor.',
            'shipping_line.required'          => 'El campo es obligatorio.',
            'departure_port.required'         => 'El campo es obligatorio.',
            'arrival_port.required'           => 'El campo es obligatorio.',


            // Puerto
            'actual_arrival_date.required'    => 'El campo es obligatorio.',

            // AF
            'bonded_warehouse_enter.required' => 'El campo es obligatorio.',
            'bonded_warehouse_exit.required'  => 'El campo es obligatorio.',
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
                'creation_date' => formatDate($doc->creation_date),
                'estimated_departure_date' => formatDate($doc->estimated_departure_date),
                'estimated_arrival_date' => formatDate($doc->estimated_arrival_date),
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
        
        // NUEVO: Cargar datos del ShippingDocument en las propiedades
        $shippingDocId = str_replace('DOC-', '', $documentId);
        $shippingDoc = ShippingDocument::find($shippingDocId);
        
        if ($shippingDoc) {
            // Producción - convertir fechas al formato Y-m-d para campos HTML date
            $this->date_theorical_load = $shippingDoc->date_theorical_load ? $shippingDoc->date_theorical_load->format('Y-m-d') : null;
            $this->date_variable_date = $shippingDoc->date_variable_date ? $shippingDoc->date_variable_date->format('Y-m-d') : null;
            $this->service_provider = $shippingDoc->service_provider;
            $this->forwarder_name = $shippingDoc->forwarder_name;
            
            // Booking - convertir fechas al formato Y-m-d
            $this->date_booking_request = $shippingDoc->date_booking_request ? $shippingDoc->date_booking_request->format('Y-m-d') : null;
            $this->date_booking_authorized = $shippingDoc->date_booking_authorized ? $shippingDoc->date_booking_authorized->format('Y-m-d') : null;
            $this->estimated_departure_date = $shippingDoc->estimated_departure_date ? $shippingDoc->estimated_departure_date->format('Y-m-d') : null;
            $this->date_etd_updated = $shippingDoc->date_etd_updated ? $shippingDoc->date_etd_updated->format('Y-m-d') : null;
            $this->container_type = $shippingDoc->container_type;
            $this->mode = $shippingDoc->mode;
            
            // Tránsito - convertir fechas al formato Y-m-d
            $this->actual_departure_date = $shippingDoc->actual_departure_date ? $shippingDoc->actual_departure_date->format('Y-m-d') : null;
            $this->estimated_arrival_date = $shippingDoc->estimated_arrival_date ? $shippingDoc->estimated_arrival_date->format('Y-m-d') : null;
            $this->date_eta_updated = $shippingDoc->date_eta_updated ? $shippingDoc->date_eta_updated->format('Y-m-d') : null;
            $this->Invoice_amount = $shippingDoc->Invoice_amount;
            $this->shipping_line = $shippingDoc->shipping_line;
            $this->arrival_status = $shippingDoc->arrival_status;
            $this->factura_merca = $shippingDoc->factura_merca;
            $this->departure_port = $shippingDoc->departure_port;
            $this->arrival_port = $shippingDoc->arrival_port;
            $this->bill_of_lading = $shippingDoc->bill_of_lading;
            $this->container_number = $shippingDoc->container_number;
            $this->tracking_id = $shippingDoc->tracking_id;
            
            // Puerto - convertir fechas al formato Y-m-d
            $this->actual_arrival_date = $shippingDoc->actual_arrival_date ? $shippingDoc->actual_arrival_date->format('Y-m-d') : null;
            
            // Almacén Fiscal - convertir fechas al formato Y-m-d
            $this->bonded_warehouse_enter = $shippingDoc->bonded_warehouse_enter ? $shippingDoc->bonded_warehouse_enter->format('Y-m-d') : null;
            $this->bonded_warehouse_exit = $shippingDoc->bonded_warehouse_exit ? $shippingDoc->bonded_warehouse_exit->format('Y-m-d') : null;
            
            // Ingresada
            $this->receipt_note = $shippingDoc->receipt_note;
            
            // Otros campos - convertir fechas al formato Y-m-d
            $this->release_date = $shippingDoc->release_date ? $shippingDoc->release_date->format('Y-m-d') : null;
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
        // Columna 0: Consolidador (Nueva)
        if ($this->newColumnId == $this->columns[0]['id']) {
            if (!is_null($this->release_date)) {
                $shippingDoc->release_date = $this->release_date;
            }
            return $shippingDoc;
        }

        // Columna 1: Producción
        if ($this->newColumnId == $this->columns[1]['id']) {
            $shippingDoc->date_theorical_load = $this->date_theorical_load;
            $shippingDoc->date_variable_date  = $this->date_variable_date;
            $shippingDoc->service_provider    = $this->service_provider;
            $shippingDoc->forwarder_name      = $this->forwarder_name;
            return $shippingDoc;
        }

        // Columna 2: Booking
        if ($this->newColumnId == $this->columns[2]['id']) {
            $shippingDoc->date_booking_request     = $this->date_booking_request;
            $shippingDoc->date_booking_authorized  = $this->date_booking_authorized;
            $shippingDoc->estimated_departure_date = $this->estimated_departure_date; // ETD inicial
            $shippingDoc->date_etd_updated         = $this->date_etd_updated;         // ETD variable
            $shippingDoc->mode                     = $this->mode;
            return $shippingDoc;
        }

        // Columna 3: Consolidador
        if ($this->newColumnId == $this->columns[3]['id']) {
            // Sin campos específicos
            return $shippingDoc;
        }

        // Columna 4: Tránsito
        if ($this->newColumnId == $this->columns[4]['id']) {
            $shippingDoc->actual_departure_date   = $this->actual_departure_date;   // ETD real
            $shippingDoc->estimated_arrival_date  = $this->estimated_arrival_date;  // ETA inicial
            $shippingDoc->date_eta_updated        = $this->date_eta_updated;        // ETA variable

            $shippingDoc->container_number     = $this->container_number;
            $shippingDoc->bill_of_lading       = $this->bill_of_lading;
            $shippingDoc->container_type       = $this->container_type;

            $shippingDoc->Invoice_amount = $this->Invoice_amount;
            $shippingDoc->shipping_line  = $this->shipping_line;
            $shippingDoc->factura_merca  = $this->factura_merca;
            $shippingDoc->tracking_id    = $this->tracking_id;
            $shippingDoc->departure_port = $this->departure_port;
            $shippingDoc->arrival_port   = $this->arrival_port;
            
            // NUEVO: Calcular automáticamente arrival_status basándose en la ETA
            $status = $shippingDoc->calculateArrivalStatus();
            $shippingDoc->arrival_status = $status['arrival_status'];
            
            return $shippingDoc;
        }

        // Columna 5: Puerto
        if ($this->newColumnId == $this->columns[5]['id']) {
            $shippingDoc->actual_arrival_date = $this->actual_arrival_date; // ETA real
            return $shippingDoc;
        }

        // Columna 6: Almacén Fiscal
        if ($this->newColumnId == $this->columns[6]['id']) {
            $shippingDoc->bonded_warehouse_enter = $this->bonded_warehouse_enter;
            $shippingDoc->bonded_warehouse_exit  = $this->bonded_warehouse_exit;
            return $shippingDoc;
        }

        // Columna 9: Ingresada
        if ($this->newColumnId == $this->columns[9]['id']) {
            $shippingDoc->receipt_note = $this->receipt_note;
            return $shippingDoc;
        }

        // Otras columnas: sin cambios
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

            // Verificamos que estamos en la columna que requiere validación (Tránsito)
            if ($this->newColumnId != $this->columns[4]['id']) {
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
        // Validar los datos del formulario - Livewire mostrará los errores automáticamente
        $this->validate($this->getRules(), $this->messages());

        try {
            // Primero validamos los códigos de tracking si es necesario (columna Tránsito)
            if ($this->newColumnId == $this->columns[4]['id']) {
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
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error al actualizar los campos del documento: ' . $e->getMessage()
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

            // Cerrar el modal después de un pequeño delay para asegurar que la UI se actualice
            $this->dispatch('close-modal', 'modal-document-move');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error in saveAndMoveDocument: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el documento: ' . $e->getMessage()
            ]);

            // No cerrar el modal en caso de error para que el usuario pueda corregir
            return;
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

        // Producción
        $this->date_theorical_load = null;
        $this->date_variable_date  = null;
        $this->service_provider    = null;
        $this->forwarder_name      = null;

        // Booking
        $this->date_booking_request    = null;
        $this->date_booking_authorized = null;
        $this->date_etd_updated        = null;
        $this->container_type          = null;
        $this->mode                    = null;
        $this->estimated_departure_date= null;

        // Tránsito
        $this->actual_departure_date   = null;
        $this->estimated_arrival_date  = null;
        $this->date_eta_updated        = null;
        $this->Invoice_amount          = null;
        $this->shipping_line           = null;
        $this->arrival_status          = null;
        $this->factura_merca           = null;
        $this->departure_port          = null;
        $this->arrival_port            = null;
        $this->hbl_number              = null;

        // Puerto
        $this->actual_arrival_date     = null;

        // AF
        $this->bonded_warehouse_enter  = null;
        $this->bonded_warehouse_exit   = null;

        // Ingresada
        $this->receipt_note            = null;

    }

    // Este método se ejecuta después de cada actualización de Livewire
    public function hydrate()
    {
        // Aseguramos que el estado de validación se mantiene controlado
        if ($this->isValidating && $this->newColumnId != $this->columns[4]['id']) {
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
