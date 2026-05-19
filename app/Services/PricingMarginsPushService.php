<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PricingMarginsPushService
{
    public function __construct(
        private readonly InternalTransitReportService $transitReportService
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildPayload(int $companyId, array $filters = []): array
    {
        return $this->transitReportService->buildPricingPayload($companyId, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function push(int $companyId, array $filters = [], ?string $overrideUrl = null): array
    {
        $payload = $this->buildPayload($companyId, $filters);
        $margins = $payload['margins'] ?? [];
        $url = $overrideUrl ?: $this->resolveUrl();

        if (! is_array($margins)) {
            throw new RuntimeException('El payload de Pricing no contiene un arreglo válido en "margins".');
        }

        if (empty($margins)) {
            return [
                'url' => $url,
                'payload' => $payload,
                'status' => null,
                'body' => null,
                'sent_count' => 0,
            ];
        }

        $request = Http::acceptJson()
            ->asJson()
            ->timeout((int) config('services.pricing.timeout', 60));

        $token = trim((string) config('services.pricing.api_token', ''));
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->post($url, [
            'margins' => $margins,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(sprintf(
                'Pricing respondió %d: %s',
                $response->status(),
                $response->body()
            ));
        }

        return [
            'url' => $url,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->json(),
            'sent_count' => count($margins),
        ];
    }

    private function resolveUrl(): string
    {
        $baseUrl = rtrim((string) config('services.pricing.base_url', ''), '/');
        $endpoint = '/' . ltrim((string) config('services.pricing.margins_endpoint', '/api/margins'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('services.pricing.base_url no está configurado.');
        }

        return $baseUrl . $endpoint;
    }
}
