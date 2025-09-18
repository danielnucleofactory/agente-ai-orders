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
                    'bonded_warehouse_exit' => ['required','date'],
                    'bonded_warehouse_enter' => ['required','date'],

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
                    'bonded_warehouse_exit.required' => 'El campo "Salida Almacen Fiscal" es obligatorio.',
                    'bonded_warehouse_exit.date' => 'El campo "Salida Almacen Fiscal" debe ser una fecha',
                    'bonded_warehouse_enter.required' => 'El campo "Entrada Almacen Fiscal" es obligatorio.',
                    'bonded_warehouse_enter.date' => 'El campo "Entrada Almacen Fiscal" debe ser una fecha',

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

                // 4) Relaciones (se buscan por nombre/código como venías usando)
                $vendorName = data_get($general, 'vendor_id') ?? data_get($general, 'vendor') ?? data_get($general, 'vendor_name');
                $shipToName = data_get($general, 'ship_to_id') ?? data_get($general, 'ship_to');
                $billToName = data_get($general, 'bill_to_id') ?? data_get($general, 'bill_to');
                $hubCode    = data_get($general, 'planned_hub_id') ?? data_get($general, 'hub');

                $vendor = Vendor::where('name', $vendorName)->firstOrFail();
                $shipTo = ShipTo::where('name', $shipToName)->firstOrFail();
                $billTo = BillTo::where('name', $billToName)->firstOrFail();
                $hub    = Hub::where('code', $hubCode)->firstOrFail();

                // 5) Totales
                $totalWeight = 0;
                foreach ($items as $item) {
                    $totalWeight += (float) data_get($item, 'peso_kg', data_get($item, 'kgs', 0));
                }
                $netTotal  = (float) (data_get($general, 'netValue', data_get($general, 'net_total', 0)));
                $companyId = $vendor->company_id;

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
}
