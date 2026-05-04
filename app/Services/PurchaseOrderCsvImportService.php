<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderCsvImportService
{
    /**
     * Mapeo CSV → purchase_orders (formato bulk API)
     */
    private const FIELD_MAP = [
        'ORDER_NUMBER'              => 'order_number',
        'VENDOR_ID'                 => 'vendor_id',
        'VENDOR_NAME'               => 'vendor_name',
        'RETAIL_GROUP'              => 'retail_group',
        'ROUTE_LABEL'               => 'route_label',
        'NET_TOTAL'                 => 'total_amount',
        'CURRENCY'                  => 'currency',
        'EMISION_DATE_PO'           => 'emision_date_po',
        'CATEGORY'                  => 'category',
        'MODE'                      => 'mode',
        'MBL_NUMBER'                => 'mbl_number',
        'CONTAINER_TYPE'            => 'container_type',
        'CONTAINER_NUMBER'          => 'container_number',
        'INCOTERMS'                 => 'incoterms',
        'LOGISTICS_INCOTERM'        => 'logistics_incoterm',
        'PRICE_INCOTERM'            => 'price_incoterm',
        'DATE_BOOKING_REQUEST'      => 'date_booking_request',
        'DATE_BOOKING_AUTHORIZED'   => 'date_booking_authorized',
        'DATE_CARGA_PO'             => 'date_carga_po',
        'DATE_THEORICAL_LOAD'       => 'date_theorical_load',
        'ETD_INITIAL_VALIDATED'     => 'etd_initial_validated',
        'DATE_ETD_INITIAL'          => 'date_etd_initial',
        'DATE_ETD'                  => 'date_etd',
        'DATE_ETD_UPDATED'          => 'date_etd',      // date_etd_updated = date_etd en BD (prioridad sobre DATE_ETD)
        'ETA_INICIAL'               => 'date_eta_initial',
        'DATE_ETA'                  => 'date_eta',
        'DATE_ETA_UPDATED'          => 'date_eta',      // date_eta_updated = date_eta en BD (prioridad sobre DATE_ETA)
        'CASE_NUMBER_FILE'          => 'case_number_file',
        'CONSOLIDATOR_NAME'         => 'consolidator_name',
        'DEPARTURE_PORT'            => 'departure_port',
        'PORT_OF_LOADING_VALIDATED' => 'port_of_loading_validated',
        'ARRIVAL_PORT'              => 'arrival_port',
        'CUSTOMS_DUA'               => 'customs_dua',
        'RECEIPT_NOTE'              => 'receipt_note',
        'RECEIPT_NOTE_DATE'         => 'receipt_note_date',
        'FACTORY_PROFORMA_NUMBER'   => 'factory_proforma_number',
        'CARGO_INVOICE_NUMBER'      => 'cargo_invoice_number',
        'FREIGHT_AMOUNT'            => 'freight_amount',
        'SERVICE_PROVIDER'          => 'service_provider',
        'CBM'                       => 'cbm',
        'INVOICE'                   => 'invoice',
        'INVOICE_AMOUNT'            => 'Invoice_amount',
        'FACTURA_MERCA'             => 'factura_merca',
        'HAS_FACTURE_MERCA'         => 'has_facture_merca',
        'VISIBILITY_NOTES'          => 'visibility_notes',
        'APPLIES_TLC'               => 'applies_tlc',
        'COMMENTS'                  => 'comments',
        'APPLY_TECHNICAL_NOTE'      => 'apply_technical_note',
        'APPLIES_AF'                => 'applies_af',
        'BONDED_WAREHOUSE_ENTER'    => 'bonded_warehouse_enter',
        'BONDED_WAREHOUSE_EXIT'     => 'bonded_warehouse_exit',
        'REASON'                    => 'reason',
        'SHIPPING_LINE'             => 'shipping_line',
        'FORWADER_DATE'             => 'forwader_date',
        'CONTAINER_FREE_DAYS'       => 'container_free_days',
        'TARIFF_TYPE'               => 'tariff_type',
        'CUSTOMER_TYPE'             => 'customer_type',
        'TRADING_COMPANY'           => 'trading_company',
        'company_id'                => 'company_id',
        'status'                    => 'status',
        'total_amount'               => 'total_amount',
        'ensurence_type'            => 'ensurence_type',
        'bill_to_id'                => 'bill_to_id',
        'kanban_status_id'          => 'kanban_status_id',
        'ship_to_id'                => 'ship_to_id',
    ];

    private const BOOLEAN_FIELDS = [
        'etd_initial_validated',
        'port_of_loading_validated',
        'has_facture_merca',
        'applies_tlc',
        'apply_technical_note',
        'applies_af',
    ];

    private const DATE_FIELDS = [
        'emision_date_po', 'date_booking_request', 'date_booking_authorized',
        'date_carga_po', 'date_theorical_load', 'date_etd_initial',
        'date_etd_updated', 'date_etd', 'date_eta_initial', 'date_eta_updated',
        'date_eta', 'receipt_note_date', 'bonded_warehouse_enter',
        'bonded_warehouse_exit', 'forwader_date',
    ];

    private const DECIMAL_FIELDS = [
        'total_amount', 'freight_amount', 'cbm', 'Invoice_amount',
    ];

    private const INTEGER_FIELDS = [
        'container_free_days', 'company_id', 'bill_to_id', 'kanban_status_id', 'ship_to_id',
    ];

    /**
     * Importa CSV a la tabla purchase_orders usando el endpoint bulk interno.
     *
     * @return array{total: int, imported: int, skipped: int, errors: int, error_messages: array}
     */
    public function importFromCsv(string $filePath): array
    {
        $stats = [
            'total' => 0,
            'imported' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_messages' => [],
        ];

        if (!file_exists($filePath)) {
            $stats['errors']++;
            $stats['error_messages'][] = "El archivo no existe: {$filePath}";
            return $stats;
        }

        $rows = $this->parseCsv($filePath);
        if (empty($rows)) {
            $stats['error_messages'][] = 'No se encontraron filas en el CSV';
            return $stats;
        }

        // Deshabilitar sincronización Porth durante la importación masiva
        $originalSyncEnabled = config('services.porth.sync_enabled', true);
        config(['services.porth.sync_enabled' => false]);

        try {
            $controller = app(\App\Http\Controllers\PurchaseOrderController::class);

            foreach ($rows as $index => $row) {
                $stats['total']++;

                $payload = $this->buildPayload($row);
                $orderNumber = $payload['order_number'] ?? null;
                $tradingCompany = $payload['trading_company'] ?? null;

                if (!$orderNumber || !$tradingCompany) {
                    $stats['skipped']++;
                    continue;
                }

                // Verificar si ya existe
                $exists = PurchaseOrder::where('order_number', $orderNumber)
                    ->where('trading_company', $tradingCompany)
                    ->exists();

                if ($exists) {
                    $stats['skipped']++;
                    continue;
                }

                try {
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
                        json_encode([$payload])
                    );
                    $request->headers->set('Content-Type', 'application/json');

                    $response = app()->handle($request);
                    $body = json_decode($response->getContent(), true);

                    $results = $body['results'] ?? [];
                    $result = $results[0] ?? null;

                    if ($result && ($result['status'] ?? '') === 'created') {
                        $stats['imported']++;
                    } elseif ($result && ($result['status'] ?? '') === 'already_exists') {
                        $stats['skipped']++;
                    } else {
                        $stats['errors']++;
                        $stats['error_messages'][] = sprintf(
                            'Línea %d (PO %s): %s',
                            $index + 2,
                            $orderNumber,
                            $result['message'] ?? $result['error'] ?? 'Error desconocido'
                        );
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $stats['error_messages'][] = "Línea " . ($index + 2) . " (PO {$orderNumber}): " . $e->getMessage();
                    Log::error('PO CSV import error', [
                        'line' => $index + 2,
                        'order_number' => $orderNumber,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        } finally {
            config(['services.porth.sync_enabled' => $originalSyncEnabled]);
        }

        return $stats;
    }

    private function parseCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'po_csv_');
        file_put_contents($tempFile, $content);

        $handle = fopen($tempFile, 'r');
        if (!$handle) {
            unlink($tempFile);
            return [];
        }

        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            fclose($handle);
            unlink($tempFile);
            return [];
        }

        $headers = array_map(fn($h) => trim(strtoupper($h)), $headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        unlink($tempFile);

        return $rows;
    }

    private function buildPayload(array $row): array
    {
        $payload = [];

        foreach (self::FIELD_MAP as $csvField => $apiField) {
            $rawValue = $row[$csvField] ?? null;
            $value = $this->cleanValue($rawValue);

            if ($value === null) {
                continue;
            }

            if (in_array($apiField, self::BOOLEAN_FIELDS)) {
                $payload[$apiField] = $this->toBoolean($value);
            } elseif (in_array($apiField, self::DATE_FIELDS)) {
                $converted = $this->convertDate($value);
                if ($converted !== null) {
                    $payload[$apiField] = $converted;
                }
            } elseif (in_array($apiField, self::DECIMAL_FIELDS)) {
                $converted = $this->convertEuropeanDecimal($value);
                if ($converted !== null) {
                    $payload[$apiField] = $converted;
                }
            } elseif (in_array($apiField, self::INTEGER_FIELDS)) {
                if (is_numeric($value)) {
                    $payload[$apiField] = (int) $value;
                }
            } else {
                $payload[$apiField] = trim($value);
            }
        }

        if (isset($payload['vendor_id'])) {
            $payload['vendor_id'] = (string) $payload['vendor_id'];
        }

        // Valores por defecto si faltan
        $payload['status'] = $payload['status'] ?? 'draft';
        $payload['company_id'] = $payload['company_id'] ?? 1;
        $payload['ensurence_type'] = $payload['ensurence_type'] ?? 'pending';
        $payload['bill_to_id'] = $payload['bill_to_id'] ?? 1;
        $payload['kanban_status_id'] = $payload['kanban_status_id'] ?? null;
        $payload['ship_to_id'] = $payload['ship_to_id'] ?? 1;

        return $payload;
    }

    private function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '<null>' || $trimmed === '-- N/A --' || $trimmed === 'NA') {
            return null;
        }
        return $trimmed;
    }

    private function toBoolean(string $value): bool
    {
        return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'SI', 'SÍ']);
    }

    private function convertDate(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-y', $value);
            if ($date) {
                if ($date->format('Y') < 2000) {
                    $date->modify('+100 years');
                }
                return $date->format('Y-m-d');
            }
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        try {
            $date = \Carbon\Carbon::parse($value);
            if ($date->year >= 2000 && $date->year <= 2100) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Ignorar
        }

        return null;
    }

    private function convertEuropeanDecimal(string $value): ?float
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/[\d.,]/', $value)) {
            return null;
        }
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }
        return is_numeric($value) ? (float) $value : null;
    }
}
