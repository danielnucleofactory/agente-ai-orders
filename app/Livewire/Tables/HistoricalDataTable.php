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
    ];

    protected $listeners = [
        'refreshTable' => '$refresh'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
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

        // Ordenar por fecha de emisión descendente
        $historicalData = $query->orderBy('emision_date_po', 'desc')
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

