<?php

namespace App\Livewire\Kanban;

use App\Models\KanbanBoard as KanbanBoardModel;
use App\Models\KanbanStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use App\Services\MaestrosApiService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\WithFileUploads;

class KanbanBoard extends Component
{
    use WithFileUploads;

    public $boardId;
    public $board;
    public $columns = [];
    public $tasks = [];
    public $tasksByColumn = [];
    public $boardType;
    public $currentTaskId;
    public $newColumnId;
    public $currentTask = null;

    public $actual_hub_id;

    // === Campos por etapa ===
    public $comment_stage_01;

    // Producción (id 2)
    public $date_variable_date;
    public $date_theorical_load;
    public $service_provider;
    public $forwarder_name;
    public $comment_stage_02;

    // Booking (id 3)
    public $date_booking_request;
    public $date_booking_authorized;
    public $date_etd_initial;
    public $date_etd_updated;
    public $mode;
    public $comment_stage_03;

    // Consolidador (id 4)
    public $comment_stage_04;

    // En Tránsito (id 5)
    public $date_atd;
    public $date_eta;
    public $date_eta_updated;
    public $container_type;
    public $container_number;
    public $bill_of_lading;
    public $shipment_amount;
    public $shipping_line;
    public $shipment_status;
    public $merchandise_invoice;
    public $tracking_id;
    public $departure_port;
    public $arrival_port;
    public $comment_stage_05;

    // Puerto (id 6)
    public $date_ata;
    public $comment_stage_06;


    // Almacén Fiscal (id 7)
    public $bonded_warehouse_enter;
    public $bonded_warehouse_exit;
    public $comment_stage_07;


    public $comment_stage_08;
    public $comment_stage_09;


    // Ingresada (id 10)
    public $receipt_note;


    public $comments = [];
    public $showCommentModal = false;

    public $comment = '';
    public $attachment = null;

    // Filtros activos
    public $activeFilters = [];

    // Array para dropdown de proveedores de servicio
    public $serviceProviderArray = [];

    // Arrays para dropdowns de la etapa "En Tránsito"
    public $shippingLineArray = [];
    public $departurePortArray = [];
    public $arrivalPortArray = [];
    public $containerTypeArray = [];

    // Arrays para dropdowns de la etapa "Booking"
    public $transportTypeArray = [];

    // Estado de carga del modal
    public $isLoadingModalData = false;

    // Agregar los listeners para los eventos
    protected $listeners = [
        'refreshKanban' => 'loadData',
        'purchaseOrderStatusUpdated' => '$refresh',
        'notificationsUpdated' => '$refresh',
        'kanbanFiltersChanged' => 'applyFilters'
    ];

    public function mount($boardId = null)
    {
        // Determinar el tipo de tablero según la ruta actual
        $currentRoute = Route::currentRouteName();

        if ($currentRoute === 'purchase-orders.index') {
            $this->boardType = 'po_stages'; // Etapas PO
        } // elseif ($currentRoute === 'shipping-documentation.index') {
            // $this->boardType = 'shipping_documentation'; // Documentación de embarque - Ocultado
        // } else {
        else {
            // Si no es ninguna de las rutas específicas, usar el tipo por defecto
            $this->boardType = 'purchase_orders';
        }

        // Si no se proporciona un ID de tablero, intentamos obtener el tablero según el tipo
        if (!$boardId) {
            // Obtener el tablero para la compañía del usuario actual según el tipo
            $companyId = auth()->user()->company_id ?? null;
            $this->board = KanbanBoardModel::where('company_id', $companyId)
                ->where('type', $this->boardType)
                ->where('is_active', true)
                ->first();

            if ($this->board) {
                $this->boardId = $this->board->id;
            }
        } else {
            $this->boardId = $boardId;
            $this->board = KanbanBoardModel::findOrFail($boardId);
            $this->boardType = $this->board->type;
        }

        $this->loadData();
    }

    public function loadData()
    {
        $this->loadColumns();
        $this->loadTasks();
        $this->organizeTasksByColumn();
    }

    /**
     * Carga las columnas (estados) del tablero Kanban.
     * 
     * IMPORTANTE: El orden de las columnas es crítico. La vista blade depende de índices
     * de array ($columns[0], $columns[1], etc.) para mostrar los campos correctos en el modal.
     * Si el orden de las columnas cambia o se eliminan columnas, el modal puede no funcionar
     * correctamente.
     * 
     * @return void
     */
    public function loadColumns()
    {
        if (!$this->board) {
            $this->columns = [];
            return;
        }

        // Cargar las columnas (estados) del tablero ordenadas por posición
        $statuses = $this->board->statuses()->orderBy('position')->get();

        if ($statuses->isEmpty()) {
            \Log::warning('KanbanBoard: No se encontraron columnas para el tablero', [
                'board_id' => $this->board->id,
                'board_type' => $this->boardType
            ]);
            $this->columns = [];
            return;
        }

        $this->columns = $statuses->map(function ($status) {
            return [
                'id' => $status->id,
                'slug' => $status->slug,
                'name' => $status->name,
                'color' => $status->color,
                'position' => $status->position,
            ];
        })->toArray();

        // Validación: Registrar si hay menos columnas de las esperadas (11 etapas)
        // Esto ayuda a detectar problemas de configuración
        $expectedMinColumns = 11;
        if (count($this->columns) < $expectedMinColumns) {
            \Log::info('KanbanBoard: Menos columnas de las esperadas', [
                'board_id' => $this->board->id,
                'columns_count' => count($this->columns),
                'expected_min' => $expectedMinColumns,
                'column_names' => collect($this->columns)->pluck('name')->toArray()
            ]);
        }
    }

