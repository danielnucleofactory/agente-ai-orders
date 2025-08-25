<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Forms\ShowPucharseOrder;
use App\Livewire\Forms\PucharseOrderDetail;
use App\Livewire\Settings\Index;
use App\Livewire\Settings\Notifications;
use App\Livewire\Settings\Password;
use App\Http\Controllers\VendorController;
use App\Livewire\Settings\History;
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\RoleEdit;
use App\Livewire\Forms\PucharseOrderConsolidateDetail;
use App\Livewire\Settings\ActiveSessions;
use App\Livewire\Settings\Kanban;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Stages;
use App\Livewire\Settings\Users;
use App\Livewire\Settings\RoleCreate;
use App\Livewire\Settings\UserCreate;
use App\Livewire\Settings\Sessions;
use App\Livewire\Settings\ApiTokens;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForecastController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::view('/', 'welcome')
    ->name('welcome');

// Dashboard routes
Route::middleware(['auth', 'verified', 'permission:has_view_dashboard'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
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

// Rutas para documentación de envío
Route::middleware(['auth'])->group(function () {
    // Vista principal de documentación de envío
    Route::view('shipping-documentation', 'shipping-documentation.index')
        ->middleware('permission:has_view_shipping_docs')
        ->name('shipping-documentation.index');

    Route::view('shipping-documentation/create', 'shipping-documentation.create')
        ->middleware('permission:has_create_shipping_docs')
        ->name('shipping-documentation.create');

    // Rutas para proveedores
    Route::view('vendors', 'vendors.index')
        ->name('vendors.index');

    Route::view('vendors/create', 'vendors.create')
        ->name('vendors.create');

    Route::get('vendors/{vendor}/edit', function ($vendor) {
        return view('vendors.edit', ['vendor' => \App\Models\Vendor::findOrFail($vendor)]);
    })->name('vendors.edit');

    // Rutas para direcciones de envío (ship-to)
    Route::view('ship-to', 'ship-to.index')
        ->name('ship-to.index');

    Route::view('ship-to/create', 'ship-to.create')
        ->name('ship-to.create');

    Route::view('ship-to/{id}/edit', 'ship-to.edit')
        ->name('ship-to.edit');

    Route::view('shipping-documentation/requests', 'shipping-documentation.requests')
        ->name('shipping-documentation.requests');

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

    // Ver detalles de una orden de compra
    Route::get('purchase-orders/{id}', ShowPucharseOrder::class)
        ->name('purchase-orders.show');

    // TODO: La ruta de arriba es lo mismo que esta (?)
    Route::get('purchase-orders/{id}/detail', PucharseOrderDetail::class)
        ->name('purchase-orders.detail');

    // Editar una orden de compra
    Route::view('purchase-orders/{id}/edit', 'purchase-orders.edit')
        ->name('purchase-orders.edit');

    // Rutas para hubs
    Route::view('hub', 'hub.index')
        ->name('hub.index');

    Route::view('hub/create', 'hub.create')
        ->name('hub.create');

    Route::get('hub/{id}/edit', function ($id) {
        return view('hub.edit', ['hub' => \App\Models\Hub::findOrFail($id)]);
    })->name('hub.edit');

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
        ->name('settings.roles');

    Route::get('settings/roles/{roleId}/edit', RoleEdit::class)
        ->name('settings.roles.edit');

    Route::get('/settings/roles/create', App\Livewire\Settings\RoleCreate::class)
        ->middleware(['auth'])
        ->name('settings.roles.create');

    Route::get('settings/kanban', Kanban::class)
        ->name('settings.kanban');

    Route::get('settings/stages', Stages::class)
        ->name('settings.stages');

    Route::get('settings/users', Users::class)
        ->name('settings.users');

    Route::get('settings/users/create', UserCreate::class)
        ->name('settings.users.create');

    Route::get('settings/users/{id}/edit', UserCreate::class)
        ->name('settings.users.edit');

    Route::get('settings/active-sessions', ActiveSessions::class)
        ->name('settings.active-sessions');

    Route::get('settings/api-tokens', ApiTokens::class)
        ->name('settings.api-tokens');

    Route::get('settings/profile', function() {
        return view('profile.index');
    })->middleware('permission:has_view_profile')->name('settings.profile');

    Route::get('settings/sessions', Sessions::class)
        ->name('settings.sessions');

    Route::get('/bill-to', [App\Http\Controllers\BillToController::class, 'index'])->name('bill-to.index');
    Route::get('/bill-to/create', [App\Http\Controllers\BillToController::class, 'create'])->name('bill-to.create');
    Route::get('/bill-to/{billTo}/edit', [App\Http\Controllers\BillToController::class, 'edit'])->name('bill-to.edit');

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

    // PO Confirmation Settings
    Route::get('/settings/po-confirmation', function () {
        return view('po-confirmation-settings');
    })->name('settings.po-confirmation');
});

Route::view('support', 'support.index')
    ->middleware(['auth'])
    ->name('support.index');

// Ruta de prueba para el módulo PO Confirmation
Route::get('/po-confirmation-test', function () {
    return view('po-confirmation-test');
})->name('po.confirmation.test');

require __DIR__ . '/auth.php';
