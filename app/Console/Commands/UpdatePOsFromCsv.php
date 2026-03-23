<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdatePOsFromCsv extends Command
{
    protected $signature = 'po:update-from-csv
                            {file : Ruta al archivo CSV (ej: actualización 50 primeras.csv)}
                            {--dry-run : Mostrar payload sin enviar a la API}
                            {--api-url= : URL del endpoint (sobrescribe config)}
                            {--batch-size=50 : Máximo de POs por request al endpoint bulk}
                            {--offset=0 : Omitir los primeros N ítems del payload (tras construir desde el CSV)}
                            {--first-batch-only : Enviar solo el primer lote y salir (para revisar antes del resto)}
                            {--delay-seconds=0 : Segundos de espera entre un lote y el siguiente (mismo comando)}
                            {--no-table : No imprimir la tabla fila por fila (útil en CSV muy grandes)}';

    protected $description = 'Actualiza POs vía API bulk (trading_company por columna TRADING_COMPANY). Lotes: --batch-size, --first-batch-only, --offset + --delay-seconds';

    /** Si la columna TRADING_COMPANY viene vacía o no existe en el CSV. */
    private const DEFAULT_TRADING_COMPANY = 'OLO1';

    private const CSV_TO_DB = [
        'ORDER_NUMBER'                      => null,
        'TRADING_COMPANY'                   => null,
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
        $batchSize = max(1, (int) $this->option('batch-size'));
        $offset = max(0, (int) $this->option('offset'));
        $firstBatchOnly = (bool) $this->option('first-batch-only');
        $delaySeconds = max(0, (int) $this->option('delay-seconds'));
        $noTable = (bool) $this->option('no-table');
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
        $this->info('Trading company: columna TRADING_COMPANY del CSV (vacío o ausente → ' . self::DEFAULT_TRADING_COMPANY . ').');
        $this->info('Tamaño de lote: ' . $batchSize);
        if ($offset > 0) {
            $this->info('Offset (ítems omitidos al inicio del payload): ' . $offset);
        }
        if ($firstBatchOnly) {
            $this->info('Modo: solo el primer lote (--first-batch-only)');
        }
        if ($delaySeconds > 0) {
            $this->info('Pausa entre lotes: ' . $delaySeconds . ' s');
        }
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

            $tradingCompany = $this->resolveTradingCompany($row);

            $payload = $this->buildPayload($row);
            if (empty($payload)) {
                $stats['skipped']++;
                $tableData[] = [$orderNumber, $tradingCompany, 'Sin cambios', '-'];
                continue;
            }

            $item = array_merge(
                ['order_number' => $orderNumber, 'trading_company' => $tradingCompany],
                $payload
            );
            $bulkPayload[] = $item;
            $stats['included']++;
            $tableData[] = [$orderNumber, $tradingCompany, 'OK', implode(', ', array_keys($payload))];
        }

        if (!$noTable) {
            $this->table(['Order Number', 'Trading', 'Estado', 'Campos'], $tableData);
            $this->newLine();
        } else {
            $this->info('Tabla omitida (--no-table). Resumen numérico abajo.');
            $this->newLine();
        }

        $includedBeforeSlice = count($bulkPayload);
        if ($offset > 0) {
            if ($offset >= $includedBeforeSlice) {
                $this->error("El offset ({$offset}) es mayor o igual al número de ítems en el payload ({$includedBeforeSlice}). Nada que enviar.");

                return self::FAILURE;
            }
            $bulkPayload = array_slice($bulkPayload, $offset);
            $this->info("Payload tras offset: " . count($bulkPayload) . " POs (se omitieron {$offset} del total construido).");
            $this->newLine();
        }

        if ($dryRun) {
            $this->info("Resumen CSV: {$stats['total']} filas | Incluidas en payload: {$stats['included']} | Omitidas: {$stats['skipped']}");
            $chunks = array_chunk($bulkPayload, $batchSize);
            if ($firstBatchOnly) {
                $chunks = array_slice($chunks, 0, 1);
            }
            $this->info('Lotes que se enviarían en esta ejecución: ' . count($chunks) . ' (hasta ' . $batchSize . ' POs por lote)');
            if ($delaySeconds > 0 && count($chunks) > 1) {
                $this->info("Entre cada lote habría una pausa de {$delaySeconds} s (excepto antes del primero).");
            }
            $this->newLine();
            $first = $chunks[0] ?? [];
            $this->line('Muestra: primeros 2 ítems del primer lote de esta ejecución:');
            $this->line(json_encode(array_slice($first, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if (empty($bulkPayload)) {
            $this->warn('No hay items para enviar.');
            return self::SUCCESS;
        }

        $chunks = array_chunk($bulkPayload, $batchSize);
        if ($firstBatchOnly) {
            $chunks = array_slice($chunks, 0, 1);
        }

        $totalChunks = count($chunks);
        $this->info('Enviando ' . array_sum(array_map('count', $chunks)) . " POs en {$totalChunks} lote(s)...");
        $this->newLine();

        try {
            $request = Http::timeout(300) // 5 minutos hacia olo.md
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]);

            if ($apiToken) {
                $request = $request->withToken($apiToken);
            }

            foreach ($chunks as $index => $chunk) {
                if ($index > 0 && $delaySeconds > 0) {
                    $this->info("Esperando {$delaySeconds} s antes del lote " . ($index + 1) . "/{$totalChunks}...");
                    sleep($delaySeconds);
                }

                $batchNum = $index + 1;
                $this->line("→ Lote {$batchNum}/{$totalChunks} (" . count($chunk) . ' POs)...');

                $response = $request->put($apiUrl, $chunk);

                if (!$response->successful()) {
                    $this->error("Error API en lote {$batchNum}/{$totalChunks}: " . $response->status());
                    $this->line($response->body());
                    if ($index === 0) {
                        $this->warn('Ningún lote se aplicó con éxito en esta ejecución. Reintenta el mismo comando más tarde (502 = servidor o servicio externo caído).');
                    } else {
                        $this->warn('Los lotes anteriores de esta ejecución ya se enviaron. Ajusta --offset si debes reanudar.');
                    }

                    return self::FAILURE;
                }

                $body = $response->json();
                if (is_array($body) && !empty($body)) {
                    if (!empty($body['summary'])) {
                        $this->line('  Resumen API: ' . json_encode($body['summary'], JSON_UNESCAPED_UNICODE));
                    } else {
                        $this->line('  Respuesta: ' . json_encode($body, JSON_UNESCAPED_UNICODE));
                    }
                }
            }

            $sent = array_sum(array_map('count', $chunks));
            $this->info("✓ Listo. Enviados {$sent} POs en {$totalChunks} lote(s).");

            if ($firstBatchOnly && !$dryRun) {
                $nextOffset = $offset + $sent;
                $quotedFile = escapeshellarg($file);
                $this->newLine();
                $this->info('Siguiente lote (solo ' . $batchSize . ' POs, revisar antes de seguir):');
                $this->line('php artisan po:update-from-csv ' . $quotedFile . ' --offset=' . $nextOffset . ' --batch-size=' . $batchSize . ' --first-batch-only --no-table');
                $this->newLine();
                $this->info('O continuar el resto automático (2 min entre lotes):');
                $this->line('php artisan po:update-from-csv ' . $quotedFile . ' --offset=' . $nextOffset . ' --batch-size=' . $batchSize . ' --delay-seconds=120 --no-table');
            }

            return self::SUCCESS;
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
        $headerLine = array_shift($lines);
        if ($headerLine === null) {
            return [];
        }
        $delimiter = $this->detectCsvDelimiter($headerLine);
        $header = str_getcsv($headerLine, $delimiter);
        $header = array_map(fn ($h) => trim($h), $header);
        $rows = [];

        foreach ($lines as $line) {
            $line = rtrim($line, "\r");
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, $delimiter);
            if (count($values) === count($header)) {
                $rows[] = array_combine($header, $values);
            }
        }

        return $rows;
    }

    /**
     * Detecta separador de columnas: tab (TSV) o punto y coma, según la línea de cabecera.
     */
    private function detectCsvDelimiter(string $headerLine): string
    {
        $tabs = substr_count($headerLine, "\t");
        $semicolons = substr_count($headerLine, ';');

        return $tabs >= $semicolons ? "\t" : ';';
    }

    private function resolveTradingCompany(array $row): string
    {
        $raw = trim((string) ($row['TRADING_COMPANY'] ?? ''));

        return $raw !== '' ? $raw : self::DEFAULT_TRADING_COMPANY;
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
                $payload[$dbField] = $raw;
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
