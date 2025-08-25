<?php

namespace App\Livewire\Forms;

use App\Models\Forecast;
use Carbon\Carbon;
use Livewire\Component;

class ForecastCreate extends Component {
    public $forecast;
    public $title;
    public $subtitle;

    public $release_date;
    public $material;
    public $short_text;
    public $purchase_requisition;
    public $supplying_plant;
    public $qty_real;
    public $uom_real;
    public $quantity_requested;
    public $delivery_date;
    public $unit_of_measure;
    public $plant;
    public $planned_delivery_time;
    public $mrp_controller;
    public $vendor_name;
    public $vendor_code;

    protected $rules = [
        'release_date' => 'nullable|date',
        'material' => 'nullable|string|max:255',
        'short_text' => 'nullable|string|max:255',
        'purchase_requisition' => 'nullable|string|max:255',
        'supplying_plant' => 'nullable|string|max:255',
        'qty_real' => 'nullable|numeric',
        'uom_real' => 'nullable|string|max:255',
        'quantity_requested' => 'nullable|numeric',
        'delivery_date' => 'nullable|date',
        'unit_of_measure' => 'nullable|string|max:255',
        'plant' => 'nullable|string|max:255',
        'planned_delivery_time' => 'nullable|integer',
        'mrp_controller' => 'nullable|string|max:255',
        'vendor_name' => 'nullable|string|max:255',
        'vendor_code' => 'nullable|string|max:255',
    ];

    public function mount($forecast = null, $title = 'Forecast', $subtitle = 'Información del forecast') {
        $this->title = $title;
        $this->subtitle = $subtitle;

        if ($forecast) {
            $this->forecast = $forecast;
            $this->release_date = $forecast->release_date;
            $this->material = $forecast->material;
            $this->short_text = $forecast->short_text;
            $this->purchase_requisition = $forecast->purchase_requisition;
            $this->supplying_plant = $forecast->supplying_plant;
            $this->qty_real = $forecast->qty_real;
            $this->uom_real = $forecast->uom_real;
            $this->quantity_requested = $forecast->quantity_requested;
            $this->delivery_date = Carbon::parse($forecast->delivery_date)->format('Y-m-d');
            $this->unit_of_measure = $forecast->unit_of_measure;
            $this->plant = $forecast->plant;
            $this->planned_delivery_time = $forecast->planned_delivery_time;
            $this->mrp_controller = $forecast->mrp_controller;
            $this->vendor_name = $forecast->vendor_name;
            $this->vendor_code = $forecast->vendor_code;
        }
    }

    public function createForecast() {
        $validatedData = $this->validate();

        Forecast::create($validatedData);

        $this->dispatch('open-modal', 'modal-forecast-created');
        $this->reset([
            'release_date', 'material', 'short_text', 'purchase_requisition',
            'supplying_plant', 'qty_real', 'uom_real', 'quantity_requested',
            'delivery_date', 'unit_of_measure', 'plant', 'planned_delivery_time',
            'mrp_controller', 'vendor_name', 'vendor_code'
        ]);
    }

    public function updateForecast() {
        $validatedData = $this->validate();

        $this->forecast->update($validatedData);

        $this->dispatch('open-modal', 'modal-forecast-created');
    }

    public function render() {
        return view('livewire.forms.forecast-create');
    }
}
