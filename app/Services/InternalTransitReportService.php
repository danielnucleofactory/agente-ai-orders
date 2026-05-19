<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class InternalTransitReportService
{
    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function build(int $companyId, array $filters = []): array
    {
        $movements = $this->getMovements($companyId, $filters);

        return [
            'meta' => [
                'company_id' => $companyId,
                'ata_from' => $filters['date_from'] ?? null,
                'ata_to' => $filters['date_to'] ?? null,
                'vendor_id' => $filters['vendor'] ?? null,
                'trading_company' => $filters['trading_company'] ?? null,
                'search' => $filters['search'] ?? null,
                'movements_count' => $movements->count(),
            ],
            'base' => $this->baseRows($movements),
            'full' => $this->aggregate(
                $movements,
                ['departure_port', 'arrival_port', 'route', 'service_provider', 'shipping_line']
            ),
            'by_route' => $this->aggregate(
                $movements,
                ['departure_port', 'arrival_port', 'route']
            ),
            'by_service_provider' => $this->aggregate(
                $movements,
                ['service_provider']
            ),
            'by_shipping_line' => $this->aggregate(
                $movements,
                ['shipping_line']
            ),
        ];
    }

    /**
     * Payload técnico para el endpoint /api/margins de Pricing.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildPricingPayload(int $companyId, array $filters = []): array
    {
        $pricingUserId = config('services.pricing.user_id');
        $allMovements = $this->getMovements($companyId, $filters);
        $pricingMovements = $allMovements
            ->filter(static fn (array $row) => isset($row['freight_amount']) && (float) $row['freight_amount'] > 0)
            ->values();
        $rows = $this->aggregatePricingRows(
            $pricingMovements,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null,
            $pricingUserId !== null ? (int) $pricingUserId : null,
        );

        return [
            'meta' => [
                'source' => 'raga',
                'notes' => 'orders',
                'company_id' => $companyId,
                'ata_from' => $filters['date_from'] ?? null,
                'ata_to' => $filters['date_to'] ?? null,
                'generated_at' => now()->toISOString(),
                'movements_count' => $allMovements->count(),
                'pricing_movements_count' => $pricingMovements->count(),
                'groups_count' => count($rows),
            ],
            'margins' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function getMovements(int $companyId, array $filters): Collection
    {
        return $this->buildBaseQuery($companyId, $filters)
            ->get()
            ->map(fn (PurchaseOrder $po) => $this->mapMovement($po))
            ->filter()
            ->unique('movement_key')
            ->values();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildBaseQuery(int $companyId, array $filters): Builder
    {
        $query = PurchaseOrder::query()
            ->withoutTrashed()
            ->with('vendor:id,name')
            ->where('company_id', $companyId)
            ->whereNotNull('container_number')
            ->whereNotNull('date_ata')
            ->whereNotNull('date_atd');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date_ata', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date_ata', '<=', $filters['date_to']);
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor_id', $filters['vendor']);
        }

        if (!empty($filters['trading_company'])) {
            $query->where('trading_company', $filters['trading_company']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . strtolower(trim((string) $filters['search'])) . '%';
            $query->where(function (Builder $subQuery) use ($searchTerm) {
                $subQuery->whereRaw('LOWER(order_number) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(container_number) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(service_provider) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(shipping_line) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(departure_port) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(arrival_port) LIKE ?', [$searchTerm])
                    ->orWhereHas('vendor', function (Builder $vendorQuery) use ($searchTerm) {
                        $vendorQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                    });
            });
        }

        return $query;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapMovement(PurchaseOrder $po): ?array
    {
        $containerNumber = strtoupper(trim((string) $po->container_number));

        if ($containerNumber === '') {
            return null;
        }

        $departurePort = $this->cleanLabel($po->departure_port, 'Sin puerto de embarque');
        $arrivalPort = $this->cleanLabel($po->arrival_port, 'Sin puerto de arribo');
        $serviceProvider = $this->cleanLabel($po->service_provider, 'Sin proveedor de servicio');
        $shippingLine = $this->cleanLabel($po->shipping_line, 'Sin naviera');
        $containerType = $this->cleanLabel($po->container_type, 'Sin tipo');
        $containerBucket = $this->normalizeContainerBucket($containerType);

        if ($containerBucket === null) {
            return null;
        }

        $atd = Carbon::parse($po->date_atd)->startOfDay();
        $ata = Carbon::parse($po->date_ata)->startOfDay();

        if ($ata->lt($atd)) {
            return null;
        }

        $transitDays = $atd->diffInDays($ata);

        return [
            'movement_key' => implode('|', [
                $containerNumber,
                $atd->format('Y-m-d'),
                $ata->format('Y-m-d'),
                $departurePort,
                $arrivalPort,
                $serviceProvider,
                $shippingLine,
            ]),
            'order_number' => (string) ($po->order_number ?? ''),
            'vendor' => $this->cleanLabel($po->vendor?->name, 'Sin proveedor'),
            'trading_company' => $this->cleanLabel($po->trading_company, 'Sin empresa'),
            'container_number' => $containerNumber,
            'container_type' => $containerType,
            'container_bucket' => $containerBucket,
            'departure_port' => $departurePort,
            'arrival_port' => $arrivalPort,
            'route' => $departurePort . ' -> ' . $arrivalPort,
            'service_provider' => $serviceProvider,
            'shipping_line' => $shippingLine,
            'atd' => $atd->format('Y-m-d'),
            'ata' => $ata->format('Y-m-d'),
            'transit_days' => $transitDays,
            'freight_amount' => $po->freight_amount !== null ? (float) $po->freight_amount : null,
        ];
    }

    private function cleanLabel(mixed $value, string $fallback): string
    {
        $label = trim((string) ($value ?? ''));

        return $label !== '' ? $label : $fallback;
    }

    private function normalizeContainerBucket(string $containerType): ?string
    {
        $normalized = strtolower(trim($containerType));

        if ($normalized === '' || str_contains($normalized, 'lcl') || str_contains($normalized, '53')) {
            return null;
        }

        if (str_contains($normalized, '20')) {
            return '20';
        }

        if (str_contains($normalized, '40') || str_contains($normalized, '45')) {
            return '40';
        }

        return null;
    }

    /**
     * @param Collection<int, array<string, mixed>> $movements
     * @param array<int, string> $dimensions
     * @return array<int, array<int, string|int|float>>
     */
    private function aggregate(Collection $movements, array $dimensions): array
    {
        $grouped = $movements
            ->groupBy(function (array $row) use ($dimensions) {
                return implode("\x1F", array_map(
                    static fn (string $dimension) => (string) ($row[$dimension] ?? ''),
                    $dimensions
                ));
            })
            ->map(function (Collection $group) use ($dimensions) {
                /** @var array<string, mixed> $first */
                $first = $group->first();
                $base = [];

                foreach ($dimensions as $dimension) {
                    $base[] = (string) ($first[$dimension] ?? '');
                }

                $group20 = $group->where('container_bucket', '20');
                $group40 = $group->where('container_bucket', '40');
                $count20 = $group20->count();
                $count40 = $group40->count();
                $total = $group->count();
                $average = round((float) $group->avg('transit_days'), 1);

                $freights20 = $group20->pluck('freight_amount')->filter(fn ($v) => $v !== null);
                $freights40 = $group40->pluck('freight_amount')->filter(fn ($v) => $v !== null);
                $avgFreight20 = $freights20->isNotEmpty() ? round((float) $freights20->avg(), 2) : null;
                $avgFreight40 = $freights40->isNotEmpty() ? round((float) $freights40->avg(), 2) : null;

                return array_merge($base, [
                    $count20,
                    $count40,
                    $total,
                    $average,
                    $avgFreight20,
                    $avgFreight40,
                ]);
            })
            ->sortBy(function (array $row) use ($dimensions) {
                return implode('|', array_slice($row, 0, count($dimensions)));
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return $grouped->all();
    }

    /**
     * @param Collection<int, array<string, mixed>> $movements
     * @return array<int, array<int, string|int>>
     */
    private function baseRows(Collection $movements): array
    {
        return $movements
            ->sortBy([
                ['ata', 'asc'],
                ['departure_port', 'asc'],
                ['arrival_port', 'asc'],
                ['container_number', 'asc'],
            ])
            ->values()
            ->map(static fn (array $row) => [
                $row['ata'],
                $row['atd'],
                $row['transit_days'],
                $row['departure_port'],
                $row['arrival_port'],
                $row['route'],
                $row['service_provider'],
                $row['shipping_line'],
                $row['container_type'],
                $row['container_number'],
                $row['vendor'],
                $row['trading_company'],
                $row['order_number'],
            ])
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $movements
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    private function aggregatePricingRows(
        Collection $movements,
        ?string $effectiveDate = null,
        ?string $expireDate = null,
        ?int $pricingUserId = null
    ): array
    {
        return $movements
            ->groupBy(function (array $row) {
                return implode("\x1F", [
                    (string) ($row['departure_port'] ?? ''),
                    (string) ($row['arrival_port'] ?? ''),
                    (string) ($row['shipping_line'] ?? ''),
                    (string) ($row['service_provider'] ?? ''),
                    (string) ($row['container_bucket'] ?? ''),
                ]);
            })
            ->map(function (Collection $group) use ($effectiveDate, $expireDate, $pricingUserId) {
                /** @var array<string, mixed> $first */
                $first = $group->first();
                $bucket = (string) ($first['container_bucket'] ?? '');
                $freightTotal = round((float) $group->sum('freight_amount'), 2);

                return [
                    'origin_port' => (string) ($first['departure_port'] ?? ''),
                    'destination_port' => (string) ($first['arrival_port'] ?? ''),
                    'carrier' => (string) ($first['shipping_line'] ?? ''),
                    'supplier' => (string) ($first['service_provider'] ?? ''),
                    'container_qty' => $bucket === '20' ? $group->count() : 0,
                    'container_qty_40' => $bucket === '40' ? $group->count() : 0,
                    'transit_time_days' => (int) round((float) $group->avg('transit_days')),
                    '20std' => $bucket === '20' && $freightTotal > 0 ? $freightTotal : null,
                    '40std' => $bucket === '40' && $freightTotal > 0 ? $freightTotal : null,
                    'effective_date' => $effectiveDate,
                    'expire_date' => $expireDate,
                    'source' => 'raga',
                    'notes' => 'orders',
                    'status' => true,
                    'user_id' => $pricingUserId,
                ];
            })
            ->sortBy(function (array $row) {
                return implode('|', [
                    $row['origin_port'] ?? '',
                    $row['destination_port'] ?? '',
                    $row['carrier'] ?? '',
                    $row['supplier'] ?? '',
                    (($row['container_qty'] ?? 0) > 0) ? '20' : '40',
                ]);
            }, SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
