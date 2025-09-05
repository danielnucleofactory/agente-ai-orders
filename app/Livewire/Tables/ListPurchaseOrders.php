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
        'actions' => true,
        'updated_at' => true,
    ];

    // NUEVO: control del modal de confirmación
    public ?int $confirmingDeleteId = null;
    public ?string $confirmingDeleteOrderNumber = null;

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

    public function render()
    {
        $purchaseOrders = PurchaseOrder::query()
            ->withoutTrashed() // 👈 añade esto
            ->when($this->search, function ($query) {
                $searchTerm = strtolower($this->search);
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(order_number) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(CAST(vendor_id AS TEXT)) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $searchTerm . '%']);
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tables.list-purchase-orders', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }
}
