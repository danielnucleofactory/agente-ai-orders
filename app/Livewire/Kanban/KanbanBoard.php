<?php

namespace App\Livewire\Kanban;

use App\Models\KanbanBoard as KanbanBoardModel;
use App\Models\KanbanStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
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
    public $container_type;
    public $mode;
    public $comment_stage_03;

    // Consolidador (id 4)
    public $comment_stage_04;

    // En Tránsito (id 5)
    public $date_atd;
    public $date_eta;
    public $date_eta_updated;
    public $container_number;
    public $bill_of_lading;
    public $shipping_line;
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
        } elseif ($currentRoute === 'shipping-documentation.index') {
            $this->boardType = 'shipping_documentation'; // Documentación de embarque
        } else {
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

    public function loadColumns()
    {
        if (!$this->board) {
            $this->columns = [];
            return;
        }

        // Cargar las columnas (estados) del tablero
        $statuses = $this->board->statuses()->orderBy('position')->get();

        $this->columns = $statuses->map(function ($status) {
            return [
                'id' => $status->id,
                'slug' => $status->slug,
                'name' => $status->name,
                'color' => $status->color,
                'position' => $status->position,
            ];
        })->toArray();
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

            // Actualizar directamente en la base de datos
            \Illuminate\Support\Facades\DB::table('purchase_orders')
                ->where('id', $taskId)
                ->update(['kanban_status_id' => $newStatus]);

            // Crear notificación para todos los usuarios (tu servicio actual)
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAll(
                'task_moved',
                'Tarea Movida',
                "La orden de compra {$task->order_number} fue movida de '{$oldColumnName}' a '{$newColumnName}' por " . auth()->user()->name,
                [
                    'task_id'   => $task->id,
                    'po_number' => $task->order_number,
                    'old_status'=> $oldColumnName,
                    'new_status'=> $newColumnName,
                ]
            );

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

        // 5) Cerrar el modal desde Livewire (sin Alpine extra)
        $this->dispatch('close-modal', $this->modalName($stage));
    }

    private function modalName(int $stage): string
    {
        return match ($stage) {
            1 => 'modal-nuevo',
            2 => 'modal-produccion',
            3 => 'modal-booking',
            4 => 'modal-consolidador',
            5 => 'modal-en-transito',
            6 => 'modal-puerto',
            7 => 'modal-alm-fiscal',
            8 => 'modal-en-otra-zf',
            9 => 'modal-recibiendo-cdi',
            10 => 'modal-ingresada',
            11 => 'modal-anulada',
            default => 'success-modal',
        };
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
                    'created_at' => $comment->created_at->format('d/m/Y H:i'),
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

    //Guardado de datos
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
                if (!is_null($val) && (!(is_string($val)) || trim($val) !== '')) {
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

            DB::commit();
            return ['ok' => true, 'updated' => $updated];
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('[KanbanBoard] saveDataByModal error', [
                'po' => $poId, 'stage' => $stage, 'msg' => $e->getMessage()
            ]);
            return ['ok' => false, 'message' => 'No se pudo guardar los datos de la etapa.'];
        }
    }

    //Validación de datos requeridos
    private function requiredRulesByStage(): array
    {
        return [
            3 => [
                'date_variable_date' => 'required|date',
                'service_provider'   => 'required|string',
                'forwarder_name'     => 'required|string',
                // Si más adelante decides exigir la teórica:
                // 'date_theorical_load' => 'required|date',
            ],

            4 => [
                'date_booking_request'    => 'required|date',
                'date_booking_authorized' => 'required|date',
                'date_etd_initial'        => 'required|date',
                'date_etd_updated'        => 'required|date',
            ],

            5 => [
                'date_atd'         => 'required|date',
                'date_eta'         => 'required|date',
                'date_eta_updated' => 'required|date',
                'container_number' => 'nullable|required_without_all:tracking_id,bill_of_lading|string',
                'bill_of_lading'   => 'nullable|required_without_all:tracking_id,container_number',   
                'tracking_id'      => 'nullable|required_without_all:container_number,bill_of_lading|string',
                'shipping_line'    => 'required|string',
                'departure_port'   => 'required|string',
                'arrival_port'     => 'required|string',
                // 'container_type' no está como requerido en el Excel
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

    private function validateStageRequirements(int $stage): void
    {
        $rules = $this->requiredRulesByStage()[$stage] ?? [];

        if (empty($rules)) {
            return; // no hay requeridos para esta etapa
        }

        $messages = [
            'required' => 'El campo es requerido.',
            'required_without_all' => 'Debe proporcionar al menos uno: Número de Booking, MBL o Número de Contenedor.',
            'date'     => 'El campo debe ser una fecha válida.',
            'string'   => 'El campo debe ser texto.',
            'numeric'  => 'El campo debe ser numérico.',
        ];

        $this->validate($rules, $messages, $this->fieldAttributeLabels());
    }

}



