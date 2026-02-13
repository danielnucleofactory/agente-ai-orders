<?php

namespace App\Console\Commands;

use App\Services\HistoricalDataImportService;
use App\Services\PurchaseOrderCsvImportService;
use Illuminate\Console\Command;

class ImportHistoricalPOCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'po:import-historical-csv 
                            {file? : Ruta al archivo CSV (por defecto: datahistorica2025.csv en la raíz del proyecto)}
                            {--target=po : Destino: "po" (purchase_orders) o "historical" (historical_purchase_orders)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga un archivo CSV a la tabla purchase_orders (PO) o historical_purchase_orders';

    /**
     * Execute the console command.
     */
    public function handle(
        PurchaseOrderCsvImportService $poImportService,
        HistoricalDataImportService $historicalImportService
    ): int {
        $file = $this->argument('file') ?? base_path('datahistorica2025.csv');
        $target = $this->option('target') ?? 'po';

        if (!file_exists($file)) {
            $this->error("El archivo no existe: {$file}");
            return self::FAILURE;
        }

        $targetLabel = $target === 'historical' ? 'historical_purchase_orders' : 'purchase_orders';
        $this->info("Importando desde: {$file}");
        $this->info("Destino: {$targetLabel}");
        $this->info('Procesando...');

        $result = $target === 'historical'
            ? $historicalImportService->importFromCsv($file)
            : $poImportService->importFromCsv($file);

        $this->newLine();
        $this->info('Resultado de la importación:');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total de filas procesadas', $result['total']],
                ['Registros importados', $result['imported']],
                ['Duplicados omitidos', $result['skipped']],
                ['Errores', $result['errors']],
            ]
        );

        if (!empty($result['error_messages'])) {
            $this->newLine();
            $this->warn('Mensajes de error:');
            foreach (array_slice($result['error_messages'], 0, 10) as $msg) {
                $this->line("  - {$msg}");
            }
            if (count($result['error_messages']) > 10) {
                $this->line('  ... y ' . (count($result['error_messages']) - 10) . ' más.');
            }
        }

        if ($result['errors'] > 0 && $result['imported'] === 0) {
            return self::FAILURE;
        }

        $this->info('Importación completada correctamente.');
        return self::SUCCESS;
    }
}
