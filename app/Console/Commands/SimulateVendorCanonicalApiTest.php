<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SimulateVendorCanonicalApiTest extends Command
{
    protected $signature = 'po:simulate-vendor-canonical-test
                            {--prod : Crear POs en producción (https://olo.orders.raga-x.ai)}
                            {--url= : URL base alternativa}';

    protected $description = 'Simula las pruebas de creación de POs por API con código canónico de vendor (trading_company-vendor_id)';

    private string $baseUrl = '';

    public function handle(): int
    {
        $customUrl = $this->option('url');
        $useProd = $this->option('prod') || $customUrl;
        $this->baseUrl = $customUrl ? rtrim($customUrl, '/') : ($useProd ? 'https://olo.orders.raga-x.ai' : '');

        $suffix = $useProd ? 'prod-XXX' : ('new-' . now()->format('His'));
        $this->info('=== Simulación de pruebas API - Código canónico de vendor ===');
        if ($useProd) {
            $this->warn('MODO PRODUCCIÓN: ' . $this->baseUrl);
        }
        $this->info("Sufijo de orden para esta ejecución: {$suffix}");
        $this->newLine();

        // Test 1: Creación individual
        $this->info('1. Creación individual (POST /api/purchase-orders)');
        $order1 = "4107568-{$suffix}";
        $payload1 = [
            'order_number' => $order1,
            'vendor_id' => '557',
            'vendor_name' => 'QINGDAO FORTUNE WOOD PRODUCTS CO. LTD.',
            'route_label' => 'Directo GT - FERRETERIA EPA, S.A. (Guatemala)',
            'retail_group' => '81',
            'total_amount' => 12814.0,
            'currency' => 'USD',
            'emision_date_po' => '2025-11-24',
            'category' => '',
            'incoterms' => 'FOB',
            'logistics_incoterm' => 'FOB',
            'price_incoterm' => 'FOB',
            'departure_port_id' => '78',
            'arrival_port_id' => '133',
            'date_theorical_load' => '2025-12-24',
            'date_carga_po' => '2025-12-24',
            'case_number_file' => '',
            'consolidator_name' => 'GLOBALOR LTD (Dropship GT)',
            'customs_dua' => '',
            'receipt_note' => '',
            'receipt_note_date' => null,
            'factory_proforma_number' => 'Po:25FX1117/EPA/010©',
            'invoice' => '',
            'Invoice_amount' => 0.0,
            'apply_technical_note' => false,
            'applies_tlc' => false,
            'reason' => 'CNY',
            'customer_type' => 'EPA',
            'trading_company' => 'OLO3',
        ];

        try {
            $response1 = $this->callApi('POST', '/api/purchase-orders', $payload1);
            if ($response1['success'] ?? false) {
                if ($this->baseUrl) {
                    $this->info("   ✓ PO creada en producción: {$order1}");
                } else {
                    $vendor = Vendor::where('vendo_code', 'OLO3-557')->first();
                    $po = PurchaseOrder::where('order_number', $order1)->first();
                    if ($vendor && $po) {
                        $this->info("   ✓ PO creada. Vendor vendo_code: {$vendor->vendo_code}, vendor_number en PO: {$po->vendor_number}");
                    } else {
                        $this->warn("   ⚠ PO creada pero no se encontró vendor OLO3-557 o PO {$order1}");
                    }
                }
            } else {
                $this->error('   ✗ Error: ' . ($response1['message'] ?? 'Desconocido'));
            }
        } catch (\Throwable $e) {
            $this->error('   ✗ Excepción: ' . $e->getMessage());
        }

        $this->newLine();

        // Test 2: Creación bulk
        $this->info('2. Creación bulk (POST /api/purchase-orders/bulk)');
        $order2a = "4107513-{$suffix}";
        $order2b = "4107519-{$suffix}";
        $payload2 = [
            [
                'order_number' => $order2a,
                'vendor_id' => '32',
                'vendor_name' => 'CLEVA INTERNATIONAL TRADING LIMITED',
                'route_label' => 'Directo GT - FERRETERIA EPA, S.A. (Guatemala)',
                'retail_group' => '84',
                'total_amount' => 36929.0,
                'currency' => 'USD',
                'emision_date_po' => '2025-11-12',
                'category' => '',
                'incoterms' => 'FOB',
                'logistics_incoterm' => 'FOB',
                'price_incoterm' => 'FOB',
                'departure_port_id' => '92',
                'arrival_port_id' => '41',
                'date_theorical_load' => '2026-01-02',
                'date_carga_po' => '2026-01-02',
                'case_number_file' => '',
                'consolidator_name' => 'GLOBALOR LTD (Dropship GT)',
                'customs_dua' => '',
                'receipt_note' => '',
                'receipt_note_date' => null,
                'factory_proforma_number' => '1x40 GT',
                'invoice' => '',
                'Invoice_amount' => 0.0,
                'apply_technical_note' => true,
                'applies_tlc' => false,
                'reason' => 'CNY',
                'customer_type' => 'EPA',
                'trading_company' => 'OLO3',
            ],
            [
                'order_number' => $order2b,
                'vendor_id' => '399',
                'vendor_name' => 'KRONOSPAN, S.L. (Costa Rica)',
                'route_label' => 'Directo CR - FERRETERIA EPA, S.A.',
                'retail_group' => '81',
                'total_amount' => 11956.0,
                'currency' => 'EUR',
                'emision_date_po' => '2025-11-14',
                'category' => '',
                'incoterms' => 'CFR',
                'logistics_incoterm' => 'CFR',
                'price_incoterm' => 'CFR',
                'departure_port_id' => '104',
                'arrival_port_id' => '118',
                'date_theorical_load' => '2026-01-01',
                'date_carga_po' => '2026-01-01',
                'case_number_file' => '6930',
                'consolidator_name' => 'GLOBALOR LTD (Dropship CR)',
                'customs_dua' => '-- N/A --',
                'receipt_note' => '21565',
                'receipt_note_date' => '2026-01-28',
                'factory_proforma_number' => '249173',
                'invoice' => '',
                'Invoice_amount' => 0.0,
                'apply_technical_note' => false,
                'applies_tlc' => true,
                'reason' => 'PEDIDO COMPRA',
                'customer_type' => 'EPA',
                'trading_company' => 'OLO3',
            ],
        ];

        try {
            $response2 = $this->callApi('POST', '/api/purchase-orders/bulk', $payload2);
            $results = $response2['results'] ?? [];
            foreach ($results as $r) {
                $status = $r['status'] ?? 'unknown';
                $msg = $r['message'] ?? '';
                if ($status === 'created' || $status === 'already_exists') {
                    $this->info("   ✓ {$r['order_number']}: {$status} - {$msg}");
                } else {
                    $this->warn("   ⚠ {$r['order_number']}: {$status} - {$msg}");
                }
            }
            if (!$this->baseUrl) {
                $vendor32 = Vendor::where('vendo_code', 'OLO3-32')->first();
                $vendor399 = Vendor::where('vendo_code', 'OLO3-399')->first();
                if ($vendor32) {
                    $this->info("   Vendor OLO3-32: {$vendor32->name}");
                }
                if ($vendor399) {
                    $this->info("   Vendor OLO3-399: {$vendor399->name}");
                }
            }
        } catch (\Throwable $e) {
            $this->error('   ✗ Excepción: ' . $e->getMessage());
        }

        if ($this->baseUrl) {
            $this->newLine();
            $this->info('=== Fin de simulación (producción) ===');
            return 0;
        }

        $this->newLine();

        // Test 3: Update con vendor nuevo (debe crearse)
        $this->info('3. Update PO (PUT /api/purchase-orders/{order_number}) - cambiar vendor a uno inexistente');
        $poForUpdate = PurchaseOrder::where('order_number', $order2a)->first();
        if ($poForUpdate) {
            try {
                $response3 = $this->callApi('PUT', "/api/purchase-orders/{$order2a}", [
                    'vendor_id' => '999',
                    'vendor_name' => 'NUEVO PROVEEDOR TEST',
                ]);
                if (($response3['success'] ?? false)) {
                    $newVendor = Vendor::where('vendo_code', 'OLO3-999')->first();
                    $poForUpdate->refresh();
                    if ($newVendor) {
                        $this->info("   ✓ Vendor OLO3-999 creado en update. PO vendor_number: {$poForUpdate->vendor_number}");
                    } else {
                        $this->warn('   ⚠ Update exitoso pero no se encontró vendor OLO3-999');
                    }
                } else {
                    $this->error('   ✗ Error: ' . ($response3['message'] ?? 'Desconocido'));
                }
            } catch (\Throwable $e) {
                $this->error('   ✗ Excepción: ' . $e->getMessage());
            }
        } else {
            $this->warn('   ⚠ No hay PO para probar update (bulk no creó 4107513)');
        }

        $this->newLine();

        // Test 4: Editar PO 4107568-090217 (creada en ejecución anterior) - cambiar vendor a 111
        $this->info('4. Editar PO 4107568-090217 (PUT) - vendor_id 111, nombre PROVEEDOR TEST');
        $orderToEdit = '4107568-090217';
        try {
            $response4 = $this->callApi('PUT', "/api/purchase-orders/{$orderToEdit}", [
                'vendor_id' => '111',
                'vendor_name' => 'PROVEEDOR TEST',
            ]);
            if ($response4['success'] ?? false) {
                $vendor111 = Vendor::where('vendo_code', 'OLO3-111')->first();
                $poEdited = PurchaseOrder::where('order_number', $orderToEdit)->first();
                if ($vendor111 && $poEdited) {
                    $this->info("   ✓ PO editada. Vendor OLO3-111: {$vendor111->name}, vendor_number en PO: {$poEdited->vendor_number}");
                } else {
                    $this->warn("   ⚠ Update exitoso pero no se encontró vendor OLO3-111 o PO {$orderToEdit}");
                }
            } else {
                $this->error('   ✗ Error: ' . ($response4['message'] ?? 'Desconocido'));
            }
        } catch (\Throwable $e) {
            $this->error('   ✗ Excepción: ' . $e->getMessage());
            $this->warn("   (La PO {$orderToEdit} debe existir de una ejecución anterior)");
        }

        $this->newLine();
        $this->info('=== Fin de simulación ===');

        return 0;
    }

    private function callApi(string $method, string $path, array $data): array
    {
        if ($this->baseUrl) {
            $url = $this->baseUrl . $path;
            $response = Http::timeout(30)
                ->withHeaders(['Accept' => 'application/json'])
                ->{strtolower($method)}($url, $data);
            return $response->json() ?? [];
        }

        $request = \Illuminate\Http\Request::create($path, $method, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode($data));

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return json_decode($response->getContent(), true) ?? [];
    }
}
