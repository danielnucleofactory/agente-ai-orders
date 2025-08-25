<?php

namespace App\Livewire\ShippingDocumentation;

use App\Models\Hub;
use App\Models\PurchaseOrder;
use App\Models\ShippingDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class ShippingDocumentationFilter extends Component
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

    public $filtersApplied = false;
    public $filterCount = 0;

    protected $listeners = [
        'refreshFilters' => 'loadFilterOptions',
        'clearShippingDocumentationFilters' => 'resetFilters'
    ];

    public function mount()
    {
        // Cargar las opciones de filtro iniciales
        $this->loadFilterOptions();

        // Restaurar filtros de la sesión
        $this->restoreFiltersFromSession();
    }

    public function loadFilterOptions()
    {
        // Obtener documentos de envío con sus órdenes de compra asociadas
        $shippingDocs = ShippingDocument::with(['purchaseOrders'])->get();

        // Inicializar arrays para los valores únicos
        $currencies = [];
        $incoterms = [];
        $plannedHubs = [];
        $actualHubs = [];
        $materialTypes = [];

        // Extraer valores únicos de las órdenes de compra asociadas a los documentos
        foreach ($shippingDocs as $doc) {
            foreach ($doc->purchaseOrders as $po) {
                // Moneda
                if ($po->currency && !in_array($po->currency, $currencies)) {
                    $currencies[] = $po->currency;
                }

                // Incoterms
                if ($po->incoterms && !in_array($po->incoterms, $incoterms)) {
                    $incoterms[] = $po->incoterms;
                }

                // Hub Planificado (ID)
                if ($po->planned_hub_id && !in_array($po->planned_hub_id, $plannedHubs)) {
                    $plannedHubs[] = $po->planned_hub_id;
                }

                // Hub Real (ID)
                if ($po->actual_hub_id && !in_array($po->actual_hub_id, $actualHubs)) {
                    $actualHubs[] = $po->actual_hub_id;
                }

                // Tipo de Material (puede ser array o string)
                if ($po->material_type) {
                    if (is_array($po->material_type)) {
                        foreach ($po->material_type as $type) {
                            if (!in_array($type, $materialTypes)) {
                                $materialTypes[] = $type;
                            }
                        }
                    } elseif (!in_array($po->material_type, $materialTypes)) {
                        $materialTypes[] = $po->material_type;
                    }
                }
            }
        }

        // Obtener información de los hubs para mostrar nombres en lugar de IDs
        $hubData = Hub::whereIn('id', array_merge($plannedHubs, $actualHubs))->get()->keyBy('id');

        // Transformar IDs a pares ID => Nombre para los hubs
        $plannedHubOptions = [];
        foreach ($plannedHubs as $hubId) {
            if (isset($hubData[$hubId])) {
                $plannedHubOptions[$hubId] = $hubData[$hubId]->name;
            }
        }

        $actualHubOptions = [];
        foreach ($actualHubs as $hubId) {
            if (isset($hubData[$hubId])) {
                $actualHubOptions[$hubId] = $hubData[$hubId]->name;
            }
        }

        // Ordenar valores
        sort($currencies);
        sort($incoterms);
        sort($materialTypes);
        asort($plannedHubOptions);
        asort($actualHubOptions);

        // Asignar a las propiedades del componente
        $this->currencies = $currencies;
        $this->incoterms = $incoterms;
        $this->plannedHubs = $plannedHubOptions;
        $this->actualHubs = $actualHubOptions;
        $this->materialTypes = $materialTypes;
    }

    public function applyFilters()
    {
        $this->filtersApplied = $this->hasActiveFilters();
        $this->updateFilterCount();
        $this->saveFiltersToSession();

        // Emitir evento para que el KanbanBoard actualice sus datos
        $this->dispatch('shippingDocumentFiltersChanged', $this->getActiveFilters());
    }

    public function resetFilters()
    {
        $this->selectedCurrency = null;
        $this->selectedIncoterm = null;
        $this->selectedPlannedHub = null;
        $this->selectedActualHub = null;
        $this->selectedMaterialType = null;

        $this->filtersApplied = false;
        $this->filterCount = 0;

        // Limpiar filtros de sesión
        Session::forget('shipping_document_filters');

        // Emitir evento para que el KanbanBoard actualice sus datos
        $this->dispatch('shippingDocumentFiltersChanged', []);
    }

    protected function hasActiveFilters()
    {
        return $this->selectedCurrency ||
               $this->selectedIncoterm ||
               $this->selectedPlannedHub ||
               $this->selectedActualHub ||
               $this->selectedMaterialType;
    }

    protected function updateFilterCount()
    {
        $this->filterCount = 0;

        if ($this->selectedCurrency) $this->filterCount++;
        if ($this->selectedIncoterm) $this->filterCount++;
        if ($this->selectedPlannedHub) $this->filterCount++;
        if ($this->selectedActualHub) $this->filterCount++;
        if ($this->selectedMaterialType) $this->filterCount++;
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

        return $filters;
    }

    protected function saveFiltersToSession()
    {
        Session::put('shipping_document_filters', [
            'currency' => $this->selectedCurrency,
            'incoterm' => $this->selectedIncoterm,
            'planned_hub' => $this->selectedPlannedHub,
            'actual_hub' => $this->selectedActualHub,
            'material_type' => $this->selectedMaterialType,
        ]);
    }

    protected function restoreFiltersFromSession()
    {
        if (Session::has('shipping_document_filters')) {
            $filters = Session::get('shipping_document_filters');

            $this->selectedCurrency = $filters['currency'] ?? null;
            $this->selectedIncoterm = $filters['incoterm'] ?? null;
            $this->selectedPlannedHub = $filters['planned_hub'] ?? null;
            $this->selectedActualHub = $filters['actual_hub'] ?? null;
            $this->selectedMaterialType = $filters['material_type'] ?? null;

            if ($this->hasActiveFilters()) {
                $this->filtersApplied = true;
                $this->updateFilterCount();
                $this->applyFilters();
            }
        }
    }

    public function render()
    {
        return view('livewire.shipping-documentation.shipping-documentation-filter');
    }
}
