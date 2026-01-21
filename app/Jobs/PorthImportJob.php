<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\PorthSyncUpdatedService;

/**
 * Job para importar datos completos de un shipment desde Porth.
 * 
 * Se dispara automáticamente después de que se asigna un porth_id a una PurchaseOrder.
 * Usa PorthSyncUpdatedService::syncById() para obtener e importar todos los datos
 * del shipment (fechas, puertos, estados, etc.).
 */
class PorthImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutos

    protected string $porthId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $porthId)
    {
        $this->porthId = $porthId;
    }

    /**
     * Execute the job.
     */
    public function handle(PorthSyncUpdatedService $syncUpdatedService)
    {
        $startTime = microtime(true);
        
        Log::info('Starting PorthImportJob', [
            'porth_id' => $this->porthId,
            'attempt' => $this->attempts()
        ]);

        try {
            // Importar datos completos del shipment usando PorthSyncUpdatedService
            $result = $syncUpdatedService->syncById($this->porthId, false);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            $status = $result['results'][$this->porthId]['status'] ?? 'unknown';
            
            if ($status === 'imported') {
                Log::info('PorthImportJob completed successfully', [
                    'porth_id' => $this->porthId,
                    'status' => $status,
                    'purchase_order_id' => $result['results'][$this->porthId]['purchase_order_id'] ?? null,
                    'order_number' => $result['results'][$this->porthId]['order_number'] ?? null,
                    'duration_ms' => $duration,
                    'attempt' => $this->attempts()
                ]);
            } else {
                Log::warning('PorthImportJob completed but no import occurred', [
                    'porth_id' => $this->porthId,
                    'status' => $status,
                    'duration_ms' => $duration,
                    'attempt' => $this->attempts()
                ]);
            }

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            Log::error('PorthImportJob failed', [
                'porth_id' => $this->porthId,
                'error' => $e->getMessage(),
                'duration_ms' => $duration,
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('PorthImportJob failed permanently', [
            'porth_id' => $this->porthId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
