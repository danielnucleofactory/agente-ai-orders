<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Carbon\Carbon;

class PorthTimelineService
{
    private const PHASES = [
        '10_ready' => [
            'label' => 'Listo para retiro',
            'icon' => 'warehouse',
            'date_field' => 'porth_ready',
        ],
        '20_to_origin_port' => [
            'label' => 'En transito a puerto origen',
            'icon' => 'truck',
            'date_field' => 'porth_to_origin_port',
        ],
        '30_at_origin_port' => [
            'label' => 'En puerto de origen',
            'icon' => 'port',
            'date_field' => 'porth_at_origin_port',
        ],
        '40_in_transit' => [
            'label' => 'En transito a puerto destino',
            'icon' => 'ship',
            'date_field' => 'porth_in_transit',
        ],
        '50_at_destination_port' => [
            'label' => 'En puerto de destino',
            'icon' => 'port',
            'date_field' => 'porth_at_destination_port',
        ],
        '60_to_final_destination' => [
            'label' => 'En transito a destino final',
            'icon' => 'truck',
            'date_field' => 'porth_to_final_destination',
        ],
        '70_delivered' => [
            'label' => 'Entregado',
            'icon' => 'check',
            'date_field' => 'porth_delivered',
        ],
    ];

    private const PHASE_ALIASES = [
        'ready' => '10_ready',
        '10_ready' => '10_ready',
        'to_origin_port' => '20_to_origin_port',
        '20_to_origin_port' => '20_to_origin_port',
        'at_origin_port' => '30_at_origin_port',
        '30_at_origin_port' => '30_at_origin_port',
        'in_transit' => '40_in_transit',
        '40_in_transit' => '40_in_transit',
        'at_destination_port' => '50_at_destination_port',
        '50_at_destination_port' => '50_at_destination_port',
        'to_final_destination' => '60_to_final_destination',
        '60_to_final_destination' => '60_to_final_destination',
        'delivered' => '70_delivered',
        '70_delivered' => '70_delivered',
    ];

    public function buildForPurchaseOrder(PurchaseOrder $purchaseOrder): ?array
    {
        $currentPhase = $this->normalizePhaseKey($purchaseOrder->porth_phase);
        $timeline = [];

        foreach (self::PHASES as $phaseKey => $phaseConfig) {
            $date = $this->resolvePhaseDate($purchaseOrder, $phaseKey);
            $timeline[] = [
                'id' => $phaseKey,
                'name' => $phaseConfig['label'],
                'icon' => $phaseConfig['icon'],
                'status' => $this->determinePhaseStatus($phaseKey, $currentPhase, $date),
                'date' => $date?->toIso8601String(),
                'is_current' => $phaseKey === $currentPhase,
                'is_completed' => $this->isPhaseCompleted($phaseKey, $currentPhase, $date),
            ];
        }

        if ($currentPhase === null && ! $this->timelineHasAtLeastOneDate($timeline)) {
            return null;
        }

        return [
            'timeline' => $timeline,
            'current_phase' => $this->phaseLabel($currentPhase),
            'current_phase_key' => $currentPhase,
            'estimated_delivery' => $this->resolveEstimatedDelivery($purchaseOrder)?->toIso8601String(),
            'cargo' => [],
            'pol' => $purchaseOrder->porth_pol_name ?? $purchaseOrder->departure_port ?? '',
            'pod' => $purchaseOrder->porth_pod_name ?? $purchaseOrder->arrival_port ?? '',
            'carrier' => $purchaseOrder->shipping_line ?? $purchaseOrder->porth_carrier_code ?? '',
        ];
    }

    private function resolveEstimatedDelivery(PurchaseOrder $purchaseOrder): ?Carbon
    {
        return $this->normalizeDateValue(
            $purchaseOrder->date_eta
            ?? $purchaseOrder->date_eta_initial
            ?? $purchaseOrder->porth_first_eta
        );
    }

    private function resolvePhaseDate(PurchaseOrder $purchaseOrder, string $phaseKey): ?Carbon
    {
        $dateField = self::PHASES[$phaseKey]['date_field'] ?? null;
        if ($dateField !== null) {
            $directDate = $this->normalizeDateValue($purchaseOrder->{$dateField} ?? null);
            if ($directDate !== null) {
                return $directDate;
            }
        }

        $itinerary = $purchaseOrder->porth_itinerary;
        if (! is_array($itinerary) || empty($itinerary)) {
            return null;
        }

        $dates = [];

        foreach ($itinerary as $event) {
            if (! is_array($event)) {
                continue;
            }

            $eventPhase = $this->normalizePhaseKey($event['phase'] ?? null);
            if ($eventPhase !== $phaseKey) {
                continue;
            }

            $eventDate = $this->normalizeDateValue($event['date'] ?? null);
            if ($eventDate !== null) {
                $dates[] = $eventDate;
            }
        }

        if (empty($dates)) {
            return null;
        }

        usort($dates, static fn (Carbon $a, Carbon $b): int => $a->getTimestamp() <=> $b->getTimestamp());

        return $dates[0];
    }

    private function determinePhaseStatus(string $phaseKey, ?string $currentPhase, ?Carbon $date): string
    {
        if ($date !== null) {
            return 'completed';
        }

        if ($phaseKey === $currentPhase) {
            return 'active';
        }

        if ($currentPhase !== null && $this->phaseOrder($phaseKey) < $this->phaseOrder($currentPhase)) {
            return 'completed';
        }

        return 'pending';
    }

    private function isPhaseCompleted(string $phaseKey, ?string $currentPhase, ?Carbon $date): bool
    {
        if ($date !== null) {
            return true;
        }

        return $currentPhase !== null && $this->phaseOrder($phaseKey) < $this->phaseOrder($currentPhase);
    }

    private function phaseOrder(string $phaseKey): int
    {
        return (int) substr($phaseKey, 0, 2);
    }

    private function phaseLabel(?string $phaseKey): ?string
    {
        if ($phaseKey === null) {
            return null;
        }

        return self::PHASES[$phaseKey]['label'] ?? $phaseKey;
    }

    private function normalizePhaseKey(?string $phase): ?string
    {
        if (! is_string($phase)) {
            return null;
        }

        $normalized = strtolower(trim($phase));

        return self::PHASE_ALIASES[$normalized] ?? null;
    }

    private function normalizeDateValue($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTimeImmutable::createFromInterface($value));
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function timelineHasAtLeastOneDate(array $timeline): bool
    {
        foreach ($timeline as $phase) {
            if (! empty($phase['date'])) {
                return true;
            }
        }

        return false;
    }
}
