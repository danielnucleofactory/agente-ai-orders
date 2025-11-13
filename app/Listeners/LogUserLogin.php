<?php

namespace App\Listeners;

use App\Models\UserAuthEvent;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class LogUserLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Evitar duplicados: verificar si ya existe un evento de login reciente (últimos 1 segundo)
        $recentEvent = UserAuthEvent::where('user_id', $event->user->id)
            ->where('event_type', 'login')
            ->where('ip_address', Request::ip())
            ->where('created_at', '>=', Carbon::now()->subSecond())
            ->first();

        if (!$recentEvent) {
            UserAuthEvent::create([
                'user_id' => $event->user->id,
                'event_type' => 'login',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'email' => $event->user->email,
            ]);
        }
    }
}
