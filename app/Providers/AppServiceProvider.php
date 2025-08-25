<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\Tables\PurchaseOrdersTable;
use App\Livewire\Ui\PurchaseOrderCard;
use Illuminate\Support\Facades\Blade;
use App\View\Components\Breadcrumb;

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
        // Registrar componente de breadcrumb explícitamente
        Blade::component('breadcrumb', Breadcrumb::class);

        // Asegurarnos de que el componente breadcrumb esté disponible en todos los entornos
        try {
            // Intentar registrar el componente, si falla, registrar el error
            \Illuminate\Support\Facades\Log::info('Registrando componente breadcrumb');

            // Registro de componentes Livewire
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
