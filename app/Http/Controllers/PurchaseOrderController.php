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
                    'trading_company'              => ['required','string'],
                    'currency'               => ['required','string'],
                    /*'category'               => ['required','string'],
                    'factory_proforma_number'=> ['nullable','string'],
                    'route_label'            => ['required','string'],
                    'date_theorical_load'    => [
                        'required',
                        'date',
                        function ($attribute, $value, $fail) use ($request) {
                            $emisionDate = $request->input('emision_date_po');

                            if ($emisionDate && $value < $emisionDate) {
                                $fail('La fecha de carga lista teórica no puede ser anterior a la fecha de emisión de la PO (' . formatDate($emisionDate) . ')');
                            }
                        }
                    ],
                    'reason'                 => ['required','string'],
                    'incoterms'              => ['required','string'],
                    'logistics_incoterm'     => ['required','string'],
                    'price_incoterm'         => ['required','string'],*/
                ];

                $messages = [
                    'order_number.required'            => 'El campo "P.O." es obligatorio.',
                    'trading_company.required'         => 'El campo "Compañía" es obligatorio.',
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

                // Buscar o crear vendor
                $vendor = null;
                if ($vendorId) {
                    // Tratar vendor_id del JSON como vendo_code
                    $vendor = Vendor::where('vendo_code', $vendorId)->first();
                    if (!$vendor) {
                        $vendor = Vendor::create([
                            'company_id' => 1,
                            'vendo_code' => (string) $vendorId,
                            'name' => $vendorName ?: ('Proveedor ' . $vendorId),
                            'status' => 'active',
                        ]);
                    }
                } elseif ($vendorName) {
                    $vendor = Vendor::where('name', $vendorName)->first();
                    if (!$vendor) {
                        $vendor = Vendor::create([
                            'company_id' => 1,
                            'name' => $vendorName,
                            'vendo_code' => 'VENDOR_' . time(),
                            'status' => 'active',
                        ]);
                    }
                }

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

                // Asignar relación con vendor si se resolvió
                if ($vendor) {
                    $poData['vendor_id'] = $vendor->id;
                    $poData['vendor_number'] = $vendor->vendo_code;
                }

                // 8) ===== NEW FIELDS FOR OLO (string) =====
                foreach ([
                             'factory_proforma_number','mbl_number','container_type','container_number','shipping_line',
                             'logistics_incoterm','reason','category','forwarder_name',
                             'cargo_invoice_number','tariff_type','route_label','arrival_status','arrival_port','departure_port',
                             'retail_group','customer_type','trading_company','service_provider','customs_dua','invoice',
                             'factura_merca','receipt_note','visibility_notes','price_incoterm','consolidator_name','vendor_number',
                         ] as $f) {
                    // Verificar si el campo existe en el array (incluso si el valor es null)
                    if (array_key_exists($f, $general)) {
                        $value = $general[$f];
                        // Si viene como string vacío, convertir a null; si tiene valor (incluyendo null explícito), guardarlo
                        $poData[$f] = ($value === '') ? null : $value;
                    }
                }

                // Mapeo especial para case_number_file (expediente en el JSON o case_number_file directo)
                if (array_key_exists('expediente', $general)) {
                    $poData['case_number_file'] = $general['expediente'];
                } elseif (array_key_exists('case_number_file', $general)) {
                    $poData['case_number_file'] = $general['case_number_file'];
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
                foreach (['Invoice_amount','freight_amount','cbm','total_amount'] as $f) {
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

                // 14) Limpiar null/"" pero mantener 0/false y campos opcionales con null
                // Campos de texto opcionales que pueden ser null (como mbl_number)
                $optionalTextFields = ['mbl_number', 'factory_proforma_number', 'factura_merca', 'case_number_file'];
                $poData = array_filter($poData, function($v, $k) use ($optionalTextFields) {
                    // Permitir null para campos de texto opcionales (para que se guarden explícitamente como null)
                    if (in_array($k, $optionalTextFields)) {
                        return true; // Mantener siempre estos campos, incluso si son null
                    }
                    // Para otros campos, eliminar null y strings vacíos
                    return $v !== null && $v !== '';
                }, ARRAY_FILTER_USE_BOTH);

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

    /**
     * Create a single purchase order from data array
     *
     * @param array $orderData Array with 'general' and 'items' keys
     * @return PurchaseOrder|null
     * @throws \Exception
     */
    private function createSinglePurchaseOrder(array $orderData): ?PurchaseOrder
    {
        // Asegurar estructuras
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
            'order_number'    => ['required','string'],
            'trading_company' => ['required','string'],
        ];

        $messages = [
            'order_number.required'    => 'El campo "P.O." es obligatorio.',
            'trading_company.required' => 'El campo "Compañía" es obligatorio.',
        ];

        $validator = Validator::make($general, $rules, $messages);
        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . $validator->errors()->first());
        }

        // Relaciones (se buscan por nombre/código y se crean si no existen)
        $vendorId = data_get($general, 'vendor_id');
        $vendorName = data_get($general, 'vendor') ?? data_get($general, 'vendor_name');

        // Buscar o crear vendor
        $vendor = null;
        if ($vendorId) {
            $vendor = Vendor::where('vendo_code', $vendorId)->first();
            if (!$vendor) {
                $vendor = Vendor::create([
                    'company_id' => 1,
                    'vendo_code' => (string) $vendorId,
                    'name' => $vendorName ?: ('Proveedor ' . $vendorId),
                    'status' => 'active',
                ]);
            }
        } elseif ($vendorName) {
            $vendor = Vendor::where('name', $vendorName)->first();
            if (!$vendor) {
                $vendor = Vendor::create([
                    'company_id' => 1,
                    'name' => $vendorName,
                    'vendo_code' => 'VENDOR_' . time(),
                    'status' => 'active',
                ]);
            }
        }

        // Totales
        $totalWeight = 0;
        foreach ($items as $item) {
            $totalWeight += (float) data_get($item, 'peso_kg', data_get($item, 'kgs', 0));
        }
        $netTotal = (float) (data_get($general, 'netValue', data_get($general, 'net_total', 0)));

        // Kanban inicial
        $kanbanStatusId = null;
        $kanbanBoard = KanbanBoard::where('company_id', 1)
            ->where('type', 'po_stages')
            ->where('is_active', true)
            ->first();

        if ($kanbanBoard) {
            $status = $kanbanBoard->statuses()->where('name', 'Recepción')->first()
                ?: $kanbanBoard->defaultStatus();
            $kanbanStatusId = $status?->id;
        }

        // Campos base
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

        // Asignar relación con vendor si se resolvió
        if ($vendor) {
            $poData['vendor_id'] = $vendor->id;
            $poData['vendor_number'] = $vendor->vendo_code;
        }

        // NEW FIELDS FOR OLO (string)
        foreach ([
                     'factory_proforma_number','mbl_number','container_type','container_number','shipping_line',
                     'logistics_incoterm','reason','category','forwarder_name',
                     'cargo_invoice_number','tariff_type','route_label','arrival_status','arrival_port','departure_port',
                     'retail_group','customer_type','trading_company','service_provider','customs_dua','invoice',
                     'factura_merca','receipt_note','visibility_notes','price_incoterm','consolidator_name','vendor_number',
                     'insurance_type',
                 ] as $f) {
            // Verificar si el campo existe en el array (incluso si el valor es null)
            if (array_key_exists($f, $general)) {
                $value = $general[$f];
                // Si viene como string vacío, convertir a null; si tiene valor (incluyendo null explícito), guardarlo
                $poData[$f] = ($value === '') ? null : $value;
            }
        }

        // Mapeo especial para case_number_file
        if (array_key_exists('expediente', $general)) {
            $poData['case_number_file'] = $general['expediente'];
        } elseif (array_key_exists('case_number_file', $general)) {
            $poData['case_number_file'] = $general['case_number_file'];
        }

        // NEW FIELDS FOR OLO (boolean)
        foreach ([
                     'is_dropship','applies_tlc','applies_af','port_of_loading_validated','has_facture_merca',
                     'used_rate_ok','uses_bonded_warehouse','apply_technical_note','etd_initial_validated',
                 ] as $f) {
            $poData[$f] = $toBool(data_get($general, $f, false));
        }

        // NEW FIELDS FOR OLO (int)
        foreach (['delay_days','container_free_days','etd_dates_difference','eta_dates_difference'] as $f) {
            if (($v = data_get($general, $f)) !== null && $v !== '') {
                $poData[$f] = (int) $v;
            }
        }

        // NEW FIELDS FOR OLO (decimal)
        foreach (['Invoice_amount','freight_amount','cbm','total_amount','other_expenses'] as $f) {
            if (($v = data_get($general, $f)) !== null && $v !== '') {
                $poData[$f] = (float) $v;
            }
        }

        // Fechas OLO
        foreach ([
                     'date_booking_request','date_booking_authorized','date_theorical_load','date_variable_date',
                     'date_carga_po','date_received',
                     'date_etd_initial','date_etd_updated','date_eta_updated','date_eta_initial',
                     'date_etd', 'date_atd', 'date_eta', 'date_ata',
                     'date_estimated_hub_arrival', 'date_actual_hub_arrival',
                     'inspection_date','vgm_cut_date','balance_payment_date','local_charges_payment_date',
                     'bonded_warehouse_enter','bonded_warehouse_exit','receipt_note_date',
                     'estimated_dc_availability_date','date_invoice_received','date_vendor_document_received','dif_load_date','emision_date_po','forwader_date',
                     'date_consolidation',
                 ] as $f) {
            if (array_key_exists($f, $general)) {
                $poData[$f] = $parseDate($general[$f]);
            }
        }

        // Cálculo de diferencias
        $etdBase = $poData['date_etd_initial'] ?? $poData['date_etd'] ?? null;
        $etdNew  = $poData['date_etd_updated'] ?? null;
        if ($etdBase && $etdNew) {
            $poData['etd_dates_difference'] = $etdNew->copy()->startOfDay()
                ->diffInDays($etdBase->copy()->startOfDay(), false);
        }

        $etaBase = $poData['date_eta'] ?? null;
        $etaNew  = $poData['date_eta_updated'] ?? null;
        if ($etaBase && $etaNew) {
            $poData['eta_dates_difference'] = $etaNew->copy()->startOfDay()
                ->diffInDays($etaBase->copy()->startOfDay(), false);
        }

        // Limpiar null pero mantener campos opcionales con null
        $optionalTextFields = ['mbl_number', 'factory_proforma_number', 'factura_merca', 'case_number_file'];
        $poData = array_filter($poData, function($v, $k) use ($optionalTextFields) {
            // Permitir siempre estos campos opcionales, incluso si son null (para que se guarden explícitamente)
            if (in_array($k, $optionalTextFields)) {
                return true; // Mantener siempre estos campos, incluso si son null
            }
            // Para otros campos, eliminar null y strings vacíos
            return $v !== null && $v !== '';
        }, ARRAY_FILTER_USE_BOTH);

        // Crear PO
        $purchaseOrder = PurchaseOrder::create($poData);

        // Ítems (si existen)
        if ($items) {
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
                $product = Product::firstOrCreate(
                    ['material_id' => $gi['material']],
                    [
                        'short_text'       => 'Product ' . $gi['material'],
                        'unit_of_measure'  => 'KG',
                        'price_per_unit'   => (float) $gi['price_per_unit'],
                    ]
                );
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

        return $purchaseOrder;
    }

    public function deleteFromApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'trading_company' => 'required|string',
        ]);

        $po = \App\Models\PurchaseOrder::where('order_number', $validated['order_number'])
            ->where('trading_company', $validated['trading_company'])
            ->firstOrFail();

        $po->delete();

        return response()->json([
            'success' => true,
            'message' => 'Orden de compra anulada con éxito.',
        ]);
    }

    /**
     * Delete a purchase order (soft delete)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'trading_company' => 'required|string',
        ]);

        $po = PurchaseOrder::where('order_number', $validated['order_number'])
            ->where('trading_company', $validated['trading_company'])
            ->firstOrFail();

        $po->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase order deleted successfully.',
        ]);
    }

    /**
     * Update an existing purchase order from external API
     *
     * @param Request $request
     * @param string $po_id
     * @return JsonResponse
     */
    public function updateFromApi(Request $request, string $po_id): JsonResponse
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
            'trading_company'         => 'trading_company',
            'mode'                    => 'mode',

            // Transporte y contenedores
            'shipping_line'           => 'shipping_line',
            'service_provider'        => 'service_provider',
            'tariff_type'             => 'tariff_type',
            'container_type'          => 'container_type',
            'container_number'        => 'container_number',
            'consolidator_name'       => 'consolidator_name',
            'forwarder_name'          => 'forwarder_name',

            // Puertos
            'departure_port'          => 'departure_port',
            'arrival_port'            => 'arrival_port',

            // Dimensiones y peso
            'cbm'                     => 'cbm',
            'weight_kg'               => 'weight_kg',
            'weight_lb'               => 'weight_lb',
            'pallet_quantity'         => 'pallet_quantity',
            'pallet_quantity_real'    => 'pallet_quantity_real',

            // Fechas
            'date_booking_request'    => 'date_booking_request',
            'date_booking_authorized' => 'date_booking_authorized',
            'forwader_date'           => 'forwader_date',
            'inspection_date'         => 'inspection_date',
            'vgm_cut_date'            => 'vgm_cut_date',
            'date_theorical_load'     => 'date_theorical_load',
            'date_variable_date'      => 'date_variable_date',
            'date_carga_po'           => 'date_carga_po',
            'release_date'            => 'release_date',
            'date_consolidation'      => 'date_consolidation',
            'date_etd_initial'        => 'date_etd_initial',
            'date_etd'                => 'date_etd',
            'date_atd'                => 'date_atd',
            'date_eta'                => 'date_eta',
            'date_eta_initial'        => 'date_eta_initial',
            'date_ata'                => 'date_ata',
            'bonded_warehouse_enter'  => 'bonded_warehouse_enter',
            'bonded_warehouse_exit'   => 'bonded_warehouse_exit',
            'receipt_note_date'       => 'receipt_note_date',
            'estimated_dc_availability_date' => 'estimated_dc_availability_date',
            'balance_payment_date'    => 'balance_payment_date',
            'local_charges_payment_date' => 'local_charges_payment_date',
            'date_invoice_received'   => 'date_invoice_received',
            'date_vendor_document_received' => 'date_vendor_document_received',

            // Diferencias y enteros
            'dif_load_date'           => 'dif_load_date',
            'etd_dates_difference'    => 'etd_dates_difference',
            'eta_dates_difference'    => 'eta_dates_difference',
            'container_free_days'     => 'container_free_days',
            'delay_days'              => 'delay_days',

            // Identificadores
            'case_number_file'        => 'case_number_file',
            'factory_proforma_number' => 'factory_proforma_number',
            'mbl_number'              => 'mbl_number',
            'tracking_id'             => 'tracking_id',
            'invoice'                 => 'invoice',
            'cargo_invoice_number'    => 'cargo_invoice_number',
            'factura_merca'           => 'factura_merca',
            'customs_dua'             => 'customs_dua',
            'receipt_note'            => 'receipt_note',

            // Costos y montos
            'Invoice_amount'          => 'Invoice_amount',
            'freight_amount'          => 'freight_amount',
            'other_expenses'          => 'other_expenses',
            'total_amount'            => 'total_amount',
            'estimated_pallet_cost'   => 'estimated_pallet_cost',
            'real_cost_estimated_po'  => 'real_cost_estimated_po',
            'real_cost_real_po'       => 'real_cost_real_po',

            // Booleanos
            'applies_tlc'             => 'applies_tlc',
            'apply_technical_note'    => 'apply_technical_note',
            'port_of_loading_validated' => 'port_of_loading_validated',
            'has_facture_merca'       => 'has_facture_merca',
            'used_rate_ok'            => 'used_rate_ok',
            'uses_bonded_warehouse'   => 'uses_bonded_warehouse',
            'etd_initial_validated'   => 'etd_initial_validated',

            // Texto y otros
            'reason'                  => 'reason',
            'customer_type'           => 'customer_type',
            'retail_group'            => 'retail_group',
            'insurance_type'          => 'insurance_type',
            'visibility_notes'        => 'visibility_notes',
            'comments'                => 'comments',
            'arrival_status'         => 'arrival_status',
            'status'                  => 'status',
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
                case 'date_booking_request':
                case 'date_booking_authorized':
                case 'forwader_date':
                case 'inspection_date':
                case 'vgm_cut_date':
                case 'date_theorical_load':
                case 'date_variable_date':
                case 'date_carga_po':
                case 'release_date':
                case 'date_consolidation':
                case 'date_etd_initial':
                case 'date_etd':
                case 'date_atd':
                case 'date_eta':
                case 'date_eta_initial':
                case 'date_ata':
                case 'bonded_warehouse_enter':
                case 'bonded_warehouse_exit':
                case 'receipt_note_date':
                case 'estimated_dc_availability_date':
                case 'balance_payment_date':
                case 'local_charges_payment_date':
                case 'date_invoice_received':
                case 'date_vendor_document_received': {
                    $po->$modelField = $value ? \Carbon\Carbon::parse($value) : null;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $value];
                    break;
                }

                // Montos (decimales)
                case 'net_total':
                case 'Invoice_amount':
                case 'freight_amount':
                case 'other_expenses':
                case 'total_amount':
                case 'estimated_pallet_cost':
                case 'real_cost_estimated_po':
                case 'real_cost_real_po':
                case 'cbm':
                case 'weight_kg':
                case 'weight_lb': {
                    $po->$modelField = ($value !== null && $value !== '') ? (float) $value : null;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => ($value !== null && $value !== '') ? (float) $value : null];
                    break;
                }

                // Booleanos
                case 'applies_tlc':
                case 'apply_technical_note':
                case 'port_of_loading_validated':
                case 'has_facture_merca':
                case 'used_rate_ok':
                case 'uses_bonded_warehouse':
                case 'etd_initial_validated': {
                    $po->$modelField = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $changes[$apiField] = ['old' => $oldValue, 'new' => (bool) $po->$modelField];
                    break;
                }

                // Enteros
                case 'etd_dates_difference':
                case 'eta_dates_difference':
                case 'dif_load_date':
                case 'container_free_days':
                case 'delay_days':
                case 'pallet_quantity':
                case 'pallet_quantity_real': {
                    $po->$modelField = ($value !== null && $value !== '') ? (int) $value : null;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => ($value !== null && $value !== '') ? (int) $value : null];
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

                // Default: texto/otros (incluye case_number_file, mbl_number, etc.)
                default: {
                    // Convertir strings vacíos a null para campos de texto opcionales
                    $finalValue = ($value === '') ? null : $value;
                    $po->$modelField = $finalValue;
                    $changes[$apiField] = ['old' => $oldValue, 'new' => $finalValue];
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

    public function bulk(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = $request->all();
        }

        // Normalizar a lista de items
        $items = [];
        if (isset($payload['items']) && is_array($payload['items'])) {
            $items = $payload['items'];
        } elseif (is_array($payload) && isset($payload[0])) {
            $items = $payload; // array plano
        } elseif (!empty($payload)) {
            $items = [$payload]; // objeto único
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No items received',
                'results' => []
            ], 422);
        }

        $results = [];

        foreach ($items as $index => $item) {
            // Validar únicamente order_number y trading_company
            $orderNumber = data_get($item, 'order_number');
            $tradingCompany = data_get($item, 'trading_company');

            if (!$orderNumber || !$tradingCompany) {
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'validation_error',
                    'message' => 'order_number and trading_company are required'
                ];
                continue;
            }

            // Buscar PO activa
            $po = PurchaseOrder::where('order_number', $orderNumber)
                ->where('trading_company', $tradingCompany)
                ->first();

            if (!$po) {
                // Si no está activa, verificar si existe eliminada
                $deleted = PurchaseOrder::onlyTrashed()
                    ->where('order_number', $orderNumber)
                    ->where('trading_company', $tradingCompany)
                    ->first();

                if ($deleted) {
                    $results[] = [
                        'index' => $index,
                        'order_number' => $orderNumber,
                        'trading_company' => $tradingCompany,
                        'status' => 'deleted',
                        'message' => 'Purchase order is deleted',
                        'deleted_at' => $deleted->deleted_at,
                    ];
                    continue;
                }

                // Si no existe, intentar crearla
                try {
                    DB::beginTransaction();

                    // Preparar el item como si fuera para createFromApi
                    $createPayload = [
                        'general' => $item,
                        'items' => $item['items'] ?? []
                    ];

                    // Crear la PO usando la misma lógica de createFromApi
                    $createdPo = $this->createSinglePurchaseOrder($createPayload);

                    if ($createdPo) {
                        DB::commit();
                        $results[] = [
                            'index' => $index,
                            'order_number' => $orderNumber,
                            'trading_company' => $tradingCompany,
                            'status' => 'created',
                            'message' => 'Purchase order created successfully',
                            'id' => $createdPo->id,
                            'created_at' => $createdPo->created_at?->toISOString(),
                    ];
                } else {
                        DB::rollBack();
                    $results[] = [
                        'index' => $index,
                        'order_number' => $orderNumber,
                            'trading_company' => $tradingCompany,
                            'status' => 'failed',
                            'message' => 'Failed to create purchase order',
                        ];
                    }
                } catch (\Throwable $e) {
                    DB::rollBack();
                    $results[] = [
                        'index' => $index,
                        'order_number' => $orderNumber,
                        'trading_company' => $tradingCompany,
                        'status' => 'failed',
                        'message' => 'Error creating purchase order: ' . $e->getMessage(),
                        'error' => $e->getMessage(),
                    ];
                }
                continue;
            }

            // Construir payload de actualización: permitir todos los campos excepto order_number/trading_company
            $updatePayload = $item;
            unset($updatePayload['order_number'], $updatePayload['trading_company']);

            if (empty($updatePayload)) {
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'skipped',
                    'message' => 'No updatable fields provided'
                ];
                continue;
            }

            try {
                DB::beginTransaction();
                // Reutilizar el mapeo/validaciones de processUpdateChanges
                $changes = $this->processUpdateChanges($po, $updatePayload);
                $po->save();

                $this->logAudit($po, $changes, $request);
                DB::commit();

                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'updated',
                    'updated_at' => $po->updated_at?->toISOString(),
                    'changes' => $changes,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Resumen
        $summary = [
            'total' => count($results),
            'updated' => collect($results)->where('status', 'updated')->count(),
            'deleted' => collect($results)->where('status', 'deleted')->count(),
            'not_found' => collect($results)->where('status', 'not_found')->count(),
            'skipped' => collect($results)->where('status', 'skipped')->count(),
            'validation_error' => collect($results)->where('status', 'validation_error')->count(),
            'error' => collect($results)->where('status', 'error')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Bulk update processed',
            'summary' => $summary,
            'results' => $results,
        ]);
    }

    /**
     * Bulk update purchase orders (only updates, does not create)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        if (empty($payload)) {
            $payload = $request->all();
        }

        // Normalizar a lista de items
        $items = [];
        if (isset($payload['items']) && is_array($payload['items'])) {
            $items = $payload['items'];
        } elseif (is_array($payload) && isset($payload[0])) {
            $items = $payload; // array plano
        } elseif (!empty($payload)) {
            $items = [$payload]; // objeto único
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No items received',
                'results' => []
            ], 422);
        }

        $results = [];

        foreach ($items as $index => $item) {
            // Validar únicamente order_number y trading_company
            $orderNumber = data_get($item, 'order_number');
            $tradingCompany = data_get($item, 'trading_company');

            if (!$orderNumber || !$tradingCompany) {
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'validation_error',
                    'message' => 'order_number and trading_company are required'
                ];
                continue;
            }

            // Buscar PO activa
            $po = PurchaseOrder::where('order_number', $orderNumber)
                ->where('trading_company', $tradingCompany)
                ->first();

            if (!$po) {
                // Si no está activa, verificar si existe eliminada
                $deleted = PurchaseOrder::onlyTrashed()
                    ->where('order_number', $orderNumber)
                    ->where('trading_company', $tradingCompany)
                    ->first();

                if ($deleted) {
                    $results[] = [
                        'index' => $index,
                        'order_number' => $orderNumber,
                        'trading_company' => $tradingCompany,
                        'status' => 'deleted',
                        'message' => 'Purchase order is deleted',
                        'deleted_at' => $deleted->deleted_at,
                    ];
                } else {
                    // PO no encontrada - reportar error (NO crear)
                    $results[] = [
                        'index' => $index,
                        'order_number' => $orderNumber,
                        'trading_company' => $tradingCompany,
                        'status' => 'not_found',
                        'message' => 'Purchase order not found'
                    ];
                }
                continue;
            }

            // Construir payload de actualización: permitir todos los campos excepto order_number/trading_company
            $updatePayload = $item;
            unset($updatePayload['order_number'], $updatePayload['trading_company']);

            if (empty($updatePayload)) {
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'skipped',
                    'message' => 'No updatable fields provided'
                ];
                continue;
            }

            try {
                DB::beginTransaction();
                // Reutilizar el mapeo/validaciones de processUpdateChanges
                $changes = $this->processUpdateChanges($po, $updatePayload);
                $po->save();

                $this->logAudit($po, $changes, $request);
                DB::commit();

                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'updated',
                    'updated_at' => $po->updated_at?->toISOString(),
                    'changes' => $changes,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                $results[] = [
                    'index' => $index,
                    'order_number' => $orderNumber,
                    'trading_company' => $tradingCompany,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Resumen
        $summary = [
            'total' => count($results),
            'updated' => collect($results)->where('status', 'updated')->count(),
            'not_found' => collect($results)->where('status', 'not_found')->count(),
            'deleted' => collect($results)->where('status', 'deleted')->count(),
            'skipped' => collect($results)->where('status', 'skipped')->count(),
            'validation_error' => collect($results)->where('status', 'validation_error')->count(),
            'error' => collect($results)->where('status', 'error')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Bulk update processed',
            'summary' => $summary,
            'results' => $results,
        ]);
    }

    public function index( Request $request ): JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'products']);

        // Si viene con filtros (query parameters), aplicarlos
        if ($request->has('order_number') || $request->has('company')) {
            if ($request->has('order_number')) {
                $query->where('order_number', $request->order_number);
            }

            if ($request->has('company')) {
                $company = $request->company;
                $query->where('trading_company', $company);
            }
        }

        $purchaseOrders = $query->get();

        // Si no se encontraron POs activas pero hay filtros específicos, verificar si existen eliminadas
        $deletedInfo = null;
        if ($purchaseOrders->isEmpty() && ($request->has('order_number') || $request->has('company'))) {
            $deletedQuery = PurchaseOrder::onlyTrashed();

            if ($request->has('order_number')) {
                $deletedQuery->where('order_number', $request->order_number);
            }

            if ($request->has('company')) {
                $deletedQuery->where('trading_company', $request->company);
            }

            $deletedPO = $deletedQuery->first();
            if ($deletedPO) {
                $deletedInfo = [
                    'message' => 'The requested purchase order was found but has been deleted',
                    'deleted_at' => $deletedPO->deleted_at,
                    'order_number' => $deletedPO->order_number,
                    'trading_company' => $deletedPO->trading_company
                ];
            }
        }

        return response()->json([
            'message' => 'Purchase orders fetched successfully',
            'data' => $purchaseOrders,
            'filters_applied' => [
                'order_number' => $request->get('order_number'),
                'company' => $request->get('company')
            ],
            'deleted_info' => $deletedInfo
        ]);
    }
}

