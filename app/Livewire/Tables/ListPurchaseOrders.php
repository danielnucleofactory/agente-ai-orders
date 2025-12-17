<?php

namespace App\Livewire\Tables;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ListPurchaseOrders extends Component
{
    use WithPagination;

    // Filtros/estado existentes
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $visibleColumns = [
        'order_number' => true,
        'vendor' => true,
        'status' => true,
        'order_date' => true,
        'total' => true,
        'route_label' => false,
        'mbl_number' => false,
        'container_number' => false,
        'customer' => false,
        'actions' => true,
        'updated_at' => true,
    ];

    // NUEVO: control del modal de confirmación
    public ?int $confirmingDeleteId = null;
    public ?string $confirmingDeleteOrderNumber = null;

    // --- propiedades de confirmación para restauración de una PO ---
    public bool $showConfirmModal = false;
    public ?string $confirmMode = null;          // 'restore' o 'delete'
    public ?int $confirmId = null;
    public ?string $confirmOrderNumber = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at', 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
    ];

    // === Acciones UI ===
    public function toggleColumn($columnName)
    {
        if (isset($this->visibleColumns[$columnName])) {
            $this->visibleColumns[$columnName] = !$this->visibleColumns[$columnName];
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()      { $this->resetPage(); }
    public function updatingStatusFilter(){ $this->resetPage(); }
    public function updatingPerPage()     { $this->resetPage(); }
    public function previousPage()        { $this->setPage($this->getPage() - 1); }
    public function nextPage()            { $this->setPage($this->getPage() + 1); }
    public function gotoPage($page)       { $this->setPage($page); }

    // === NUEVO: flujo de borrado ===
    public function confirmDelete(int $id): void
    {
        $po = PurchaseOrder::query()->select('id','order_number')->findOrFail($id);
        $this->confirmingDeleteId = $po->id;
        $this->confirmingDeleteOrderNumber = (string) $po->order_number;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteOrderNumber = null;
    }

    public function deleteConfirmed(): void
    {
        if (!$this->confirmingDeleteId) return;

        DB::transaction(function () {
            $po = PurchaseOrder::findOrFail($this->confirmingDeleteId);
            $po->delete(); // Soft delete
        });

        // 👇 ESTA LÍNEA dispara el refresh del kanban
        $this->dispatch('refreshKanban');

        session()->flash('message', 'Orden de compra eliminada.');

        $this->cancelDelete();
        $this->resetPage();
    }

    public function getAvailableKanbanStatuses()
    {
        $companyId = auth()->user()->company_id ?? null;
        
        if (!$companyId) {
            return \App\Models\KanbanStatus::select('id', 'name')
                ->orderBy('id')
                ->get();
        }

        // Obtener solo las etapas del tablero de Purchase Orders de la empresa del usuario
        return \App\Models\KanbanStatus::whereHas('board', function ($query) use ($companyId) {
            $query->where('company_id', $companyId)
                  ->where(function($q) {
                      $q->where('type', 'po_stages')
                        ->orWhere('type', 'purchase_orders');
                  })
                  ->where('is_active', true);
        })
        ->select('id', 'name')
        ->orderBy('position')
        ->orderBy('id')
        ->get();
    }

    public function render()
    {
        $purchaseOrders = \App\Models\PurchaseOrder::query()
            ->withTrashed() // incluye activas + anuladas
            ->with(['kanbanStatus', 'billTo']) // cargar relación kanban status y billTo para cliente
            ->when($this->search, function ($query) {
                $searchTerm = strtolower($this->search);
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(order_number) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(CAST(vendor_id AS TEXT)) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $searchTerm . '%']);
                });
            })
            ->when($this->statusFilter === '__trashed', function ($q) {
                $q->onlyTrashed(); // ⬅️ muestra solo anuladas
            })
            ->when($this->statusFilter === '__no_kanban', function ($q) {
                // filtra por órdenes sin kanban status
                $q->whereNull('deleted_at')->whereNull('kanban_status_id');
            })
            ->when(str_starts_with($this->statusFilter, 'kanban_'), function ($q) {
                // filtra por kanban status específico
                $kanbanStatusId = (int) str_replace('kanban_', '', $this->statusFilter);
                // Solo aplicar el filtro si el ID es válido (mayor a 0)
                if ($kanbanStatusId > 0) {
                    $q->whereNull('deleted_at')->where('kanban_status_id', $kanbanStatusId);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tables.list-purchase-orders', [
            'purchaseOrders' => $purchaseOrders,
            'kanbanStatuses' => $this->getAvailableKanbanStatuses()
        ]);
    }

    // == Flujo de restauración de una PO ==

    public function confirmRestore(int $id): void
    {
        $po = \App\Models\PurchaseOrder::withTrashed()
            ->select('id','order_number')
            ->findOrFail($id);

        $this->confirmId = $po->id;
        $this->confirmOrderNumber = $po->order_number;
        $this->confirmMode = 'restore';
        $this->showConfirmModal = true;
    }

    // Cierra/cancela
    public function cancelConfirm(): void
    {
        $this->reset(['showConfirmModal','confirmMode','confirmId','confirmOrderNumber']);
    }

    // Click en “Restaurar” dentro del modal
    public function restoreConfirmed(): void
    {
        $id = $this->confirmId;
        $this->cancelConfirm();      // cerrar modal de inmediato
        $this->restore($id);         // reutiliza tu método restore() existente
    }
    public function restore(int $id): void
    {
        $po = PurchaseOrder::withTrashed()->findOrFail($id);

        if (! $po->trashed()) {
            session()->flash('message', 'La orden no está anulada.');
            return;
        }

        \DB::transaction(function () use ($po) {
            $po->restore(); // ← el trait SoftCascadeDeletes restaurará hijos/pivots
        });

        session()->flash('message', "Orden #{$po->order_number} restaurada con éxito.");
        $this->resetPage(); // refresca la paginación de la tabla
    }




}
