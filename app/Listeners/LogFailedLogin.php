<?php

namespace App\Listeners;

use App\Models\UserAuthEvent;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Request;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // El evento Failed tiene las credenciales pero no el usuario (porque falló)
        $email = $event->credentials['email'] ?? null;
        
        UserAuthEvent::create([
            'user_id' => null, // No hay usuario porque el login falló
            'event_type' => 'failed',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'email' => $email,
        ]);
    }
}
