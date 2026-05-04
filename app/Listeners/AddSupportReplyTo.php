<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

/**
 * Listener que agrega Reply-To de soporte a todos los correos salientes.
 * 
 * - Solo agrega Reply-To si no está ya definido (respeta Reply-To explícitos)
 * - Lee el email de soporte desde config('mail.support_email')
 * - Solo actúa si el valor está definido
 * 
 * Esto permite que los correos salgan desde el dominio verificado (ej: ologistics.com)
 * pero las respuestas lleguen al inbox de soporte (ej: soporte@raga-x.ai).
 */
class AddSupportReplyTo
{
    /**
     * Handle the event.
     */
    public function handle(MessageSending $event): void
    {
        $supportEmail = config('mail.support_email');
        
        // Solo actuar si el email de soporte está configurado
        if (empty($supportEmail)) {
            return;
        }
        
        $message = $event->message;
        
        // Obtener Reply-To actual del mensaje
        $currentReplyTo = $message->getReplyTo();
        
        // Solo agregar si no hay Reply-To definido
        if (empty($currentReplyTo)) {
            $supportName = config('mail.support.name', config('app.name', 'Soporte'));
            
            $message->replyTo(new Address($supportEmail, $supportName));
            
            \Log::debug('AddSupportReplyTo: Reply-To agregado', [
                'reply_to' => $supportEmail,
                'subject' => $message->getSubject(),
            ]);
        }
    }
}
