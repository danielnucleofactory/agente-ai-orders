<?php

namespace App\Livewire\Support;

use Livewire\Component;
use App\Models\SupportRequest;
use App\Mail\SupportRequestNotification;
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

    public function mount(): void
    {
        sort($this->subjectOptions, SORT_NATURAL | SORT_FLAG_CASE);
    }

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
            // Opcional: Crear registro en base de datos si se necesita tracking
            $supportRequest = null;
            if (Auth::check()) {
                $supportRequest = SupportRequest::create([
                    'user_id' => Auth::id(),
                    'subject' => $this->subject,
                    'message' => $this->description,
                    'status' => 'pending',
                ]);
            }

            // Obtener los correos de destino - usar config() primero, luego env() como fallback
            $supportEmail = config('mail.support_email') ?: config('mail.support.address') ?: env('MAIL_SUPPORT_EMAIL');
            $adminEmail = config('mail.admin_email') ?: config('mail.admin.address') ?: env('MAIL_ADMIN_EMAIL');
            
            // Limpiar espacios y comillas si existen
            $supportEmail = $supportEmail ? trim($supportEmail, " \t\n\r\0\x0B\"'") : null;
            $adminEmail = $adminEmail ? trim($adminEmail, " \t\n\r\0\x0B\"'") : null;
            
            Log::info('Iniciando envío de correo único de soporte', [
                'user_email' => $this->email,
                'support_email_raw' => $supportEmail,
                'admin_email_raw' => $adminEmail,
                'support_email_valid' => $supportEmail && filter_var($supportEmail, FILTER_VALIDATE_EMAIL),
                'admin_email_valid' => $adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL),
            ]);
            
            // Validar que existe email de soporte
            if (!$supportEmail || !filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
                Log::error('✗ No se puede enviar correo: email de soporte inválido o vacío', [
                    'support_email' => $supportEmail
                ]);
                $this->showSuccessModal = true;
                $this->reset(['name', 'email', 'subject', 'description']);
                return;
            }

            // Construir lista de emails para CC (usuario + admin si es diferente)
            $ccEmails = [];
            
            // Agregar usuario en CC
            if ($this->email && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $ccEmails[] = $this->email;
            }
            
            // Agregar admin en CC si es diferente al de soporte
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL) && $adminEmail !== $supportEmail) {
                $ccEmails[] = $adminEmail;
            }
            
            // Enviar un único correo a soporte con CC al usuario y admin
            try {
                Mail::to($supportEmail)->send(
                    new SupportRequestNotification($this->name, $this->email, $this->subject, $this->description, $ccEmails)
                );
                
                Log::info('✓ Correo único de notificación enviado', [
                    'to' => $supportEmail,
                    'cc' => $ccEmails,
                    'user_in_cc' => in_array($this->email, $ccEmails),
                    'admin_in_cc' => $adminEmail && $adminEmail !== $supportEmail && in_array($adminEmail, $ccEmails),
                    'support_request_id' => $supportRequest?->id,
                ]);
            } catch (\Exception $e) {
                Log::error('✗ Error al enviar correo único de soporte', [
                    'to' => $supportEmail,
                    'cc' => $ccEmails,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $this->showSuccessModal = true;
            $this->reset(['name', 'email', 'subject', 'description']);
            $this->dispatch('open-modal', 'modal-support-request-sent');
            $this->dispatch('show-success', 'Tu solicitud de soporte ha sido enviada exitosamente.');
        } catch (\Exception $e) {
            // Log del error pero no mostrar el error al usuario
            Log::error('✗ Error general al enviar solicitud de soporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            $this->showSuccessModal = true;
            $this->reset(['name', 'email', 'subject', 'description']);
        }
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
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
