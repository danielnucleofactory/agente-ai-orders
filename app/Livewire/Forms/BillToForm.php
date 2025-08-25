<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\BillTo;
use Illuminate\Support\Facades\Auth;

class BillToForm extends Component
{
    public $billToId;
    public $title;
    public $subtitle;
    public $isEdit = false;

    // Propiedades para el formulario
    public $name;
    public $email;
    public $contact_person;
    public $direccion;
    public $codigo_postal;
    public $pais;
    public $estado;
    public $telefono;
    public $status = 'active';
    public $notes;

    // Propiedades para controlar los modales
    public $showSuccessModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    /**
     * Mount the component with optional BillTo model
     */
    public function mount($billTo = null)
    {
        if ($billTo) {
            $this->billToId = $billTo->id;
            $this->name = $billTo->name;
            $this->email = $billTo->email;
            $this->contact_person = $billTo->contact_person;
            $this->direccion = $billTo->address;
            $this->codigo_postal = $billTo->postal_code;
            $this->pais = $billTo->country;
            $this->estado = $billTo->state;
            $this->telefono = $billTo->phone;
            $this->notes = $billTo->notes;
            $this->isEdit = true;
            $this->company_id = auth()->user()->company_id;

            $this->title = 'Editar Dirección de Facturación';
            $this->subtitle = 'Actualiza los datos de la dirección de facturación';
        } else {
            $this->title = 'Nueva Dirección de Facturación';
            $this->subtitle = 'Ingresa los datos para crear una nueva dirección de facturación';
        }
    }

    /**
     * Save or update the BillTo model
     */
    public function saveBillTo()
    {
        $this->validate();

        $data = [
            'company_id' => auth()->user()->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'contact_person' => $this->contact_person,
            'address' => $this->direccion,
            'postal_code' => $this->codigo_postal,
            'country' => $this->pais,
            'state' => $this->estado,
            'phone' => $this->telefono,
            'status' => 'active',
            'notes' => $this->notes,
        ];


        if ($this->isEdit) {
            $billTo = BillTo::findOrFail($this->billToId);
            $billTo->update($data);
        } else {
            BillTo::create($data);
        }

        // Mostrar modal de éxito
        if ($this->isEdit) {
            $this->dispatch('open-modal', 'modal-bill-to-updated');
        } else {
            $this->dispatch('open-modal', 'modal-bill-to-created');
        }
    }

    public function closeModal()
    {
        return redirect()->route('bill-to.index');
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.forms.bill-to-form');
    }
}
