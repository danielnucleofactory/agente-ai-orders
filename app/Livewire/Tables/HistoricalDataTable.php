<?php

namespace App\Livewire\Tables;

use App\Models\HistoricalPurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricalDataTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public string $sortField = 'emision_date_po';
    public string $sortDirection = 'desc';
    public $filters = [
        'vendor' => '',
        'date_from' => '',
        'date_to' => '',
        'trading_company' => '',
    ];

    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []],
        'perPage' => ['except' => 25],
        'sortField' => ['except' => 'emision_date_po'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'refreshTable' => '$refresh'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        \Log::debug('HistoricalDataTable search updated', [
            'search' => $this->search,
        ]);
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowed = [
            'order_number',
            'vendor_name',
            'emision_date_po',
            'net_total',
            'container_number',
            'date_etd',
            'date_eta',
            'trading_company',
        ];

        if (!in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = HistoricalPurchaseOrder::query();

        // Aplicar búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('vendor_name', 'like', '%' . $this->search . '%')
                  ->orWhere('container_number', 'like', '%' . $this->search . '%')
                  ->orWhere('mbl_number', 'like', '%' . $this->search . '%');
            });
        }

        // Aplicar filtros
        if (!empty($this->filters['vendor'])) {
            $query->where('vendor_id', $this->filters['vendor']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('emision_date_po', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('emision_date_po', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['trading_company'])) {
            $query->where('trading_company', $this->filters['trading_company']);
        }

        // Ordenamiento (allowlist + tie-breaker estable)
        $sortField = $this->sortField ?: 'emision_date_po';
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = [
            'order_number' => 'order_number',
            'vendor_name' => 'vendor_name',
            'emision_date_po' => 'emision_date_po',
            'net_total' => 'net_total',
            'container_number' => 'container_number',
            'date_etd' => 'date_etd',
            'date_eta' => 'date_eta',
            'trading_company' => 'trading_company',
        ];
        $column = $allowedSorts[$sortField] ?? 'emision_date_po';

        $historicalData = $query
            ->orderBy($column, $sortDir)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        // Log para debugging (remover en producción si es necesario)
        \Log::debug('HistoricalDataTable render', [
            'total' => $historicalData->total(),
            'count' => $historicalData->count(),
            'current_page' => $historicalData->currentPage(),
            'search' => $this->search,
            'filters' => $this->filters,
        ]);

        // Obtener opciones para filtros
        $vendors = HistoricalPurchaseOrder::select('vendor_id', 'vendor_name')
            ->whereNotNull('vendor_id')
            ->distinct()
            ->orderBy('vendor_name')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->vendor_id => $item->vendor_name];
            });

        $tradingCompanies = HistoricalPurchaseOrder::select('trading_company')
            ->whereNotNull('trading_company')
            ->distinct()
            ->orderBy('trading_company')
            ->pluck('trading_company');

        return view('livewire.tables.historical-data-table', [
            'historicalData' => $historicalData,
            'vendors' => $vendors,
            'tradingCompanies' => $tradingCompanies,
        ]);
    }
}

