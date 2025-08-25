<?php

namespace App\Livewire\Forms;

use App\Models\Hub;
use Livewire\Component;

class HubForm extends Component {

    public $hub;
    public $title;
    public $subtitle;

    public $code;
    public $name;
    public $country;
    public $documentary_cut;
    public $zarpe;

    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'documentary_cut' => 'required|string|max:255',
        'zarpe' => 'required|string|max:255',
    ];

    public function mount($hub = null) {
        $this->isEdit = $hub !== null;

        if ($this->isEdit) {
            $this->hub = $hub;
            $this->code = $hub->code;
            $this->name = $hub->name;
            $this->country = $hub->country;
            $this->documentary_cut = $hub->documentary_cut;
            $this->zarpe = $hub->zarpe;

            if (!$this->title) {
                $this->title = 'Editar Hub';
                $this->subtitle = 'Edite los datos del hub';
            }
        } else {
            if (!$this->title) {
                $this->title = 'Nuevo Hub';
                $this->subtitle = 'Ingrese los datos para crear un nuevo hub';
            }
        }
    }

    public function saveHub() {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'documentary_cut' => 'required|string|max:255',
            'zarpe' => 'required|string|max:255',
        ], [
            'name.required' => 'El nombre es requerido',
            'code.required' => 'El código es requerido',
            'country.required' => 'El país es requerido',
            'documentary_cut.required' => 'El corte documental es requerido',
            'zarpe.required' => 'El zarpe es requerido',
        ]);

        $hubData = [
            'name' => $this->name,
            'code' => $this->code,
            'country' => $this->country,
            'documentary_cut' => $this->documentary_cut,
            'zarpe' => $this->zarpe,
        ];

        if ($this->isEdit) {
            $this->hub->update($hubData);
            session()->flash('message', 'Hub actualizado correctamente.');
            $this->dispatch('open-modal', 'modal-hub-updated');
        } else {
            $hubData['company_id'] = auth()->user()->company_id;
            $this->hub = Hub::create($hubData);
            session()->flash('message', 'Hub creado correctamente.');
            $this->dispatch('open-modal', 'modal-hub-created');
        }
    }

    public function closeModal() {
        $this->dispatch('close-modal', 'modal-hub-created');
        $this->dispatch('close-modal', 'modal-hub-updated');
        return redirect()->route('hub.index');
    }

    public function backToList() {
        return redirect()->route('hub.index');
    }

    public function render() {
        return view('livewire.forms.hub-form');
    }
}
