<?php

namespace App\Livewire\Support;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Mail\SupportRequestMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

            $supportEmail = config('mail.support.address');
            $formUserEmail = $this->email; // Correo ingresado en el formulario
            $adminEmail = config('mail.admin.address');

            if (empty($supportEmail)) {
                throw new \Exception('La dirección de correo de soporte no está configurada. Por favor, contacta al administrador.');
            }

            if (empty($formUserEmail)) {
                throw new \Exception('El correo electrónico es obligatorio.');
            }

            // Enviar el correo
            // TO: soporte
            // CC: usuario del formulario y administrador
            $mail = Mail::to($supportEmail);
            
            // Agregar CC al usuario del formulario
            $mail->cc($formUserEmail);
            
            // Agregar CC al administrador si está configurado
            if (!empty($adminEmail)) {
                $mail->cc($adminEmail);
            }

            Log::info('Enviando correo de soporte', [
                'to' => $supportEmail,
                'cc' => array_filter([$formUserEmail, $adminEmail]),
                'support_request_id' => $supportRequest->id,
                'mail_driver' => config('mail.default'),
            ]);
            
            $mail->send(new SupportRequestMail($supportRequest, Auth::user()));

            Log::info('Correo de soporte enviado exitosamente', [
                'support_request_id' => $supportRequest->id,
            ]);

            $this->reset(['subject', 'description']);
            $this->showSuccessModal = true;
            $this->dispatch('open-modal', 'modal-support-request-sent');
            $this->dispatch('show-success', 'Tu solicitud de soporte ha sido enviada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al enviar solicitud de soporte', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            $this->dispatch('show-error', 'Hubo un error al enviar tu solicitud: ' . $e->getMessage());
            session()->flash('error', 'Hubo un error al enviar tu solicitud: ' . $e->getMessage());
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
