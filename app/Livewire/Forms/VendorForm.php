<?php

namespace App\Livewire\Forms;

use App\Models\Vendor;
use App\Support\EmailList;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class VendorForm extends Component
{
    public $vendor;
    public $vendor_id;
    public $name;
    public $vendo_code;
    public $email;
    public $contact_person;
    public $address;
    public $postal_code;
    public $country;
    public $state;
    public $phone;
    public $status;
    public $notes;
    public $isEdit = false;

    public $title;
    public $subtitle;

    public $statusOptions = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'vendo_code' => 'nullable|string|max:255',
        'email' => 'nullable|string|max:1000',
        'contact_person' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:20',
        'country' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'phone' => 'nullable|string|max:20',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ];

    public function mount($vendor = null)
    {
        $this->isEdit = $vendor !== null;

        if ($this->isEdit) {
            $this->vendor = $vendor;
            $this->vendor_id = $vendor->id;
            $this->name = $vendor->name;
            $this->vendo_code = $vendor->vendo_code;
            $this->email = $vendor->email;
            $this->contact_person = $vendor->contact_person;
            $this->address = $vendor->address;
            $this->postal_code = $vendor->postal_code;
            $this->country = $vendor->country;
            $this->state = $vendor->state;
            $this->phone = $vendor->phone;
            $this->status = $vendor->status;
            $this->notes = $vendor->notes;
            $this->title = 'Editar Proveedor';
            $this->subtitle = 'Edite los datos del proveedor';
        } else {
            $this->status = 'active';
            $this->title = 'Nuevo Proveedor';
            $this->subtitle = 'Ingrese los datos para crear un nuevo proveedor';
        }
    }

    public function saveVendor()
    {
        $this->validate();
        $this->validateEmailList();

        $normalizedEmails = EmailList::normalize($this->email);

        $vendorData = [
            'name' => $this->name,
            'vendo_code' => $this->vendo_code,
            'email' => $normalizedEmails,
            'contact_person' => $this->contact_person,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'state' => $this->state,
            'phone' => $this->phone,
            'status' => $this->status,
            'notes' => $this->notes,
            'company_id' => Auth::user()->company_id,
        ];

        if ($this->isEdit) {
            $this->vendor->update($vendorData);
            session()->flash('message', 'Proveedor actualizado correctamente.');
        } else {
            $vendorData['company_id'] = Auth::user()->company_id;
            Vendor::create($vendorData);
            session()->flash('message', 'Proveedor creado correctamente.');
        }

        return redirect()->route('vendors.index');
    }

    protected function validateEmailList(): void
    {
        $emails = EmailList::parse($this->email);

        if ($this->email !== null && trim((string) $this->email) !== '' && $emails === []) {
            Validator::make(
                ['email' => null],
                ['email' => 'required'],
                ['email.required' => 'Debe ingresar al menos un correo válido.']
            )->validate();
        }

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Validator::make(
                    ['email' => $email],
                    ['email' => 'email'],
                    ['email.email' => "El correo {$email} no es válido."]
                )->validate();
            }
        }
    }

    public function render()
    {
        return view('livewire.forms.vendor-form');
    }
}
