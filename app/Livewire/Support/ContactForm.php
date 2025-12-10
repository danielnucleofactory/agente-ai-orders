<?php

namespace App\Livewire\Support;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Notifications\SupportRequestReceived;
use App\Notifications\SupportRequestNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

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

            // 1. Enviar confirmación al usuario
            Auth::user()->notify(new SupportRequestReceived($supportRequest));

            // 2. Enviar notificación al equipo de soporte
            $supportEmail = config('mail.support_email');
            if ($supportEmail) {
                try {
                    Notification::route('mail', $supportEmail)
                        ->notify(new SupportRequestNotification($supportRequest));
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación a soporte', [
                        'email' => $supportEmail,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 3. Enviar notificación al administrador
            $adminEmail = config('mail.admin_email');
            if ($adminEmail && $adminEmail !== $supportEmail) {
                try {
                    Notification::route('mail', $adminEmail)
                        ->notify(new SupportRequestNotification($supportRequest));
                } catch (\Exception $e) {
                    Log::error('Error enviando notificación a admin', [
                        'email' => $adminEmail,
                        'error' => $e->getMessage()
                    ]);
                }
            }

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
            Log::error('Error al crear solicitud de soporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
