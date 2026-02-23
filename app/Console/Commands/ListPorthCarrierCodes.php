<?php

namespace App\Console\Commands;

use App\Services\PorthTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListPorthCarrierCodes extends Command
{
    protected $signature = 'porth:list-carrier-codes
                            {--export= : Ruta de archivo para exportar (ej: docs/porth_carrier_codes.txt)}
                            {--json : Exportar como JSON (nombre => código)}
                            {--codes-only : Solo listar códigos, uno por línea}';

    protected $description = 'Lista los carrierCode de navieras (líneas de envío) para usar en creación de embarques Porth. Fuente: ports_and_shippinglines.csv (ocean).';

    public function __construct(
        protected PorthTranslationService $translationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $lines = $this->translationService->getShippingLinesForExport();

        if ($lines->isEmpty()) {
            $this->warn('No se encontraron navieras. Revisa que exista ports_and_shippinglines.csv con columnas NAME SHIPPING LINE / TYPE / CODE (solo type=ocean).');
            return self::FAILURE;
        }

        $codesOnly = $this->option('codes-only');
        $exportPath = $this->option('export');
        $asJson = $this->option('json');

        $uniqueLines = $lines->unique('code')->sortBy('code')->values();
        $carrierCodes = $uniqueLines->pluck('code');
        $codeToName = $uniqueLines->keyBy('code')->map->name;

        if ($codesOnly) {
            $this->line($carrierCodes->implode("\n"));
            $this->newLine();
            $this->info('Total: ' . $carrierCodes->count() . ' carrier codes.');
        } else {
            $this->table(
                ['carrierCode', 'Nombre (Maestros)'],
                $uniqueLines->map(fn ($item) => [$item['code'], $item['name']])->toArray()
            );
            $this->info('Total: ' . $uniqueLines->count() . ' carrier codes.');
        }

        if ($exportPath !== null) {
            $fullPath = base_path($exportPath);
            $dir = dirname($fullPath);
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            if ($asJson) {
                $content = json_encode($codeToName->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } elseif ($codesOnly) {
                $content = $carrierCodes->implode("\n") . "\n";
            } else {
                $header = "# carrierCode\tNombre (Maestros)\n";
                $content = $header . $uniqueLines->map(fn ($item) => $item['code'] . "\t" . $item['name'])->implode("\n") . "\n";
            }

            File::put($fullPath, $content);
            $this->info('Exportado a: ' . $fullPath);
        }

        return self::SUCCESS;
    }
}
