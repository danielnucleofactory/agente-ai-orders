<?php

namespace App\Livewire\Dashboards;

use App\Models\PurchaseOrder;
use App\Services\TransitTimeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ControlDashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    private const PRODUCTION_STAGE_ID = 2;

    private const BOOKING_STAGE_ID = 3;

    private const TRANSIT_STAGE_ID = 5;

    private const PORT_STAGE_ID = 6;

    public array $expandedStageNames = ['Producción'];

    public array $expandedRuleKeys = [];

    protected array $ruleIdsCache = [];

    protected array $ruleDetailsCache = [];

    protected ?array $stageTotalsCache = null;

    public function getRulesCatalogProperty(): Collection
    {
        return collect([
            [
                'key' => 'production_missing_required_fields',
                'stage' => 'Producción',
                'title' => 'PO con información incompleta',
                'rule' => 'La PO debe tener información en estos campos: Proveedor mercancía, Carga lista teórica, Carga lista variable',
                'validates' => 'Que la PO fue creada correctamente desde Intelix',
                'broken_when' => 'Cuando no hay dato en esos campos',
                'origin' => 'Seguimiento',
                'responsible' => 'Intelix',
                'status' => 'active',
            ],
            [
                'key' => 'production_booking_management_delay',
                'stage' => 'Producción',
                'title' => 'PO con fecha vencida',
                'rule' => 'La PO sigue en producción después de carga lista validada + 7 días',
                'validates' => 'Se hizo la gestión de booking',
                'broken_when' => 'Cuando la fecha es carga lista validada + 8 días y la PO sigue en Producción',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'production_missing_incoterms',
                'stage' => 'Producción',
                'title' => 'PO con información incompleta',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'PO sin incoterm compra, precio, logistico',
                'origin' => 'Seguimiento',
                'responsible' => 'Intelix',
                'status' => 'active',
            ],
            [
                'key' => 'booking_missing_authorization_date',
                'stage' => 'Booking',
                'title' => 'PO con información incompleta',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'Cuando Fecha autorización no tiene datos',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'booking_missing_container_and_carrier',
                'stage' => 'Booking',
                'title' => 'PO con información incompleta',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'Si estamos a 4 días de la fecha indicada en ETD inicial, no tenemos contenedor y naviera',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'booking_stage_automation',
                'stage' => 'Booking',
                'title' => 'No cambió automáticamente a En tránsito',
                'rule' => 'Cambio automático de etapa',
                'validates' => 'Que la automatización de cambio de etapa funciona',
                'broken_when' => 'Si la PO tiene ATD y sigue en booking',
                'origin' => 'Raga',
                'responsible' => 'Raga',
                'status' => 'active',
            ],
            [
                'key' => 'transit_stage_automation',
                'stage' => 'En tránsito',
                'title' => 'No cambió automáticamente a Puerto',
                'rule' => 'Cambio automático de etapa',
                'validates' => 'Que la automatización de cambio de etapa funciona',
                'broken_when' => 'Si la PO tiene ATA y sigue en tránsito',
                'origin' => 'Raga',
                'responsible' => 'Raga',
                'status' => 'active',
            ],
            [
                'key' => 'transit_missing_tracking_fields',
                'stage' => 'En tránsito',
                'title' => 'Tracking incompleto en tránsito',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'Cualquiera de estos campos está vacío: Contenedor, Naviera, Doc de tránsito, Proveedor de servicio, Monto Felte. Check tarifa está en verdadero',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'transit_missing_dates',
                'stage' => 'En tránsito',
                'title' => 'Fechas de tránsito incompletas',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'Cualquiera de estos campos está vacío: ETD inicial, ETD variable, ATD, ETA inicial, ETA variable',
                'origin' => 'Porth',
                'responsible' => 'Tracking',
                'status' => 'active',
            ],
            [
                'key' => 'port_missing_ata',
                'stage' => 'Puerto',
                'title' => 'Falta ATA',
                'rule' => 'Hay campos sin datos',
                'validates' => 'Que toda la información requerida para estar en esta etapa está en la PO',
                'broken_when' => 'Si la PO no tiene ATA',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'port_stage_delay',
                'stage' => 'Puerto',
                'title' => 'PO excedió permanencia en Puerto',
                'rule' => 'Cambio de etapa',
                'validates' => 'Que la PO ya avanzó a la siguiente etapa en plazo',
                'broken_when' => 'Cuando una PO sigue en puerto después de los días indicados por región como límite para seguir en puerto',
                'origin' => 'Usuario',
                'responsible' => 'OLO',
                'status' => 'active',
            ],
            [
                'key' => 'general_date_consistency',
                'stage' => 'General',
                'title' => 'Consistencia de fechas',
                'rule' => 'Consistencia de fechas',
                'validates' => 'Que las fechas asociadas al transporte sean consistentes',
                'broken_when' => 'Ninguna fecha de ETD inicial, ETD variable, ATD, ETA inicial, ETA variable, ATA, puede ser menor a la fecha de carga lista variable.',
                'origin' => 'Porth',
                'responsible' => 'Tracking',
                'status' => 'active',
            ],
        ]);
    }

    public function toggleRule(string $ruleKey): void
    {
        if (in_array($ruleKey, $this->expandedRuleKeys, true)) {
            $this->expandedRuleKeys = array_values(array_filter(
                $this->expandedRuleKeys,
                fn ($key) => $key !== $ruleKey
            ));

            return;
        }

        $this->expandedRuleKeys[] = $ruleKey;
    }

    public function toggleStage(string $stageName): void
    {
        if (in_array($stageName, $this->expandedStageNames, true)) {
            $this->expandedStageNames = array_values(array_filter(
                $this->expandedStageNames,
                fn ($name) => $name !== $stageName
            ));

            return;
        }

        $this->expandedStageNames[] = $stageName;
    }

    public function getSummaryRowsProperty(): Collection
    {
        return $this->rulesCatalog
            ->groupBy('stage')
            ->map(function (Collection $rules, string $stageName) {
                $rawRules = $rules->map(function (array $rule) {
                    $poIds = $this->ruleIds($rule['key']);
                    $isExpanded = in_array('group_' . md5($rule['key']), $this->expandedRuleKeys, true)
                        || in_array($rule['key'], $this->expandedRuleKeys, true);

                    return array_merge($rule, [
                        'po_ids' => $poIds,
                        'po_count' => $poIds->count(),
                        'details' => $isExpanded ? $this->ruleDetails($rule['key']) : null,
                        'is_expanded' => in_array($rule['key'], $this->expandedRuleKeys, true),
                    ]);
                })->values();

                $totalPos = $this->totalPurchaseOrdersForStage($stageName);
                $errorPos = $rawRules
                    ->pluck('po_ids')
                    ->flatMap(fn (Collection $ids) => $ids)
                    ->unique()
                    ->count();

                $rulesWithCounts = $rawRules
                    ->groupBy(fn (array $rule) => $rule['title'] . '|' . $rule['responsible'] . '|' . $rule['origin'])
                    ->map(function (Collection $group) use ($totalPos) {
                        $first = $group->first();
                        $groupKey = 'group_' . md5($group->pluck('key')->sort()->implode('|'));
                        $isExpanded = in_array($groupKey, $this->expandedRuleKeys, true);
                        $poIds = $group
                            ->pluck('po_ids')
                            ->flatMap(fn (Collection $ids) => $ids)
                            ->unique()
                            ->values();
                        $poCount = $poIds->count();

                        $detailRows = $isExpanded
                            ? $group
                                ->pluck('key')
                                ->flatMap(fn (string $key) => $this->ruleDetails($key))
                                ->groupBy('id')
                                ->map(function (Collection $poRows) {
                                    $firstRow = $poRows->first();
                                    $combinedProblemItems = $poRows
                                        ->pluck('problem_items')
                                        ->filter(fn ($items) => is_array($items) && ! empty($items))
                                        ->flatMap(fn (array $items) => $items)
                                        ->map(fn (string $item) => trim($item))
                                        ->filter()
                                        ->unique()
                                        ->values();

                                    $combinedProblemData = $combinedProblemItems->isNotEmpty()
                                        ? $this->formatMissingItems($combinedProblemItems->all())
                                        : $poRows
                                            ->pluck('problem_data')
                                            ->filter()
                                            ->unique()
                                            ->implode("\n");

                                    return array_merge($firstRow, [
                                        'problem_data' => $combinedProblemData,
                                        'problem_items' => $combinedProblemItems->all(),
                                        'allowed_days' => $firstRow['allowed_days'] ?? $firstRow['expected_port_days'] ?? null,
                                        'current_days' => $firstRow['current_days'] ?? $firstRow['current_port_days'] ?? null,
                                        'delay_days' => $firstRow['delay_days'] ?? null,
                                    ]);
                                })
                                ->values()
                            : collect();

                        return [
                            'key' => $groupKey,
                            'title' => $first['title'],
                            'stage' => $first['stage'],
                            'origin' => $first['origin'],
                            'responsible' => $first['responsible'],
                            'rule_keys' => $group->pluck('key')->values()->all(),
                            'po_ids' => $poIds,
                            'po_count' => $poCount,
                            'details' => $isExpanded ? $detailRows : null,
                            'is_expanded' => $isExpanded,
                            'percentage' => $totalPos > 0
                                ? round(($poCount / $totalPos) * 100, 1)
                                : null,
                        ];
                    })
                    ->values();

                return [
                    'stage' => $stageName,
                    'total_pos' => $totalPos,
                    'error_pos' => $errorPos,
                    'percentage' => $totalPos > 0 ? round(($errorPos / $totalPos) * 100, 1) : 0,
                    'is_expanded' => in_array($stageName, $this->expandedStageNames, true),
                    'rules' => $rulesWithCounts,
                ];
            })
            ->values();
    }

    public function getResponsibleSummaryRowsProperty(): Collection
    {
        $rows = [];

        foreach ($this->summaryRows as $stageRow) {
            foreach ($stageRow['rules'] as $ruleRow) {
                if (! ($ruleRow['details'] instanceof Collection) || ! is_numeric($ruleRow['po_count'])) {
                    $poIds = collect($ruleRow['po_ids'] ?? []);
                } else {
                    $poIds = collect($ruleRow['po_ids'] ?? $ruleRow['details']->pluck('id')->all());
                }

                $responsible = $ruleRow['responsible'];
                if (! isset($rows[$responsible])) {
                    $rows[$responsible] = [
                        'responsible' => $responsible,
                        'po_ids' => [],
                    ];
                }

                $rows[$responsible]['po_ids'] = array_values(array_unique(array_merge(
                    $rows[$responsible]['po_ids'],
                    $poIds->all()
                )));
            }
        }

        $countsByResponsible = collect($rows)
            ->map(fn (array $row) => count($row['po_ids']));

        $displayedTotalPoCount = $countsByResponsible->sum();

        return collect($rows)
            ->map(function (array $row) use ($displayedTotalPoCount) {
                $poCount = count($row['po_ids']);

                return [
                    'responsible' => $row['responsible'],
                    'po_count' => $poCount,
                    'percentage' => $displayedTotalPoCount > 0
                        ? round(($poCount / $displayedTotalPoCount) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('po_count')
            ->values();
    }

    public function getResponsibleSummaryTotalsProperty(): array
    {
        $totalPoCount = $this->responsibleSummaryRows->sum('po_count');

        return [
            'po_count' => $totalPoCount,
            'percentage' => $totalPoCount > 0 ? 100 : 0,
        ];
    }

    public function getStageSummaryTotalsProperty(): array
    {
        $errorPos = $this->summaryRows->sum('error_pos');
        $totalPos = $this->summaryRows->sum('total_pos');

        return [
            'error_pos' => $errorPos,
            'total_pos' => $totalPos,
            'percentage' => $totalPos > 0
                ? round(($errorPos / $totalPos) * 100, 1)
                : 0,
        ];
    }

    public function detailRows(string $ruleKey): Collection
    {
        foreach ($this->summaryRows as $stageRow) {
            foreach ($stageRow['rules'] as $ruleRow) {
                if ($ruleRow['key'] === $ruleKey) {
                    return $ruleRow['details'] instanceof Collection ? $ruleRow['details'] : collect();
                }
            }
        }

        $details = $this->ruleDetails($ruleKey);
        return $details instanceof Collection ? $details : collect();
    }

    public function shouldShowTimingColumns(string $ruleKey): bool
    {
        $rows = $this->detailRows($ruleKey);

        return $rows->contains(function (array $row) {
            return ! is_null($row['current_days'] ?? null)
                || ! is_null($row['allowed_days'] ?? null);
        });
    }

    public function missingFields(PurchaseOrder $purchaseOrder): array
    {
        $missing = [];

        if (blank($purchaseOrder->vendor_id)) {
            $missing[] = 'Proveedor mercancía';
        }

        if (blank($purchaseOrder->date_theorical_load)) {
            $missing[] = 'Carga lista teórica';
        }

        if (blank($purchaseOrder->date_variable_date)) {
            $missing[] = 'Carga lista variable';
        }

        return $missing;
    }

    private function formatMissingItems(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $lines = collect(array_values($items))
            ->map(fn (string $item, int $index) => ($index + 1) . '. ' . $item)
            ->implode("\n");

        return "Datos faltantes:\n" . $lines;
    }

    private function overdueDaysFromDate(Carbon|string|null $date, int $allowedDays = 0): ?int
    {
        if (blank($date)) {
            return null;
        }

        $baseDate = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date)->startOfDay();

        $delayDays = $baseDate->diffInDays(Carbon::today()->startOfDay(), false) - $allowedDays;

        return max(0, $delayDays);
    }

    private function ruleDetails(string $ruleKey): ?Collection
    {
        if (array_key_exists($ruleKey, $this->ruleDetailsCache)) {
            return $this->ruleDetailsCache[$ruleKey];
        }

        $details = match ($ruleKey) {
            'production_missing_required_fields' => $this->productionMissingRequiredFieldsDetails(),
            'booking_missing_authorization_date' => $this->bookingMissingAuthorizationDateDetails(),
            'transit_missing_tracking_fields' => $this->transitMissingTrackingFieldsDetails(),
            'transit_missing_dates' => $this->transitMissingDatesDetails(),
            'production_booking_management_delay' => $this->productionBookingManagementDelayDetails(),
            'production_missing_incoterms' => $this->productionMissingIncotermsDetails(),
            'booking_missing_container_and_carrier' => $this->bookingMissingContainerAndCarrierDetails(),
            'booking_stage_automation' => $this->bookingStageAutomationDetails(),
            'transit_stage_automation' => $this->transitStageAutomationDetails(),
            'port_missing_ata' => $this->portMissingAtaDetails(),
            'port_stage_delay' => $this->portStageDelayDetails(),
            'general_date_consistency' => $this->generalDateConsistencyDetails(),
            default => null,
        };

        return $this->ruleDetailsCache[$ruleKey] = $details;
    }

    private function ruleIds(string $ruleKey): Collection
    {
        if (array_key_exists($ruleKey, $this->ruleIdsCache)) {
            return $this->ruleIdsCache[$ruleKey];
        }

        $ids = match ($ruleKey) {
            'production_missing_required_fields' => PurchaseOrder::query()
                ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
                ->where(function ($q) {
                    $q->whereNull('vendor_id')
                        ->orWhereNull('date_theorical_load')
                        ->orWhereNull('date_variable_date');
                })
                ->pluck('id'),
            'booking_missing_authorization_date' => PurchaseOrder::query()
                ->where('kanban_status_id', self::BOOKING_STAGE_ID)
                ->whereNull('date_booking_authorized')
                ->pluck('id'),
            'production_booking_management_delay' => PurchaseOrder::query()
                ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
                ->where('carga_lista_validada', true)
                ->whereNotNull('date_variable_date')
                ->whereDate('date_variable_date', '<=', Carbon::today()->subDays(8))
                ->pluck('id'),
            'production_missing_incoterms' => PurchaseOrder::query()
                ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
                ->where(function ($q) {
                    $q->whereNull('incoterms')
                        ->orWhere('incoterms', '')
                        ->orWhereNull('price_incoterm')
                        ->orWhere('price_incoterm', '')
                        ->orWhereNull('logistics_incoterm')
                        ->orWhere('logistics_incoterm', '');
                })
                ->pluck('id'),
            'booking_missing_container_and_carrier' => PurchaseOrder::query()
                ->where('kanban_status_id', self::BOOKING_STAGE_ID)
                ->whereNotNull('date_etd_initial')
                ->whereBetween('date_etd_initial', [Carbon::today(), Carbon::today()->addDays(4)])
                ->where(function ($q) {
                    $q->whereNull('container_number')
                        ->orWhere('container_number', '')
                        ->orWhereNull('shipping_line')
                        ->orWhere('shipping_line', '');
                })
                ->pluck('id'),
            'booking_stage_automation' => PurchaseOrder::query()
                ->where('kanban_status_id', self::BOOKING_STAGE_ID)
                ->whereNotNull('date_atd')
                ->pluck('id'),
            'transit_missing_tracking_fields' => PurchaseOrder::query()
                ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
                ->where(function ($q) {
                    $q->whereNull('container_number')
                        ->orWhere('container_number', '')
                        ->orWhereNull('shipping_line')
                        ->orWhere('shipping_line', '')
                        ->orWhereNull('mbl_number')
                        ->orWhere('mbl_number', '')
                        ->orWhereNull('service_provider')
                        ->orWhere('service_provider', '')
                        ->orWhere(function ($freightQuery) {
                            $freightQuery->where('used_rate_ok', true)
                                ->where(function ($inner) {
                                    $inner->whereNull('freight_amount')
                                        ->orWhere('freight_amount', 0);
                                });
                        });
                })
                ->pluck('id'),
            'transit_missing_dates' => PurchaseOrder::query()
                ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
                ->where(function ($q) {
                    $q->whereNull('date_etd_initial')
                        ->orWhereNull('date_etd')
                        ->orWhereNull('date_atd')
                        ->orWhereNull('date_eta_initial')
                        ->orWhereNull('date_eta');
                })
                ->pluck('id'),
            'transit_stage_automation' => PurchaseOrder::query()
                ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
                ->whereNotNull('date_ata')
                ->pluck('id'),
            'port_missing_ata' => PurchaseOrder::query()
                ->where('kanban_status_id', self::PORT_STAGE_ID)
                ->whereNull('date_ata')
                ->pluck('id'),
            'port_stage_delay' => $this->portStageDelayIds(),
            'general_date_consistency' => $this->generalDateConsistencyIds(),
            default => collect(),
        };

        return $this->ruleIdsCache[$ruleKey] = collect($ids)->values();
    }

    private function totalPurchaseOrdersForStage(string $stageName): int
    {
        $totals = $this->stageTotals();

        $stageId = match ($stageName) {
            'Producción' => self::PRODUCTION_STAGE_ID,
            'Booking' => self::BOOKING_STAGE_ID,
            'En tránsito' => self::TRANSIT_STAGE_ID,
            'Puerto' => self::PORT_STAGE_ID,
            'General' => 'general',
            default => null,
        };

        if ($stageId === null) {
            return 0;
        }

        if ($stageId === 'general') {
            return $totals['all'];
        }

        return $totals[$stageId] ?? 0;
    }

    private function stageTotals(): array
    {
        if ($this->stageTotalsCache !== null) {
            return $this->stageTotalsCache;
        }

        $grouped = PurchaseOrder::query()
            ->selectRaw('kanban_status_id, COUNT(*) as aggregate')
            ->groupBy('kanban_status_id')
            ->pluck('aggregate', 'kanban_status_id')
            ->map(fn ($count) => (int) $count)
            ->all();

        $grouped['all'] = array_sum($grouped);

        return $this->stageTotalsCache = $grouped;
    }

    private function portStageDelayIds(): Collection
    {
        $transitTimeService = app(TransitTimeService::class);

        return PurchaseOrder::query()
            ->select(['id', 'date_ata', 'porth_pol', 'porth_pod', 'departure_port', 'arrival_port'])
            ->where('kanban_status_id', self::PORT_STAGE_ID)
            ->whereNotNull('date_ata')
            ->get()
            ->filter(function (PurchaseOrder $purchaseOrder) use ($transitTimeService) {
                $departureRef = ! empty($purchaseOrder->porth_pol) ? $purchaseOrder->porth_pol : $purchaseOrder->departure_port;
                $arrivalRef = ! empty($purchaseOrder->porth_pod) ? $purchaseOrder->porth_pod : $purchaseOrder->arrival_port;

                $portDays = $transitTimeService->getPortDaysForPorts($departureRef, $arrivalRef);
                if ($portDays === null) {
                    return false;
                }

                $ataDate = $purchaseOrder->date_ata instanceof Carbon
                    ? $purchaseOrder->date_ata->copy()
                    : Carbon::parse($purchaseOrder->date_ata);

                return $ataDate->copy()->addDays($portDays)->startOfDay()->lt(Carbon::today()->startOfDay());
            })
            ->pluck('id')
            ->values();
    }

    private function generalDateConsistencyIds(): Collection
    {
        $dateFields = [
            'date_etd_initial',
            'date_etd',
            'date_atd',
            'date_eta_initial',
            'date_eta',
            'date_ata',
        ];

        return PurchaseOrder::query()
            ->select(array_merge(['id', 'date_variable_date'], $dateFields, ['kanban_status_id']))
            ->whereNotNull('date_variable_date')
            ->get()
            ->filter(function (PurchaseOrder $purchaseOrder) use ($dateFields) {
                $baseDate = $purchaseOrder->date_variable_date instanceof Carbon
                    ? $purchaseOrder->date_variable_date->copy()->startOfDay()
                    : Carbon::parse($purchaseOrder->date_variable_date)->startOfDay();

                foreach ($dateFields as $field) {
                    $value = $purchaseOrder->{$field};
                    if (blank($value)) {
                        continue;
                    }

                    $comparisonDate = $value instanceof Carbon
                        ? $value->copy()->startOfDay()
                        : Carbon::parse($value)->startOfDay();

                    if ($comparisonDate->lt($baseDate)) {
                        return true;
                    }
                }

                return false;
            })
            ->pluck('id')
            ->values();
    }

    private function productionMissingRequiredFieldsDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
            ->where(function ($q) {
                $q->whereNull('vendor_id')
                    ->orWhereNull('date_theorical_load')
                    ->orWhereNull('date_variable_date');
            })
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Producción',
                    'responsible' => 'Intelix',
                    'problem_items' => $this->missingFields($purchaseOrder),
                    'problem_data' => $this->formatMissingItems($this->missingFields($purchaseOrder)),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function bookingMissingAuthorizationDateDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::BOOKING_STAGE_ID)
            ->whereNull('date_booking_authorized')
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Booking',
                    'responsible' => 'OLO',
                    'problem_items' => ['Fecha autorización'],
                    'problem_data' => $this->formatMissingItems(['Fecha autorización']),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function productionBookingManagementDelayDetails(): Collection
    {
        $cutoff = Carbon::today()->subDays(8);

        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
            ->where('carga_lista_validada', true)
            ->whereNotNull('date_variable_date')
            ->whereDate('date_variable_date', '<=', $cutoff)
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $validatedDate = optional($purchaseOrder->date_variable_date)?->format('d/m/Y');
                $limitDate = optional($purchaseOrder->date_variable_date)?->copy()->addDays(8)->format('d/m/Y');
                $delayDays = $this->overdueDaysFromDate($purchaseOrder->date_variable_date, 8);
                $currentDays = blank($purchaseOrder->date_variable_date)
                    ? null
                    : Carbon::parse($purchaseOrder->date_variable_date)->startOfDay()->diffInDays(Carbon::today()->startOfDay());

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Producción',
                    'responsible' => 'OLO',
                    'problem_data' => 'Carga lista validada: ' . ($validatedDate ?? 'Sin fecha') . ' | Venció: ' . ($limitDate ?? 'Sin fecha'),
                    'current_days' => $currentDays,
                    'allowed_days' => 8,
                    'delay_days' => $delayDays,
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function productionMissingIncotermsDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::PRODUCTION_STAGE_ID)
            ->where(function ($q) {
                $q->whereNull('incoterms')
                    ->orWhere('incoterms', '')
                    ->orWhereNull('price_incoterm')
                    ->orWhere('price_incoterm', '')
                    ->orWhereNull('logistics_incoterm')
                    ->orWhere('logistics_incoterm', '');
            })
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $missing = [];

                if (blank($purchaseOrder->incoterms)) {
                    $missing[] = 'Incoterm compra';
                }

                if (blank($purchaseOrder->price_incoterm)) {
                    $missing[] = 'Incoterm precio';
                }

                if (blank($purchaseOrder->logistics_incoterm)) {
                    $missing[] = 'Incoterm logístico';
                }

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Producción',
                    'responsible' => 'Intelix',
                    'problem_items' => $missing,
                    'problem_data' => $this->formatMissingItems($missing),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function bookingMissingContainerAndCarrierDetails(): Collection
    {
        $cutoffStart = Carbon::today();
        $cutoffEnd = Carbon::today()->addDays(4);

        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::BOOKING_STAGE_ID)
            ->whereNotNull('date_etd_initial')
            ->whereBetween('date_etd_initial', [$cutoffStart, $cutoffEnd])
            ->where(function ($q) {
                $q->whereNull('container_number')
                    ->orWhere('container_number', '')
                    ->orWhereNull('shipping_line')
                    ->orWhere('shipping_line', '');
            })
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $missing = [];

                if (blank($purchaseOrder->container_number)) {
                    $missing[] = 'Contenedor';
                }

                if (blank($purchaseOrder->shipping_line)) {
                    $missing[] = 'Naviera';
                }

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Booking',
                    'responsible' => 'OLO',
                    'problem_items' => $missing,
                    'problem_data' => 'ETD inicial: ' . (optional($purchaseOrder->date_etd_initial)?->format('d/m/Y') ?? 'Sin fecha') . "\n" . $this->formatMissingItems($missing),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function bookingStageAutomationDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::BOOKING_STAGE_ID)
            ->whereNotNull('date_atd')
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $delayDays = $this->overdueDaysFromDate($purchaseOrder->date_atd);
                $currentDays = blank($purchaseOrder->date_atd)
                    ? null
                    : Carbon::parse($purchaseOrder->date_atd)->startOfDay()->diffInDays(Carbon::today()->startOfDay());

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Booking',
                    'responsible' => 'Raga',
                    'problem_data' => 'ATD: ' . (optional($purchaseOrder->date_atd)?->format('d/m/Y') ?? 'Sin fecha'),
                    'current_days' => $currentDays,
                    'allowed_days' => 0,
                    'delay_days' => $delayDays,
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function transitMissingTrackingFieldsDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
            ->where(function ($q) {
                $q->whereNull('container_number')
                    ->orWhere('container_number', '')
                    ->orWhereNull('shipping_line')
                    ->orWhere('shipping_line', '')
                    ->orWhereNull('mbl_number')
                    ->orWhere('mbl_number', '')
                    ->orWhereNull('service_provider')
                    ->orWhere('service_provider', '')
                    ->orWhere(function ($freightQuery) {
                        $freightQuery->where('used_rate_ok', true)
                            ->where(function ($inner) {
                                $inner->whereNull('freight_amount')
                                    ->orWhere('freight_amount', 0);
                            });
                    });
            })
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $missing = [];

                if (blank($purchaseOrder->container_number)) {
                    $missing[] = 'Contenedor';
                }

                if (blank($purchaseOrder->shipping_line)) {
                    $missing[] = 'Naviera';
                }

                if (blank($purchaseOrder->mbl_number)) {
                    $missing[] = 'Doc de tránsito';
                }

                if (blank($purchaseOrder->service_provider)) {
                    $missing[] = 'Proveedor de servicio';
                }

                if ((bool) $purchaseOrder->used_rate_ok && (blank($purchaseOrder->freight_amount) || (float) $purchaseOrder->freight_amount === 0.0)) {
                    $missing[] = 'Monto flete';
                }

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'En tránsito',
                    'responsible' => 'OLO',
                    'problem_items' => $missing,
                    'problem_data' => $this->formatMissingItems($missing),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function transitMissingDatesDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
            ->where(function ($q) {
                $q->whereNull('date_etd_initial')
                    ->orWhereNull('date_etd')
                    ->orWhereNull('date_atd')
                    ->orWhereNull('date_eta_initial')
                    ->orWhereNull('date_eta');
            })
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $missing = [];

                if (blank($purchaseOrder->date_etd_initial)) {
                    $missing[] = 'ETD inicial';
                }

                if (blank($purchaseOrder->date_etd)) {
                    $missing[] = 'ETD variable';
                }

                if (blank($purchaseOrder->date_atd)) {
                    $missing[] = 'ATD';
                }

                if (blank($purchaseOrder->date_eta_initial)) {
                    $missing[] = 'ETA inicial';
                }

                if (blank($purchaseOrder->date_eta)) {
                    $missing[] = 'ETA variable';
                }

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'En tránsito',
                    'responsible' => 'Tracking',
                    'problem_items' => $missing,
                    'problem_data' => $this->formatMissingItems($missing),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function transitStageAutomationDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::TRANSIT_STAGE_ID)
            ->whereNotNull('date_ata')
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                $delayDays = $this->overdueDaysFromDate($purchaseOrder->date_ata);
                $currentDays = blank($purchaseOrder->date_ata)
                    ? null
                    : Carbon::parse($purchaseOrder->date_ata)->startOfDay()->diffInDays(Carbon::today()->startOfDay());

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'En tránsito',
                    'responsible' => 'Raga',
                    'problem_data' => 'ATA: ' . (optional($purchaseOrder->date_ata)?->format('d/m/Y') ?? 'Sin fecha'),
                    'current_days' => $currentDays,
                    'allowed_days' => 0,
                    'delay_days' => $delayDays,
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function portMissingAtaDetails(): Collection
    {
        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::PORT_STAGE_ID)
            ->whereNull('date_ata')
            ->orderBy('order_number')
            ->get()
            ->map(function (PurchaseOrder $purchaseOrder) {
                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Puerto',
                    'responsible' => 'OLO',
                    'problem_items' => ['ATA'],
                    'problem_data' => $this->formatMissingItems(['ATA']),
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            });
    }

    private function portStageDelayDetails(): Collection
    {
        $transitTimeService = app(TransitTimeService::class);

        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->where('kanban_status_id', self::PORT_STAGE_ID)
            ->whereNotNull('date_ata')
            ->orderBy('order_number')
            ->get()
            ->filter(function (PurchaseOrder $purchaseOrder) use ($transitTimeService) {
                $departureRef = ! empty($purchaseOrder->porth_pol) ? $purchaseOrder->porth_pol : $purchaseOrder->departure_port;
                $arrivalRef = ! empty($purchaseOrder->porth_pod) ? $purchaseOrder->porth_pod : $purchaseOrder->arrival_port;

                $portDays = $transitTimeService->getPortDaysForPorts($departureRef, $arrivalRef);
                if ($portDays === null) {
                    return false;
                }

                $ataDate = $purchaseOrder->date_ata instanceof Carbon
                    ? $purchaseOrder->date_ata->copy()
                    : Carbon::parse($purchaseOrder->date_ata);

                return $ataDate->copy()->addDays($portDays)->startOfDay()->lt(Carbon::today()->startOfDay());
            })
            ->map(function (PurchaseOrder $purchaseOrder) use ($transitTimeService) {
                $departureRef = ! empty($purchaseOrder->porth_pol) ? $purchaseOrder->porth_pol : $purchaseOrder->departure_port;
                $arrivalRef = ! empty($purchaseOrder->porth_pod) ? $purchaseOrder->porth_pod : $purchaseOrder->arrival_port;
                $portDays = $transitTimeService->getPortDaysForPorts($departureRef, $arrivalRef);
                $ataDate = $purchaseOrder->date_ata instanceof Carbon
                    ? $purchaseOrder->date_ata->copy()->startOfDay()
                    : Carbon::parse($purchaseOrder->date_ata)->startOfDay();
                $limitDate = $ataDate->copy()->addDays((int) $portDays);
                $currentPortDays = $ataDate->diffInDays(Carbon::today()->startOfDay());
                $delayDays = max(0, $currentPortDays - (int) $portDays);

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Puerto',
                    'responsible' => 'OLO',
                    'problem_data' => 'ATA: ' . $ataDate->format('d/m/Y') . ' | Límite puerto: ' . $limitDate->format('d/m/Y'),
                    'allowed_days' => (int) $portDays,
                    'current_days' => $currentPortDays,
                    'delay_days' => $delayDays,
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            })
            ->values();
    }

    private function generalDateConsistencyDetails(): Collection
    {
        $dateFields = [
            'date_etd_initial' => 'ETD inicial',
            'date_etd' => 'ETD variable',
            'date_atd' => 'ATD',
            'date_eta_initial' => 'ETA inicial',
            'date_eta' => 'ETA variable',
            'date_ata' => 'ATA',
        ];

        return PurchaseOrder::query()
            ->with(['vendor:id,name,vendo_code', 'kanbanStatus:id,name'])
            ->whereNotNull('date_variable_date')
            ->orderBy('order_number')
            ->get()
            ->filter(function (PurchaseOrder $purchaseOrder) use ($dateFields) {
                $baseDate = $purchaseOrder->date_variable_date instanceof Carbon
                    ? $purchaseOrder->date_variable_date->copy()->startOfDay()
                    : Carbon::parse($purchaseOrder->date_variable_date)->startOfDay();

                foreach (array_keys($dateFields) as $field) {
                    $value = $purchaseOrder->{$field};
                    if (blank($value)) {
                        continue;
                    }

                    $comparisonDate = $value instanceof Carbon
                        ? $value->copy()->startOfDay()
                        : Carbon::parse($value)->startOfDay();

                    if ($comparisonDate->lt($baseDate)) {
                        return true;
                    }
                }

                return false;
            })
            ->map(function (PurchaseOrder $purchaseOrder) use ($dateFields) {
                $baseDate = $purchaseOrder->date_variable_date instanceof Carbon
                    ? $purchaseOrder->date_variable_date->copy()->startOfDay()
                    : Carbon::parse($purchaseOrder->date_variable_date)->startOfDay();

                $invalidFields = [];
                $maxDelayDays = 0;
                foreach ($dateFields as $field => $label) {
                    $value = $purchaseOrder->{$field};
                    if (blank($value)) {
                        continue;
                    }

                    $comparisonDate = $value instanceof Carbon
                        ? $value->copy()->startOfDay()
                        : Carbon::parse($value)->startOfDay();

                    if ($comparisonDate->lt($baseDate)) {
                        $daysEarly = $comparisonDate->diffInDays($baseDate);
                        $maxDelayDays = max($maxDelayDays, $daysEarly);
                        $invalidFields[] = $label . ': ' . $comparisonDate->format('d/m/Y');
                    }
                }

                return [
                    'id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'stage' => $purchaseOrder->kanbanStatus->name ?? 'Sin etapa',
                    'responsible' => 'Tracking',
                    'problem_data' => 'Carga lista variable: ' . $baseDate->format('d/m/Y') . ' | Fechas inválidas: ' . implode(', ', $invalidFields),
                    'delay_days' => $maxDelayDays > 0 ? $maxDelayDays : null,
                    'vendor' => $purchaseOrder->vendor?->name,
                ];
            })
            ->values();
    }

    public function render()
    {
        return view('livewire.dashboards.control-dashboard', [
            'summaryRows' => $this->summaryRows,
            'stageSummaryTotals' => $this->stageSummaryTotals,
            'responsibleSummaryRows' => $this->responsibleSummaryRows,
            'responsibleSummaryTotals' => $this->responsibleSummaryTotals,
        ]);
    }
}
