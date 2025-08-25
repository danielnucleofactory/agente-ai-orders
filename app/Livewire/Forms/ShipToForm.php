<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\ShipTo;
use Illuminate\Support\Facades\Auth;

class ShipToForm extends Component
{
    public $shipToId;
    public $title;
    public $subtitle;
    public $isEdit = false;

    // Propiedades correspondientes a los campos de la tabla
    public $company_id;
    public $name;
    public $email;
    public $contact_person;
    public $address;
    public $postal_code;
    public $country;
    public $state;
    public $phone;
    public $status = 'active';
    public $notes;

    // Propiedades para controlar los modales
    public $showSuccessModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'contact_person' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:255',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->shipToId = $id;
            $this->isEdit = true;
            $this->title = 'Editar Dirección de Envío';
            $this->subtitle = 'Modificar información de la dirección de envío';

            $shipTo = ShipTo::findOrFail($id);

            $this->company_id = $shipTo->company_id;
            $this->name = $shipTo->name;
            $this->email = $shipTo->email;
            $this->contact_person = $shipTo->contact_person;
            $this->address = $shipTo->address;
            $this->postal_code = $shipTo->postal_code;
            $this->country = $shipTo->country;
            $this->state = $shipTo->state;
            $this->phone = $shipTo->phone;
            $this->status = $shipTo->status;
            $this->notes = $shipTo->notes;

        } else {
            $this->title = 'Nueva Dirección de Envío';
            $this->subtitle = 'Crear una nueva dirección de envío';

            // Inicializar con la compañía del usuario actual si es aplicable
            // Esto depende de tu lógica de negocio específica
            $this->company_id = Auth::user()->company_id ?? null;
        }
    }

    public function saveShipTo()
    {
        $this->validate();

        if ($this->isEdit) {
            $shipTo = ShipTo::findOrFail($this->shipToId);
        } else {
            $shipTo = new ShipTo();
        }

        $shipTo->company_id = $this->company_id;
        $shipTo->name = $this->name;
        $shipTo->email = $this->email;
        $shipTo->contact_person = $this->contact_person;
        $shipTo->address = $this->address;
        $shipTo->postal_code = $this->postal_code;
        $shipTo->country = $this->country;
        $shipTo->state = $this->state;
        $shipTo->phone = $this->phone;
        $shipTo->status = $this->status;
        $shipTo->notes = $this->notes;

        $shipTo->save();

        // Mostrar modal de éxito
        if ($this->isEdit) {
            $this->dispatch('open-modal', 'modal-ship-to-updated');
        } else {
            $this->dispatch('open-modal', 'modal-ship-to-created');
        }
    }

    public function closeModal()
    {
        return redirect()->route('ship-to.index');
    }

    public function render()
    {
        return view('livewire.forms.ship-to-form');
    }
}
