<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdatePOsFromCsv extends Command
{
    protected $signature = 'po:update-from-csv
                            {file : Ruta al archivo CSV (ej: actualización 50 primeras.csv)}
                            {--dry-run : Mostrar payload sin enviar a la API}
                            {--api-url= : URL del endpoint (sobrescribe config)}';

    protected $description = 'Actualiza Purchase Orders en producción vía API bulk (trading_company=OLO1)';

    private const TRADING_COMPANY = 'OLO1';

    private const CSV_TO_DB = [
        'ORDER_NUMBER'                      => null,
        'Estado o Etapa'                    => 'kanban_status_id',
        'Contenedor'                        => 'container_number',
        'Naviera'                           => 'shipping_line',
        'Tipo de contenedor'                 => 'container_type',
        'Documento de transito'              => 'mbl_number',
        'Solicitud de booking'               => 'date_booking_request',
        'Autorizacion de booking'            => 'date_booking_authorized',
        'Fecha de asignacion de agente de carga' => 'forwader_date',
        'ETD inicial'                        => 'date_etd_initial',
        'ETD variable'                       => 'date_etd',
        'ATD'                               => 'date_atd',
        'ETA Inicial'                        => 'date_eta_initial',
        'ETA Variable'                       => 'date_eta',
        'ATA'                               => 'date_ata',
        'Monto de flete'                     => 'freight_amount',
        'Factura de flete'                   => 'cargo_invoice_number',
        'Tipo de tarifa'                     => 'tariff_type',
        'Proveedor de servicio'              => 'service_provider',
        'Ingreso a AF'                       => 'bonded_warehouse_enter',
        'Salida de Almacen fiscal'           => 'bonded_warehouse_exit',
        'Usa Almacen fiscal'                => 'uses_bonded_warehouse',
    ];

    /** Mapeo CSV Estado → kanban_status_id (board 1) */
    private const ESTADO_TO_KANBAN_ID = [
        'Alm. Fiscal'  => 7,
        'Transito'     => 5,
        'Produccion'   => 2,
        'Booking'      => 3,
    ];

    public function handle(): int
    {
        $file = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $apiUrl = $this->option('api-url') ?: config('services.po_bulk_update.api_url');
        $apiToken = config('services.po_bulk_update.api_token');

        if (!file_exists($file)) {
            $this->error("El archivo no existe: {$file}");
            return self::FAILURE;
        }

        $rows = $this->parseCsv($file);
        if (empty($rows)) {
            $this->error('No se encontraron filas en el CSV.');
            return self::FAILURE;
        }

        $this->info($dryRun ? '🔍 Modo DRY-RUN: No se enviará nada a la API.' : '⚠️  Modo EJECUCIÓN: Se enviará a la API.');
        $this->info('Endpoint: ' . $apiUrl);
        $this->info('Trading company: ' . self::TRADING_COMPANY);
        $this->newLine();

        $bulkPayload = [];
        $tableData = [];
        $stats = ['total' => 0, 'included' => 0, 'skipped' => 0];

        foreach ($rows as $row) {
            $stats['total']++;
            $orderNumber = trim($row['ORDER_NUMBER'] ?? '');
            if (!$orderNumber) {
                $stats['skipped']++;
                continue;
            }

            $payload = $this->buildPayload($row);
            if (empty($payload)) {
                $stats['skipped']++;
                $tableData[] = [$orderNumber, 'Sin cambios', '-'];
                continue;
            }

            $item = array_merge(
                ['order_number' => $orderNumber, 'trading_company' => self::TRADING_COMPANY],
                $payload
            );
            $bulkPayload[] = $item;
            $stats['included']++;
            $tableData[] = [$orderNumber, 'OK', implode(', ', array_keys($payload))];
        }

        $this->table(['Order Number', 'Estado', 'Campos'], $tableData);
        $this->newLine();

        if ($dryRun) {
            $this->info("Resumen: {$stats['total']} filas | Incluidas en payload: {$stats['included']} | Omitidas: {$stats['skipped']}");
            $this->newLine();
            $this->line('Payload que se enviaría (primeros 2 items):');
            $this->line(json_encode(array_slice($bulkPayload, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        if (empty($bulkPayload)) {
            $this->warn('No hay items para enviar.');
            return self::SUCCESS;
        }

        try {
            $request = Http::timeout(120)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]);

            if ($apiToken) {
                $request = $request->withToken($apiToken);
            }

            $response = $request->put($apiUrl, $bulkPayload);

            if ($response->successful()) {
                $this->info('✓ Actualización enviada correctamente. ' . count($bulkPayload) . ' POs.');
                $body = $response->json();
                if (!empty($body)) {
                    $this->line(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                return self::SUCCESS;
            }

            $this->error('Error API: ' . $response->status());
            $this->line($response->body());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
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

        $lines = explode("\n", $content);
        $header = str_getcsv(array_shift($lines), ';');
        $header = array_map(fn ($h) => trim($h), $header);
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $values = str_getcsv($line, ';');
            if (count($values) === count($header)) {
                $rows[] = array_combine($header, $values);
            }
        }

        return $rows;
    }

    private function buildPayload(array $row): array
    {
        $payload = [];

        foreach (self::CSV_TO_DB as $csvCol => $dbField) {
            if ($dbField === null) {
                continue;
            }

            $raw = trim($row[$csvCol] ?? '');
            if ($raw === '' || $raw === '0') {
                if ($csvCol === 'Usa Almacen fiscal') {
                    $payload['uses_bonded_warehouse'] = false;
                }
                continue;
            }

            if ($csvCol === 'Estado o Etapa') {
                $id = self::ESTADO_TO_KANBAN_ID[$raw] ?? null;
                if ($id !== null) {
                    $payload[$dbField] = $id;
                }
                continue;
            }

            if ($csvCol === 'Tipo de contenedor') {
                $payload[$dbField] = $this->normalizeContainerType($raw);
                continue;
            }

            if ($csvCol === 'Usa Almacen fiscal') {
                $payload[$dbField] = $this->toBoolean($raw);
                continue;
            }

            $dateFields = [
                'date_booking_request', 'date_booking_authorized', 'forwader_date',
                'date_etd_initial', 'date_etd', 'date_atd', 'date_eta_initial', 'date_eta', 'date_ata',
                'bonded_warehouse_enter', 'bonded_warehouse_exit',
            ];
            if (in_array($dbField, $dateFields)) {
                $parsed = $this->parseDate($raw);
                if ($parsed !== null) {
                    $payload[$dbField] = $parsed;
                }
                continue;
            }

            if ($dbField === 'freight_amount') {
                $parsed = $this->parseDecimal($raw);
                if ($parsed !== null) {
                    $payload[$dbField] = $parsed;
                }
                continue;
            }

            $payload[$dbField] = $raw;
        }

        return $payload;
    }

    private function normalizeContainerType(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^Contenedor\s+(.+)$/i', $value, $m)) {
            return trim($m[1]);
        }
        return $value;
    }

    private function toBoolean(string $value): bool
    {
        return in_array(strtoupper(trim($value)), ['Y', 'YES', '1', 'TRUE', 'SI', 'SÍ']);
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || in_array($value, ['0', '00-01-00'], true)) {
            return null;
        }

        if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('d-m-y', $value);
            if ($date) {
                if ((int) $date->format('Y') < 2000) {
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

        try {
            $date = \Carbon\Carbon::parse($value);
            if ($date->year >= 2000 && $date->year <= 2100) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // ignore
        }

        return null;
    }

    private function parseDecimal(string $value): ?float
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
