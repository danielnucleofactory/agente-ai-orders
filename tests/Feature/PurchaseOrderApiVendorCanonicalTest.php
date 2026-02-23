<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Database\Seeders\CompanySeeder;
use Database\Seeders\KanbanBoardSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderApiVendorCanonicalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompanySeeder::class);
        $this->seed(KanbanBoardSeeder::class);
    }

    /** @test */
    public function it_creates_vendor_with_canonical_code_on_single_po_creation(): void
    {
        $payload = [
            'order_number' => '4107568',
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

        $response = $this->postJson('/api/purchase-orders', $payload);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $vendor = Vendor::where('vendo_code', 'OLO3-557')->first();
        $this->assertNotNull($vendor, 'Vendor con vendo_code OLO3-557 debe existir');
        $this->assertEquals('QINGDAO FORTUNE WOOD PRODUCTS CO. LTD.', $vendor->name);

        $po = PurchaseOrder::where('order_number', '4107568')->first();
        $this->assertNotNull($po);
        $this->assertEquals($vendor->id, $po->vendor_id);
        $this->assertEquals('OLO3-557', $po->vendor_number);
    }

    /** @test */
    public function it_creates_vendors_with_canonical_codes_on_bulk_po_creation(): void
    {
        $payload = [
            [
                'order_number' => '4107513',
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
                'order_number' => '4107519',
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

        $response = $this->postJson('/api/purchase-orders/bulk', $payload);

        $response->assertStatus(200);

        $vendor32 = Vendor::where('vendo_code', 'OLO3-32')->first();
        $this->assertNotNull($vendor32, 'Vendor con vendo_code OLO3-32 debe existir');
        $this->assertEquals('CLEVA INTERNATIONAL TRADING LIMITED', $vendor32->name);

        $vendor399 = Vendor::where('vendo_code', 'OLO3-399')->first();
        $this->assertNotNull($vendor399, 'Vendor con vendo_code OLO3-399 debe existir');
        $this->assertEquals('KRONOSPAN, S.L. (Costa Rica)', $vendor399->name);

        $po1 = PurchaseOrder::where('order_number', '4107513')->first();
        $this->assertNotNull($po1);
        $this->assertEquals($vendor32->id, $po1->vendor_id);
        $this->assertEquals('OLO3-32', $po1->vendor_number);

        $po2 = PurchaseOrder::where('order_number', '4107519')->first();
        $this->assertNotNull($po2);
        $this->assertEquals($vendor399->id, $po2->vendor_id);
        $this->assertEquals('OLO3-399', $po2->vendor_number);
    }

    /** @test */
    public function it_creates_vendor_on_update_when_vendor_does_not_exist(): void
    {
        // Primero crear una PO con vendor 32
        $vendor = Vendor::create([
            'company_id' => 1,
            'name' => 'CLEVA INTERNATIONAL TRADING LIMITED',
            'vendo_code' => 'OLO3-32',
            'status' => 'active',
        ]);

        $po = PurchaseOrder::create([
            'company_id' => 1,
            'order_number' => '4107513',
            'trading_company' => 'OLO3',
            'vendor_id' => $vendor->id,
            'vendor_number' => 'OLO3-32',
            'status' => 'draft',
            'currency' => 'USD',
            'net_total' => 36929.0,
            'total' => 36929.0,
        ]);

        // Actualizar vendor_id a uno nuevo (557) que no existe - debe crearse
        $response = $this->putJson("/api/purchase-orders/4107513", [
            'vendor_id' => '557',
            'vendor_name' => 'QINGDAO FORTUNE WOOD PRODUCTS CO. LTD.',
        ]);

        $response->assertStatus(200);

        $newVendor = Vendor::where('vendo_code', 'OLO3-557')->first();
        $this->assertNotNull($newVendor, 'Vendor OLO3-557 debe haberse creado en el update');
        $this->assertEquals('QINGDAO FORTUNE WOOD PRODUCTS CO. LTD.', $newVendor->name);

        $po->refresh();
        $this->assertEquals($newVendor->id, $po->vendor_id);
        $this->assertEquals('OLO3-557', $po->vendor_number);
    }
}
