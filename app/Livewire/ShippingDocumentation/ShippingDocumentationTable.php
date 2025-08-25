<?php

namespace App\Livewire\ShippingDocumentation;

use App\Models\ShippingDocument;
use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class ShippingDocumentationTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $sortField = 'creation_date';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $visibleColumns = [
        'document_number' => true,
        'purchase_orders' => true,
        'status' => true,
        'modality' => true,
        'incoterms' => true,
        'vendor' => true,
        'hub' => true,
        'actions' => true,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'creation_date'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
    ];

    protected $listeners = ['refresh' => '$refresh'];

    protected $updatesQueryString = ['search', 'sortField', 'sortDirection', 'statusFilter'];

    public function mount()
    {
        $this->search = request()->query('search', '');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

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

    /**
     * Get the shipping documents query
     */
    private function getShippingDocumentsQuery()
    {
        $query = ShippingDocument::query()
            ->with(['purchaseOrders', 'company'])
            ->when($this->search, function ($query) {
                $query->where('document_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('purchaseOrders', function ($query) {
                        $query->where('order_number', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            });

        // Handle sorting
        if ($this->sortField === 'creation_date') {
            $query->orderBy('creation_date', $this->sortDirection);
        } elseif ($this->sortField === 'document_number') {
            $query->orderBy('document_number', $this->sortDirection);
        } elseif ($this->sortField === 'status') {
            $query->orderBy('status', $this->sortDirection);
        } elseif ($this->sortField === 'weight_kg') {
            $query->orderBy('total_weight_kg', $this->sortDirection);
        } else {
            // Default sorting
            $query->orderBy('creation_date', 'desc');
        }

        return $query;
    }

    public function render()
    {
        $shippingDocuments = $this->getShippingDocumentsQuery()->paginate($this->perPage);

        // Group purchase orders by shipping document
        $groupedPurchaseOrders = [];
        foreach ($shippingDocuments as $document) {
            $groupedPurchaseOrders[$document->id] = $document->purchaseOrders;
        }

        return view('livewire.shipping-documentation.shipping-documentation-table', [
            'shippingDocuments' => $shippingDocuments,
            'groupedPurchaseOrders' => $groupedPurchaseOrders
        ]);
    }
}
