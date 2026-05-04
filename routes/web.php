<?php

use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardKPIController;
use App\Http\Controllers\ForecastController;
use App\Livewire\Forms\PucharseOrderConsolidateDetail;
use App\Livewire\Forms\PucharseOrderDetail;
use App\Livewire\Forms\ShowPucharseOrder;
use App\Livewire\Settings\ActiveSessions;
use App\Livewire\Settings\ApiTokens;
use App\Livewire\Settings\History;
use App\Livewire\Settings\Index;
use App\Livewire\Settings\Kanban;
use App\Livewire\Settings\Notifications;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\RoleEdit;
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\Sessions;
use App\Livewire\Settings\Stages;
use App\Livewire\Settings\UserCreate;
use App\Livewire\Settings\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Dashboard routes
Route::middleware(['auth', 'verified', 'permission:has_view_dashboard'])->group(function () {
    // Dashboard principal ahora es el KPI dashboard
    Route::view('dashboard', 'dashboard-kpi')->name('dashboard');
    // Mantener rutas del dashboard antiguo por compatibilidad (si se necesitan)
    Route::get('dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::match(['get', 'post'], 'dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    // Mantener ruta dashboard-kpi como alias
    Route::view('dashboard-kpi', 'dashboard-kpi')->name('dashboard.kpi');

    // Dashboard KPI API endpoints (para consumir desde JavaScript)
    Route::prefix('dashboard-kpi/api')->group(function () {
        Route::get('/', [DashboardKPIController::class, 'index'])->name('dashboard.kpi.api');
        Route::get('/filter-options', [DashboardKPIController::class, 'getFilterOptions'])->name('dashboard.kpi.filter-options');
        Route::get('/kpi-summary', [DashboardKPIController::class, 'getKPISummary'])->name('dashboard.kpi.summary');

        // Vista Tendencia - Cantidad de PO
        Route::get('/pos-by-stage', [DashboardKPIController::class, 'posByStage'])->name('dashboard.kpi.pos-by-stage');
        Route::get('/pos-delay-cl', [DashboardKPIController::class, 'posDelayCL'])->name('dashboard.kpi.pos-delay-cl');
        Route::get('/pos-advance-cl', [DashboardKPIController::class, 'posAdvanceCL'])->name('dashboard.kpi.pos-advance-cl');
        Route::get('/capacity', [DashboardKPIController::class, 'capacity'])->name('dashboard.kpi.capacity');
        Route::get('/transshipment', [DashboardKPIController::class, 'transshipment'])->name('dashboard.kpi.transshipment');
        Route::get('/pos-with-ata', [DashboardKPIController::class, 'posWithATA'])->name('dashboard.kpi.pos-with-ata');
        Route::get('/transit-time', [DashboardKPIController::class, 'transitTime'])->name('dashboard.kpi.transit-time');

        // Vista Comparativo
        Route::post('/compare-atd', [DashboardKPIController::class, 'compareATD'])->name('dashboard.kpi.compare-atd');
        Route::post('/compare-ata', [DashboardKPIController::class, 'compareATA'])->name('dashboard.kpi.compare-ata');
        Route::post('/compare-delay-cl', [DashboardKPIController::class, 'compareDelayCL'])->name('dashboard.kpi.compare-delay-cl');
        Route::post('/compare-advance-cl', [DashboardKPIController::class, 'compareAdvanceCL'])->name('dashboard.kpi.compare-advance-cl');

        // Vista PO vs TEUs
        Route::get('/po-vs-teus/stage', [DashboardKPIController::class, 'poVsTeusByStage'])->name('dashboard.kpi.po-vs-teus-stage');
        Route::get('/po-vs-teus/period', [DashboardKPIController::class, 'poVsTeusByPeriod'])->name('dashboard.kpi.po-vs-teus-period');
        Route::get('/po-vs-teus/vendor', [DashboardKPIController::class, 'poVsTeusByVendor'])->name('dashboard.kpi.po-vs-teus-vendor');
        Route::get('/po-vs-teus/shipping-line', [DashboardKPIController::class, 'poVsTeusByShippingLine'])->name('dashboard.kpi.po-vs-teus-shipping-line');

        // Vista Proyección
        Route::get('/future-arrivals', [DashboardKPIController::class, 'futureArrivals'])->name('dashboard.kpi.future-arrivals');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth', 'permission:has_view_profile'])
    ->name('profile');

Route::view('new-purchase-order', 'new-purchase-order')
    ->middleware(['auth', 'permission:has_create_orders'])
    ->name('new-purchase-order');

Route::middleware(['auth'])->group(function () {
    // Vista principal de Productos
    Route::view('products', 'products.index')
        ->middleware('permission:has_view_products')
        ->name('products.index');

    // Formulario creación de productos
    Route::view('products/create', 'products.create')
        ->middleware('permission:has_create_products')
        ->name('products.create');

    Route::get('products/{product}/edit', function ($product) {
        return view('products.edit', ['product' => \App\Models\Product::findOrFail($product)]);
    })->middleware('permission:has_edit_products')->name('products.edit');

    Route::view('products/forecast', 'products.forecast')
        ->middleware('permission:has_view_forecast_table')
        ->name('products.forecast');

    // Forecast dashboard routes
    Route::middleware('permission:has_view_forecast_graph')->group(function () {
        Route::get('products/forecast-graph', [ForecastController::class, 'index'])->name('products.forecast-graph');
        Route::get('products/forecast-graph/data', [ForecastController::class, 'getData'])->name('products.forecast-graph.data');
        Route::get('products/forecast-graph/export', [ForecastController::class, 'export'])->name('products.forecast-graph.export');
    });

    Route::get('products/forecast-edit/{id}', function ($id) {
        return view('products.forecast-edit', ['forecast' => \App\Models\Forecast::findOrFail($id)]);
    })->middleware('permission:has_edit_forecast')->name('products.forecast.edit');
});

// Rutas para documentación de envío - OCULTADO
Route::middleware(['auth'])->group(function () {
    // Vista principal de documentación de envío
    // Route::view('shipping-documentation', 'shipping-documentation.index')
    //     ->middleware('permission:has_view_shipping_docs')
    //     ->name('shipping-documentation.index');

    // Route::view('shipping-documentation/create', 'shipping-documentation.create')
    //     ->middleware('permission:has_create_shipping_docs')
    //     ->name('shipping-documentation.create');

    // Rutas para proveedores
    Route::view('vendors', 'vendors.index')
        ->middleware('permission:has_view_vendors')
        ->name('vendors.index');

    Route::view('vendors/create', 'vendors.create')
        ->middleware('permission:has_create_vendors')
        ->name('vendors.create');

    Route::get('vendors/{vendor}/edit', function ($vendor) {
        return view('vendors.edit', ['vendor' => \App\Models\Vendor::findOrFail($vendor)]);
    })->middleware('permission:has_edit_vendors')->name('vendors.edit');

    // Rutas para direcciones de envío (ship-to)
    Route::view('ship-to', 'ship-to.index')
        ->middleware('permission:has_view_ship_to')
        ->name('ship-to.index');

    Route::view('ship-to/create', 'ship-to.create')
        ->middleware('permission:has_create_ship_to')
        ->name('ship-to.create');

    Route::view('ship-to/{id}/edit', 'ship-to.edit')
        ->middleware('permission:has_edit_ship_to')
        ->name('ship-to.edit');

    // Route::view('shipping-documentation/requests', 'shipping-documentation.requests')
    //     ->name('shipping-documentation.requests');

    // Rutas para órdenes de compra (si no existen ya)
    Route::view('purchase-orders', 'purchase-orders.index')
        ->name('purchase-orders.index');

    // Formulario creación de órdenes de compra
    Route::view('purchase-orders/create', 'purchase-orders.create')
        ->name('purchase-orders.create');

    // Seguimiento órdenes de compra
    Route::view('purchase-orders/tracking', 'purchase-orders.kanban')
        ->name('purchase-orders.tracking');

    Route::view('purchase-orders/consolidated-orders', 'purchase-orders.consolidated-orders')
        ->name('purchase-orders.consolidated-orders');

    Route::get('purchase-orders/consolidated-orders/{id}/detail', PucharseOrderConsolidateDetail::class)
        ->name('purchase-orders.consolidated-order-detail');

    // Solicitudes y aprobaciones
    Route::view('purchase-orders/requests', 'purchase-orders.requests')
        ->name('purchase-orders.requests');

    // Kanban de órdenes de compra
    Route::get('purchase-orders/kanban/{boardId?}', \App\Livewire\Kanban\KanbanBoard::class)
        ->name('purchase-orders.kanban');

    // Listar tableros Kanban
    Route::get('purchase-orders/kanban-boards', \App\Livewire\Kanban\KanbanBoardList::class)
        ->name('purchase-orders.kanban-boards');

    // Ver detalles de una orden de compra (ruta más específica debe ir primero)
    Route::get('purchase-orders/{id}/detail', PucharseOrderDetail::class)
        ->name('purchase-orders.detail');

    // Editar una orden de compra (ruta más específica debe ir primero)
    Route::view('purchase-orders/{id}/edit', 'purchase-orders.edit')
        ->name('purchase-orders.edit');

    // Ver detalles de una orden de compra (ruta genérica debe ir después de las específicas)
    Route::get('purchase-orders/{id}', ShowPucharseOrder::class)
        ->name('purchase-orders.show');

    // Rutas para hubs
    Route::view('hub', 'hub.index')
        ->middleware('permission:has_view_hubs')
        ->name('hub.index');

    Route::view('hub/create', 'hub.create')
        ->middleware('permission:has_create_hubs')
        ->name('hub.create');

    Route::get('hub/{id}/edit', function ($id) {
        return view('hub.edit', ['hub' => \App\Models\Hub::findOrFail($id)]);
    })->middleware('permission:has_edit_hubs')->name('hub.edit');

});

// Rutas para configuraciones
Route::middleware(['auth'])->group(function () {
    Route::get('settings', Index::class)
        ->name('settings.index');

    Route::get('settings/notifications', Notifications::class)
        ->name('settings.notifications');

    Route::get('settings/password', Password::class)
        ->name('settings.password');

    Route::get('settings/history', History::class)
        ->name('settings.history');

    Route::get('settings/roles', Roles::class)
        ->middleware('permission:has_view_roles')
        ->name('settings.roles');

    Route::get('settings/roles/{roleId}/edit', RoleEdit::class)
        ->middleware('permission:has_edit_roles')
        ->name('settings.roles.edit');

    Route::get('/settings/roles/create', App\Livewire\Settings\RoleCreate::class)
        ->middleware('permission:has_create_roles')
        ->name('settings.roles.create');

    Route::get('settings/kanban', Kanban::class)
        ->name('settings.kanban');

    Route::get('settings/stages', Stages::class)
        ->name('settings.stages');

    Route::get('settings/users', Users::class)
        ->middleware('permission:has_view_users')
        ->name('settings.users');

    Route::get('settings/users/create', UserCreate::class)
        ->middleware('permission:has_create_users')
        ->name('settings.users.create');

    Route::get('settings/users/{id}/edit', UserCreate::class)
        ->middleware('permission:has_edit_users')
        ->name('settings.users.edit');

    Route::get('settings/active-sessions', ActiveSessions::class)
        ->name('settings.active-sessions');

    Route::get('settings/api-tokens', ApiTokens::class)
        ->name('settings.api-tokens');

    Route::get('settings/profile', function () {
        return view('profile.index');
    })->middleware('permission:has_view_profile')->name('settings.profile');

    Route::get('settings/sessions', Sessions::class)
        ->name('settings.sessions');

    Route::view('settings/po-confirmation', 'po-confirmation-settings')
        ->name('po-confirmation.settings.index');

    // Companies (permisos dedicados; asignación típica: Super Administrador)
    Route::get('settings/companies', \App\Livewire\Settings\Companies::class)
        ->middleware('permission:has_view_companies')
        ->name('settings.companies');

    Route::get('settings/companies/create', \App\Livewire\Settings\CompanyCreate::class)
        ->middleware('permission:has_create_companies')
        ->name('settings.companies.create');

    Route::get('settings/companies/{id}/edit', \App\Livewire\Settings\CompanyCreate::class)
        ->middleware('permission:has_edit_companies')
        ->name('settings.companies.edit');

    Route::get('/bill-to', [App\Http\Controllers\BillToController::class, 'index'])
        ->middleware('permission:has_view_bill_to')
        ->name('bill-to.index');
    Route::get('/bill-to/create', [App\Http\Controllers\BillToController::class, 'create'])
        ->middleware('permission:has_create_bill_to')
        ->name('bill-to.create');
    Route::get('/bill-to/{billTo}/edit', [App\Http\Controllers\BillToController::class, 'edit'])
        ->middleware('permission:has_edit_bill_to')
        ->name('bill-to.edit');

    // Rutas para autorizaciones
    Route::get('/authorizations', [AuthorizationController::class, 'index'])->name('authorizations.index');
    Route::get('/authorizations/{request}', [AuthorizationController::class, 'show'])->name('authorizations.show');
    Route::post('/authorizations/{request}/approve', [AuthorizationController::class, 'approve'])->name('authorizations.approve');
    Route::post('/authorizations/{request}/reject', [AuthorizationController::class, 'reject'])->name('authorizations.reject');

    Route::post('logout-session', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Has cerrado sesión correctamente.');
    })->name('logout-session');
});

Route::middleware(['auth'])->group(function () {
    // Ruta para descargar archivos de media con autenticación
    Route::get('media/{mediaId}/download', [\App\Http\Controllers\MediaController::class, 'download'])
        ->name('media.download');

    Route::get('support/contact', \App\Livewire\Support\ContactForm::class)
        ->name('support.contact');

    Route::view('historical-data', 'historical-data.index')
        ->middleware('permission:has_view_historical_data')
        ->name('historical-data.index');

    Route::get('historical-data/export', [\App\Http\Controllers\HistoricalDataController::class, 'export'])
        ->middleware('permission:has_view_historical_data')
        ->name('historical-data.export');

    // Rutas para Maestros
    Route::view('maestros/container-types', 'maestros.container-types.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.container-types.index');

    Route::view('maestros/ports', 'maestros.ports.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.ports.index');

    Route::view('maestros/transport-types', 'maestros.transport-types.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.transport-types.index');

    Route::view('maestros/shipping-lines', 'maestros.shipping-lines.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.shipping-lines.index');

    Route::view('maestros/service-providers', 'maestros.service-providers.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.service-providers.index');

    Route::view('maestros/rate-types', 'maestros.rate-types.index')
        ->middleware('permission:has_view_maestros')
        ->name('maestros.rate-types.index');
});

// Ruta de prueba para el módulo PO Confirmation
Route::get('/po-confirmation-test', function () {
    return view('po-confirmation-test');
})->name('po.confirmation.test');

require __DIR__.'/auth.php';
