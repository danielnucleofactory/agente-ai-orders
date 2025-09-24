<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\KanbanBoard;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



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

            // 1) Soporte JSON o x-www-form-urlencoded
            $payload = $request->json()->all();
            if (empty($payload)) {
                $payload = $request->all();
            }

            // 2) Normalizar a array de órdenes
            if (isset($payload['purchase_orders']) && is_array($payload['purchase_orders'])) {
                $orders = $payload['purchase_orders'];
            } elseif (isset($payload['orders']) && is_array($payload['orders'])) {
                $orders = $payload['orders'];
            } elseif (isset($payload['general']) || isset($payload['items'])) {
                $orders = [$payload];
            } else {
                // Campos sueltos = 1 orden
                $orders = [[
                    'general' => $payload,
                    'items'   => $payload['items'] ?? [],
                ]];
            }

            $results = [];

            foreach ($orders as $orderData) {
                // 3) Asegurar estructuras
                $general = data_get($orderData, 'general', $orderData) ?? [];
                $items   = data_get($orderData, 'items', []);
                if (!is_array($items)) $items = [];

                // Helpers
                $parseDate = static function ($v) {
                    if ($v === null || $v === '') return null;
                    return \Illuminate\Support\Carbon::parse($v);
                };
                $toBool = static function ($v) {
                    if (is_bool($v)) return $v;
                    $b = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    return $b ?? false;
                };

                // Validacion campos OLO
                $rules = [
                    // Requeridos
                    'order_number'           => ['required','string'],
                    'category'               => ['required','string'],
                    'factory_proforma_number'=> ['required','string'],
                    'route_label'            => ['required','string'],
                    'date_theorical_load'    => ['required','date'],
                    'reason'                 => ['required','string'],
                    'incoterms'              => ['required','string'],
                    'logistics_incoterm'     => ['required','string'],
                    'price_incoterm'         => ['required','string'],
                ];

                $messages = [
                    'order_number.required'            => 'El campo "P.O." es obligatorio.',
                    'net_total.required'               => 'El campo "Monto" es obligatorio.',
                    'category.required'                => 'El campo "Categoria" es obligatorio.',
                    'factory_proforma_number.required' => 'El campo "Proforma Fábrica" es obligatorio.',
                    'route_label.required'             => 'El campo "Ruta Logística" es obligatorio.',
                    'date_theorical_load.required'     => 'El campo "Carga Lista Teórica" es obligatorio.',
                    'date_theorical_load.date'         => 'El campo "Carga Lista Teórica" debe ser una fecha válida.',
                    'reason.required'                  => 'El campo "Motivo" es obligatorio.',
                    'incoterms.required'               => 'El "Incoterm de compra" es obligatorio.',
                    'logistics_incoterm.required'      => 'El "Incoterm de logística" es obligatorio.',
                    'price_incoterm.required'          => 'El "Incoterm de precios" es obligatorio.',
                ];

                $validator = Validator::make($general, $rules, $messages);
                if ($validator->fails()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Validación fallida.',
                        'errors'  => $validator->errors(),
                        'data'    => null,
                    ], 422);
                }

                // 4) Relaciones (se buscan por nombre/código y se crean si no existen)
                $vendorId = data_get($general, 'vendor_id');
                $vendorName = data_get($general, 'vendor') ?? data_get($general, 'vendor_name');

