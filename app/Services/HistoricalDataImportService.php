<?php

namespace App\Services;

use App\Models\HistoricalPurchaseOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HistoricalDataImportService
{
    /**
     * Import historical data from CSV file
     *
     * @param string $filePath
     * @return array
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

        try {
            // Leer el contenido del archivo y remover BOM UTF-8 si existe
            $content = file_get_contents($filePath);
            if ($content === false) {
                $stats['errors']++;
                $stats['error_messages'][] = "No se pudo leer el archivo: {$filePath}";
                return $stats;
            }

            // Remover BOM UTF-8 si existe
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }

            // Crear un archivo temporal sin BOM
            $tempFile = tempnam(sys_get_temp_dir(), 'csv_import_');
            file_put_contents($tempFile, $content);

            $handle = fopen($tempFile, 'r');
            if ($handle === false) {
                unlink($tempFile);
                $stats['errors']++;
                $stats['error_messages'][] = "No se pudo abrir el archivo temporal";
                return $stats;
            }

            // Leer headers (primera línea)
            $headers = fgetcsv($handle, 0, ';');
            if ($headers === false) {
                fclose($handle);
                unlink($tempFile);
                $stats['errors']++;
                $stats['error_messages'][] = "No se pudieron leer los headers del CSV";
                return $stats;
            }

            // Normalizar headers (trim y uppercase)
            $headers = array_map(function($header) {
                return trim(strtoupper($header));
            }, $headers);

            // Validar headers requeridos
            $requiredHeaders = ['ORDER_NUMBER', 'TRADING_COMPANY'];
            foreach ($requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    fclose($handle);
                    $stats['errors']++;
                    $stats['error_messages'][] = "Header requerido no encontrado: {$required}";
                    return $stats;
                }
            }

            DB::beginTransaction();

            $lineNumber = 1; // Empezamos en 1 porque ya leímos los headers

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                $stats['total']++;

                try {
                    // Combinar headers con valores
                    $data = array_combine($headers, $row);

                    // Mapear y normalizar datos
                    $mappedData = $this->mapCsvRow($data);

                    // Verificar duplicados (order_number + trading_company)
                    $exists = HistoricalPurchaseOrder::where('order_number', $mappedData['order_number'])
                        ->where('trading_company', $mappedData['trading_company'] ?? null)
                        ->exists();

                    if ($exists) {
                        $stats['skipped']++;
                        continue;
                    }

                    // Crear registro
                    HistoricalPurchaseOrder::create($mappedData);
                    $stats['imported']++;

                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['error_messages'][] = "Línea {$lineNumber}: " . $e->getMessage();
                    Log::error("Error importando línea {$lineNumber}", [
                        'error' => $e->getMessage(),
                        'data' => $data ?? null,
                    ]);
                }
            }

            DB::commit();
            fclose($handle);
            unlink($tempFile);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            $stats['errors']++;
            $stats['error_messages'][] = "Error general: " . $e->getMessage();
            Log::error("Error en importación de datos históricos", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $stats;
    }

    /**
     * Map CSV row data to database fields
     *
     * @param array $row
     * @return array
     */
    protected function mapCsvRow(array $row): array
    {
        $mapped = [];

        // Mapeo directo de campos
        $fieldMapping = [
            'ORDER_NUMBER' => 'order_number',
            'VENDOR_ID' => 'vendor_id',
            'VENDOR_NAME' => 'vendor_name',
            'RETAIL_GROUP' => 'retail_group',
            'ROUTE_LABEL' => 'route_label',
            'CURRENCY' => 'currency',
            'EMISION_DATE_PO' => 'emision_date_po',
            'CATEGORY' => 'category',
            'MODE' => 'mode',
            'MBL_NUMBER' => 'mbl_number',
            'CONTAINER_TYPE' => 'container_type',
            'CONTAINER_NUMBER' => 'container_number',
            'INCOTERMS' => 'incoterms',
            'LOGISTICS_INCOTERM' => 'logistics_incoterm',
            'PRICE_INCOTERM' => 'price_incoterm',
            'DATE_BOOKING_REQUEST' => 'date_booking_request',
            'DATE_BOOKING_AUTHORIZED' => 'date_booking_authorized',
            'DATE_CARGA_PO' => 'date_carga_po',
            'DATE_THEORICAL_LOAD' => 'date_theorical_load',
            'DIF_LOAD_DATE' => 'dif_load_date',
            'DATE_ETD_INITIAL' => 'date_etd_initial',
            'DATE_ETD_UPDATED' => 'date_etd_updated',
            'DATE_ETD' => 'date_etd',
            'ETD_DATES_DIFFERENCE' => 'etd_dates_difference',
            'ETA_INICIAL' => 'eta_inicial',
            'DATE_ETA_UPDATED' => 'date_eta_updated',
            'DATE_ETA' => 'date_eta',
            'ETA_DATES_DIFFERENCE' => 'eta_dates_difference',
            'CASE_NUMBER_FILE' => 'case_number_file',
            'CONSOLIDATOR_NAME' => 'consolidator_name',
            'DEPARTURE_PORT' => 'departure_port',
            'ARRIVAL_PORT' => 'arrival_port',
            'CUSTOMS_DUA' => 'customs_dua',
            'RECEIPT_NOTE' => 'receipt_note',
            'RECEIPT_NOTE_DATE' => 'receipt_note_date',
            'FACTORY_PROFORMA_NUMBER' => 'factory_proforma_number',
            'CARGO_INVOICE_NUMBER' => 'cargo_invoice_number',
            'SERVICE_PROVIDER' => 'service_provider',
            'INVOICE' => 'invoice',
            'FACTURA_MERCA' => 'factura_merca',
            'VISIBILITY_NOTES' => 'visibility_notes',
            'COMMENTS' => 'comments',
            'REASON' => 'reason',
            'SHIPPING_LINE' => 'shipping_line',
            'CONTAINER_FREE_DAYS' => 'container_free_days',
            'TARIFF_TYPE' => 'tariff_type',
            'CUSTOMER_TYPE' => 'customer_type',
            'TRADING_COMPANY' => 'trading_company',
            'company_id' => 'company_id',
            'status' => 'status',
            'ensurence_type' => 'ensurence_type',
            'bill_to_id' => 'bill_to_id',
            'kanban_status_id' => 'kanban_status_id',
            'ship_to_id' => 'ship_to_id',
        ];

        // Campos que son integer aunque contengan "date" en el nombre
        $integerFields = ['dif_load_date', 'etd_dates_difference', 'eta_dates_difference', 'container_free_days'];
        
        // Campos que son fechas
        $dateFields = [
            'date_booking_request', 'date_booking_authorized', 'date_carga_po', 
            'date_theorical_load', 'date_etd_initial', 'date_etd_updated', 'date_etd',
            'eta_inicial', 'date_eta_updated', 'date_eta', 'receipt_note_date',
            'bonded_warehouse_enter', 'bonded_warehouse_exit', 'forwader_date',
            'emision_date_po'
        ];

        foreach ($fieldMapping as $csvField => $dbField) {
            if (isset($row[$csvField])) {
                $value = trim($row[$csvField]);
                if ($value !== '' && $value !== '-- N/A --' && $value !== 'NA') {
                    // Normalizar según el tipo de campo (integer tiene prioridad)
                    if (in_array($dbField, $integerFields)) {
                        // Campos integer
                        $normalized = $this->normalizeInteger($value);
                        if ($normalized !== null) {
                            $mapped[$dbField] = $normalized;
                        }
                    } elseif (in_array($dbField, $dateFields)) {
                        // Campos de fecha
                        $normalized = $this->normalizeDate($value);
                        if ($normalized !== null) {
                            $mapped[$dbField] = $normalized;
                        }
                    } else {
                        // Otros campos
                        $mapped[$dbField] = $value;
                    }
                }
            }
        }

        // Campos numéricos especiales (pueden tener comas como separadores de miles)
        if (isset($row['NET_TOTAL'])) {
            $mapped['net_total'] = $this->normalizeDecimal($row['NET_TOTAL']);
        }

        if (isset($row['FREIGHT_AMOUNT'])) {
            $mapped['freight_amount'] = $this->normalizeDecimal($row['FREIGHT_AMOUNT']);
        }

        if (isset($row['CBM'])) {
            $mapped['cbm'] = $this->normalizeDecimal($row['CBM']);
        }

        if (isset($row['INVOICE_AMOUNT'])) {
            $mapped['invoice_amount'] = $this->normalizeDecimal($row['INVOICE_AMOUNT']);
        }

        if (isset($row['total_amount'])) {
            $mapped['total_amount'] = $this->normalizeDecimal($row['total_amount']);
        }

        // Campos booleanos
        if (isset($row['CARGA_LISTA_VALIDADA'])) {
            $mapped['carga_lista_validada'] = $this->normalizeBoolean($row['CARGA_LISTA_VALIDADA']);
        }

        if (isset($row['ETD_INITIAL_VALIDATED'])) {
            $mapped['etd_initial_validated'] = $this->normalizeBoolean($row['ETD_INITIAL_VALIDATED']);
        }

        if (isset($row['PORT_OF_LOADING_VALIDATED'])) {
            $mapped['port_of_loading_validated'] = $this->normalizeBoolean($row['PORT_OF_LOADING_VALIDATED']);
        }

        if (isset($row['HAS_FACTURE_MERCA'])) {
            $mapped['has_facture_merca'] = $this->normalizeBoolean($row['HAS_FACTURE_MERCA']);
        }

        if (isset($row['APPLIES_TLC'])) {
            $mapped['applies_tlc'] = $this->normalizeBoolean($row['APPLIES_TLC']);
        }

        if (isset($row['APPLY_TECHNICAL_NOTE'])) {
            $mapped['apply_technical_note'] = $this->normalizeBoolean($row['APPLY_TECHNICAL_NOTE']);
        }

        if (isset($row['APPLIES_AF'])) {
            $mapped['applies_af'] = $this->normalizeBoolean($row['APPLIES_AF']);
        }

        return $mapped;
    }


    /**
     * Normalize date value
     *
     * @param string $value
     * @return string|null
     */
    protected function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if (empty($value) || $value === '-- N/A --' || $value === 'NA' || $value === '') {
            return null;
        }

        // Si es un número puro (sin guiones ni barras), no es una fecha
        if (is_numeric($value) && strpos($value, '-') === false && strpos($value, '/') === false) {
            return null;
        }

        // Si es un número negativo, no es una fecha
        if (is_numeric($value) && (int)$value < 0) {
            return null;
        }

        // Intentar parsear diferentes formatos de fecha
        try {
            $date = \Carbon\Carbon::parse($value);
            // Validar que la fecha sea razonable (entre 1900 y 2100)
            if ($date->year < 1900 || $date->year > 2100) {
                return null;
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("No se pudo parsear fecha: {$value}");
            return null;
        }
    }

    /**
     * Normalize decimal value (removes commas from thousands)
     *
     * @param string $value
     * @return float|null
     */
    protected function normalizeDecimal(string $value): ?float
    {
        $value = trim($value);
        if (empty($value) || $value === '-- N/A --') {
            return null;
        }

        // Remover comas (separadores de miles) y convertir a float
        $value = str_replace(',', '', $value);
        
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Normalize integer value
     *
     * @param string $value
     * @return int|null
     */
    protected function normalizeInteger(string $value): ?int
    {
        $value = trim($value);
        if (empty($value) || $value === '-- N/A --' || $value === 'NA' || $value === '') {
            return null;
        }

        // Si es numérico (incluyendo negativos), convertir a int
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Si no es numérico, retornar null
        return null;
    }

    /**
     * Normalize boolean value
     *
     * @param string $value
     * @return bool
     */
    protected function normalizeBoolean(string $value): bool
    {
        $value = strtoupper(trim($value));
        return in_array($value, ['Y', 'YES', '1', 'TRUE', 'SI', 'SÍ']);
    }
}

