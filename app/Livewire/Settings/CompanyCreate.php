<?php

namespace App\Livewire\Settings;

use App\Models\Company;
use Livewire\Component;

class CompanyCreate extends Component
{
    public $id = null;
    public $name;
    public $address;
    public $country;
    public $city;
    public $zip;
    public $phone;
    public $description;
    public $website;

    public $title;
    public $subtitle;

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $company = Company::findOrFail($id);
            $this->id = $id;
            $this->name = $company->name;
            $this->address = $company->address;
            $this->country = $company->country;
            $this->city = $company->city;
            $this->zip = $company->zip;
            $this->phone = $company->phone;
            $this->description = $company->description;
            $this->website = $company->website;
            $this->title = 'Editar Empresa';
            $this->subtitle = 'Modifica la información de la empresa';
        } else {
            $this->title = 'Crear Empresa';
            $this->subtitle = 'Ingresa la información de la nueva empresa';
        }
    }

    public function save()
    {
        $this->validate($this->rules(), [
            'name.required' => 'El nombre es requerido',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'website.url' => 'El sitio web debe ser una URL válida',
        ]);

        if ($this->id) {
            $company = Company::findOrFail($this->id);

            $company->update([
                'name' => $this->name,
                'address' => $this->address,
                'country' => $this->country,
                'city' => $this->city,
                'zip' => $this->zip,
                'phone' => $this->phone,
                'description' => $this->description,
                'website' => $this->website,
            ]);

            $this->dispatch('open-modal', 'modal-company-created');
        } else {
            $company = Company::create([
                'name' => $this->name,
                'address' => $this->address,
                'country' => $this->country,
                'city' => $this->city,
                'zip' => $this->zip,
                'phone' => $this->phone,
                'description' => $this->description,
                'website' => $this->website,
            ]);

            $this->dispatch('open-modal', 'modal-company-created');
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'modal-company-created');
        return redirect()->route('settings.companies');
    }

    public function backToList()
    {
        return redirect()->route('settings.companies');
    }

    public function render()
    {
        return view('livewire.settings.company-create')
            ->layout('layouts.settings.user-management');
    }
}
