<?php

namespace App\Livewire\Kanban;

use App\Exports\ActivePurchaseOrdersExport;
use App\Exports\InternalTransitReportExport;
use App\Services\InternalTransitReportService;
use App\Support\SelectOptions;
use App\Models\Hub;
use App\Models\PurchaseOrder;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KanbanFilters extends Component
{
    public $currencies = [];
    public $incoterms = [];
    public $plannedHubs = [];
    public $actualHubs = [];
    public $materialTypes = [];

    public $selectedCurrency = null;
    public $selectedIncoterm = null;
    public $selectedPlannedHub = null;
    public $selectedActualHub = null;
    public $selectedMaterialType = null;
    public $ataDateFrom = null;
    public $ataDateTo = null;

    // Nuevo filtro de búsqueda por texto
    public $searchText = '';

    public $filtersApplied = false;
    public $filterCount = 0;

    protected $listeners = [
        'refreshFilters' => 'loadFilterOptions',
        'clearKanbanFilters' => 'resetFilters'
    ];

    public function mount()
    {
        // Cargar las opciones de filtro iniciales
        $this->loadFilterOptions();

        // Limpiar filtros de sesión al montar para que no persistan después de refrescar la página
        Session::forget('kanban_filters');

        // NO restaurar filtros de la sesión para que no persistan después de refrescar la página
        // Los filtros solo se mantienen durante la sesión activa, pero se limpian al refrescar
        // Si se necesita restaurar filtros, se puede hacer manualmente con el botón de aplicar
    }

    public function loadFilterOptions()
    {
        // Obtener opciones únicas de la base de datos
        $purchaseOrders = PurchaseOrder::select('currency', 'incoterms', 'planned_hub_id', 'actual_hub_id', 'material_type')
            ->where('company_id', auth()->user()->company_id)
            ->get();

        // Extraer valores únicos para cada filtro
        $this->currencies = $purchaseOrders->pluck('currency')->filter()->unique()->values()->toArray();
        $this->incoterms = $purchaseOrders->pluck('incoterms')->filter()->unique()->values()->toArray();
        sort($this->currencies, SORT_NATURAL | SORT_FLAG_CASE);
        sort($this->incoterms, SORT_NATURAL | SORT_FLAG_CASE);

        // Obtener los hubs planificados y reales
        $plannedHubIds = $purchaseOrders->pluck('planned_hub_id')->filter()->unique()->values()->toArray();
        $actualHubIds = $purchaseOrders->pluck('actual_hub_id')->filter()->unique()->values()->toArray();

        // Cargar nombres de hubs
        $hubs = Hub::whereIn('id', array_merge($plannedHubIds, $actualHubIds))->get();

        $this->plannedHubs = $hubs->whereIn('id', $plannedHubIds)
            ->pluck('name', 'id')
            ->toArray();
        asort($this->plannedHubs, SORT_NATURAL | SORT_FLAG_CASE);

        $this->actualHubs = $hubs->whereIn('id', $actualHubIds)
            ->pluck('name', 'id')
            ->toArray();
        asort($this->actualHubs, SORT_NATURAL | SORT_FLAG_CASE);

                                        // Hardcoded types based on database analysis - más confiable que parsing dinámico
        // Basado en los valores reales encontrados: Standard, dangerous, estibable, exclusive, general
        $this->materialTypes = [
            'dangerous',
            'estibable',
            'exclusive',
            'general',
            'Standard'
        ];

        // También intentar cargar dinámicamente como respaldo
        $dynamicTypes = [];
        foreach ($purchaseOrders as $po) {
            $materialType = $po->material_type;

            if (!empty($materialType)) {
                if (is_array($materialType)) {
                    foreach ($materialType as $type) {
                        if (!empty($type) && is_string($type)) {
                            $dynamicTypes[] = trim($type);
                        }
                    }
                } elseif (is_string($materialType)) {
                    $decoded = json_decode($materialType, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $type) {
                            if (!empty($type) && is_string($type)) {
                                $dynamicTypes[] = trim($type);
                            }
                        }
                    } else {
                        $dynamicTypes[] = trim($materialType);
                    }
                }
            }
        }

        // Combinar tipos hardcoded con dinámicos
        if (!empty($dynamicTypes)) {
            $this->materialTypes = array_unique(array_merge($this->materialTypes, $dynamicTypes));
        }

        $this->currencies = SelectOptions::sortList($this->currencies);
        $this->incoterms = SelectOptions::sortList($this->incoterms);
        $this->plannedHubs = SelectOptions::sortAssociative($this->plannedHubs);
        $this->actualHubs = SelectOptions::sortAssociative($this->actualHubs);
        $this->materialTypes = SelectOptions::sortList($this->materialTypes);
    }

    public function applyFilters()
    {
        $this->filtersApplied = $this->hasActiveFilters();
        $this->updateFilterCount();
        // NO guardar en sesión para que no persistan después de refrescar la página
        // $this->saveFiltersToSession();

        // Emitir evento para que el KanbanBoard actualice sus datos
        $this->dispatch('kanbanFiltersChanged', $this->getActiveFilters());
    }

    public function resetFilters()
    {
        $this->selectedCurrency = null;
        $this->selectedIncoterm = null;
        $this->selectedPlannedHub = null;
        $this->selectedActualHub = null;
        $this->selectedMaterialType = null;
        $this->ataDateFrom = null;
        $this->ataDateTo = null;
        $this->searchText = '';

        $this->filtersApplied = false;
        $this->filterCount = 0;

        // Limpiar filtros de sesión (por si acaso quedó algo)
        Session::forget('kanban_filters');

        // Emitir evento para que el KanbanBoard actualice sus datos
        $this->dispatch('kanbanFiltersChanged', []);
    }

    protected function hasActiveFilters()
    {
        return $this->selectedCurrency ||
               $this->selectedIncoterm ||
               $this->selectedPlannedHub ||
               $this->selectedActualHub ||
               $this->selectedMaterialType ||
               $this->ataDateFrom ||
               $this->ataDateTo ||
               !empty(trim($this->searchText));
    }

    protected function updateFilterCount()
    {
        $this->filterCount = 0;

        if ($this->selectedCurrency) $this->filterCount++;
        if ($this->selectedIncoterm) $this->filterCount++;
        if ($this->selectedPlannedHub) $this->filterCount++;
        if ($this->selectedActualHub) $this->filterCount++;
        if ($this->selectedMaterialType) $this->filterCount++;
        if ($this->ataDateFrom) $this->filterCount++;
        if ($this->ataDateTo) $this->filterCount++;
        if (!empty(trim($this->searchText))) $this->filterCount++;
    }

    protected function getActiveFilters()
    {
        $filters = [];

        if ($this->selectedCurrency) {
            $filters['currency'] = $this->selectedCurrency;
        }

        if ($this->selectedIncoterm) {
            $filters['incoterms'] = $this->selectedIncoterm;
        }

        if ($this->selectedPlannedHub) {
            $filters['planned_hub_id'] = $this->selectedPlannedHub;
        }

        if ($this->selectedActualHub) {
            $filters['actual_hub_id'] = $this->selectedActualHub;
        }

        if ($this->selectedMaterialType) {
            $filters['material_type'] = $this->selectedMaterialType;
        }

        if ($this->ataDateFrom) {
            $filters['date_from'] = $this->ataDateFrom;
        }

        if ($this->ataDateTo) {
            $filters['date_to'] = $this->ataDateTo;
        }

        if (!empty(trim($this->searchText))) {
            $filters['search_text'] = trim($this->searchText);
        }

        return $filters;
    }

    protected function saveFiltersToSession()
    {
        Session::put('kanban_filters', [
            'currency' => $this->selectedCurrency,
            'incoterm' => $this->selectedIncoterm,
            'planned_hub' => $this->selectedPlannedHub,
            'actual_hub' => $this->selectedActualHub,
            'material_type' => $this->selectedMaterialType,
            'ata_date_from' => $this->ataDateFrom,
            'ata_date_to' => $this->ataDateTo,
            'search_text' => $this->searchText,
        ]);
    }

    protected function restoreFiltersFromSession()
    {
        if (Session::has('kanban_filters')) {
            $filters = Session::get('kanban_filters');

            $this->selectedCurrency = $filters['currency'] ?? null;
            $this->selectedIncoterm = $filters['incoterm'] ?? null;
            $this->selectedPlannedHub = $filters['planned_hub'] ?? null;
            $this->selectedActualHub = $filters['actual_hub'] ?? null;
            $this->selectedMaterialType = $filters['material_type'] ?? null;
            $this->ataDateFrom = $filters['ata_date_from'] ?? null;
            $this->ataDateTo = $filters['ata_date_to'] ?? null;
            $this->searchText = $filters['search_text'] ?? '';

            if ($this->hasActiveFilters()) {
                $this->filtersApplied = true;
                $this->updateFilterCount();
                $this->dispatch('kanbanFiltersChanged', $this->getActiveFilters());
            }
        }
    }

    public function downloadActivePOs(): BinaryFileResponse
    {
        $companyId = auth()->user()->company_id ?? 0;
        $filters   = $this->getActiveFilters();

        return Excel::download(
            new ActivePurchaseOrdersExport($companyId, $filters),
            'pos_activas_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function downloadInternalTransitReport(InternalTransitReportService $service): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('access-raga-transit-report'), 403);

        $companyId = auth()->user()->company_id ?? 0;
        $filters = $this->getActiveFilters();
        $report = $service->build($companyId, [
            'search' => $filters['search_text'] ?? null,
            'vendor' => null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'trading_company' => null,
        ]);

        $from = $filters['date_from'] ?? 'all';
        $to = $filters['date_to'] ?? 'all';

        return Excel::download(
            new InternalTransitReportExport($report),
            'reporte_transito_ata_' . str_replace('-', '', (string) $from) . '_a_' . str_replace('-', '', (string) $to) . '_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.kanban.kanban-filters');
    }
}
