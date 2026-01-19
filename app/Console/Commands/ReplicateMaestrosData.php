<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MaestrosApiService;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;

class ReplicateMaestrosData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maestros:replicate 
                            {--source-client= : Cliente fuente que tiene los datos (opcional)}
                            {--dry-run : Ejecutar sin hacer cambios reales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replica datos de maestros desde un cliente fuente a todos los demás clientes';

    protected $maestrosService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->maestrosService = new MaestrosApiService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $sourceClient = $this->option('source-client');

        $this->info('🔄 Iniciando replicación de datos de maestros...');
        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        // Obtener todos los clientes únicos de las POs
        $allClients = PurchaseOrder::select('trading_company')
            ->distinct()
            ->whereNotNull('trading_company')
            ->where('trading_company', '!=', '')
            ->pluck('trading_company')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($allClients)) {
            $this->error('❌ No se encontraron clientes en las Purchase Orders');
            return Command::FAILURE;
        }

        $this->info("📋 Clientes encontrados: " . implode(', ', $allClients));

        // Si se especificó un cliente fuente, usarlo; sino, encontrar el que tiene más datos
        if ($sourceClient) {
            if (!in_array($sourceClient, $allClients)) {
                $this->error("❌ El cliente fuente '{$sourceClient}' no existe en las POs");
                return Command::FAILURE;
            }
            $sourceClients = [$sourceClient];
        } else {
            $sourceClients = $this->findSourceClients($allClients);
        }

        if (empty($sourceClients)) {
            $this->error('❌ No se encontró ningún cliente con datos en los maestros');
            return Command::FAILURE;
        }

        $this->info("📦 Cliente(s) fuente: " . implode(', ', $sourceClients));
        $targetClients = array_diff($allClients, $sourceClients);

        if (empty($targetClients)) {
            $this->info('✅ Todos los clientes ya tienen datos o solo hay un cliente');
            return Command::SUCCESS;
        }

        $this->info("🎯 Clientes destino: " . implode(', ', $targetClients));

        // Tipos de maestros a replicar
        $maestroTypes = [
            'ports' => [
                'method' => 'getPorts',
                'createMethod' => 'createPort',
                'nameField' => 'name',
            ],
            'shipping-lines' => [
                'method' => 'getShippingLines',
                'createMethod' => 'createShippingLine',
                'nameField' => 'name',
            ],
            'container-types' => [
                'method' => 'getContainerTypes',
                'createMethod' => 'createContainerType',
                'nameField' => 'name',
            ],
            'service-providers' => [
                'method' => 'getServiceProviders',
                'createMethod' => 'createServiceProvider',
                'nameField' => 'name',
            ],
            'transport-types' => [
                'method' => 'getTransportTypes',
                'createMethod' => 'createTransportType',
                'nameField' => 'name',
            ],
            'rate-types' => [
                'method' => 'getRateTypes',
                'createMethod' => 'createRateType',
                'nameField' => 'name',
            ],
        ];

        $totalCreated = 0;
        $totalErrors = 0;

        foreach ($maestroTypes as $typeName => $config) {
            $this->newLine();
            $this->info("📦 Procesando: {$typeName}");

            // Obtener datos del cliente fuente
            $sourceData = null;
            foreach ($sourceClients as $sourceClientName) {
                $response = $this->maestrosService->{$config['method']}([
                    'company' => $sourceClientName,
                    'trading_company' => $sourceClientName,
                    'active' => 'true',
                    'per_page' => 1000,
                ]);

                if ($response && isset($response['data']) && !empty($response['data'])) {
                    $sourceData = $response['data'];
                    $this->info("   ✓ Encontrados " . count($sourceData) . " registros en '{$sourceClientName}'");
                    break;
                }
            }

            if (empty($sourceData)) {
                $this->warn("   ⚠️  No se encontraron datos para {$typeName}");
                continue;
            }

            // Replicar para cada cliente destino
            foreach ($targetClients as $targetClient) {
                $this->line("   → Replicando para '{$targetClient}'...");

                // Verificar si el cliente ya tiene datos
                $existingResponse = $this->maestrosService->{$config['method']}([
                    'company' => $targetClient,
                    'trading_company' => $targetClient,
                    'per_page' => 1,
                ]);

                if ($existingResponse && isset($existingResponse['data']) && !empty($existingResponse['data'])) {
                    $this->line("     ⏭️  Ya tiene datos, omitiendo...");
                    continue;
                }

                // Replicar cada registro
                $created = 0;
                $errors = 0;

                foreach ($sourceData as $item) {
                    // Preparar datos para crear (copiar todos los campos excepto ID y company)
                    $newData = [];
                    foreach ($item as $key => $value) {
                        // Excluir campos que no deben copiarse
                        if (!in_array($key, ['id', 'company', 'trading_company', 'created_at', 'updated_at'])) {
                            $newData[$key] = $value;
                        }
                    }

                    // Asignar el nuevo cliente
                    $newData['company'] = $targetClient;
                    $newData['trading_company'] = $targetClient;

                    if (!$dryRun) {
                        $result = $this->maestrosService->{$config['createMethod']}($newData);
                        if ($result) {
                            $created++;
                        } else {
                            $errors++;
                            Log::warning("Error replicando {$typeName} para {$targetClient}", [
                                'item' => $item[$config['nameField']] ?? 'unknown',
                            ]);
                        }
                    } else {
                        $created++;
                    }
                }

                if ($dryRun) {
                    $this->line("     📝 [DRY-RUN] Se crearían {$created} registros");
                } else {
                    if ($created > 0) {
                        $this->info("     ✓ Creados {$created} registros");
                        $totalCreated += $created;
                    }
                    if ($errors > 0) {
                        $this->error("     ✗ Errores: {$errors}");
                        $totalErrors += $errors;
                    }
                }
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info('✅ Simulación completada (modo dry-run)');
        } else {
            $this->info("✅ Replicación completada!");
            $this->info("   ✓ Total creados: {$totalCreated}");
            if ($totalErrors > 0) {
                $this->warn("   ⚠️  Total errores: {$totalErrors}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Encuentra el cliente fuente que tiene más datos en los maestros
     *
     * @param array $clients
     * @return array
     */
    protected function findSourceClients(array $clients): array
    {
        $clientDataCount = [];

        $maestroTypes = [
            'getPorts',
            'getShippingLines',
            'getContainerTypes',
            'getServiceProviders',
            'getTransportTypes',
            'getRateTypes',
        ];

        foreach ($clients as $client) {
            $totalCount = 0;
            foreach ($maestroTypes as $method) {
                $response = $this->maestrosService->$method([
                    'company' => $client,
                    'trading_company' => $client,
                    'active' => 'true',
                    'per_page' => 1,
                ]);

                if ($response && isset($response['data']) && !empty($response['data'])) {
                    // Obtener el total real
                    $fullResponse = $this->maestrosService->$method([
                        'company' => $client,
                        'trading_company' => $client,
                        'active' => 'true',
                        'per_page' => 1000,
                    ]);

                    if ($fullResponse && isset($fullResponse['data'])) {
                        $totalCount += count($fullResponse['data']);
                    }
                }
            }
            $clientDataCount[$client] = $totalCount;
        }

        // Ordenar por cantidad de datos (descendente)
        arsort($clientDataCount);

        // Retornar el cliente con más datos (o los que tengan datos)
        $maxCount = max($clientDataCount);
        if ($maxCount > 0) {
            return array_keys(array_filter($clientDataCount, fn($count) => $count === $maxCount));
        }

        return [];
    }
}
