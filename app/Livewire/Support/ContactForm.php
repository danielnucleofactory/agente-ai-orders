<?php

namespace App\Livewire\Support;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Notifications\SupportRequestReceived;
use Illuminate\Support\Facades\Auth;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $subject = '';
    public $description = '';
    public $showSuccessModal = false;

    public $subjectOptions = [
        'Consulta técnica',
        'Problema con facturación',
        'Soporte de proveedores',
        'Problema con tarifas',
        'Registro de vehículos',
        'Configuración de cuenta',
        'Otro',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string',
        'description' => 'required|string|min:10',
    ];

    protected $messages = [
        'name.required' => 'El nombre es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El correo debe ser válido.',
        'subject.required' => 'Por favor selecciona un asunto.',
        'description.required' => 'La descripción es obligatoria.',
        'description.min' => 'La descripción debe tener al menos 10 caracteres.',
    ];


    public function submit()
    {
        $this->validate();

        try {
            $supportRequest = SupportRequest::create([
                'user_id' => Auth::id(),
                'subject' => $this->subject,
                'message' => $this->description,
                'status' => 'pending',
            ]);

            Auth::user()->notify(new SupportRequestReceived($supportRequest));

            $this->reset(['subject', 'description']);
            $this->showSuccessModal = true;
            
            // Método 1: Dispatch de Livewire (método principal)
            $this->dispatch('open-modal', 'modal-support-request-sent');
            
            // Método 2: Fallback con JavaScript directo
            $this->js('
                console.log("Abriendo modal de éxito");
                window.dispatchEvent(new CustomEvent("open-modal", { detail: "modal-support-request-sent" }));
            ');

            session()->flash('success', 'Tu solicitud de soporte ha sido enviada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Hubo un error al enviar tu solicitud. Por favor, intenta nuevamente.');
        }
    }

    public function closeModal()
    {
        $this->showSuccessModal = false;
    }

    public function render()
    {
        return view('livewire.support.contact-form')
            ->layout('layouts.app');
    }
}
