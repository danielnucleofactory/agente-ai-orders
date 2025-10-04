<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShippingDocument;
use App\Services\PorthSyncService;

class CheckPorthSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'porth:check-sync {document_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Porth sync status and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $documentId = $this->argument('document_id');
        
        $this->info('=== Porth Sync Status Check ===');
        
        // Check configuration
        $this->info('Configuration:');
        $this->line('- API Key configured: ' . (config('services.porth.api_key') ? 'Yes' : 'No'));
        $this->line('- Base URL: ' . config('services.porth.base_url'));
        $this->line('- Sync enabled: ' . (config('services.porth.sync_enabled') ? 'Yes' : 'No'));
        
        // Check specific document if provided
        if ($documentId) {
            $document = ShippingDocument::find($documentId);
            if (!$document) {
                $this->error("Document with ID {$documentId} not found");
                return;
            }
            
            $this->info("\nDocument Details:");
            $this->line("- ID: {$document->id}");
            $this->line("- Document Number: {$document->document_number}");
            $this->line("- MBL Number: " . ($document->mbl_number ?? 'N/A'));
            $this->line("- Container Number: " . ($document->container_number ?? 'N/A'));
            $this->line("- Tracking ID: " . ($document->tracking_id ?? 'N/A'));
            $this->line("- Porth Shipment ID: " . ($document->porth_shipment_id ?? 'N/A'));
            
            // Test sync manually
            if ($this->confirm('Do you want to test sync with Porth for this document?')) {
                $this->info('Testing sync...');
                $syncService = new PorthSyncService();
                $result = $syncService->syncDocument($document->id, ShippingDocument::class);
                $this->line('Sync result: ' . ($result ? 'Success' : 'Failed'));
            }
        } else {
            // Show recent documents
            $this->info("\nRecent Shipping Documents:");
            $documents = ShippingDocument::orderBy('updated_at', 'desc')->limit(5)->get();
            
            foreach ($documents as $doc) {
                $this->line("- ID: {$doc->id} | Doc: {$doc->document_number} | MBL: " . ($doc->mbl_number ?? 'N/A') . " | Porth: " . ($doc->porth_shipment_id ?? 'N/A'));
            }
        }
        
        // Check queue status
        $this->info("\nQueue Status:");
        $this->line('- Porth sync queue jobs: ' . \DB::table('jobs')->where('queue', 'porth-sync')->count());
        $this->line('- Failed jobs: ' . \DB::table('failed_jobs')->count());
    }
}