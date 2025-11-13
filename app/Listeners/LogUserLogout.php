<?php

namespace App\Listeners;

use App\Models\UserAuthEvent;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        // El evento Logout puede tener user null si la sesión ya expiró
        if ($event->user) {
            UserAuthEvent::create([
                'user_id' => $event->user->id,
                'event_type' => 'logout',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'email' => $event->user->email,
            ]);
        }
    }
}
