<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RagaOrders\Webhook\Http\Controllers\WebhookSettingsController;

/**
 * Registra rutas informativas de Webhooks solo si ningún otro proveedor (p. ej. módulo interno)
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

            try {
                Route::middleware(['web', 'auth'])->group(function () {
                    if (class_exists(WebhookSettingsController::class)) {
                        Route::prefix('webhook/settings')->group(function () {
                            Route::get('/', [WebhookSettingsController::class, 'index'])->name('webhook.settings.index');
                            Route::get('/logs', [WebhookSettingsController::class, 'logs'])->name('webhook.settings.logs');
                            Route::get('/events/{eventName}/logs', [WebhookSettingsController::class, 'eventLogs'])->name('webhook.settings.events.logs');
                        });

                        Route::redirect('settings/webhook', '/webhook/settings');
                        return;
                    }

                    Route::view('settings/webhook', 'webhook-settings')
                        ->name('webhook.settings.index');
                });
            } catch (\LogicException) {
                // Nombre reservado por otra ruta en el mismo ciclo (defensa en profundidad).
            }
        });
    }
}