    public function loadTasks()
    {
        if (!$this->board) {
            $this->tasks = [];
            return;
        }

        // Columnas del tablero actual
        $this->loadColumns();
        $defaultStatus = $this->board->statuses()->where('is_default', true)->first();

        // Obtener los IDs de los estados de este tablero
        $statusIds = collect($this->columns)->pluck('id')->toArray();

        // Detectar el ID de la columna "Anulada" en este tablero (fallback 10 si no la encuentra)
        $anuladaStatusId = optional(
            collect($this->columns)->first(function ($c) {
                return (isset($c['slug']) && strtolower($c['slug']) === 'anulada')
                    || strtolower($c['name']) === 'anulada';
            })
        )['id'] ?? 10;

        $companyId = auth()->user()->company_id ?? null;

        // === 1) PO activas (NO borradas) -> van a su columna actual ===
        $activeQuery = \App\Models\PurchaseOrder::with(['company', 'kanbanStatus', 'vendor'])
            ->withoutTrashed()
            ->where('company_id', $companyId);

        // Aplicar filtros si están activos
        $this->applyQueryFilters($activeQuery);

        $activeOrders = $activeQuery->get();

        // Limpiar el array de tareas
        $this->tasks = [];

        foreach ($activeOrders as $order) {
            // Asignar estado por defecto si no tiene
            if (!$order->kanban_status_id && $defaultStatus) {
                $order->update(['kanban_status_id' => $defaultStatus->id]);
                $order->refresh();
            }

            // Saltar si no tiene estado o no pertenece a este tablero
            if (!$order->kanban_status_id || !in_array($order->kanban_status_id, $statusIds)) {
                continue;
            }

            $this->tasks[] = [
                'id' => $order->id,
                'po' => $order->order_number,
                'vendor' => $order->vendor->name ?? 'N/A',
                'vendor_id' => $order->vendor_id,
                'status' => $order->kanban_status_id,
                'status_slug' => $order->kanbanStatus->slug ?? 'unknown',
                'order_date' => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                'requested_delivery_date' => $order->requested_delivery_date ? $order->requested_delivery_date->format('Y-m-d') : null,
                'total' => $order->total,
                'company' => $order->company->name ?? 'N/A',
                'created_at' => $order->created_at,
                'currency' => $order->currency,
                'incoterms' => $order->incoterms,
                'planned_hub_id' => $order->planned_hub_id,
                'actual_hub_id' => $order->actual_hub_id,
                'material_type' => $order->material_type,
            ];
        }

        // === 2) PO ANULADAS = soft-deleted -> SIEMPRE a la columna "Anulada" ===
        $trashedQuery = \App\Models\PurchaseOrder::onlyTrashed()
            ->with(['company', 'kanbanStatus', 'vendor'])
            ->where('company_id', $companyId);

        $this->applyQueryFilters($trashedQuery); // respeta filtros activos

        $trashedOrders = $trashedQuery->get();

        foreach ($trashedOrders as $order) {
            $this->tasks[] = [
                'id' => $order->id,
                'po' => $order->order_number,
                'vendor' => $order->vendor->name ?? 'N/A',
                'vendor_id' => $order->vendor_id,
                'status' => $anuladaStatusId,          // <- forzamos columna "Anulada"
                'status_slug' => 'anulada',
                'order_date' => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                'requested_delivery_date' => $order->requested_delivery_date ? $order->requested_delivery_date->format('Y-m-d') : null,
                'total' => $order->total,
                'company' => $order->company->name ?? 'N/A',
                'created_at' => $order->created_at,    // para mantener el orden cronológico
                'currency' => $order->currency,
                'incoterms' => $order->incoterms,
                'planned_hub_id' => $order->planned_hub_id,
                'actual_hub_id' => $order->actual_hub_id,
                'material_type' => $order->material_type,
            ];
        }

        // Finalmente organizar en columnas y ordenar por created_at desc
        $this->organizeTasksByColumn();
    }

    protected function applyQueryFilters($query)
    {
        if (empty($this->activeFilters)) {
            return;
        }

        if (isset($this->activeFilters['currency'])) {
            $query->whereRaw('LOWER(currency) = LOWER(?)', [$this->activeFilters['currency']]);
        }

        if (isset($this->activeFilters['incoterms'])) {
            $query->whereRaw('LOWER(incoterms) = LOWER(?)', [$this->activeFilters['incoterms']]);
        }

        if (isset($this->activeFilters['planned_hub_id'])) {
            $query->where('planned_hub_id', $this->activeFilters['planned_hub_id']);
        }

        if (isset($this->activeFilters['actual_hub_id'])) {
            $query->where('actual_hub_id', $this->activeFilters['actual_hub_id']);
        }

        if (isset($this->activeFilters['material_type'])) {
            $materialType = $this->activeFilters['material_type'];
            $query->where(function ($q) use ($materialType) {
                // Los datos están como: "[\"general\",\"dangerous\"]"
                // Buscar sin comillas ya que están escapadas en el JSON
                $searchPatterns = [
                    $materialType,                      // exacto
                    strtolower($materialType),          // minúsculas
                    strtoupper($materialType),          // mayúsculas
                    ucfirst(strtolower($materialType))  // primera mayúscula
                ];

                foreach ($searchPatterns as $pattern) {
                    $q->orWhereRaw('material_type::text LIKE ?', ['%' . $pattern . '%']);
                }
            });
        }

        // Nuevo filtro de búsqueda de texto case-insensitive
        if (isset($this->activeFilters['search_text'])) {
            $searchText = $this->activeFilters['search_text'];
            $query->where(function ($q) use ($searchText) {
                $q->whereRaw('LOWER(order_number) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereRaw('LOWER(currency) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereRaw('LOWER(incoterms) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereRaw('LOWER(CAST(total AS CHAR)) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereRaw('LOWER(tracking_id) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereRaw('LOWER(material_type::text) LIKE LOWER(?)', ["%{$searchText}%"])
                    ->orWhereHas('company', function ($companyQuery) use ($searchText) {
                        $companyQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchText}%"]);
                    })
                    ->orWhereHas('vendor', function ($vendorQuery) use ($searchText) {
                        $vendorQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchText}%"]);
                    });
            });
        }
    }

    public function applyFilters($filters = [])
    {
        $this->activeFilters = $filters;
        $this->loadData();

        // Notificar a otros componentes que los datos han sido actualizados
        $this->dispatch('refreshKanban');
    }

    public function organizeTasksByColumn()
    {
        $this->tasksByColumn = [];

        // Inicializar un array vacío para cada columna
        foreach ($this->columns as $column) {
            $this->tasksByColumn[$column['id']] = [];
        }

        // Organizar las tareas por columna
        foreach ($this->tasks as $task) {
            if (isset($this->tasksByColumn[$task['status']])) {
                $this->tasksByColumn[$task['status']][] = $task;
            }
        }

        // Ordenar las tareas por fecha de creación (de más nueva a más antigua) en cada columna
        foreach ($this->tasksByColumn as $columnId => $tasks) {
            usort($this->tasksByColumn[$columnId], function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });
        }
    }

