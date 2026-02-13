<?php

namespace App\Console\Commands;

use App\Services\HistoricalDataImportService;
use Illuminate\Console\Command;

class ImportHistoricalPOCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'po:import-historical-csv 
                            {file? : Ruta al archivo CSV (por defecto: datahistorica2025.csv en la raíz del proyecto)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga un archivo CSV de datos históricos a la tabla historical_purchase_orders (PO históricos)';

    /**
     * Execute the console command.
     */
    public function handle(HistoricalDataImportService $importService): int
    {
        $file = $this->argument('file') ?? base_path('datahistorica2025.csv');

        if (!file_exists($file)) {
            $this->error("El archivo no existe: {$file}");
            return self::FAILURE;
        }

        $this->info("Importando desde: {$file}");
        $this->info('Procesando...');

        $result = $importService->importFromCsv($file);

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
