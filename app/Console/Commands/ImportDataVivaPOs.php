<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportDataVivaPOs extends Command
{
    protected $signature = 'import:data-viva
                            {file : Ruta al archivo CSV de Data Viva}
                            {--url= : URL base de la API (default: APP_URL)}
                            {--delay=5 : Segundos de espera entre cada llamada}
                            {--dry-run : Solo muestra el payload sin enviar}';

    protected $description = 'Importa POs desde CSV de Data Viva usando el endpoint de creación';

    /**
     * Campos del CSV que se omiten (no existen en el modelo o son auto-calculados)
     */
    private const SKIP_FIELDS = [
        'ESTADO_SEGUIMIENTO',
        'CARGA_LISTA_VALIDADA',
        'DIF_LOAD_DATE',
        'ETD_DATES_DIFFERENCE',
        'ETA_DATES_DIFFERENCE',
        'FECHA_SHIPPING_DOCS',
        'DATE_ETD_UPDATED',
        'DATE_ETA_UPDATED',
    ];

    /**
     * Mapeo CSV → API field name
     */
    private const FIELD_MAP = [
        'ORDER_NUMBER'              => 'order_number',
        'VENDOR_ID'                 => 'vendor_id',
        'VENDOR_NAME'               => 'vendor_name',
        'RETAIL_GROUP'              => 'retail_group',
        'ROUTE_LABEL'               => 'route_label',
        'NET_TOTAL'                 => 'net_total',
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
        'ETA_INICIAL'               => 'date_eta_initial',
        'DATE_ETA'                  => 'date_eta',
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
        'INSPECTION_DATE'           => 'inspection_date',
        'VGM_CUT_DATE'              => 'vgm_cut_date',
        'BALANCE_PAYMENT_DATE'      => 'balance_payment_date',
        'LOCAL_CHARGES_PAYMENT_DATE' => 'local_charges_payment_date',
        'RELEASE_DATE'              => 'release_date',
        'TRADING_COMPANY'           => 'trading_company',
    ];

    /**
     * Campos booleanos (Y/N → true/false)
     */
    private const BOOLEAN_FIELDS = [
        'etd_initial_validated',
        'port_of_loading_validated',
        'has_facture_merca',
        'applies_tlc',
        'apply_technical_note',
        'applies_af',
    ];

    /**
     * Campos de fecha que necesitan conversión
     */
    private const DATE_FIELDS = [
        'emision_date_po',
        'date_booking_request',
        'date_booking_authorized',
        'date_carga_po',
        'date_theorical_load',
        'date_etd_initial',
        'date_etd',
        'date_eta_initial',
        'date_eta',
        'receipt_note_date',
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        'forwader_date',
        'inspection_date',
        'vgm_cut_date',
        'balance_payment_date',
        'local_charges_payment_date',
        'release_date',
    ];

    /**
     * Campos decimales con formato europeo (punto=miles, coma=decimal)
     */
    private const DECIMAL_FIELDS = [
        'net_total',
        'freight_amount',
        'cbm',
        'Invoice_amount',
    ];

    /**
     * Campos enteros
     */
    private const INTEGER_FIELDS = [
        'container_free_days',
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file');
        $baseUrl = rtrim($this->option('url') ?? config('app.url'), '/');
        $delay = (int) $this->option('delay');
        $dryRun = $this->option('dry-run');

        $apiUrl = $baseUrl . '/api/purchase-orders';

        // Validar archivo
        if (!file_exists($filePath)) {
            $this->error("Archivo no encontrado: {$filePath}");
            return 1;
        }

        $this->info("=== Importación Data Viva POs ===");
        $this->info("Archivo: {$filePath}");
        $this->info("API URL: {$apiUrl}");
        $this->info("Delay: {$delay}s entre llamadas");
        if ($dryRun) {
            $this->warn(">>> MODO DRY-RUN: No se enviarán requests <<<");
        }
        $this->newLine();

        // Leer CSV
        $rows = $this->parseCsv($filePath);
        if (empty($rows)) {
            $this->error("No se encontraron filas para procesar.");
            return 1;
        }

        $this->info("Filas encontradas: " . count($rows));
        $this->newLine();

        $success = 0;
        $failed = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;
            $orderNumber = $row['ORDER_NUMBER'] ?? 'N/A';
            $this->info("--- PO #{$rowNum}: {$orderNumber} ---");

            // Convertir fila CSV a payload de API
            $payload = $this->buildPayload($row);

            if (empty($payload['order_number']) || empty($payload['trading_company'])) {
                $this->error("  Faltan campos requeridos (order_number o trading_company). Saltando.");
                $failed++;
                continue;
            }

            if ($dryRun) {
                $this->line("  Payload:");
                $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $success++;
                continue;
            }

            // Enviar al API
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($apiUrl, $payload);

                if ($response->successful()) {
                    $body = $response->json();
                    $poId = data_get($body, 'data.id') ?? data_get($body, 'data.0.id') ?? '?';
                    $this->info("  OK - PO creada (ID: {$poId})");
                    $success++;
                } else {
                    $this->error("  ERROR HTTP {$response->status()}: " . $response->body());
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  EXCEPCION: " . $e->getMessage());
                $failed++;
            }

            // Delay entre llamadas (excepto la última)
            if ($rowNum < count($rows)) {
                $this->line("  Esperando {$delay}s...");
                sleep($delay);
            }
        }

        $this->newLine();
        $this->info("=== Resultado ===");
        $this->info("Total: " . count($rows));
        $this->info("Exitosas: {$success}");
        if ($failed > 0) {
            $this->error("Fallidas: {$failed}");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Parsea el CSV con delimitador ; y retorna array de filas asociativas
     */
    private function parseCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);

        // Remover BOM UTF-8
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'dataviva_');
        file_put_contents($tempFile, $content);

        $handle = fopen($tempFile, 'r');
        if (!$handle) {
            unlink($tempFile);
            return [];
        }

        // Leer headers
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
            } else {
                $this->warn("Fila con número incorrecto de columnas (" . count($row) . " vs " . count($headers) . "), saltando.");
            }
        }

        fclose($handle);
        unlink($tempFile);

        return $rows;
    }

    /**
     * Construye el payload JSON a partir de una fila del CSV
     */
    private function buildPayload(array $row): array
    {
        $payload = [];

        foreach (self::FIELD_MAP as $csvField => $apiField) {
            // Obtener valor raw
            $rawValue = $row[$csvField] ?? null;

            // Limpiar
            $value = $this->cleanValue($rawValue);

            // Si es null después de limpiar, omitir
            if ($value === null) {
                continue;
            }

            // Convertir según tipo de campo
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
                // String: trim
                $payload[$apiField] = trim($value);
            }
        }

        // vendor_id siempre como string (el controller lo busca como vendo_code)
        if (isset($payload['vendor_id'])) {
            $payload['vendor_id'] = (string) $payload['vendor_id'];
        }

        return $payload;
    }

    /**
     * Limpia un valor raw del CSV.
     * Retorna null si es vacío, <null>, o -- N/A --
     */
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

    /**
     * Convierte Y/N a booleano
     */
    private function toBoolean(string $value): bool
    {
        return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'SI', 'SÍ']);
    }

    /**
     * Convierte fecha DD-MM-YY o DD/MM/YYYY a formato ISO YYYY-MM-DD
     */
    private function convertDate(string $value): ?string
    {
        $value = trim($value);

        // DD-MM-YY (ej: 07-08-25)
        if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-y', $value);
            if ($date) {
                // Asegurar siglo correcto (25 → 2025, no 1925)
                if ($date->format('Y') < 2000) {
                    $date->modify('+100 years');
                }
                return $date->format('Y-m-d');
            }
        }

        // DD/MM/YYYY (ej: 29/10/2025)
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // DD-MM-YYYY
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // Intentar Carbon como fallback
        try {
            $date = \Carbon\Carbon::parse($value);
            if ($date->year >= 2000 && $date->year <= 2100) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Ignorar
        }

        $this->warn("  No se pudo parsear fecha: '{$value}'");
        return null;
    }

    /**
     * Convierte número en formato europeo a float.
     * Formato europeo: punto = miles, coma = decimal
     *   37.804     → 37804
     *   22.127,28  → 22127.28
     *   9.028,50   → 9028.50
     *   3.320      → 3320
     */
    private function convertEuropeanDecimal(string $value): ?float
    {
        $value = trim($value);

        if ($value === '' || !preg_match('/[\d.,]/', $value)) {
            return null;
        }

        // Si tiene coma → la coma es decimal, los puntos son miles
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);   // Quitar puntos (miles)
            $value = str_replace(',', '.', $value);  // Coma → punto (decimal)
        } else {
            // Solo puntos → son separadores de miles (no hay decimal)
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
