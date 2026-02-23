<?php

namespace App\Console\Commands;

use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BulkInsertQatestPOs extends Command
{
    protected $signature = 'po:bulk-insert-qatest
                            {--prod : Enviar a producción (https://olo.md.orders.raga-x.ai/api/v1/purchase-orders/bulk)}
                            {--url= : URL base alternativa para el endpoint bulk}';

    protected $description = 'Inserta 12 POs (3 plantillas × 4 copias) con sufijo qatest-001..004 y vendor_id incremental por plantilla (ej: 32→32,33,34,35; 399→399,400,401,402).';

    private const PROD_BULK_URL = 'https://olo.md.orders.raga-x.ai/api/v1/purchase-orders/bulk';

    public function handle(): int
    {
        $templates = $this->getTemplates();

        $items = [];
        foreach ($templates as $baseOrder => $template) {
            $baseVendorId = (int) $template['vendor_id'];
            for ($i = 1; $i <= 4; $i++) {
                $suffix = sprintf('qatest-%03d', $i);
                $vendorId = (string) ($baseVendorId + $i - 1); // 32→32,33,34,35; 399→399,400,401,402; 557→557,558,559,560
                $items[] = array_merge($template, [
                    'order_number' => "{$baseOrder}-{$suffix}",
                    'vendor_id' => $vendorId,
                ]);
            }
        }

        $prodUrl = $this->option('url') ?: ($this->option('prod') ? self::PROD_BULK_URL : null);

        if ($prodUrl) {
            $this->warn('MODO PRODUCCIÓN: ' . $prodUrl);
            $data = $this->sendToProd($prodUrl, $items);
        } else {
            $this->info('Enviando ' . count($items) . ' POs al endpoint bulk (local)...');
            $data = $this->sendToLocal($items);
        }

        $results = $data['results'] ?? [];
        $summary = $data['summary'] ?? [];
        $created = 0;
        $exists = 0;
        $failed = 0;

        foreach ($results as $r) {
            $status = $r['status'] ?? 'unknown';
            $orderNumber = $r['order_number'] ?? '?';
            $msg = $r['message'] ?? '';
            if ($status === 'created') {
                $created++;
                $this->line("  <info>✓</info> {$orderNumber}: created (ID: " . ($r['id'] ?? '?') . ")");
            } elseif ($status === 'already_exists') {
                $exists++;
                $this->line("  <comment>○</comment> {$orderNumber}: already_exists");
            } else {
                $failed++;
                $this->error("  ✗ {$orderNumber}: {$status} - {$msg}");
            }
        }

        $this->newLine();
        $this->info("Resumen: created={$created}, already_exists={$exists}, failed={$failed}");
        if (!empty($summary)) {
            $this->table(['Total', 'Created', 'Already exists', 'Failed'], [[
                $summary['total'] ?? 0,
                $summary['created'] ?? 0,
                $summary['already_exists'] ?? 0,
                $summary['failed'] ?? 0,
            ]]);
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sendToLocal(array $items): array
    {
        $request = Request::create(
            '/api/purchase-orders/bulk',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            json_encode($items)
        );
        $request->headers->set('Content-Type', 'application/json');

        $controller = app(PurchaseOrderController::class);
        $response = $controller->bulk($request);

        return $response->getData(true);
    }

    private function sendToProd(string $url, array $items): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $token = env('ORDERS_PROD_API_TOKEN');
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $response = Http::timeout(120)
            ->withHeaders($headers)
            ->post($url, $items);

        if (!$response->successful()) {
            $this->error('Error HTTP ' . $response->status() . ': ' . $response->body());
            return ['results' => [], 'summary' => ['failed' => count($items)]];
        }

        return $response->json() ?? [];
    }

    private function getTemplates(): array
    {
        return [
            '4107513' => [
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
            '4107519' => [
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
            '4107568' => [
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
            ],
        ];
    }
}
