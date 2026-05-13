<?php

namespace App\Jobs;

use App\Services\PorthSyncFailureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\PorthSyncService;

class PorthSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutos

    protected $documentId;
    protected $documentType;

    /**
     * Create a new job instance.
     */
    public function __construct($documentId, $documentType)
    {
        $this->documentId = $documentId;
        $this->documentType = $documentType;
    }

    /**
     * Execute the job.
     */
    public function handle(PorthSyncService $porthSyncService)
    {
        $startTime = microtime(true);
        
        Log::info('Starting PorthSyncJob', [
            'document_id' => $this->documentId,
            'document_type' => $this->documentType,
            'attempt' => $this->attempts()
        ]);

        try {
            $result = $porthSyncService->syncDocument($this->documentId, $this->documentType);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            if ($result) {
                Log::info('PorthSyncJob completed successfully', [
                    'document_id' => $this->documentId,
                    'document_type' => $this->documentType,
                    'duration_ms' => $duration,
                    'attempt' => $this->attempts()
                ]);
            } else {
                Log::warning('PorthSyncJob completed but no sync occurred', [
                    'document_id' => $this->documentId,
                    'document_type' => $this->documentType,
                    'duration_ms' => $duration,
                    'attempt' => $this->attempts()
                ]);

                throw new \RuntimeException('PorthSyncJob completed without creating or linking a shipment.');
            }

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            Log::error('PorthSyncJob failed', [
                'document_id' => $this->documentId,
                'document_type' => $this->documentType,
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
        Log::error('PorthSyncJob failed permanently', [
            'document_id' => $this->documentId,
            'document_type' => $this->documentType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        app(PorthSyncFailureService::class)->recordSyncJobFailure(
            (int) $this->documentId,
            (string) $this->documentType,
            $exception,
            (int) $this->attempts()
        );
    }
}