//                // Buscar o crear vendor
//                if ($vendorId) {
//                    $vendor = Vendor::where('vendo_code', $vendorId)->first();
//                    if (!$vendor) {
//                        // Crear vendor con vendo_code y nombre por defecto
//                        $vendor = Vendor::create([
//                            'company_id' => 1, // Usar company_id por defecto, se actualizará después
//                            'vendo_code' => $vendorId,
//                            'name' => 'Proveedor con falta de datos ' . $vendorId,
//                            'status' => 'active'
//                        ]);
//                    }
//                } else {
//                    $vendor = Vendor::where('name', $vendorName)->first();
//                    if (!$vendor) {
//                        // Crear vendor con nombre
//                        $vendor = Vendor::create([
//                            'company_id' => 1, // Usar company_id por defecto, se actualizará después
//                            'name' => $vendorName,
//                            'vendo_code' => 'VENDOR_' . time(), // Generar código único
//                            'status' => 'active'
//                        ]);
//                    }
//                }
//
//                // Obtener company_id del vendor
//                $companyId = $vendor->company_id;

                // 5) Totales
                $totalWeight = 0;
                foreach ($items as $item) {
                    $totalWeight += (float) data_get($item, 'peso_kg', data_get($item, 'kgs', 0));
                }
                $netTotal  = (float) (data_get($general, 'netValue', data_get($general, 'net_total', 0)));

                // 6) (Opcional) Kanban inicial
                $kanbanStatusId = null;
                $kanbanBoard = KanbanBoard::where('company_id', 1) //De momento, queda como 1. Hay que modificarlo
                    ->where('type', 'po_stages')
                    ->where('is_active', true)
                    ->first();

                if ($kanbanBoard) {
                    $status = $kanbanBoard->statuses()->where('name', 'Recepción')->first()
                        ?: $kanbanBoard->defaultStatus();
                    $kanbanStatusId = $status?->id;
                }

                // 7) Campos base
                $poData = [
                    'company_id'   => 1,
                    'order_number' => data_get($general, 'order_number') ?? \Illuminate\Support\Str::uuid()->toString(),
                    'status'       => 'draft',
                    'order_date'   => now(),
                    'currency'     => data_get($general, 'currency', 'USD'),
                    'incoterms'    => data_get($general, 'incoterms', 'EXW'),
                    'net_total'    => $netTotal,
                    'total'        => $netTotal,
                    'weight_kg'    => $totalWeight,
                    'material_type'  => json_encode(['Standard']),
                    'ensurence_type' => 'pending',
                    'mode'           => data_get($general, 'mode', 'AIR'),
                    'kanban_status_id' => $kanbanStatusId,
                    'length_cm' => (float) data_get($general, 'length_cm', 0),
                    'width_cm'  => (float) data_get($general, 'width_cm', 0),
                    'height_cm' => (float) data_get($general, 'height_cm', 0),
                    'date_required_in_destination' => $parseDate(data_get($general, 'date_required_in_destination')),
                ];

                // 8) ===== NEW FIELDS FOR OLO (string) =====
                foreach ([
                             'factory_proforma_number','mbl_number','container_type','container_number','shipping_line',
                             'logistics_incoterm','reason','category','forwarder_name',
                             'cargo_invoice_number','tariff_type','route_label','arrival_status','arrival_port','departure_port',
                             'retail_group','customer_type','trading_company','service_provider','customs_dua','invoice',
                             'factura_merca','receipt_note','visibility_notes','price_incoterm','consolidator_name','vendor_number',
                         ] as $f) {
                    if (array_key_exists($f, $general)) {
                        $poData[$f] = $general[$f];
                    }
                }

                // Mapeo especial para case_number_file (expediente en el JSON)
                if (array_key_exists('expediente', $general)) {
                    $poData['case_number_file'] = $general['expediente'];
                }

                // 9) ===== NEW FIELDS FOR OLO (boolean) =====
                foreach ([
                             'is_dropship','applies_tlc','applies_af','port_of_loading_validated','has_facture_merca',
                             'used_rate_ok','uses_bonded_warehouse','apply_technical_note','etd_initial_validated',
                         ] as $f) {
                    $poData[$f] = $toBool(data_get($general, $f, false));
                }

                // 10) ===== NEW FIELDS FOR OLO (int) =====
                foreach (['delay_days','container_free_days','etd_dates_difference','eta_dates_difference'] as $f) {
                    if (($v = data_get($general, $f)) !== null && $v !== '') {
                        $poData[$f] = (int) $v;
                    }
                }

                // 11) ===== NEW FIELDS FOR OLO (decimal) =====
                foreach (['Invoice_amount','freight_amount','cbm'] as $f) {
                    if (($v = data_get($general, $f)) !== null && $v !== '') {
                        $poData[$f] = (float) $v;
                    }
                }

                // 12) ===== Fechas OLO y Fechas del Formulario =====
                foreach ([
                             'date_booking_request','date_booking_authorized','date_theorical_load','date_variable_date',
                             'date_carga_po','date_received',
                             'date_etd_initial','date_etd_updated','date_eta_updated',
                             'date_etd', 'date_atd', 'date_eta', 'date_ata',
                             'date_estimated_hub_arrival', 'date_actual_hub_arrival',
                             'inspection_date','vgm_cut_date','balance_payment_date','local_charges_payment_date',
                             'bonded_warehouse_enter','bonded_warehouse_exit','receipt_note_date',
                             'estimated_dc_availability_date','date_invoice_received','date_vendor_document_received','dif_load_date','emision_date_po','forwader_date',
                         ] as $f) {
                    if (array_key_exists($f, $general)) {
                        $poData[$f] = $parseDate($general[$f]);
                    }
                }

                // 13) Cálculo de diferencias (firmadas): positivo = atraso; negativo = adelanto
                // ETD: preferimos (updated - initial). Si no hay initial, caemos a (updated - etd).
                $etdBase = $poData['date_etd_initial'] ?? $poData['date_etd'] ?? null;
                $etdNew  = $poData['date_etd_updated']  ?? null;
                if ($etdBase && $etdNew) {
                    $poData['etd_dates_difference'] = $etdNew->copy()->startOfDay()
                        ->diffInDays($etdBase->copy()->startOfDay(), false);
                }

                // ETA: (updated - eta)
                $etaBase = $poData['date_eta'] ?? null;
                $etaNew  = $poData['date_eta_updated'] ?? null;
                if ($etaBase && $etaNew) {
                    $poData['eta_dates_difference'] = $etaNew->copy()->startOfDay()
                        ->diffInDays($etaBase->copy()->startOfDay(), false);
                }

                // 14) Limpiar null/"" pero mantener 0/false
                $poData = array_filter($poData, fn($v) => $v !== null && $v !== '');

                // 15) Crear PO
                $purchaseOrder = PurchaseOrder::create($poData);

                // 16) Ítems (si existen)
                if ($items) {
                    // Agrupar por material
                    $groupedItems = [];
                    foreach ($items as $it) {
                        $mat = data_get($it, 'material');
                        if (!$mat) continue;

                        $groupedItems[$mat]['material'] = $mat;
                        $groupedItems[$mat]['price_per_unit'] = (float) data_get($it, 'price_per_unit', 0);
                        $groupedItems[$mat]['peso_kg'] = ((float) ($groupedItems[$mat]['peso_kg'] ?? 0))
                            + (float) data_get($it, 'peso_kg', data_get($it, 'kgs', 0));
                    }

                    foreach ($groupedItems as $gi) {
                        //Busca el producto y si no, lo crea
                        $product = Product::firstOrCreate(
                            ['material_id' => $gi['material']],
                            [
                                'short_text'       => 'Product ' . $gi['material'],
                                'unit_of_measure'  => 'KG',
                                'price_per_unit'   => (float) $gi['price_per_unit'],
                            ]
                        );
                        // Actualizar precio si cambió
                        $newPrice = (float) $gi['price_per_unit'];
                        if (abs((float) $product->price_per_unit - $newPrice) > 0.0001) {
                            $product->price_per_unit = $newPrice;
                            $product->save();
                        }

                        $purchaseOrder->products()->attach($product->id, [
                            'quantity'   => (int) round((float) data_get($gi, 'peso_kg', 0)),
                            'unit_price' => $newPrice,
                        ]);
                    }
                }

                $results[] = [
                    'order_number' => $purchaseOrder->order_number,
                    'id'           => $purchaseOrder->id,
                    'status'       => 'success',
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders created successfully',
                'data'    => $results
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al crear órdenes de compra desde API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    public function deleteFromApi(Request $request, string $order_number): JsonResponse
    {
        $po = \App\Models\PurchaseOrder::where('order_number', $order_number)->firstOrFail();
        $po->delete(); // soft delete

        return response()->json([
            'success' => true,
            'message' => 'Orden de compra anulada con éxito.',
        ]);
    }

    /**
     * Update an existing purchase order from external API
     *
     * @param Request $request
     * @param int $po_id
     * @return JsonResponse
     */
    public function updateFromApi(Request $request, int $po_id): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1) Verificar que la PO existe
            $purchaseOrder = PurchaseOrder::where('order_number', $po_id)->firstOrFail();

            // 2) Verificar estado editable
            $nonEditableStatuses = ['shipped', 'delivered', 'cancelled'];
            if (in_array($purchaseOrder->status, $nonEditableStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede actualizar una orden en estado: ' . $purchaseOrder->status,
                    'error_code' => 'STATUS_NOT_EDITABLE'
                ], 409);
            }

            // 3) Verificar idempotencia si se proporciona
            $idempotencyKey = $request->header('Idempotency-Key');
            if ($idempotencyKey) {
                $existingUpdate = \Cache::get("po_update_{$idempotencyKey}");
                if ($existingUpdate) {
                    return response()->json($existingUpdate, 200);
                }
            }

            // 4) Obtener payload
            $payload = $request->json()->all();
            if (empty($payload)) {
                $payload = $request->all();
            }

            // 5) Validar que hay al menos un cambio
            if (empty($payload)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar al menos un campo para actualizar',
                    'error_code' => 'NO_CHANGES'
                ], 422);
            }

            // 6) Procesar cambios
            $changes = $this->processUpdateChanges($purchaseOrder, $payload);

            // 7) Guardar cambios
            $purchaseOrder->save();

            // 8) Preparar respuesta
            $response = [
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'status' => $purchaseOrder->status,
                    'updated_at' => $purchaseOrder->updated_at->toISOString(),
                    'changes' => $changes
                ]
            ];

            // 9) Cachear respuesta para idempotencia
            if ($idempotencyKey) {
                \Cache::put("po_update_{$idempotencyKey}", $response, 3600); // 1 hora
            }

            // 10) Registrar auditoría
            $this->logAudit($purchaseOrder, $changes, $request);

            DB::commit();

            return response()->json($response, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found',
                'error_code' => 'PO_NOT_FOUND'
            ], 404);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error al actualizar orden de compra desde API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'UPDATE_ERROR'
            ], 400);
        }
    }

    /**
     * Process update changes for purchase order (English - Only)
     */
    function processUpdateChanges(PurchaseOrder $po, array $payload): array
    {
        $changes = [];

        // Mapeo de campos de la API a campos del modelo (claves en INGLÉS)
        $fieldMapping = [
            // Campos básicos
            'order_number'            => 'order_number',
            'vendor_number'           => 'vendor_number',
            'vendor_name'             => 'vendor_name',
            'route_label'             => 'route_label',
            'net_total'               => 'net_total',
            'currency'                => 'currency',
            'emision_date_po'         => 'emision_date_po',
            'category'                => 'category',
            'incoterms'               => 'incoterms',
            'logistics_incoterm'      => 'logistics_incoterm',
            'price_incoterm'          => 'price_incoterm',

            // Fechas y diferencias
            'date_theorical_load'     => 'date_theorical_load',
            'dif_load_date'           => 'dif_load_date',
            'date_etd'                => 'date_etd',
            'date_eta'                => 'date_eta',
            'etd_dates_difference'    => 'etd_dates_difference',
            'eta_dates_difference'    => 'eta_dates_difference',

            // Otros
            'case_number_file'        => 'case_number_file',
            'status'                  => 'status',
            'departure_port'          => 'departure_port',
            'arrival_port'            => 'arrival_port',
            'customs_dua'             => 'customs_dua',
            'receipt_note'            => 'receipt_note',
            'receipt_note_date'       => 'receipt_note_date',
            'factory_proforma_number' => 'factory_proforma_number',
            'invoice'                 => 'invoice',
            'Invoice_amount'          => 'Invoice_amount',
            'applies_tlc'             => 'applies_tlc',
            'apply_technical_note'    => 'apply_technical_note',
            'reason'                  => 'reason',
            'customer_type'           => 'customer_type',

            // OLO existentes
            'retail_group'            => 'retail_group',
            'comments_count'          => 'comments_count', // calculado/solo referencia

        ];

        foreach ($payload as $apiField => $value) {
            if (!isset($fieldMapping[$apiField])) {
                continue; // Ignorar campos no mapeados
            }

            $modelField = $fieldMapping[$apiField];
            $oldValue = $po->$modelField;

            switch ($apiField) {
                case 'vendor_name': {
                    $vendor = \App\Models\Vendor::where('name', $value)->first();
                    if (!$vendor) {
                        throw new \Exception("Vendor not found: {$value}");
                    }
                    $po->vendor_id = $vendor->id;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $vendor->name];
                    break;
                }
                case 'vendor_number': {
                    $vendor = \App\Models\Vendor::where('vendo_code', $value)->first();
                    if (!$vendor) {
                        throw new \Exception("Vendor not found with code: {$value}");
                    }
                    $po->vendor_id = $vendor->id;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $vendor->vendo_code];
                    break;
                }

                // Fechas
                case 'emision_date_po':
                case 'date_theorical_load':
                case 'receipt_note_date':
                case 'date_etd':
                case 'date_eta': {
                    $po->$modelField = \Carbon\Carbon::parse($value);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }

                // Montos
                case 'net_total':
                case 'Invoice_amount': {
                    $po->$modelField = (float) $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (float) $value];
                    break;
                }

                // Booleanos
                case 'applies_tlc':
                case 'apply_technical_note': {
                    $po->$modelField = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (bool) $po->$modelField];
                    break;
                }

                // Diferencias (enteros)
                case 'etd_dates_difference':
                case 'eta_dates_difference':
                case 'dif_load_date': {
                    $po->$modelField = (int) $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (int) $value];
                    break;
                }

                // Enums
                case 'status': {
                    $allowedStatuses = ['draft', 'pending', 'approved', 'shipped', 'delivered', 'cancelled'];
                    if (!in_array($value, $allowedStatuses, true)) {
                        throw new \Exception("Invalid status: {$value}. Allowed: " . implode(', ', $allowedStatuses));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }
                case 'currency': {
                    $allowedCurrencies = ['USD', 'EUR', 'CRC'];
                    if (!in_array($value, $allowedCurrencies, true)) {
                        throw new \Exception("Invalid currency: {$value}. Allowed: " . implode(', ', $allowedCurrencies));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }
                case 'incoterms':
                case 'logistics_incoterm':
                case 'price_incoterm': {
                    $allowedIncoterms = ['CIF','CIP','CFR','CPT','DAT','DAP','DDP','DEQ','DES','EXD','EXQ','EXW','FCA','FOB'];
                    if (!in_array($value, $allowedIncoterms, true)) {
                        throw new \Exception("Invalid incoterm: {$value}. Allowed: " . implode(', ', $allowedIncoterms));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }

                // Default: texto/otros (incluye case_number_file)
                default: {
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }
            }
        }

        // Recalcular totales si se actualizó net_total
        if (isset($payload['net_total'])) {
            $po->total = $po->net_total;
        }

        return $changes;
    }

    /**
     * Log audit information
     */
    private function logAudit(PurchaseOrder $po, array $changes, Request $request): void
    {
        $auditData = [
            'po_id' => $po->id,
            'order_number' => $po->order_number,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->header('X-Request-ID'),
            'idempotency_key' => $request->header('Idempotency-Key'),
            'changes' => $changes,
            'timestamp' => now()->toISOString()
        ];

        \Log::info('Purchase Order Updated via API', $auditData);
    }
}
