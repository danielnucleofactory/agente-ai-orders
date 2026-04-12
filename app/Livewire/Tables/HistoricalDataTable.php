<?php

namespace App\Livewire\Tables;

use App\Livewire\Components\ReusableTable;
use App\Models\HistoricalPurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HistoricalDataTable extends ReusableTable
{
    public $filters = [
        'vendor' => '',
        'date_from' => '',
        'date_to' => '',
        'trading_company' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => []],
        'perPage' => ['except' => 25],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $listeners = [
        'refreshTable' => '$refresh',
    ];

    public function mount(
        $headers = [],
        $sortable = [],
        $searchable = [],
        $filterable = [],
        $filterOptions = [],
        $withCount = [],
        $model = null,
        $rows = [],
        $relationColumns = [],
        $actions = false,
        $baseRoute = '',
        $routeKeyName = 'id',
        $actionsView = true,
        $actionsEdit = true,
        $actionsDelete = true,
        $viewPermission = null,
        $editPermission = null,
        $deletePermission = null,
        $showSelectColumn = false,
        $sortFieldAliases = [],
        $customActionsView = null,
        $maestroCatalogKey = null
    ): void {
        parent::mount(
            headers: [
                'order_number' => 'Orden',
                'vendor_name' => 'Proveedor',
                'emision_date_po' => 'Fecha emisión',
                'net_total' => 'Total neto',
                'container_number' => 'Contenedor',
                'date_etd' => 'ETD',
                'date_eta' => 'ETA',
                'trading_company' => 'Empresa',
            ],
            sortable: [],
            searchable: [],
            filterable: [],
            filterOptions: [],
            model: null,
            rows: [],
            actions: false,
        );

        $this->useModel = false;
        $this->showSearch = false;
        $this->showPerPage = true;
        $this->perPage = 25;
        $this->sortField = 'emision_date_po';
        $this->sortDirection = 'desc';
        $this->emptyMessage = 'No se encontraron registros históricos';
    }

    public function getProcessedRowsProperty(): LengthAwarePaginator
    {
        $query = HistoricalPurchaseOrder::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                    ->orWhere('vendor_name', 'like', '%' . $this->search . '%')
                    ->orWhere('container_number', 'like', '%' . $this->search . '%')
                    ->orWhere('mbl_number', 'like', '%' . $this->search . '%');
            });
        }

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

        $dir = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'desc';
        $col = $this->sortField;
        $allowed = ['order_number', 'vendor_name', 'emision_date_po', 'net_total', 'container_number', 'date_etd', 'date_eta', 'trading_company'];
        if (!in_array($col, $allowed, true)) {
            $col = 'emision_date_po';
            $dir = 'desc';
        }

        $query->orderBy($col, $dir)->orderBy('id', 'desc');

        return $query->paginate($this->perPage)->through(function (HistoricalPurchaseOrder $record) {
            $vendor = e($record->vendor_name ?? '');
            $net = $record->net_total
                ? number_format((float) $record->net_total, 2) . ' ' . e($record->currency ?? '')
                : 'N/A';
            $emision = $record->emision_date_po ? formatDateOnly($record->emision_date_po) : 'N/A';
            $etd = $record->date_etd ? formatDate($record->date_etd) : 'N/A';
            $eta = $record->date_eta ? formatDate($record->date_eta) : 'N/A';

            return [
                'id' => $record->id,
                'order_number' => $record->order_number,
                'order_number_html' => '<span class="whitespace-nowrap font-medium text-gray-900">' . e($record->order_number) . '</span>',
                'vendor_name' => $record->vendor_name,
                'vendor_name_html' => '<div class="max-w-xs truncate font-dm-sans text-sm text-[#2E2E2E]" title="' . $vendor . '">' . $vendor . '</div>',
                'emision_date_po' => $emision,
                'net_total' => $net,
                'container_number' => e($record->container_number ?? 'N/A'),
                'date_etd' => $etd,
                'date_eta' => $eta,
                'trading_company' => e($record->trading_company ?? 'N/A'),
            ];
        });
    }

    public function render()
    {
        $vendors = HistoricalPurchaseOrder::query()
            ->select('vendor_id', 'vendor_name')
            ->whereNotNull('vendor_id')
            ->distinct()
            ->orderBy('vendor_name')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->vendor_id => $item->vendor_name]);

        $tradingCompanies = HistoricalPurchaseOrder::query()
            ->select('trading_company')
            ->whereNotNull('trading_company')
            ->distinct()
            ->orderBy('trading_company')
            ->pluck('trading_company');

        return view('livewire.tables.historical-data-table', [
            'processedRows' => $this->getProcessedRowsProperty(),
            'vendors' => $vendors,
            'tradingCompanies' => $tradingCompanies,
        ]);
    }
}
