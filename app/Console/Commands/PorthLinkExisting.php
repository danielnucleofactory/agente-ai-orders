<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\PorthApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PorthLinkExisting extends Command
{
    protected $signature = 'porth:link-existing 
                            {--dry-run : Modo prueba, no modifica datos}
                            {--limit= : Limitar cantidad de POs a procesar}
                            {--force : Forzar re-link incluso si ya tiene porth_id}';
    
    protected $description = 'Vincula PurchaseOrders existentes con Porth buscando por MBL, contenedor o booking.';

    protected PorthApiService $api;
    protected int $linked = 0;
    protected int $notFound = 0;
    protected int $skipped = 0;
    protected int $errors = 0;

    public function __construct(PorthApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = (bool) $this->option('force');

        $this->info("Iniciando vinculación de PurchaseOrders existentes con Porth");
        $this->info("Modo: " . ($dryRun ? 'DRY-RUN (no modifica datos)' : 'REAL'));

        if (!$this->api->isEnabled()) {
            $this->error("API de Porth no configurada. Verifica PORTH_API_KEY en .env");
            return self::FAILURE;
        }

        // Buscar PurchaseOrders con información de tracking pero sin porth_id
        $query = PurchaseOrder::query()
            ->where(function ($q) {
                $q->whereNotNull('mbl_number')
                    ->orWhereNotNull('container_number')
                    ->orWhereNotNull('tracking_id');
            });

        if (!$force) {
            $query->whereNull('porth_id');
        }

        if ($limit) {
            $query->limit($limit);
        }

        $purchaseOrders = $query->get();
        $total = $purchaseOrders->count();

        if ($total === 0) {
            $this->info("No se encontraron PurchaseOrders para vincular.");
            return self::SUCCESS;
        }

        $this->info("PurchaseOrders a procesar: {$total}");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($purchaseOrders as $po) {
            $this->processPurchaseOrder($po, $dryRun);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->displaySummary($total, $dryRun);

        return self::SUCCESS;
    }

    protected function processPurchaseOrder(PurchaseOrder $po, bool $dryRun): void
    {
        $identifiers = $this->extractIdentifiers($po);

        if (empty($identifiers)) {
            $this->skipped++;
            return;
        }

        // Intentar encontrar en Porth usando los identificadores disponibles
        $porthData = $this->findInPorth($identifiers);

        if (!$porthData) {
            $this->notFound++;
            Log::info('porth_link:not_found', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'identifiers' => $identifiers,
            ]);
            return;
        }

        $porthId = $porthData['id'] ?? null;

        if (!$porthId) {
            $this->notFound++;
            return;
        }

        if ($dryRun) {
            $this->linked++;
            Log::info('porth_link:would_link', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'porth_id' => $porthId,
                'matched_by' => $identifiers,
            ]);
            return;
        }

        try {
            $po->porth_id = $porthId;
            $po->save();

            $this->linked++;

            Log::info('porth_link:linked', [
                'purchase_order_id' => $po->id,
                'order_number' => $po->order_number,
                'porth_id' => $porthId,
            ]);

        } catch (\Exception $e) {
            $this->errors++;
            Log::error('porth_link:error', [
                'purchase_order_id' => $po->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function extractIdentifiers(PurchaseOrder $po): array
    {
        $identifiers = [];

        if (!empty($po->mbl_number)) {
            $identifiers['mbl'] = $po->mbl_number;
        }

        if (!empty($po->container_number)) {
            $identifiers['container'] = $po->container_number;
        }

        if (!empty($po->tracking_id)) {
            $identifiers['tracking_id'] = $po->tracking_id;
        }

        return $identifiers;
    }

    protected function findInPorth(array $identifiers): ?array
    {
        // Prioridad: tracking_id > mbl > container > booking
        $searchOrder = ['tracking_id', 'mbl', 'container', 'booking'];

        foreach ($searchOrder as $type) {
            if (!isset($identifiers[$type])) {
                continue;
            }

            $value = $identifiers[$type];
            $result = match ($type) {
                'tracking_id' => $this->api->getShipmentById($value),
                'mbl' => $this->api->getShipmentByMasterBl($value),
                'container' => $this->api->getShipmentByContainer($value),
                'booking' => $this->api->getShipmentByBooking($value),
                default => null,
            };

            if ($result && isset($result['id'])) {
                Log::info('porth_link:found', [
                    'type' => $type,
                    'value' => $value,
                    'porth_id' => $result['id'],
                ]);
                return $result;
            }
        }

        return null;
    }

    protected function displaySummary(int $total, bool $dryRun): void
    {
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total procesados', $total],
                ['Vinculados' . ($dryRun ? ' (simulado)' : ''), $this->linked],
                ['No encontrados en Porth', $this->notFound],
                ['Omitidos (sin identificadores)', $this->skipped],
                ['Errores', $this->errors],
            ]
        );

        if ($dryRun && $this->linked > 0) {
            $this->newLine();
            $this->warn("Modo DRY-RUN: No se modificaron datos. Ejecuta sin --dry-run para aplicar cambios.");
        }
    }
}
