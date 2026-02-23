<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Services\PorthImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Comando de prueba para simular una actualización de Porth
 * 
 * Simula datos de Porth con cambios (ej: cambio de puertos) y procesa
 * la actualización para probar el flujo de webhooks.
 */
class TestPorthUpdate extends Command
{
    protected $signature = 'porth:test-update 
                            {--po-id= : ID de la Purchase Order a actualizar}
                            {--porth-id= : ID de Porth a usar (si no se proporciona, busca una PO con porth_id)}';
    
    protected $description = 'Simula una actualización de Porth para probar el flujo de webhooks';

    public function __construct(
        protected PorthImportService $importService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $poId = $this->option('po-id');
        $porthId = $this->option('porth-id');

        // Buscar PO
        if ($poId) {
            $po = PurchaseOrder::find($poId);
            if (!$po) {
                $this->error("No se encontró la PO con ID: {$poId}");
                return self::FAILURE;
            }
            $porthId = $porthId ?? $po->porth_id;
        } else {
            // Buscar cualquier PO con porth_id
            $po = PurchaseOrder::whereNotNull('porth_id')->first();
            if (!$po) {
                $this->error("No se encontró ninguna PO con porth_id. Usa --po-id o --porth-id para especificar.");
                return self::FAILURE;
            }
            $porthId = $porthId ?? $po->porth_id;
        }

        if (!$porthId) {
            $this->error("No se pudo determinar el porth_id. Usa --porth-id para especificar.");
            return self::FAILURE;
        }

        $this->info("Simulando actualización de Porth para:");
        $this->line("  - PO ID: {$po->id}");
        $this->line("  - Order Number: {$po->order_number}");
        $this->line("  - Porth ID: {$porthId}");
        $this->newLine();

        // Obtener valores actuales para simular cambios
        $currentPol = $po->porth_pol ?? 'USNYC';
        $currentPod = $po->porth_pod ?? 'MXVER';
        $currentPolName = $po->porth_pol_name ?? 'New York';
        $currentPodName = $po->porth_pod_name ?? 'Veracruz';

        // Simular cambio de puertos (cambiar a nuevos valores)
        $newPol = $currentPol === 'USNYC' ? 'USLAX' : 'USNYC';
        $newPod = $currentPod === 'MXVER' ? 'MXMAN' : 'MXVER';
        $newPolName = $newPol === 'USLAX' ? 'Los Angeles' : 'New York';
        $newPodName = $newPod === 'MXMAN' ? 'Manzanillo' : 'Veracruz';

        $this->info("Simulando cambios:");
        $this->line("  - POL: {$currentPol} → {$newPol} ({$currentPolName} → {$newPolName})");
        $this->line("  - POD: {$currentPod} → {$newPod} ({$currentPodName} → {$newPodName})");
        $this->newLine();

        // Construir payload simulado de Porth
        $simulatedPorthData = [
            'id' => $porthId,
            'shipmentNumber' => $po->porth_shipment_number ?? 12345,
            'carrierCode' => $po->porth_carrier_code ?? 'MAEU',
            'pol' => $newPol,
            'pod' => $newPod,
            'polName' => $newPolName,
            'podName' => $newPodName,
            'phase' => $po->porth_phase ?? 'in_transit',
            'priority' => $po->porth_priority ?? 'normal',
            'modality' => $po->porth_modality ?? 'FCL',
            'freightType' => $po->freight_type ?? 'ocean',
            'origin' => $newPolName . ', USA',
            'finalDestination' => $newPodName . ', Mexico',
            'etd' => $po->date_etd?->toIso8601String() ?? now()->addDays(5)->toIso8601String(),
            'eta' => $po->date_eta?->toIso8601String() ?? now()->addDays(20)->toIso8601String(),
            'firstEtd' => $po->porth_first_etd?->toIso8601String() ?? now()->addDays(5)->toIso8601String(),
            'firstEta' => $po->porth_first_eta?->toIso8601String() ?? now()->addDays(20)->toIso8601String(),
            'masterBl' => $po->mbl_number,
            'container' => $po->container_number,
            'bookingNumber' => null,
            'manualTracking' => $po->porth_manual_tracking ?? false,
        ];

        $this->info("Procesando actualización...");
        $this->line("");

        try {
            // Procesar la actualización (esto debería disparar el webhook)
            $updatedPO = $this->importService->importShipment($simulatedPorthData);

            if ($updatedPO) {
                $this->info("✓ Actualización procesada correctamente");
                $this->line("");
                $this->info("PO actualizada:");
                $this->line("  - ID: {$updatedPO->id}");
                $this->line("  - Order Number: {$updatedPO->order_number}");
                $this->line("  - Nuevo POL: {$updatedPO->porth_pol} ({$updatedPO->porth_pol_name})");
                $this->line("  - Nuevo POD: {$updatedPO->porth_pod} ({$updatedPO->porth_pod_name})");
                
                if ($updatedPO->departure_port) {
                    $this->line("  - Puerto de salida traducido: {$updatedPO->departure_port}");
                }
                if ($updatedPO->arrival_port) {
                    $this->line("  - Puerto de llegada traducido: {$updatedPO->arrival_port}");
                }

                $this->newLine();
                $this->info("Revisa los logs para ver el webhook disparado:");
                $this->line("  tail -f storage/logs/laravel.log | grep porth_import");
                
                return self::SUCCESS;
            } else {
                $this->warn("⚠ La actualización no encontró POs para actualizar");
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("✗ Error al procesar actualización: {$e->getMessage()}");
            $this->line("");
            $this->line("Trace:");
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
