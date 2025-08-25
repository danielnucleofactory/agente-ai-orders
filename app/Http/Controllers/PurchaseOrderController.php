<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\ShipTo;
use App\Models\BillTo;
use App\Models\Vendor;
use App\Models\Hub;
use App\Models\Product;
use App\Models\KanbanBoard;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * Create a new purchase order from external API
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFromApi(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            $results = [];

            // Verificar si el JSON es un array o un objeto simple
            if (!isset($data['general'])) {
                // Es un array de órdenes
                Log::info('Recibido array de órdenes', ['count' => count($data)]);
                $orders = $data;
            } else {
                // Es una sola orden
                Log::info('Recibida una sola orden', ['order' => $data['general']['order_number'] ?? 'unknown']);
                $orders = [$data];
            }

            foreach ($orders as $orderData) {
                $general = $orderData['general'];

                // Find vendor by name
                $vendor = Vendor::where('name', $general['vendor_id'])->first();
                if (!$vendor) {
                    throw new \Exception("Vendor with name '{$general['vendor_id']}' not found");
                }

                // Find shipTo by name
                $shipTo = ShipTo::where('name', $general['ship_to_id'])->first();
                if (!$shipTo) {
                    throw new \Exception("Ship To '{$general['ship_to_id']}' not found");
                }

                // Find billTo by name
                $billTo = BillTo::where('name', $general['bill_to_id'])->first();
                if (!$billTo) {
                    throw new \Exception("Bill To '{$general['bill_to_id']}' not found");
                }

                // Find hub by code
                $hub = Hub::where('code', $general['planned_hub_id'])->first();
                if (!$hub) {
                    throw new \Exception("Hub with code {$general['planned_hub_id']} not found");
                }

                // Parse dates
                $requiredDate = !empty($general['date_required_in_destination'])
                    ? Carbon::createFromFormat('Y/m/d', $general['date_required_in_destination'])
                    : now(); // Default to current date if empty

                $plannedPickupDate = !empty($general['date_planned_pickup'])
                    ? Carbon::createFromFormat('Y/m/d', $general['date_planned_pickup'])
                    : null;

                // Calcular el peso total a partir de los items
                $totalWeight = 0;
                foreach ($orderData['items'] as $item) {
                    $totalWeight += $item['peso_kg'];
                }

                // Obtener el netValue directamente del JSON
                $netTotal = $general['netValue'];

                // Valores por defecto
                $companyId = $vendor->company_id;

                // Obtener un status de kanban para nuevas órdenes
                $kanbanStatusId = null;
                $kanbanBoard = KanbanBoard::where('company_id', $companyId)
                    ->where('type', 'po_stages')
                    ->where('is_active', true)
                    ->first();

                if ($kanbanBoard) {
                    $recepcionStatus = $kanbanBoard->statuses()
                        ->where('name', 'Recepción')
                        ->first();

                    if (!$recepcionStatus) {
                        $recepcionStatus = $kanbanBoard->defaultStatus();
                    }

                    if ($recepcionStatus) {
                        $kanbanStatusId = $recepcionStatus->id;
                    }
                }

                // Preparar los datos para la orden de compra
                $poData = [
                    'company_id' => $companyId,
                    'order_number' => $general['order_number'],
                    'status' => 'draft',
                    'vendor_id' => $vendor->id,
                    'ship_to_id' => $shipTo->id,
                    'bill_to_id' => $billTo->id,
                    'order_date' => now(),
                    'currency' => !empty($general['currency']) ? $general['currency'] : 'USD', // Default currency
                    'incoterms' => !empty($general['incoterms']) ? $general['incoterms'] : 'EXW', // Default incoterms
                    'net_total' => $netTotal,
                    'total' => $netTotal,
                    'weight_kg' => $totalWeight,
                    'date_required_in_destination' => $requiredDate,
                    'date_planned_pickup' => $plannedPickupDate,
                    'planned_hub_id' => $hub->id,
                    'material_type' => json_encode(['Standard']),
                    'ensurence_type' => 'pending',
                    'mode' => !empty($general['mode']) ? $general['mode'] : 'AIR', // Default mode
                    'kanban_status_id' => $kanbanStatusId,

                    // Campos obligatorios con valores predeterminados
                    'length_cm' => 0,
                    'width_cm' => 0,
                    'height_cm' => 0
                ];

                // Valores adicionales opcionales si vienen en el JSON
                if (isset($general['length_cm'])) {
                    $poData['length_cm'] = $general['length_cm'];
                }

                if (isset($general['width_cm'])) {
                    $poData['width_cm'] = $general['width_cm'];
                }

                if (isset($general['height_cm'])) {
                    $poData['height_cm'] = $general['height_cm'];
                }

                // Filtrar valores nulos o vacíos
                $poData = array_filter($poData, function($value) {
                    return $value !== null && $value !== '' || $value === 0 || $value === 0.0;
                });

                Log::info('Datos preparados para crear la PO', [
                    'order_number' => $general['order_number'],
                    'net_total' => $netTotal,
                    'date_planned_pickup' => $plannedPickupDate ? $plannedPickupDate->format('Y-m-d') : null
                ]);

                // Crear la orden de compra
                $purchaseOrder = PurchaseOrder::create($poData);

                Log::info('Orden creada', [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'net_total' => $purchaseOrder->net_total
                ]);

                // Procesar y asociar los productos
                $groupedItems = [];
                foreach ($orderData['items'] as $itemData) {
                    $materialId = $itemData['material'];
                    if (!isset($groupedItems[$materialId])) {
                        $groupedItems[$materialId] = [
                            'material' => $materialId,
                            'price_per_unit' => $itemData['price_per_unit'],
                            'peso_kg' => $itemData['peso_kg']
                        ];
                    } else {
                        $groupedItems[$materialId]['peso_kg'] += $itemData['peso_kg'];
                    }
                }

                foreach ($groupedItems as $itemData) {
                    // Buscar el producto por material_id
                    $product = Product::where('material_id', $itemData['material'])->first();

                    $newPrice = (float) $itemData['price_per_unit'];
                    $priceUpdated = false;

                    if ($product) {
                        // Verificar si el precio ha cambiado
                        $currentPrice = (float) $product->price_per_unit;

                        if (abs($currentPrice - $newPrice) > 0.0001) { // Comparar con margen para evitar problemas de redondeo
                            // Actualizar el precio si es diferente
                            Log::info("Actualizando precio del producto {$product->material_id}", [
                                'old_price' => $currentPrice,
                                'new_price' => $newPrice
                            ]);

                            $product->price_per_unit = $newPrice;
                            $product->save();
                            $priceUpdated = true;
                        }
                    } else {
                        // Si el producto no existe, crearlo
                        $product = Product::create([
                            'material_id' => $itemData['material'],
                            'short_text' => 'Product ' . $itemData['material'],
                            'unit_of_measure' => 'KG',
                            'price_per_unit' => $newPrice,
                        ]);

                        Log::info("Producto creado con ID {$product->id}", [
                            'material_id' => $itemData['material'],
                            'price' => $newPrice
                        ]);

                        $priceUpdated = true; // Consideramos que un producto nuevo también tiene "precio actualizado"
                    }

                    // Usar peso_kg como la cantidad del producto
                    $quantity = (int) round($itemData['peso_kg']);

                    // Adjuntar producto a la orden de compra
                    $purchaseOrder->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'unit_price' => $newPrice, // Usar el nuevo precio para esta orden
                    ]);

                    Log::info("Producto asociado a la orden", [
                        'product_id' => $product->id,
                        'material_id' => $product->material_id,
                        'quantity' => $quantity,
                        'unit_price' => $newPrice
                    ]);
                }

                $results[] = [
                    'order_number' => $purchaseOrder->order_number,
                    'id' => $purchaseOrder->id,
                    'status' => 'success'
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders created successfully',
                'data' => $results
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear órdenes de compra desde API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }
}
