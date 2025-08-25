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

class KanbanBoard extends Component {
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

    public $comment_stage_01;
    public $comment_stage_02;
    public $comment_stage_03;
    public $comment_stage_04;
    public $comment_stage_05;
    public $comment_stage_06;
    public $comment_stage_07;
    public $comment_stage_08;
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

    public function mount($boardId = null) {
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

    public function loadData() {
        $this->loadColumns();
        $this->loadTasks();
        $this->organizeTasksByColumn();
    }

    public function loadColumns() {
        if (!$this->board) {
            $this->columns = [];
            return;
        }

        // Cargar las columnas (estados) del tablero
        $statuses = $this->board->statuses()->orderBy('position')->get();

        $this->columns = $statuses->map(function($status) {
            return [
                'id' => $status->id,
                'slug' => $status->slug,
                'name' => $status->name,
                'color' => $status->color,
                'position' => $status->position,
            ];
        })->toArray();
    }

    public function loadTasks() {
        if (!$this->board) {
            $this->tasks = [];
            return;
        }

        // Obtener el estado por defecto
        $defaultStatus = $this->board->statuses()->where('is_default', true)->first();

        // Obtener los IDs de los estados de este tablero
        $statusIds = collect($this->columns)->pluck('id')->toArray();

        // Cargar las órdenes de compra de la compañía del usuario
        $companyId = auth()->user()->company_id ?? null;
        $query = PurchaseOrder::with(['company', 'kanbanStatus', 'vendor'])
            ->where('company_id', $companyId);

        // Aplicar filtros si están activos
        $this->applyQueryFilters($query);

        $purchaseOrders = $query->get();

        // Limpiar el array de tareas
        $this->tasks = [];

        foreach ($purchaseOrders as $order) {
            // Si la orden no tiene un estado de Kanban asignado, asignarle el estado por defecto
            if (!$order->kanban_status_id && $defaultStatus) {
                $order->update(['kanban_status_id' => $defaultStatus->id]);
                $order->refresh();
            }

            // Si después de intentar asignar un estado, sigue sin tenerlo, o si el estado no pertenece a este tablero, continuar
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
            $query->where(function($q) use ($materialType) {
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
            $query->where(function($q) use ($searchText) {
                $q->whereRaw('LOWER(order_number) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(currency) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(incoterms) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(CAST(total AS CHAR)) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(tracking_id) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereRaw('LOWER(material_type::text) LIKE LOWER(?)', ["%{$searchText}%"])
                  ->orWhereHas('company', function($companyQuery) use ($searchText) {
                      $companyQuery->whereRaw('LOWER(name) LIKE LOWER(?)', ["%{$searchText}%"]);
                  })
                  ->orWhereHas('vendor', function($vendorQuery) use ($searchText) {
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

    public function organizeTasksByColumn() {
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
            usort($this->tasksByColumn[$columnId], function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });
        }
    }

    public function moveTask($taskId, $newStatus) {
        // Log para depuración
        \Log::info("Moving task $taskId to status $newStatus");

        try {
            // Obtener el estado anterior
            $task = PurchaseOrder::findOrFail($taskId);
            $oldStatus = $task->kanban_status_id;

            // Obtener nombres de columnas para el mensaje
            $oldColumnName = KanbanStatus::find($oldStatus)->name ?? 'desconocido';
            $newColumnName = KanbanStatus::find($newStatus)->name ?? 'desconocido';

            // Actualizar directamente en la base de datos
            DB::table('purchase_orders')
                ->where('id', $taskId)
                ->update(['kanban_status_id' => $newStatus]);

            // Crear notificación para todos los usuarios
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAll(
                'task_moved',
                'Tarea Movida',
                "La orden de compra {$task->order_number} fue movida de '{$oldColumnName}' a '{$newColumnName}' por " . auth()->user()->name,
                [
                    'task_id' => $task->id,
                    'po_number' => $task->order_number,
                    'old_status' => $oldColumnName,
                    'new_status' => $newColumnName,
                    'moved_by' => auth()->user()->name
                ]
            );

            // Log para depuración
            \Log::info("Task moved successfully");

            // Recargar los datos
            $this->loadData();

            // Limpiar los datos temporales
            $this->currentTaskId = null;
            $this->newColumnId = null;
            $this->currentTask = null;

            // Forzar la actualización de la vista
            $this->dispatch('refreshKanban');
            $this->dispatch('purchaseOrderStatusUpdated');
            $this->dispatch('notificationsUpdated');
        } catch (\Exception $e) {
            \Log::error("Error moving task: " . $e->getMessage());
        }
    }

    public function setCurrentTask($taskId, $newColumnId) {
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

    public function saveAttachment($poId) {
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

    public function setActualHubId($taskId, $hubId) {
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
    private function getOperacionName($columnId)
    {
        $operaciones = [
            1 => 'Hub Teórico',
            2 => 'Hub Teórico',
            3 => 'Validación Operativa',
            4 => 'Pickup',
            5 => 'En Tránsito',
            6 => 'Llegada a Hub',
            7 => 'Validación Operativa Cliente',
            8 => 'Consolidación Hub Real',
            9 => 'Gestión Documental'
        ];

        return $operaciones[$columnId] ?? 'Operación no especificada';
    }

    public function getCommentsWithAttachments($taskId) {
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

    public function setPickupDate($taskId, $pickupDate) {
        \Log::info("Setting pickup date for task $taskId: " . $pickupDate);

        try {
            DB::table('purchase_orders')
                ->where('id', $taskId)
                ->update(['date_actual_pickup' => $pickupDate]);
        } catch (\Exception $e) {
            \Log::error("Error setting pickup date: " . $e->getMessage());
        }
    }

    public function setTrackingId($taskId, $trackingId) {
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
}
