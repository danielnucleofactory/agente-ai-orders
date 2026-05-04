<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\Tables\PurchaseOrdersTable;
use App\Livewire\Ui\PurchaseOrderCard;
use Illuminate\Support\Facades\Blade;
use App\View\Components\Breadcrumb;
use Carbon\Carbon;
use App\Models\PurchaseOrder;
use App\Models\ShippingDocument;
use App\Models\Vendor;
use App\Observers\PurchaseOrderObserver;
use App\Observers\ShippingDocumentObserver;
use App\Observers\VendorObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Mail\Events\MessageSending;
use App\Listeners\LogUserLogin;
use App\Listeners\LogUserLogout;
use App\Listeners\LogFailedLogin;
use App\Listeners\AddSupportReplyTo;
use App\Listeners\BlockVendorConfirmationEmails;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vista de ajustes Webhooks solo cuando el módulo no debe cargar esa ruta. Si el módulo está
        // habilitado y existe en disco, no registramos aquí (evita duplicar nombre aunque el módulo
        // registre la ruta en un booted posterior al nuestro).
        $this->app->booted(function () {
            $webhookModuleOn = filter_var(env('WEBHOOK_MODULE_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
            $webhookModulePresent = File::exists(base_path('internal_modules/orders-module-webhook'));
            if ($webhookModuleOn && $webhookModulePresent) {
                return;
            }

            if (Route::has('webhook.settings.index')) {
                return;
            }

            Route::middleware(['web', 'auth'])->group(function () {
                Route::view('settings/webhook', 'webhook-settings')
                    ->name('webhook.settings.index');
            });
        });

        // Decorar WebhookService para ocultar date_variable_date y current_timestamp en el payload
        // (sin modificar internal_modules)
        if (class_exists(\RagaOrders\Webhook\Services\WebhookService::class)) {
            $this->app->extend(\RagaOrders\Webhook\Services\WebhookService::class, function ($service) {
                return new \App\Services\WebhookPayloadFilterDecorator($service);
            });
        }

        // Registrar observers para auditoría
        PurchaseOrder::observe(PurchaseOrderObserver::class);
        ShippingDocument::observe(ShippingDocumentObserver::class);
        Vendor::observe(VendorObserver::class);

        // Registrar listeners para eventos de autenticación
        Event::listen(Login::class, LogUserLogin::class);
        Event::listen(Logout::class, LogUserLogout::class);
        Event::listen(Failed::class, LogFailedLogin::class);

        // Cortar correos hacia proveedores salvo que se habiliten explícitamente.
        Event::listen(MessageSending::class, BlockVendorConfirmationEmails::class);

        // Agregar Reply-To de soporte a todos los correos salientes (si no tienen uno definido)
        Event::listen(MessageSending::class, AddSupportReplyTo::class);

        // Registrar componente de breadcrumb explícitamente
        Blade::component('breadcrumb', Breadcrumb::class);


        // Asegurarnos de que el componente breadcrumb esté disponible en todos los entornos
        try {            // Registro de componentes Livewire
            Livewire::component('tables.purchase-orders-table', PurchaseOrdersTable::class);
            Livewire::component('ui.purchase-order-card', PurchaseOrderCard::class);
            Livewire::component('tables.vendors-table', \App\Livewire\Tables\VendorsTable::class);
            Livewire::component('forms.vendor-form', \App\Livewire\Forms\VendorForm::class);
            Livewire::component('tables.ship-to-table', \App\Livewire\Tables\ShipToTable::class);
            Livewire::component('forms.ship-to-form', \App\Livewire\Forms\ShipToForm::class);
            Livewire::component('forms.create-purchase-order', \App\Livewire\Forms\CreatePucharseOrder::class);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al registrar componentes', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
