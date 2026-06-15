<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PorthSyncBacklog;
use App\Models\PorthSyncFailure;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PorthPurchaseOrderReportService
{
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<int, string>>
     */
    public function rows(int $companyId, array $filters = []): array
    {
        $orders = $this->buildQuery($companyId, $filters)->get();
        $porthIds = $orders
            ->pluck('porth_id')
            ->filter()
            ->map(static fn ($id) => (string) $id)
            ->unique()
            ->values();

        $backlogErrors = $this->latestBacklogErrors($porthIds);
        $failureErrors = $this->latestFailureErrors($porthIds);

        return $orders
            ->map(fn (PurchaseOrder $po) => $this->mapRow($po, $backlogErrors, $failureErrors))
            ->all();
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildQuery(int $companyId, array $filters): Builder
    {
        $query = PurchaseOrder::query()
            ->withoutTrashed()
            ->where('company_id', $companyId)
            ->operationalForDashboard()
            ->orderBy('order_number');

        if (!empty($filters['currency'])) {
            $query->whereRaw('LOWER(currency) = LOWER(?)', [$filters['currency']]);
        }

        if (!empty($filters['incoterms'])) {
            $query->whereRaw('LOWER(incoterms) = LOWER(?)', [$filters['incoterms']]);
        }

        if (!empty($filters['planned_hub_id'])) {
            $query->where('planned_hub_id', $filters['planned_hub_id']);
        }

        if (!empty($filters['actual_hub_id'])) {
            $query->where('actual_hub_id', $filters['actual_hub_id']);
        }

        if (!empty($filters['material_type'])) {
            $materialType = (string) $filters['material_type'];
            $query->where(function (Builder $subQuery) use ($materialType) {
                foreach ([$materialType, strtolower($materialType), strtoupper($materialType), ucfirst(strtolower($materialType))] as $pattern) {
                    $subQuery->orWhereRaw('material_type::text LIKE ?', ['%' . $pattern . '%']);
                }
            });
        }

        if (!empty($filters['search_text'])) {
            $searchTerm = '%' . strtolower(trim((string) $filters['search_text'])) . '%';
            $query->where(function (Builder $subQuery) use ($searchTerm) {
                $subQuery->whereRaw('LOWER(order_number) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(porth_id) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(container_number) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(shipping_line) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(mbl_number) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(tracking_id) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(departure_port) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(arrival_port) LIKE ?', [$searchTerm]);
            });
        }

        return $query;
    }

    /**
     * @param Collection<int, string> $porthIds
     * @return Collection<string, string>
     */
    private function latestBacklogErrors(Collection $porthIds): Collection
    {
        if ($porthIds->isEmpty()) {
            return collect();
        }

        return PorthSyncBacklog::query()
            ->whereIn('porth_id', $porthIds)
            ->whereNotNull('last_error')
            ->orderByDesc('updated_at')
            ->get(['porth_id', 'last_error'])
            ->unique('porth_id')
            ->mapWithKeys(static fn (PorthSyncBacklog $item) => [
                (string) $item->porth_id => (string) $item->last_error,
            ]);
    }

    /**
     * @param Collection<int, string> $porthIds
     * @return Collection<string, string>
     */
    private function latestFailureErrors(Collection $porthIds): Collection
    {
        if ($porthIds->isEmpty()) {
            return collect();
        }

        return PorthSyncFailure::query()
            ->whereIn('porth_id', $porthIds)
            ->where(function (Builder $query) {
                $query->whereNotNull('last_retry_error')
                    ->orWhereNotNull('error_message');
            })
            ->orderByDesc('last_failed_at')
            ->orderByDesc('updated_at')
            ->get(['porth_id', 'last_retry_error', 'error_message'])
            ->unique('porth_id')
            ->mapWithKeys(static fn (PorthSyncFailure $failure) => [
                (string) $failure->porth_id => (string) ($failure->last_retry_error ?: $failure->error_message),
            ]);
    }

    /**
     * @param Collection<string, string> $backlogErrors
     * @param Collection<string, string> $failureErrors
     * @return array<int, string>
     */
    private function mapRow(PurchaseOrder $po, Collection $backlogErrors, Collection $failureErrors): array
    {
        $porthId = (string) ($po->porth_id ?? '');

        return [
            (string) ($po->order_number ?? ''),
            $porthId,
            (string) ($po->porth_phase ?? ''),
            $this->formatDateTime($po->last_porth_sync_at),
            $porthId !== '' ? (string) ($backlogErrors->get($porthId) ?: $failureErrors->get($porthId) ?: '') : '',
            (string) ($po->container_number ?? ''),
            (string) ($po->shipping_line ?? ''),
            $this->trackingReference($po),
            (string) ($po->departure_port ?? ''),
            (string) ($po->arrival_port ?? ''),
            $this->formatDateTime($po->date_atd),
            $this->formatDateTime($po->date_eta),
            $this->formatDateTime($po->date_ata),
            $this->formatDateTime($po->date_etd),
            $this->formatDateTime($po->date_eta_initial),
            $this->formatDateTime($po->date_etd_initial),
        ];
    }

    private function trackingReference(PurchaseOrder $po): string
    {
        foreach (['mbl_number', 'tracking_id'] as $field) {
            $value = trim((string) ($po->{$field} ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function formatDateTime(mixed $value): string
    {
        if (blank($value)) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
