<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Registra la ruta informativa de Webhooks solo si ningún otro proveedor (p. ej. módulo interno)
 * definió ya el nombre. Debe cargarse el último en bootstrap/providers.php para que el callback
 * booted quede al final de la cola y Route::has sea fiable.
 */
class WebhookSettingsRouteFallbackServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            if (Route::has('webhook.settings.index')) {
                return;
            }

            // try {
            //     Route::middleware(['web', 'auth'])->group(function () {
            //         Route::view('settings/webhook', 'webhook-settings')
            //             ->name('webhook.settings.index');
            //     });
            // } catch (\LogicException) {
            //     // Nombre reservado por otra ruta en el mismo ciclo (defensa en profundidad).
            // }
        });
    }
}
