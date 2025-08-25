<?php

namespace App\Livewire\Ui;

use Livewire\Component;
use App\Models\PurchaseOrder;

class CounterPo extends Component {
    public $poInTransit;
    public $poConsolidated;
    public $poFinished;

    public function mount()
    {
        $this->updateCounters();
    }

    protected function getListeners()
    {
        return [
            'purchaseOrderStatusUpdated' => 'updateCounters',
            'refresh' => '$refresh'
        ];
    }

    public function updateCounters()
    {
        // Contador de PO en tránsito
        $this->poInTransit = PurchaseOrder::whereHas('kanbanStatus', function($query) {
            $query->whereIn('name', [
                'En tránsito terrestre',
                'Llegada al hub',
                'Validación operativa con el cliente',
                'Consolidación en Hub real',
                'Gestión documental'
            ]);
        })->count();

        // Contador de PO que están en documentos de embarque
        $this->poConsolidated = PurchaseOrder::whereHas('shippingDocuments')->count();

        // Contador de PO en estados finales
        $this->poFinished = PurchaseOrder::whereHas('kanbanStatus', function($query) {
            $query->whereIn('name', [
                'Liberación/entrega y Facturación',
                'Archivado'
            ]);
        })->count();
    }

    public function render() {
        return view('livewire.ui.counter-po');
    }
}
