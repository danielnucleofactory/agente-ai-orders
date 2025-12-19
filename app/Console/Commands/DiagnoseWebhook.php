<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RagaOrders\Webhook\Models\WebhookEndpoint;
use RagaOrders\Webhook\Models\WebhookEvent;

class DiagnoseWebhook extends Command
{
    protected $signature = 'webhook:diagnose';
    protected $description = 'Diagnose webhook module configuration and status';

    public function handle()
    {
        $this->info('=== Webhook Module Diagnosis ===');
        $this->newLine();

        // 1. Check if module is enabled
        $this->info('1. Module Configuration:');
        $enabled = config('webhook.enabled', false);
        $this->line("   - Module Enabled: " . ($enabled ? 'YES' : 'NO'));
        $this->line("   - WEBHOOK_MODULE_ENABLED env: " . env('WEBHOOK_MODULE_ENABLED', 'not set'));
        $this->newLine();

        // 2. Check if function exists
        $this->info('2. Function Availability:');
        $functionExists = function_exists('dispatch_webhook');
        $this->line("   - dispatch_webhook() exists: " . ($functionExists ? 'YES' : 'NO'));
        $this->newLine();

        // 3. Check if service provider is registered
        $this->info('3. Service Provider:');
        $providerClass = \RagaOrders\Webhook\WebhookServiceProvider::class;
        $providerExists = class_exists($providerClass);
        $this->line("   - WebhookServiceProvider class exists: " . ($providerExists ? 'YES' : 'NO'));

        if ($providerExists) {
            $registered = app()->getProviders($providerClass);
            $this->line("   - WebhookServiceProvider registered: " . (!empty($registered) ? 'YES' : 'NO'));
        }
        $this->newLine();

        // 4. Check if helpers file exists
        $this->info('4. Helpers File:');
        $helpersPath = base_path('internal_modules/orders-module-webhook/src/helpers.php');
        $helpersExists = file_exists($helpersPath);
        $this->line("   - helpers.php exists: " . ($helpersExists ? 'YES' : 'NO'));
        $this->line("   - Path: {$helpersPath}");
        $this->newLine();

        // 5. Check webhook events
        $this->info('5. Webhook Events:');
        try {
            $events = WebhookEvent::all();
            $this->line("   - Total events: " . $events->count());
            $this->line("   - Active events: " . $events->where('is_active', true)->count());

            $poUpdated = WebhookEvent::where('name', 'purchase_order.updated')->first();
            if ($poUpdated) {
                $this->line("   - purchase_order.updated exists: YES (active: " . ($poUpdated->is_active ? 'YES' : 'NO') . ")");
            } else {
                $this->line("   - purchase_order.updated exists: NO");
            }
        } catch (\Exception $e) {
            $this->error("   - Error checking events: " . $e->getMessage());
        }
        $this->newLine();

        // 6. Check webhook endpoints
        $this->info('6. Webhook Endpoints:');
        try {
            $endpoints = WebhookEndpoint::where('event_name', 'purchase_order.updated')->get();
            $this->line("   - Total endpoints for purchase_order.updated: " . $endpoints->count());
            $activeEndpoints = $endpoints->where('is_active', true);
            $this->line("   - Active endpoints: " . $activeEndpoints->count());

            foreach ($activeEndpoints as $endpoint) {
                $this->line("     * ID: {$endpoint->id}, URL: {$endpoint->url}");
            }
        } catch (\Exception $e) {
            $this->error("   - Error checking endpoints: " . $e->getMessage());
        }
        $this->newLine();

        // 7. Check queue configuration
        $this->info('7. Queue Configuration:');
        $queueConnection = config('webhook.queue_connection', config('queue.default'));
        $queueName = config('webhook.queue', 'default');
        $this->line("   - Queue connection: {$queueConnection}");
        $this->line("   - Queue name: {$queueName}");
        $this->newLine();

        // 8. Test function call
        $this->info('8. Function Test:');
        if ($functionExists) {
            try {
                $testPayload = [
                    'event' => 'test',
                    'timestamp' => now()->toIso8601String(),
                    'data' => ['test' => true],
                ];

                // Don't actually dispatch, just check if it would work
                $this->line("   - Function is callable: YES");
                $this->line("   - Module enabled check: " . (config('webhook.enabled', false) ? 'PASS' : 'FAIL'));
            } catch (\Exception $e) {
                $this->error("   - Error testing function: " . $e->getMessage());
            }
        } else {
            $this->error("   - Function is NOT available");
        }
        $this->newLine();

        // Summary
        $this->info('=== Summary ===');
        $issues = [];

        if (!$enabled) {
            $issues[] = "Module is not enabled (set WEBHOOK_MODULE_ENABLED=true)";
        }

        if (!$functionExists) {
            $issues[] = "dispatch_webhook() function is not available";
        }

        if (!$helpersExists) {
            $issues[] = "helpers.php file not found";
        }

        if (empty($issues)) {
            $this->info('✓ All checks passed! Webhook module should be working.');
        } else {
            $this->error('✗ Issues found:');
            foreach ($issues as $issue) {
                $this->error("  - {$issue}");
            }
        }

        return 0;
    }
}

