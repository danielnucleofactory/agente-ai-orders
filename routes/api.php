<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DashboardKPIController;
use App\Http\Controllers\AgentOrdersController;

// Rutas públicas (sin autenticación)
Route::get('/status', function () {
    return response()->json([
        'status' => 'API funcionando correctamente',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Public endpoints for purchase orders from external API
Route::post('/purchase-orders', [PurchaseOrderController::class, 'createFromApi']);
Route::put('/purchase-orders/{po_id}', [PurchaseOrderController::class, 'updateFromApi']);
Route::delete('/purchase-orders/cancel', [PurchaseOrderController::class, 'deleteFromApi']);
Route::delete('/purchase-orders', [PurchaseOrderController::class, 'destroy']);
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
Route::post('/purchase-orders/search', [PurchaseOrderController::class, 'index']);
Route::post('/purchase-orders/bulk', [PurchaseOrderController::class, 'bulk']);
Route::post('/purchase-orders/bulk-update', [PurchaseOrderController::class, 'bulkUpdate']);

// Rutas protegidas con autenticación de token API
Route::middleware('api.token')->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'company' => $request->user()->company
        ]);
    });

    Route::get('/my-purchase-orders', function (Request $request) {
        $user = $request->user();
        $orders = $user->company ?
            $user->company->purchaseOrders()->with(['vendor', 'products'])->paginate(10) :
            collect([]);

        return response()->json([
            'data' => $orders,
            'user' => $user->name,
            'company' => $user->company->name ?? 'Sin compañía'
        ]);
    });

    Route::post('/my-purchase-orders', function (Request $request) {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'description' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0'
        ]);

        return response()->json([
            'message' => 'Orden de compra creada exitosamente',
            'data' => $validated,
            'created_by' => $request->user()->name
        ], 201);
    });

    Route::get('/dashboard-stats', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'stats' => [
                'total_orders' => $user->company ? $user->company->purchaseOrders()->count() : 0,
                'pending_orders' => $user->company ? $user->company->purchaseOrders()->where('status', 'pending')->count() : 0,
                'completed_orders' => $user->company ? $user->company->purchaseOrders()->where('status', 'completed')->count() : 0,
            ],
            'user_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'company' => $user->company->name ?? 'Sin compañía'
            ]
        ]);
    });

    Route::put('/profile', function (Request $request) {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'sometimes|string|max:20'
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->fresh()
        ]);
    });
});

// ============================================
// Dashboard KPI Routes (con autenticación web)
// ============================================
Route::middleware('auth:sanctum')->prefix('dashboard-kpi')->group(function () {
    Route::get('/', [DashboardKPIController::class, 'index']);
    Route::get('/pos-by-stage', [DashboardKPIController::class, 'posByStage']);
    Route::get('/pos-delay-cl', [DashboardKPIController::class, 'posDelayCL']);
    Route::get('/pos-advance-cl', [DashboardKPIController::class, 'posAdvanceCL']);
    Route::get('/capacity', [DashboardKPIController::class, 'capacity']);
    Route::get('/transshipment', [DashboardKPIController::class, 'transshipment']);
    Route::get('/pos-with-ata', [DashboardKPIController::class, 'posWithATA']);
    Route::get('/transit-time', [DashboardKPIController::class, 'transitTime']);
    Route::post('/compare-atd', [DashboardKPIController::class, 'compareATD']);
    Route::post('/compare-ata', [DashboardKPIController::class, 'compareATA']);
    Route::post('/compare-delay-cl', [DashboardKPIController::class, 'compareDelayCL']);
    Route::post('/compare-advance-cl', [DashboardKPIController::class, 'compareAdvanceCL']);
    Route::get('/po-vs-teus/stage', [DashboardKPIController::class, 'poVsTeusByStage']);
    Route::get('/po-vs-teus/period', [DashboardKPIController::class, 'poVsTeusByPeriod']);
    Route::get('/po-vs-teus/vendor', [DashboardKPIController::class, 'poVsTeusByVendor']);
    Route::get('/po-vs-teus/shipping-line', [DashboardKPIController::class, 'poVsTeusByShippingLine']);
    Route::get('/future-arrivals', [DashboardKPIController::class, 'futureArrivals']);
});

// ============================================
// Agente IA — endpoints REST internos
// ============================================
Route::prefix('agent')->middleware(\App\Http\Middleware\AgentTokenAuth::class)->group(function () {
    Route::get('orders',                    [AgentOrdersController::class, 'orders']);
    Route::get('orders/ata',                [AgentOrdersController::class, 'ordersByAta']);
    Route::get('orders/delayed-in-transit', [AgentOrdersController::class, 'ordersDelayedInTransit']);
    Route::get('shipments',                 [AgentOrdersController::class, 'shipments']);
    Route::get('shipments/{id}',            [AgentOrdersController::class, 'shipmentById']);
});