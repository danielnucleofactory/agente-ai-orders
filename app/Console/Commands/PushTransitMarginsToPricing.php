<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PricingMarginsPushService;
use Illuminate\Console\Command;

final class PushTransitMarginsToPricing extends Command
{
    protected $signature = 'pricing:push-transit-margins
                            {company_id : Company ID de Orders}
                            {--date-from= : Fecha ATA desde (YYYY-MM-DD)}
                            {--date-to= : Fecha ATA hasta (YYYY-MM-DD)}
                            {--vendor= : Vendor ID}
                            {--trading-company= : Trading company}
                            {--search= : Búsqueda libre}
                            {--api-url= : URL destino para sobrescribir config}
                            {--dry-run : Construye el payload pero no lo envía}
                            {--show-payload : Imprime el payload completo}';

    protected $description = 'Construye y envía a Pricing los márgenes agregados desde el reporte interno de tránsito.';

    public function __construct(
        private readonly PricingMarginsPushService $pricingMarginsPushService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $companyId = (int) $this->argument('company_id');
        $filters = $this->buildFilters();
        $apiUrl = $this->option('api-url') ? trim((string) $this->option('api-url')) : null;
        $dryRun = (bool) $this->option('dry-run');
        $showPayload = (bool) $this->option('show-payload');

        $payload = $this->pricingMarginsPushService->buildPayload($companyId, $filters);
        $margins = $payload['margins'] ?? [];
        $meta = $payload['meta'] ?? [];

        $this->info('Resumen del payload');
        $this->line('  - Company ID: ' . $companyId);
        $this->line('  - ATA desde: ' . ($meta['ata_from'] ?? 'N/A'));
        $this->line('  - ATA hasta: ' . ($meta['ata_to'] ?? 'N/A'));
        $this->line('  - Movimientos deduplicados: ' . ($meta['movements_count'] ?? 0));
        $this->line('  - Grupos agregados: ' . ($meta['groups_count'] ?? 0));
        $this->line('  - Márgenes a enviar: ' . (is_array($margins) ? count($margins) : 0));

        if ($showPayload) {
            $this->newLine();
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif (is_array($margins) && ! empty($margins)) {
            $this->newLine();
            $this->line('Muestra de los primeros 2 márgenes:');
            $this->line(json_encode(array_slice($margins, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry-run: no se envió nada a Pricing.');
            return self::SUCCESS;
        }

        if (! is_array($margins) || empty($margins)) {
            $this->newLine();
            $this->warn('No hay márgenes para enviar con ese filtro.');
            return self::SUCCESS;
        }

        try {
            $result = $this->pricingMarginsPushService->push($companyId, $filters, $apiUrl);
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Error al empujar a Pricing: ' . $e->getMessage());
            return self::FAILURE;
        }

        $body = $result['body'] ?? null;
        $createdCount = is_array($body['data'] ?? null) ? count($body['data']) : null;

        $this->newLine();
        $this->info('Push completado');
        $this->line('  - URL: ' . ($result['url'] ?? 'N/A'));
        $this->line('  - Status: ' . (string) ($result['status'] ?? 'N/A'));
        $this->line('  - Márgenes enviados: ' . (string) ($result['sent_count'] ?? 0));
        if ($createdCount !== null) {
            $this->line('  - Márgenes creados según Pricing: ' . $createdCount);
        }
        if (is_array($body) && isset($body['message'])) {
            $this->line('  - Mensaje: ' . (string) $body['message']);
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFilters(): array
    {
        return array_filter([
            'date_from' => $this->option('date-from'),
            'date_to' => $this->option('date-to'),
            'vendor' => $this->option('vendor') !== null ? (int) $this->option('vendor') : null,
            'trading_company' => $this->option('trading-company'),
            'search' => $this->option('search'),
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