    public function moveTask($taskId, $newStatus)
    {
        // Log para depuración
        \Log::info("Moving task $taskId to status $newStatus");

        try {
            // Trae también anuladas y bloquéalo si está soft-deleted
            $task = \App\Models\PurchaseOrder::withTrashed()->findOrFail($taskId);

            if ($task->trashed()) {
                \Log::warning("Intento de mover PO anulada {$task->order_number} ({$task->id})");
                // Refresca el kanban para devolver visualmente la tarjeta a su columna
                $this->dispatch('refreshKanban');
                session()->flash('message', 'Esta orden está anulada y no puede cambiar de etapa.');
                return;
            }

            // Obtener el estado anterior
            $oldStatus = $task->kanban_status_id;

            // Obtener nombres de columnas para el mensaje
            $oldColumnName = \App\Models\KanbanStatus::find($oldStatus)->name ?? 'desconocido';
            $newColumnName = \App\Models\KanbanStatus::find($newStatus)->name ?? 'desconocido';

            // Actualizar usando Eloquent para que se dispare el Observer y se registre en el historial
            $task->update(['kanban_status_id' => $newStatus]);

            // Crear notificación para todos los usuarios (tu servicio actual)
            \Log::info("Attempting to create notifications for task move", [
                'task_id' => $task->id,
                'order_number' => $task->order_number,
                'old_status' => $oldColumnName,
                'new_status' => $newColumnName,
                'user' => auth()->user()->name
            ]);

            try {
                $notificationService = app(\App\Services\NotificationService::class);
                $notifications = $notificationService->notifyAll(
                    'task_moved',
                    'Tarea Movida',
                    "La orden de compra {$task->order_number} fue movida de '{$oldColumnName}' a '{$newColumnName}' por " . auth()->user()->name,
                    [
                        'order_id'  => $task->id,
                        'task_id'   => $task->id,
                        'order_number' => $task->order_number,
                        'po_number' => $task->order_number,
                        'old_status'=> $oldColumnName,
                        'new_status'=> $newColumnName,
                    ]
                );

                \Log::info("Notifications created successfully", [
                    'task_id' => $task->id,
                    'notifications_count' => count($notifications)
                ]);
            } catch (\Exception $e) {
                \Log::error("Error creating notifications", [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Recargar datos y refrescar vista
            $this->loadData();
            $this->dispatch('refreshKanban');
            $this->dispatch('purchaseOrderStatusUpdated');
            $this->dispatch('notificationsUpdated');
        } catch (\Exception $e) {
            \Log::error("Error moving task: " . $e->getMessage());
        }
    }

    //Metodo para guardar datos y luego mover de etapa
    public function saveAndMove(): void
    {
        $poId = (int)($this->currentTaskId ?? 0);
        $stage = (int)($this->newColumnId ?? 0);

        $po = PurchaseOrder::withTrashed()->find($poId);
        if ($po && $po->trashed()) {
            session()->flash('message', 'Esta orden está anulada y no puede cambiar de etapa.');
            $this->dispatch('refreshKanban'); // revierte visualmente el movimiento
            return;
        }

        if (!$poId || !$stage) {
            session()->flash('message', 'Falta la PO o la etapa.');
            return;
        }

        $this->validateStageRequirements($stage);

        // 1) Guardar comentario + adjunto si existen
        $hasComment = is_string($this->comment ?? '') && trim($this->comment) !== '';
        if ($hasComment || $this->attachment) {
            $this->setComments($poId, (string)($this->comment ?? ''));
        }

        // 2) Guardar campos del formulario de la etapa
        $this->saveDataByModal(); // ya maneja transacción y payload por etapa

        // 3) Mover a la etapa nueva (y notificar)
        $this->moveTask($poId, $stage);

        // 4) Limpiar inputs de la etapa y UI
        foreach (($this->fieldsByStage()[$stage] ?? []) as $f) {
            if (property_exists($this, $f)) {
                $this->$f = null;
            }
        }
        $this->comment = '';
        $this->attachment = null;

        // 5) Cerrar el modal unificado
        $this->dispatch('close-modal', 'modal-po-stage-change');
    }

    public function setCurrentTask($taskId, $newColumnId)
    {
        $this->currentTaskId = $taskId;
        $this->newColumnId = $newColumnId;

        // Buscar la tarea actual entre las tareas cargadas
        foreach ($this->tasks as $task) {
            if ($task['id'] == $taskId) {
                $this->currentTask = $task;
                break;
            }
        }

        // NUEVO: Cargar datos de la PO en las propiedades del componente
        $po = PurchaseOrder::find($taskId);
        if ($po) {
            // Producción - convertir fechas al formato Y-m-d para campos HTML date
            $this->date_variable_date = $po->date_variable_date ? $po->date_variable_date->format('Y-m-d') : null;
            $this->date_theorical_load = $po->date_theorical_load ? $po->date_theorical_load->format('Y-m-d') : null;
            $this->service_provider = $po->service_provider;
            $this->forwarder_name = $po->forwarder_name;

            // Cargar proveedores de servicio si estamos en la etapa de producción
            // IMPORTANTE: Cargar service_provider ANTES de llamar a loadServiceProviders
            // para que el método pueda agregar el valor guardado al array si la API no devuelve datos
            if ($newColumnId == 2 && $po->trading_company) {
                $this->loadServiceProviders($po->trading_company, $po->service_provider);
            }

            // Cargar transport types si estamos en la etapa "Booking"
            if ($newColumnId == 3 && $po->trading_company) {
                $this->mode = $po->mode;
                $this->loadTransportTypes($po->trading_company, $po->mode);
            }

            // Cargar shipping lines, puertos y container types si estamos en la etapa "En Tránsito"
            // IMPORTANTE: Cargar los valores ANTES de llamar a los métodos de carga
            // para que los métodos puedan agregar los valores guardados al array si la API no devuelve datos
            if ($newColumnId == 5 && $po->trading_company) {
                $this->shipping_line = $po->shipping_line;
                $this->departure_port = $po->departure_port;
                $this->arrival_port = $po->arrival_port;
                $this->container_type = $po->container_type;
                
                \Log::info('KanbanBoard: Loading data for En Tránsito stage', [
                    'po_id' => $po->id,
                    'shipping_line' => $this->shipping_line,
                    'departure_port' => $this->departure_port,
                    'arrival_port' => $this->arrival_port,
                    'container_type' => $this->container_type,
                    'trading_company' => $po->trading_company
                ]);
                
                $this->loadShippingLines($po->trading_company, $po->shipping_line);
                $this->loadPorts($po->trading_company, $po->departure_port, $po->arrival_port);
                $this->loadContainerTypes($po->trading_company, $po->container_type);
            }

            // Booking - convertir fechas al formato Y-m-d
            $this->date_booking_request = $po->date_booking_request ? $po->date_booking_request->format('Y-m-d') : null;
            $this->date_booking_authorized = $po->date_booking_authorized ? $po->date_booking_authorized->format('Y-m-d') : null;
            $this->date_etd_initial = $po->date_etd_initial ? $po->date_etd_initial->format('Y-m-d') : null;
            $this->date_etd_updated = $po->date_etd_updated ? $po->date_etd_updated->format('Y-m-d') : null;
            if ($newColumnId != 3) {
                $this->mode = $po->mode;
            }

            // En Tránsito - convertir fechas al formato Y-m-d
            $this->date_atd = $po->date_atd ? $po->date_atd->format('Y-m-d') : null;
            $this->date_eta = $po->date_eta ? $po->date_eta->format('Y-m-d') : null;
            $this->date_eta_updated = $po->date_eta_updated ? $po->date_eta_updated->format('Y-m-d') : null;
            if ($newColumnId != 5) {
                $this->container_type = $po->container_type;
            }
            $this->container_number = $po->container_number;
            $this->bill_of_lading = $po->bill_of_lading;
            $this->shipment_amount = $po->shipment_amount ?? null;
            // shipping_line, departure_port y arrival_port ya se cargaron arriba si estamos en etapa 5
            if ($newColumnId != 5) {
                $this->shipping_line = $po->shipping_line;
                $this->departure_port = $po->departure_port;
                $this->arrival_port = $po->arrival_port;
            }
            $this->shipment_status = $po->shipment_status ?? null;
            $this->merchandise_invoice = $po->merchandise_invoice ?? null;
            $this->tracking_id = $po->tracking_id;

            // Puerto - convertir fechas al formato Y-m-d
            $this->date_ata = $po->date_ata ? $po->date_ata->format('Y-m-d') : null;

            // Almacén Fiscal - convertir fechas al formato Y-m-d
            $this->bonded_warehouse_enter = $po->bonded_warehouse_enter ? $po->bonded_warehouse_enter->format('Y-m-d') : null;
            $this->bonded_warehouse_exit = $po->bonded_warehouse_exit ? $po->bonded_warehouse_exit->format('Y-m-d') : null;

            // Ingresada
            $this->receipt_note = $po->receipt_note;
        }
    }

    public function saveAttachment($poId)
    {
        $this->validate([
            'attachment' => 'required|file|max:10240', // 10MB max
        ]);

        // Obtener la PO
        $purchaseOrder = PurchaseOrder::findOrFail($poId);

        // Guardar el archivo utilizando Spatie Media Library
        $purchaseOrder->addMediaFromRequest('attachment')
            ->toMediaCollection('attachments');

        // Notificar al usuario
        session()->flash('message', 'Archivo adjunto guardado correctamente');

        // Recargar datos
        $this->loadData();
    }

    public function setActualHubId($taskId, $hubId)
    {
        \Log::info("Actual Hub ID updated: " . $this->actual_hub_id);

        DB::table('purchase_orders')
            ->where('id', $taskId)
            ->update(['actual_hub_id' => $this->actual_hub_id]);

        // Recargar los datos
        $this->loadData();

        // Limpiar los datos temporales
        $this->currentTaskId = null;
        $this->newColumnId = null;
        $this->currentTask = null;

        // Forzar la actualización de la vista
        $this->dispatch('refreshKanban');
        $this->dispatch('purchaseOrderStatusUpdated');
    }

    public function setComments($taskId, $comment)
    {
        // Si no hay comentario, no hacemos nada y retornamos
        if (empty(trim($comment))) {
            return;
        }

        \Log::info("Setting comments for task $taskId: " . $comment);

        try {
            $operacion = $this->getOperacionName($this->newColumnId);

            $commentModel = PurchaseOrderComment::create([
                'purchase_order_id' => $taskId,
                'user_id' => auth()->id(),
                'comment' => $comment,
                'operacion' => $operacion
            ]);

            if ($this->attachment) {
                $commentModel
                    ->addMedia($this->attachment->getRealPath())
                    ->usingName($this->attachment->getClientOriginalName())
                    ->usingFileName($this->attachment->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }

            // Limpiar los campos después de guardar
            $this->comment = '';
            $this->attachment = null;

            // Dispatch webhook event for updated purchase order (comment added)
            if (function_exists('dispatch_webhook')) {
                try {
                    $po = PurchaseOrder::find($taskId);
                    if ($po) {
                        $po->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        $freshPo = $po->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                        // Convertir a array y asegurar que sea JSON serializable
                        $poData = $freshPo->toArray();
                        $poData = json_decode(json_encode($poData), true);

                        dispatch_webhook('purchase_order.updated', [
                            'purchase_order_id' => $po->id,
                            'order_number' => $po->order_number,
                            'changes' => ['comments' => 'new_comment_added'],
                            'data' => $poData,
                        ]);

                        \Log::info('dispatch_webhook completed after comment creation from KanbanBoard', [
                            'po_id' => $po->id,
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('Error dispatching webhook after comment creation', [
                        'po_id' => $taskId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error("Error setting comments: " . $e->getMessage());
        }
    }

    // Método helper para obtener el nombre de la operación
    // KanbanBoard.php
    private function getOperacionName($columnId)
    {
        $operaciones = [
            1 => 'Nuevo',
            2 => 'Consolidador',
            3 => 'Producción',
            4 => 'Booking',
            5 => 'En Tránsito',
            6 => 'Puerto',
            7 => 'Alm Fiscal',
            8 => 'En otra ZF',
            9 => 'Recibiendo CDI',
            10 => 'Ingresada',
            11 => 'Anulada',
        ];

        return $operaciones[$columnId] ?? 'Operación no especificada';
    }


    public function getCommentsWithAttachments($taskId)
    {
        return PurchaseOrderComment::with(['user', 'media'])
            ->where('purchase_order_id', $taskId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => $comment->user->name,
                    'created_at' => formatDateTime($comment->created_at),
                    'attachment' => $comment->getAttachment() ? [
                        'name' => $comment->getAttachment()->name,
                        'url' => $comment->getAttachment()->getUrl()
                    ] : null
                ];
            });
    }

    public function setPickupDate($taskId, $pickupDate)
    {
        \Log::info("Setting pickup date for task $taskId: " . $pickupDate);

        try {
            DB::table('purchase_orders')
                ->where('id', $taskId)
                ->update(['date_actual_pickup' => $pickupDate]);
        } catch (\Exception $e) {
            \Log::error("Error setting pickup date: " . $e->getMessage());
        }
    }

    public function setTrackingId($taskId, $trackingId)
    {
        \Log::info("Setting tracking ID for task $taskId: " . $trackingId);

        try {
            DB::table('purchase_orders')
                ->where('id', $taskId)
                ->update(['tracking_id' => $trackingId]);
        } catch (\Exception $e) {
            \Log::error("Error setting tracking ID: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kanban.kanban-board', [
            'tasksByColumn' => $this->tasksByColumn,
            'boardType' => $this->boardType,
            'hasActiveFilters' => !empty($this->activeFilters)
        ])->layout('layouts.app');
    }

    /**
     * Mapeo de campos por etapa del kanban.
     *
     * NOTA: Los índices (2, 3, 4, etc.) corresponden a los IDs de las columnas KanbanStatus
     * en la base de datos. Estos IDs pueden variar según la configuración del tablero.
     *
     * Mapeo esperado de etapas:
     * - 1: Nuevo
     * - 2: Producción
     * - 3: Booking
     * - 4: Consolidador
     * - 5: En Tránsito
     * - 6: Puerto
     * - 7: Almacén Fiscal
     * - 8: En otra ZF
     * - 9: Recibiendo CDI
     * - 10: Ingresada
     * - 11: Anulada
     *
     * @return array<int, array<string>> Array indexado por ID de etapa con lista de campos
     */
    private function fieldsByStage(): array
    {
        return [
            2 => ['date_variable_date', 'date_theorical_load', 'service_provider', 'forwarder_name'], // Producción
            3 => ['date_booking_request', 'date_booking_authorized', 'date_etd_initial', 'date_etd_updated', 'mode'], // Booking
            4 => [], // Consolidador (sin campos específicos)
            5 => [
                'date_atd', 'date_eta', 'date_eta_updated', 'container_type',
                'container_number', 'bill_of_lading',
                'shipment_amount', 'shipping_line', 'shipment_status', 'merchandise_invoice',
                'tracking_id', 'departure_port', 'arrival_port',
            ], // En transito
            6 => ['date_ata'], // Puerto
            7 => ['bonded_warehouse_enter', 'bonded_warehouse_exit', 'date_ata'], // Alm. Fiscal
            10 => ['receipt_note'], // Ingresada
        ];
    }

    public function saveDataByModal(): array
    {
        $poId = (int)($this->currentTaskId ?? 0);
        $stage = (int)($this->newColumnId ?? 0);

        if (!$poId || !$stage) {
            return ['ok' => false, 'message' => 'Falta la PO o la etapa seleccionada.'];
        }

        $fields = $this->fieldsByStage()[$stage] ?? [];
        if (empty($fields)) {
            // Esta etapa no tiene campos a persistir
            return ['ok' => true, 'updated' => 0];
        }

        // Armar payload solo con props existentes y con valor
        $payload = [];
        foreach ($fields as $name) {
            if (property_exists($this, $name)) {
                $val = $this->$name;
                // Filtrar valores especiales que indican "no hay datos"
                if (!is_null($val) && $val !== '__no_data__' && (!(is_string($val)) || trim($val) !== '')) {
                    $payload[$name] = $val;
                }
            }
        }

        if (empty($payload)) {
            return ['ok' => true, 'updated' => 0];
        }

        try {
            DB::beginTransaction();

            $updated = DB::table('purchase_orders')
                ->where('id', $poId)
                ->update($payload);

            // si quieres, puedes verificar que exista la PO
             if ($updated === 0) { throw new \RuntimeException('PO no encontrada'); }

            // NUEVO: Actualizar automáticamente arrival_status y delay_days si se actualizó la ETA
            $po = PurchaseOrder::find($poId);
            if ($po && (isset($payload['date_eta']) || isset($payload['date_eta_updated']))) {
                $po->updateArrivalStatus();
            }

            DB::commit();

            // Dispatch webhook event for updated purchase order
            if ($po && function_exists('dispatch_webhook')) {
                try {
                    \Log::info('About to dispatch webhook for PO update from KanbanBoard', [
                        'po_id' => $po->id,
                        'order_number' => $po->order_number,
                    ]);

                    $po->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                    $freshPo = $po->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                    // Convertir a array y asegurar que sea JSON serializable
                    $poData = $freshPo->toArray();
                    $poData = json_decode(json_encode($poData), true);

                    dispatch_webhook('purchase_order.updated', [
                        'purchase_order_id' => $po->id,
                        'order_number' => $po->order_number,
                        'changes' => $payload,
                        'data' => $poData,
                    ]);

                    \Log::info('dispatch_webhook completed from KanbanBoard', [
                        'po_id' => $po->id,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Error in webhook dispatch from KanbanBoard', [
                        'po_id' => $po->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // No lanzar la excepción para no interrumpir el flujo principal
                }
            }

            return ['ok' => true, 'updated' => $updated];
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('[KanbanBoard] saveDataByModal error', [
                'po' => $poId, 'stage' => $stage, 'msg' => $e->getMessage()
            ]);
            return ['ok' => false, 'message' => 'No se pudo guardar los datos de la etapa.'];
        }
    }

    /**
     * Reglas de validación requeridas por etapa del kanban.
     *
     * IMPORTANTE: Los índices deben coincidir con los IDs de las columnas KanbanStatus
     * y con los campos definidos en fieldsByStage().
     *
     * Validaciones complejas:
     * - Etapa 5 (En Tránsito): Usa 'required_without_all' para container_number, bill_of_lading
     *   y tracking_id. Esto significa que al menos uno de estos tres campos debe estar presente.
     *
     * @return array<int, array<string, string>> Array indexado por ID de etapa con reglas de validación
     */
    private function requiredRulesByStage(): array
    {
        return [
            2 => [
                'date_variable_date' => 'required|date',
                'service_provider'   => 'required|string',
                // forwarder_name está oculto en la vista, por lo que no debe ser requerido
                // 'forwarder_name'     => 'required|string',
                // Si más adelante decides exigir la teórica:
                // 'date_theorical_load' => 'required|date',
            ],

            3 => [
                'date_booking_request'    => 'required|date',
                'date_booking_authorized' => 'required|date',
                'date_etd_initial'        => 'required|date',
                'date_etd_updated'        => 'required|date',
                'mode'                    => 'required|string',
            ],

            5 => [
                'date_atd'         => 'required|date',
                'date_eta'         => 'required|date',
                'date_eta_updated' => 'required|date',
                // Validación compleja: al menos uno de estos tres campos debe estar presente
                'container_number' => 'nullable|required_without_all:tracking_id,bill_of_lading|string',
                'bill_of_lading'   => 'nullable|required_without_all:tracking_id,container_number',
                'tracking_id'      => 'nullable|required_without_all:container_number,bill_of_lading|string',
                'shipping_line'    => 'required|string',
                'departure_port'   => 'required|string',
                'arrival_port'     => 'required|string',
                // 'container_type' no está como requerido
            ],

            6 => [
                'date_ata' => 'required|date',
            ],

            7 => [
                'bonded_warehouse_enter' => 'required|date',
                'bonded_warehouse_exit'  => 'required|date',
                'date_ata'               => 'required|date',
            ],

            8 => [
                // Si quisieras hacerlo requerido:
                // 'receipt_note' => 'required|string',
            ],
        ];
    }

    private function fieldAttributeLabels(): array
    {
        return [
            'date_variable_date'     => 'Carga Lista Variable',
            'date_theorical_load'    => 'Carga Lista Teórica',
            'service_provider'       => 'Proveedor de Servicio',
            'forwarder_name'         => 'Agente de Carga',
            'date_booking_request'   => 'Solicitud de booking',
            'date_booking_authorized'=> 'Aut. Booking',
            'date_etd_initial'       => 'ETD Inicial',
            'date_etd_updated'       => 'ETD Variable',
            'mode'                   => 'Modo de transporte',
            'date_atd'               => 'ETD Real',
            'date_eta'               => 'ETA inicial',
            'date_eta_updated'       => 'ETA variable',
            'container_number'       => 'Contenedor',
            'container_type'         => 'Tipo de contenedor',
            'bill_of_lading'         => 'BL',
            'shipping_line'          => 'Naviera',
            'tracking_id'            => 'Tracking',
            'departure_port'         => 'Puerto de embarque',
            'arrival_port'           => 'Puerto de arribo',
            'date_ata'               => 'ETA Real',
            'bonded_warehouse_enter' => 'Ingreso a AF',
            'bonded_warehouse_exit'  => 'Salida AF',
            'receipt_note'      => 'Nota de Recibo',
        ];
    }

    /**
     * Valida los campos requeridos para una etapa específica.
     *
     * Si la validación falla, Livewire automáticamente mostrará los errores
     * en la vista y no ejecutará el resto del método saveAndMove().
     *
     * @param int $stage ID de la etapa (columna KanbanStatus)
     * @return void
     * @throws \Illuminate\Validation\ValidationException Si la validación falla
     */
    private function validateStageRequirements(int $stage): void
    {
        $rules = $this->requiredRulesByStage()[$stage] ?? [];

        if (empty($rules)) {
            return; // no hay requeridos para esta etapa
        }

        $messages = [
            'required' => 'El campo :attribute es requerido.',
            'required_without_all' => 'Debe proporcionar al menos uno: Número de Booking, MBL o Número de Contenedor.',
            'date'     => 'El campo :attribute debe ser una fecha válida.',
            'string'   => 'El campo :attribute debe ser texto.',
            'numeric'  => 'El campo :attribute debe ser numérico.',
        ];

        $this->validate($rules, $messages, $this->fieldAttributeLabels());
    }

    /**
     * Get MaestrosApiService instance
     */
    protected function getMaestrosApiService(): MaestrosApiService
    {
        return app(MaestrosApiService::class);
    }

    /**
     * Cargar proveedores de servicio desde la API
     * 
     * @param string $tradingCompany
     * @param string|null $currentServiceProvider Valor actual guardado en la PO (opcional)
     * @return void
     */
    protected function loadServiceProviders(string $tradingCompany, ?string $currentServiceProvider = null): void
    {
        // PRIMERO: Asegurar que el valor guardado esté en el array desde el inicio
        // Esto garantiza que esté disponible inmediatamente cuando Livewire renderiza el select
        $serviceProviderToAdd = $currentServiceProvider ?? $this->service_provider;
        
        $this->serviceProviderArray = [];
        
        if ($serviceProviderToAdd && trim($serviceProviderToAdd) !== '') {
            $this->serviceProviderArray[$serviceProviderToAdd] = $serviceProviderToAdd;
            \Log::info('KanbanBoard: Added saved service provider to array', [
                'service_provider' => $serviceProviderToAdd,
                'array_count' => count($this->serviceProviderArray)
            ]);
        }
        
        $tradingCompanyValue = trim($tradingCompany ?? '');
        if (empty($tradingCompanyValue)) {
            \Log::info('KanbanBoard: No trading company, returning with saved value only', [
                'array_count' => count($this->serviceProviderArray)
            ]);
            if (empty($this->serviceProviderArray)) {
                $this->serviceProviderArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente";
            }
            return;
        }

        try {
            $apiService = $this->getMaestrosApiService();

            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000,
            ];

            $serviceProvidersResponse = $this->getServiceProvidersWithCustomTimeout($apiService, $apiParams, 8);
            $serviceProvidersArray = $this->processApiResponse($serviceProvidersResponse, 'name', 'name');
            
            \Log::info('KanbanBoard: API response for service providers', [
                'response_count' => count($serviceProvidersArray),
                'current_array_count' => count($this->serviceProviderArray)
            ]);
            
            // Combinar resultados de la API con el valor guardado (sin sobrescribir)
            foreach ($serviceProvidersArray as $key => $value) {
                if (!isset($this->serviceProviderArray[$key])) {
                    $this->serviceProviderArray[$key] = $value;
                }
            }
            
            \Log::info('KanbanBoard: Final service providers array', [
                'final_count' => count($this->serviceProviderArray)
            ]);

            // Si después de todos los intentos el array está vacío, mostrar mensaje
            if (empty($this->serviceProviderArray)) {
                $this->serviceProviderArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }
        } catch (\Exception $e) {
            \Log::error('Error loading service providers in KanbanBoard', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
                'array_count' => count($this->serviceProviderArray)
            ]);
            // Los valores guardados ya están en el array desde el inicio, así que no los perdemos
            if (empty($this->serviceProviderArray)) {
                $this->serviceProviderArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }
        }
    }

    /**
     * Cargar shipping lines desde la API
     * 
     * @param string $tradingCompany
     * @param string|null $currentShippingLine Valor actual guardado en la PO (opcional)
     * @return void
     */
    protected function loadShippingLines(string $tradingCompany, ?string $currentShippingLine = null): void
    {
        // PRIMERO: Asegurar que el valor guardado esté en el array desde el inicio
        // Esto garantiza que esté disponible inmediatamente cuando Livewire renderiza el select
        $shippingLineToAdd = $currentShippingLine ?? $this->shipping_line;
        
        $this->shippingLineArray = [];
        
        if ($shippingLineToAdd) {
            $this->shippingLineArray[$shippingLineToAdd] = $shippingLineToAdd;
            \Log::info('KanbanBoard: Added saved shipping line to array', [
                'shipping_line' => $shippingLineToAdd,
                'array_count' => count($this->shippingLineArray)
            ]);
        }
        
        $tradingCompanyValue = trim($tradingCompany ?? '');
        if (empty($tradingCompanyValue)) {
            \Log::info('KanbanBoard: No trading company, returning with saved value only', [
                'array_count' => count($this->shippingLineArray)
            ]);
            // Si no hay trading company y no hay valor guardado, mostrar mensaje
            if (empty($this->shippingLineArray)) {
                $this->shippingLineArray['__no_data__'] = '⚠️ No hay datos disponibles';
            }
            return; // Ya agregamos el valor guardado arriba
        }

        try {
            $apiService = $this->getMaestrosApiService();

            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000,
            ];

            // Intentar solo una vez con timeout más corto (8 segundos)
            $shippingLinesResponse = $this->getShippingLinesWithCustomTimeout($apiService, $apiParams, 8);
            $shippingLinesArray = $this->processApiResponse($shippingLinesResponse, 'name', 'name');
            
            \Log::info('KanbanBoard: API response for shipping lines', [
                'response_count' => count($shippingLinesArray),
                'current_array_count' => count($this->shippingLineArray)
            ]);
            
            // Combinar los valores de la API con el valor guardado (sin duplicar)
            foreach ($shippingLinesArray as $key => $value) {
                if (!isset($this->shippingLineArray[$key])) {
                    $this->shippingLineArray[$key] = $value;
                }
            }
            
            \Log::info('KanbanBoard: Final shipping lines array', [
                'final_count' => count($this->shippingLineArray)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading shipping lines in KanbanBoard', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
                'array_count' => count($this->shippingLineArray)
            ]);
            // El valor guardado ya está en el array desde el inicio, así que no lo perdemos
        }
        
        // Si después de todos los intentos el array está vacío (y no hay valor guardado), agregar mensaje informativo
        if (empty($this->shippingLineArray)) {
            $this->shippingLineArray['__no_data__'] = '⚠️ No hay datos disponibles para el cliente "' . $tradingCompanyValue . '"';
        }
    }

    /**
     * Cargar puertos desde la API
     * 
     * @param string $tradingCompany
     * @param string|null $currentDeparturePort Valor actual guardado en la PO para puerto de embarque (opcional)
     * @param string|null $currentArrivalPort Valor actual guardado en la PO para puerto de arribo (opcional)
     * @return void
     */
    protected function loadPorts(string $tradingCompany, ?string $currentDeparturePort = null, ?string $currentArrivalPort = null): void
    {
        // PRIMERO: Asegurar que los valores guardados estén en los arrays desde el inicio
        // Esto garantiza que estén disponibles inmediatamente cuando Livewire renderiza el select
        $departurePortToAdd = $currentDeparturePort ?? $this->departure_port;
        $arrivalPortToAdd = $currentArrivalPort ?? $this->arrival_port;
        
        \Log::info('KanbanBoard: loadPorts called', [
            'currentDeparturePort' => $currentDeparturePort,
            'currentArrivalPort' => $currentArrivalPort,
            'this->departure_port' => $this->departure_port,
            'this->arrival_port' => $this->arrival_port,
            'departurePortToAdd' => $departurePortToAdd,
            'arrivalPortToAdd' => $arrivalPortToAdd
        ]);
        
        $this->departurePortArray = [];
        $this->arrivalPortArray = [];
        
        if ($departurePortToAdd && trim($departurePortToAdd) !== '') {
            $this->departurePortArray[$departurePortToAdd] = $departurePortToAdd;
            \Log::info('KanbanBoard: Added saved departure port to array', [
                'departure_port' => $departurePortToAdd,
                'array_count' => count($this->departurePortArray)
            ]);
        } else {
            \Log::warning('KanbanBoard: No departure port to add', [
                'departurePortToAdd' => $departurePortToAdd
            ]);
        }
        
        if ($arrivalPortToAdd && trim($arrivalPortToAdd) !== '') {
            $this->arrivalPortArray[$arrivalPortToAdd] = $arrivalPortToAdd;
            \Log::info('KanbanBoard: Added saved arrival port to array', [
                'arrival_port' => $arrivalPortToAdd,
                'array_count' => count($this->arrivalPortArray)
            ]);
        } else {
            \Log::warning('KanbanBoard: No arrival port to add', [
                'arrivalPortToAdd' => $arrivalPortToAdd
            ]);
        }
        
        $tradingCompanyValue = trim($tradingCompany ?? '');
        if (empty($tradingCompanyValue)) {
            \Log::info('KanbanBoard: No trading company, returning with saved values only', [
                'departure_count' => count($this->departurePortArray),
                'arrival_count' => count($this->arrivalPortArray)
            ]);
            // Si no hay trading company y no hay valores guardados, mostrar mensaje
            if (empty($this->departurePortArray)) {
                $this->departurePortArray['__no_data__'] = '⚠️ No hay datos disponibles';
            }
            if (empty($this->arrivalPortArray)) {
                $this->arrivalPortArray['__no_data__'] = '⚠️ No hay datos disponibles';
            }
            return; // Ya agregamos los valores guardados arriba
        }

        try {
            $apiService = $this->getMaestrosApiService();

            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000,
            ];

            // Intentar solo una vez con timeout más corto (8 segundos)
            $portsResponse = $this->getPortsWithCustomTimeout($apiService, $apiParams, 8);
            $portsArray = $this->processApiResponse($portsResponse, 'name', 'name');
            
            \Log::info('KanbanBoard: API response for ports', [
                'response_count' => count($portsArray),
                'current_departure_count' => count($this->departurePortArray),
                'current_arrival_count' => count($this->arrivalPortArray)
            ]);
            
            // Combinar los valores de la API con los valores guardados (sin duplicar)
            foreach ($portsArray as $key => $value) {
                if (!isset($this->departurePortArray[$key])) {
                    $this->departurePortArray[$key] = $value;
                }
                if (!isset($this->arrivalPortArray[$key])) {
                    $this->arrivalPortArray[$key] = $value;
                }
            }
            
            \Log::info('KanbanBoard: Final ports arrays', [
                'final_departure_count' => count($this->departurePortArray),
                'final_arrival_count' => count($this->arrivalPortArray)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading ports in KanbanBoard', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
                'departure_count' => count($this->departurePortArray),
                'arrival_count' => count($this->arrivalPortArray)
            ]);
            // Los valores guardados ya están en los arrays desde el inicio, así que no los perdemos
        }
        
        // Si después de todos los intentos los arrays están vacíos (y no hay valores guardados), agregar mensaje informativo
        if (empty($this->departurePortArray)) {
            $this->departurePortArray['__no_data__'] = '⚠️ No hay datos disponibles para el cliente "' . $tradingCompanyValue . '"';
        }
        if (empty($this->arrivalPortArray)) {
            $this->arrivalPortArray['__no_data__'] = '⚠️ No hay datos disponibles para el cliente "' . $tradingCompanyValue . '"';
        }
    }

    /**
     * Cargar tipos de contenedor desde la API
     * 
     * @param string $tradingCompany
     * @param string|null $currentContainerType Valor actual guardado en la PO (opcional)
     * @return void
     */
    protected function loadContainerTypes(string $tradingCompany, ?string $currentContainerType = null): void
    {
        $containerTypeToAdd = $currentContainerType ?? $this->container_type;
        
        $this->containerTypeArray = [];
        
        if ($containerTypeToAdd && trim($containerTypeToAdd) !== '') {
            $this->containerTypeArray[$containerTypeToAdd] = $containerTypeToAdd;
            \Log::info('KanbanBoard: Added saved container type to array', [
                'container_type' => $containerTypeToAdd,
                'array_count' => count($this->containerTypeArray)
            ]);
        }
        
        $tradingCompanyValue = trim($tradingCompany ?? '');
        if (empty($tradingCompanyValue)) {
            \Log::info('KanbanBoard: No trading company, returning with saved value only', [
                'array_count' => count($this->containerTypeArray)
            ]);
            if (empty($this->containerTypeArray)) {
                $this->containerTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompany}'";
            }
            return;
        }

        try {
            $apiService = $this->getMaestrosApiService();

            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000,
            ];

            $containerTypesResponse = $this->getContainerTypesWithCustomTimeout($apiService, $apiParams, 8);
            $containerTypesArray = $this->processApiResponse($containerTypesResponse, 'name', 'name');
            
            \Log::info('KanbanBoard: API response for container types', [
                'response_count' => count($containerTypesArray),
                'current_array_count' => count($this->containerTypeArray)
            ]);
            
            foreach ($containerTypesArray as $key => $value) {
                if (!isset($this->containerTypeArray[$key])) {
                    $this->containerTypeArray[$key] = $value;
                }
            }
            
            \Log::info('KanbanBoard: Final container types array', [
                'final_count' => count($this->containerTypeArray)
            ]);

            if (empty($this->containerTypeArray)) {
                $this->containerTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }

        } catch (\Exception $e) {
            \Log::error('Error loading container types in KanbanBoard', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
                'array_count' => count($this->containerTypeArray)
            ]);
            if (empty($this->containerTypeArray)) {
                $this->containerTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }
        }
    }

    /**
     * Cargar tipos de transporte desde la API
     * 
     * @param string $tradingCompany
     * @param string|null $currentMode Valor actual guardado en la PO (opcional)
     * @return void
     */
    protected function loadTransportTypes(string $tradingCompany, ?string $currentMode = null): void
    {
        $modeToAdd = $currentMode ?? $this->mode;
        
        $this->transportTypeArray = [];
        
        if ($modeToAdd && trim($modeToAdd) !== '') {
            $this->transportTypeArray[$modeToAdd] = $modeToAdd;
            \Log::info('KanbanBoard: Added saved transport type to array', [
                'mode' => $modeToAdd,
                'array_count' => count($this->transportTypeArray)
            ]);
        }
        
        $tradingCompanyValue = trim($tradingCompany ?? '');
        if (empty($tradingCompanyValue)) {
            \Log::info('KanbanBoard: No trading company, returning with saved value only', [
                'array_count' => count($this->transportTypeArray)
            ]);
            if (empty($this->transportTypeArray)) {
                $this->transportTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompany}'";
            }
            return;
        }

        try {
            $apiService = $this->getMaestrosApiService();

            $apiParams = [
                'company' => $tradingCompanyValue,
                'trading_company' => $tradingCompanyValue,
                'active' => 'true',
                'per_page' => 1000,
            ];

            $transportTypesResponse = $this->getTransportTypesWithCustomTimeout($apiService, $apiParams, 8);
            $transportTypesArray = $this->processApiResponse($transportTypesResponse, 'name', 'name');
            
            \Log::info('KanbanBoard: API response for transport types', [
                'response_count' => count($transportTypesArray),
                'current_array_count' => count($this->transportTypeArray)
            ]);
            
            foreach ($transportTypesArray as $key => $value) {
                if (!isset($this->transportTypeArray[$key])) {
                    $this->transportTypeArray[$key] = $value;
                }
            }
            
            \Log::info('KanbanBoard: Final transport types array', [
                'final_count' => count($this->transportTypeArray)
            ]);

            if (empty($this->transportTypeArray)) {
                $this->transportTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }

        } catch (\Exception $e) {
            \Log::error('Error loading transport types in KanbanBoard', [
                'error' => $e->getMessage(),
                'trading_company' => $tradingCompanyValue,
                'array_count' => count($this->transportTypeArray)
            ]);
            if (empty($this->transportTypeArray)) {
                $this->transportTypeArray['__no_data__'] = "⚠️ No hay datos disponibles para el cliente '{$tradingCompanyValue}'";
            }
        }
    }

    /**
     * Obtener shipping lines con timeout personalizado
     * 
     * @param MaestrosApiService $apiService
     * @param array $params
     * @param int $timeout Segundos de timeout
     * @return array|null
     */
    protected function getShippingLinesWithCustomTimeout($apiService, array $params, int $timeout = 8): ?array
    {
        try {
            $baseUrl = config('services.maestros.base_url');
            $url = rtrim($baseUrl, '/') . '/api/v1/shipping-lines';

            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Timeout or error loading shipping lines', [
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener puertos con timeout personalizado
     * 
     * @param MaestrosApiService $apiService
     * @param array $params
     * @param int $timeout Segundos de timeout
     * @return array|null
     */
    protected function getPortsWithCustomTimeout($apiService, array $params, int $timeout = 8): ?array
    {
        try {
            $baseUrl = config('services.maestros.base_url');
            $url = rtrim($baseUrl, '/') . '/api/v1/ports';

            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Timeout or error loading ports', [
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener container types con timeout personalizado
     * 
     * @param MaestrosApiService $apiService
     * @param array $params
     * @param int $timeout Segundos de timeout
     * @return array|null
     */
    protected function getContainerTypesWithCustomTimeout($apiService, array $params, int $timeout = 8): ?array
    {
        try {
            $baseUrl = config('services.maestros.base_url');
            $url = rtrim($baseUrl, '/') . '/api/v1/container-types';

            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Timeout or error loading container types', [
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener transport types con timeout personalizado
     * 
     * @param MaestrosApiService $apiService
     * @param array $params
     * @param int $timeout Segundos de timeout
     * @return array|null
     */
    protected function getTransportTypesWithCustomTimeout($apiService, array $params, int $timeout = 8): ?array
    {
        try {
            $baseUrl = config('services.maestros.base_url');
            $url = rtrim($baseUrl, '/') . '/api/v1/transport-types';

            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Timeout or error loading transport types', [
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener service providers con timeout personalizado
     * 
     * @param MaestrosApiService $apiService
     * @param array $params
     * @param int $timeout Segundos de timeout
     * @return array|null
     */
    protected function getServiceProvidersWithCustomTimeout($apiService, array $params, int $timeout = 8): ?array
    {
        try {
            $baseUrl = config('services.maestros.base_url');
            $url = rtrim($baseUrl, '/') . '/api/v1/service-providers';

            $response = \Illuminate\Support\Facades\Http::timeout($timeout)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Timeout or error loading service providers', [
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Process API response and convert to array format for dropdowns
     * 
     * @param array|null $response
     * @param string $keyField Field to use as array key
     * @param string $valueField Field to use as array value
     * @return array
     */
    protected function processApiResponse($response, $keyField = 'name', $valueField = 'name')
    {
        if (!$response || !isset($response['data'])) {
            return [];
        }

        $result = [];
        foreach ($response['data'] as $item) {
            $key = $item[$keyField] ?? $item['name'] ?? '';
            $value = $item[$valueField] ?? $item['name'] ?? '';
            if ($key && $value) {
                $result[$key] = $value;
            }
        }

        // Ordenar alfabéticamente por valor
        asort($result);

        return $result;
    }

}



