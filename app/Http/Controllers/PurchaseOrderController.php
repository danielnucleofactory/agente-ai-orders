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

                    // Proveedor: al menos UNA de estas 3
                    'vendor_id'   => ['required_without_all:vendor,vendor_name'],
                    'vendor'      => ['required_without_all:vendor_id,vendor_name'],
                    'vendor_name' => ['required_without_all:vendor_id,vendor'],
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

                    'vendor_id.required_without_all'   => 'Debe enviar al menos uno de: vendor_id, vendor o vendor_name.',
                    'vendor.required_without_all'      => 'Debe enviar al menos uno de: vendor_id, vendor o vendor_name.',
                    'vendor_name.required_without_all' => 'Debe enviar al menos uno de: vendor_id, vendor o vendor_name.',
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
                $shipToName = data_get($general, 'ship_to_id') ?? data_get($general, 'ship_to') ?? 'Ship To con falta de datos';
                $billToName = data_get($general, 'bill_to_id') ?? data_get($general, 'bill_to') ?? 'Bill To con falta de datos';
                $hubCode    = data_get($general, 'planned_hub_id') ?? data_get($general, 'hub') ?? 'HUB_DEFAULT';

                // Buscar o crear vendor
                if ($vendorId) {
                    $vendor = Vendor::where('vendo_code', $vendorId)->first();
                    if (!$vendor) {
                        // Crear vendor con vendo_code y nombre por defecto
                        $vendor = Vendor::create([
                            'company_id' => 1, // Usar company_id por defecto, se actualizará después
                            'vendo_code' => $vendorId,
                            'name' => 'Proveedor con falta de datos ' . $vendorId,
                            'status' => 'active'
                        ]);
                    }
                } else {
                    $vendor = Vendor::where('name', $vendorName)->first();
                    if (!$vendor) {
                        // Crear vendor con nombre
                        $vendor = Vendor::create([
                            'company_id' => 1, // Usar company_id por defecto, se actualizará después
                            'name' => $vendorName,
                            'vendo_code' => 'VENDOR_' . time(), // Generar código único
                            'status' => 'active'
                        ]);
                    }
                }

                // Obtener company_id del vendor
                $companyId = $vendor->company_id;

                // Buscar o crear ShipTo
                $shipTo = ShipTo::where('name', $shipToName)->first();
                if (!$shipTo) {
                    $shipTo = ShipTo::create([
                        'company_id' => $companyId,
                        'name' => $shipToName,
                        'status' => 'active'
                    ]);
                }

                // Buscar o crear BillTo
                $billTo = BillTo::where('name', $billToName)->first();
                if (!$billTo) {
                    $billTo = BillTo::create([
                        'company_id' => $companyId,
                        'name' => $billToName
                    ]);
                }

                // Buscar o crear Hub
                $hub = Hub::where('code', $hubCode)->first();
                if (!$hub) {
                    $hub = Hub::create([
                        'code' => $hubCode,
                        'name' => 'Hub con falta de datos ' . $hubCode,
                        'country' => 'Unknown',
                        'operation_days' => 0
                    ]);
                }

                // 5) Totales
                $totalWeight = 0;
                foreach ($items as $item) {
                    $totalWeight += (float) data_get($item, 'peso_kg', data_get($item, 'kgs', 0));
                }
                $netTotal  = (float) (data_get($general, 'netValue', data_get($general, 'net_total', 0)));

                // 6) (Opcional) Kanban inicial
                $kanbanStatusId = null;
                $kanbanBoard = KanbanBoard::where('company_id', $companyId)
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
                    'company_id'   => $companyId,
                    'order_number' => data_get($general, 'order_number') ?? \Illuminate\Support\Str::uuid()->toString(),
                    'status'       => 'draft',
                    'vendor_id'    => $vendor->id,
                    'ship_to_id'   => $shipTo->id,
                    'bill_to_id'   => $billTo->id,
                    'order_date'   => now(),
                    'currency'     => data_get($general, 'currency', 'USD'),
                    'incoterms'    => data_get($general, 'incoterms', 'EXW'),
                    'net_total'    => $netTotal,
                    'total'        => $netTotal,
                    'weight_kg'    => $totalWeight,
                    'planned_hub_id' => $hub->id,
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
            $purchaseOrder = PurchaseOrder::findOrFail($po_id);

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
     * Process update changes for purchase order
     */
    private function processUpdateChanges(PurchaseOrder $po, array $payload): array
    {
        $changes = [];
        
        // Mapeo de campos de la API a campos del modelo
        $fieldMapping = [
            // Campos básicos
            'PO' => 'order_number',
            'PROVEEDOR_NO' => 'vendor_number',
            'PROVEEDOR_NOMBRE' => 'vendor_name',
            'RUTA_LOGISTICA' => 'route_label',
            'MONTO_PO' => 'net_total',
            'MONEDA_PO' => 'currency',
            'FECHA_EMISION_PO' => 'emision_date_po',
            'CATEGORIA' => 'category',
            'INCOTERM_COMPRA' => 'incoterms',
            'INCOTERM_LOGISTICA' => 'logistics_incoterm',
            'INCOTERM_PRECIOS' => 'price_incoterm',
            'FECHA_CARGOLIST' => 'date_theorical_load',
            'DIF_FECHA_CARGA' => 'dif_load_date',
            'ETD_ESTIMADO' => 'date_etd',
            'DIF_FECHAS_ETD' => 'etd_dates_difference',
            'ETA_ESTIMADO' => 'date_eta',
            'DIF_FECHAS_ETA' => 'eta_dates_difference',
            'EXPEDIENTE' => 'case_number_file',
            'ESTADO' => 'status',
            'PUERTO_EMBARQUE' => 'departure_port',
            'PUERTO_ARRIBO' => 'arrival_port',
            'DUA_INTERNAMIENTO' => 'customs_dua',
            'NOTA_RECIBO' => 'receipt_note',
            'FECHA_NR' => 'receipt_note_date',
            'PROFORMA_FABRICA' => 'factory_proforma_number',
            'FACTURA' => 'invoice',
            'MONTO_FACTURA' => 'Invoice_amount',
            'APLICA_TLC' => 'applies_tlc',
            'APLICA_NOTA_TECNICA' => 'apply_technical_note',
            'MOTIVO' => 'reason',
            'TIPO_CLIENTE' => 'customer_type',
            
            // Campos adicionales OLO
            'GRUPO_REPOSITOR' => 'retail_group',
            'CANT_COMENTARIOS' => 'comments_count', // Campo calculado
        ];

        // Procesar cada campo del payload
        foreach ($payload as $apiField => $value) {
            if (!isset($fieldMapping[$apiField])) {
                continue; // Ignorar campos no mapeados
            }

            $modelField = $fieldMapping[$apiField];
            $oldValue = $po->$modelField;

            // Procesar campos especiales
            switch ($apiField) {
                case 'PROVEEDOR_NOMBRE':
                    // Buscar vendor por nombre
                    $vendor = Vendor::where('name', $value)->first();
                    if ($vendor) {
                        $po->vendor_id = $vendor->id;
                        $changes[$apiField] = ['old' => $oldValue, 'new' => $vendor->name];
                    } else {
                        throw new \Exception("Vendor not found: {$value}");
                    }
                    break;

                case 'PROVEEDOR_NO':
                    // Buscar vendor por código
                    $vendor = Vendor::where('vendo_code', $value)->first();
                    if ($vendor) {
                        $po->vendor_id = $vendor->id;
                        $changes[$apiField] = ['old' => $oldValue, 'new' => $vendor->vendo_code];
                    } else {
                        throw new \Exception("Vendor not found with code: {$value}");
                    }
                    break;

                case 'FECHA_EMISION_PO':
                case 'FECHA_CARGOLIST':
                case 'FECHA_NR':
                    // Procesar fechas
                    $po->$modelField = \Carbon\Carbon::parse($value);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;

                case 'ETD_ESTIMADO':
                case 'ETA_ESTIMADO':
                    // Procesar fechas ETD/ETA
                    $po->$modelField = \Carbon\Carbon::parse($value);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;

                case 'MONTO_PO':
                case 'MONTO_FACTURA':
                    // Procesar montos
                    $po->$modelField = (float) $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (float) $value];
                    break;

                case 'APLICA_TLC':
                case 'APLICA_NOTA_TECNICA':
                    // Procesar booleanos
                    $po->$modelField = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (bool) $value];
                    break;

                case 'DIF_FECHAS_ETD':
                case 'DIF_FECHAS_ETA':
                    // Procesar diferencias de fechas
                    $po->$modelField = (int) $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (int) $value];
                    break;

                case 'ESTADO':
                    // Validar estado
                    $allowedStatuses = ['draft', 'pending', 'approved', 'shipped', 'delivered', 'cancelled'];
                    if (!in_array($value, $allowedStatuses)) {
                        throw new \Exception("Invalid status: {$value}. Allowed: " . implode(', ', $allowedStatuses));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;

                case 'MONEDA_PO':
                    // Validar moneda
                    $allowedCurrencies = ['USD', 'EUR', 'CRC'];
                    if (!in_array($value, $allowedCurrencies)) {
                        throw new \Exception("Invalid currency: {$value}. Allowed: " . implode(', ', $allowedCurrencies));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;

                case 'INCOTERM_COMPRA':
                case 'INCOTERM_LOGISTICA':
                case 'INCOTERM_PRECIOS':
                    // Validar incoterms
                    $allowedIncoterms = ['CIF', 'CIP', 'CFR', 'CPT', 'DAT', 'DAP', 'DDP', 'DEQ', 'DES', 'EXD', 'EXQ', 'EXW', 'FCA', 'FOB'];
                    if (!in_array($value, $allowedIncoterms)) {
                        throw new \Exception("Invalid incoterm: {$value}. Allowed: " . implode(', ', $allowedIncoterms));
                    }
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;

                default:
                    // Campos de texto simples
                    $po->$modelField = $value;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
            }
        }

        // Recalcular totales si se actualizó el monto
        if (isset($payload['MONTO_PO'])) {
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
