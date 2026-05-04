<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportCsvPurchaseOrders extends Component
{
    use WithFileUploads;

    public $csvFile;
    public bool $showModal = false;
    public bool $importing = false;
    public ?array $importResult = null;
    public array $importLog = [];
    public int $totalRows = 0;

    /**
     * Campos del CSV que se omiten (no existen en BD o son auto-calculados)
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
     * Mapeo CSV → API (formato bulk endpoint)
     * Los campos de maestros usan sufijo _id para que el middleware los resuelva
     */
    private const FIELD_MAP = [
        'ORDER_NUMBER'              => 'order_number',
        'VENDOR_ID'                 => 'vendor_id',
        'VENDOR_NAME'               => 'vendor_name',
        'RETAIL_GROUP'              => 'retail_group',          // No es maestro, va tal cual
        'ROUTE_LABEL'               => 'route_label',
        'NET_TOTAL'                 => 'total_amount',          // bulk usa total_amount
        'CURRENCY'                  => 'currency',
        'EMISION_DATE_PO'           => 'emision_date_po',
        'CATEGORY'                  => 'category',
        'MODE'                      => 'mode_id',              // Maestro: middleware resuelve
        'MBL_NUMBER'                => 'mbl_number',
        'CONTAINER_TYPE'            => 'container_type_id',    // Maestro: middleware resuelve
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
        'DEPARTURE_PORT'            => 'departure_port_id',    // Maestro: middleware resuelve
        'PORT_OF_LOADING_VALIDATED' => 'port_of_loading_validated',
        'ARRIVAL_PORT'              => 'arrival_port_id',      // Maestro: middleware resuelve
        'CUSTOMS_DUA'               => 'customs_dua',
        'RECEIPT_NOTE'              => 'receipt_note',
        'RECEIPT_NOTE_DATE'         => 'receipt_note_date',
        'FACTORY_PROFORMA_NUMBER'   => 'factory_proforma_number',
        'CARGO_INVOICE_NUMBER'      => 'cargo_invoice_number',
        'FREIGHT_AMOUNT'            => 'freight_amount',
        'SERVICE_PROVIDER'          => 'service_provider',     // No es maestro, va tal cual
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
        'SHIPPING_LINE'             => 'shipping_line_id',     // Maestro: middleware resuelve
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
        'date_etd', 'date_eta_initial',
        'date_eta', 'receipt_note_date', 'bonded_warehouse_enter',
        'bonded_warehouse_exit', 'forwader_date', 'inspection_date',
        'vgm_cut_date', 'balance_payment_date', 'local_charges_payment_date',
        'release_date',
    ];

    private const DECIMAL_FIELDS = [
        'total_amount', 'freight_amount', 'cbm', 'Invoice_amount',
    ];

    private const INTEGER_FIELDS = [
        'container_free_days',
    ];

    public function openModal(): void
    {
        $this->showModal = true;
        $this->reset(['csvFile', 'importResult', 'importLog', 'importing', 'totalRows']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['csvFile', 'importResult', 'importLog', 'importing', 'totalRows']);
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'csvFile.required' => 'Debe seleccionar un archivo CSV.',
            'csvFile.mimes' => 'El archivo debe ser CSV (.csv o .txt).',
            'csvFile.max' => 'El archivo no puede ser mayor a 10MB.',
        ]);

        $this->importing = true;
        $this->importResult = null;
        $this->importLog = [];

        set_time_limit(300);

        try {
            $filePath = $this->csvFile->getRealPath();

            if (!file_exists($filePath)) {
                throw new \Exception('El archivo no se pudo cargar correctamente.');
            }

            // Parsear CSV
            $rows = $this->parseCsv($filePath);
            $this->totalRows = count($rows);

            if ($this->totalRows === 0) {
                throw new \Exception('No se encontraron filas para procesar en el CSV.');
            }

            // Construir payloads para todas las POs
            $bulkPayload = [];
            foreach ($rows as $row) {
                $payload = $this->buildPayload($row);
                if (!empty($payload['order_number']) && !empty($payload['trading_company'])) {
                    $bulkPayload[] = $payload;
                } else {
                    $orderNumber = $row['ORDER_NUMBER'] ?? 'N/A';
                    $this->importLog[] = [
                        'order' => $orderNumber,
                        'status' => 'error',
                        'message' => 'Campos requeridos faltantes (order_number o trading_company)',
                    ];
                }
            }

            if (empty($bulkPayload)) {
                throw new \Exception('Ninguna fila tiene los campos requeridos.');
            }

            // Enviar todo en UN solo request al endpoint bulk
            $apiUrl = rtrim(config('app.url'), '/') . '/api/purchase-orders/bulk';

            Log::info('ImportCsvPurchaseOrders: Enviando bulk request', [
                'total_pos' => count($bulkPayload),
                'api_url' => $apiUrl,
            ]);

            $response = Http::timeout(120)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, $bulkPayload);

            if ($response->successful()) {
                $body = $response->json();
                $results = $body['results'] ?? [];
                $summary = $body['summary'] ?? [];

                $success = 0;
                $failed = 0;
                $errors = [];

                foreach ($results as $result) {
                    $orderNumber = $result['order_number'] ?? 'N/A';
                    $status = $result['status'] ?? 'unknown';

                    if ($status === 'created') {
                        $success++;
                        $poId = $result['id'] ?? '?';
                        $this->importLog[] = [
                            'order' => $orderNumber,
                            'status' => 'ok',
                            'message' => "Creada (ID: {$poId})",
                        ];
                    } elseif ($status === 'already_exists') {
                        $success++;
                        $this->importLog[] = [
                            'order' => $orderNumber,
                            'status' => 'ok',
                            'message' => 'Ya existía (ID: ' . ($result['id'] ?? '?') . ')',
                        ];
                    } else {
                        $failed++;
                        $msg = $result['message'] ?? $status;
                        $errors[] = "PO {$orderNumber}: {$msg}";
                        $this->importLog[] = [
                            'order' => $orderNumber,
                            'status' => 'error',
                            'message' => $msg,
                        ];
                    }
                }

                $this->importResult = [
                    'total' => count($bulkPayload),
                    'success' => $success,
                    'failed' => $failed,
                    'errors' => $errors,
                ];
            } else {
                $errorMsg = $response->json('message') ?? $response->body();
                Log::error('ImportCsvPurchaseOrders: Error en bulk API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                $this->importResult = [
                    'total' => count($bulkPayload),
                    'success' => 0,
                    'failed' => count($bulkPayload),
                    'errors' => ["HTTP {$response->status()}: {$errorMsg}"],
                ];

                // Marcar todas como error en el log
                foreach ($bulkPayload as $p) {
                    $this->importLog[] = [
                        'order' => $p['order_number'] ?? 'N/A',
                        'status' => 'error',
                        'message' => "HTTP {$response->status()}",
                    ];
                }
            }

            Log::info('ImportCsvPurchaseOrders: Importación finalizada', $this->importResult ?? []);

        } catch (\Exception $e) {
            Log::error('ImportCsvPurchaseOrders: Error general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->importResult = [
                'total' => 0,
                'success' => 0,
                'failed' => 1,
                'errors' => [$e->getMessage()],
            ];
        }

        $this->importing = false;
    }

    /**
     * Parsea CSV con delimitador ;
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

    /**
     * Construye el payload JSON a partir de una fila CSV (formato bulk)
     */
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

        // vendor_id siempre como string
        if (isset($payload['vendor_id'])) {
            $payload['vendor_id'] = (string) $payload['vendor_id'];
        }

        // Los campos _id de maestros van como string (el middleware los resuelve)
        foreach (['departure_port_id', 'arrival_port_id', 'mode_id', 'container_type_id', 'shipping_line_id'] as $idField) {
            if (isset($payload[$idField])) {
                $payload[$idField] = (string) $payload[$idField];
            }
        }

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

        // DD-MM-YY
        if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-y', $value);
            if ($date) {
                if ($date->format('Y') < 2000) {
                    $date->modify('+100 years');
                }
                return $date->format('Y-m-d');
            }
        }

        // DD/MM/YYYY
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
            $value = str_replace('.', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    public function render()
    {
        return view('livewire.import-csv-purchase-orders');
    }
}
