<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\KanbanStatus;
use App\Models\Company;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardKPIService
{
    protected TransitTimeService $transitTimeService;

    public function __construct(TransitTimeService $transitTimeService)
    {
        $this->transitTimeService = $transitTimeService;
    }

    /**
     * Calcula TEUs basado en el tipo de contenedor
     * 20" = 1, 40" = 2, LCL = 0.25
     */
    public function calculateTEUs(?string $containerType): float
    {
        if (empty($containerType)) {
            return 0;
        }

        $type = strtoupper(trim($containerType));

        // Contenedor 40 pies
        if (str_contains($type, '40') || str_contains($type, "40'") || str_contains($type, '40"')) {
            return 2.0;
        }

        // Contenedor 20 pies
        if (str_contains($type, '20') || str_contains($type, "20'") || str_contains($type, '20"')) {
            return 1.0;
        }

        // LCL (Less than Container Load)
        if (str_contains($type, 'LCL')) {
            return 0.25;
        }

        // Por defecto, si no se reconoce, asumir 1 TEU
        return 1.0;
    }

    /**
     * Obtiene el mapeo de etapas desde KanbanStatus
     */
    public function getStageMapping(?int $companyId = null): array
    {
        $query = KanbanStatus::query();

        if ($companyId) {
            $query->whereHas('board', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->where(function ($subQ) {
                        $subQ->where('type', 'po_stages')
                            ->orWhere('type', 'purchase_orders');
                    });
            });
        } else {
            $query->where('kanban_board_id', 1);
        }

        return $query->pluck('name', 'id')->toArray();
    }

    /**
     * Mapea el nombre de etapa a una categoría estándar
     */
    public function mapStageToCategory(string $stageName): ?string
    {
        $stageNameLower = strtolower($stageName);

        if (str_contains($stageNameLower, 'producción') || str_contains($stageNameLower, 'produccion')) {
            return 'Producción';
        }
        if (str_contains($stageNameLower, 'booking')) {
            return 'Booking';
        }
        if (str_contains($stageNameLower, 'tránsito') || str_contains($stageNameLower, 'transito')) {
            return 'Tránsito';
        }
        if (str_contains($stageNameLower, 'transbordo')) {
            return 'Transbordo';
        }
        if (str_contains($stageNameLower, 'puerto') || str_contains($stageNameLower, 'arribo')) {
            return 'Arribo';
        }
        if (str_contains($stageNameLower, 'ingresada')) {
            return 'Ingresada';
        }
        if (str_contains($stageNameLower, 'anulada')) {
            return 'Anulada';
        }

        return null;
    }

    /**
     * Obtiene la query base con filtros aplicados
     */
    protected function getBaseQuery(array $filters = [])
    {
        $query = PurchaseOrder::query();

        // Filtro por compañía del usuario
        $companyId = auth()->user()->company_id ?? null;
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        Log::info('DashboardKPIService::getBaseQuery', [
            'filters' => $filters,
            'company_id' => $companyId,
        ]);

        // Filtros de fecha (usando created_at como fecha de período)
        // Solo aplicar si se proporcionan explícitamente
        if (!empty($filters['date_from']) && $filters['date_from'] !== 'null') {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to']) && $filters['date_to'] !== 'null') {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Filtro por proveedor de mercancía
        if (!empty($filters['vendor_id'])) {
            $vendorIds = is_array($filters['vendor_id']) ? $filters['vendor_id'] : [$filters['vendor_id']];
            $vendorIds = array_filter($vendorIds);
            if (!empty($vendorIds)) {
                $query->whereIn('vendor_id', $vendorIds);
            }
        }

        // Filtro por naviera
        if (!empty($filters['shipping_line'])) {
            $shippingLines = is_array($filters['shipping_line']) ? $filters['shipping_line'] : [$filters['shipping_line']];
            $shippingLines = array_filter($shippingLines);
            if (!empty($shippingLines)) {
                $query->whereIn('shipping_line', $shippingLines);
            }
        }

        // Filtro por proveedor de servicios
        if (!empty($filters['service_provider'])) {
            $providers = is_array($filters['service_provider']) ? $filters['service_provider'] : [$filters['service_provider']];
            $providers = array_filter($providers);
            if (!empty($providers)) {
                $query->where(function ($q) use ($providers) {
                    $q->whereIn('service_provider', $providers)
                        ->orWhereIn('forwarder_name', $providers);
                });
            }
        }

        // Filtro por puerto de embarque
        if (!empty($filters['departure_port'])) {
            $ports = is_array($filters['departure_port']) ? $filters['departure_port'] : [$filters['departure_port']];
            $ports = array_filter($ports);
            if (!empty($ports)) {
                $query->whereIn('departure_port', $ports);
            }
        }

        // Filtro por puerto de destino
        if (!empty($filters['arrival_port'])) {
            $ports = is_array($filters['arrival_port']) ? $filters['arrival_port'] : [$filters['arrival_port']];
            $ports = array_filter($ports);
            if (!empty($ports)) {
                $query->whereIn('arrival_port', $ports);
            }
        }

        // Filtro por ruta logística
        if (!empty($filters['route_label'])) {
            $routes = is_array($filters['route_label']) ? $filters['route_label'] : [$filters['route_label']];
            $routes = array_filter($routes);
            if (!empty($routes)) {
                $query->whereIn('route_label', $routes);
            }
        }

        // Filtro por etapa (kanban_status_id o nombre)
        if (!empty($filters['stage'])) {
            $stages = is_array($filters['stage']) ? $filters['stage'] : [$filters['stage']];
            $stages = array_filter($stages);
            if (!empty($stages)) {
                // Si es numérico, asumir que es kanban_status_id
                if (is_numeric($stages[0])) {
                    $query->whereIn('kanban_status_id', $stages);
                } else {
                    // Si es texto, buscar por nombre
                    $query->whereHas('kanbanStatus', function ($q) use ($stages) {
                        $q->whereIn('name', $stages);
                    });
                }
            }
        }

        // Filtro por cliente (trading_company)
        if (!empty($filters['trading_company'])) {
            $tradingCompanies = is_array($filters['trading_company']) 
                ? $filters['trading_company'] 
                : [$filters['trading_company']];
            $tradingCompanies = array_filter($tradingCompanies);
            if (!empty($tradingCompanies)) {
                $query->whereIn('trading_company', $tradingCompanies);
            }
        }

        // Filtro por número de orden
        if (!empty($filters['order_number'])) {
            $query->where('order_number', 'like', '%' . $filters['order_number'] . '%');
        }

        return $query;
    }

    // ============================================
    // VISTA TENDENCIA - Cantidad de PO
    // ============================================

    /**
     * POs por Etapa
     * COUNT(PO_ID) agrupado por etapa
     */
    public function getPOsByStage(array $filters = []): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;
            $stageMapping = $this->getStageMapping($companyId);

            $query = $this->getBaseQuery($filters)
                ->with('kanbanStatus')
                ->get();

            Log::info('getPOsByStage - Query result', [
                'count' => $query->count(),
                'filters' => $filters,
            ]);

            $stages = [
                'Producción' => ['count' => 0, 'teus' => 0],
                'Booking' => ['count' => 0, 'teus' => 0],
                'Tránsito' => ['count' => 0, 'teus' => 0],
                'Transbordo' => ['count' => 0, 'teus' => 0],
                'Arribo' => ['count' => 0, 'teus' => 0],
            ];

            $totalPOs = 0;
            $totalTEUs = 0;

            foreach ($query as $po) {
                if ($po->kanban_status_id && isset($stageMapping[$po->kanban_status_id])) {
                    $stageName = $stageMapping[$po->kanban_status_id];
                    $category = $this->mapStageToCategory($stageName);

                    if ($category && isset($stages[$category])) {
                        $stages[$category]['count']++;
                        $teus = $this->calculateTEUs($po->container_type);
                        $stages[$category]['teus'] += $teus;
                        $totalPOs++;
                        $totalTEUs += $teus;
                    }
                }
            }

            // Calcular porcentajes
            $result = [];
            foreach ($stages as $stageName => $data) {
                $result[] = [
                    'stage' => $stageName,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'percentage' => $totalPOs > 0 ? round(($data['count'] / $totalPOs) * 100, 1) : 0,
                    'teus_percentage' => $totalTEUs > 0 ? round(($data['teus'] / $totalTEUs) * 100, 1) : 0,
                ];
            }

            return [
                'data' => $result,
                'total_pos' => $totalPOs,
                'total_teus' => round($totalTEUs, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOsByStage', ['error' => $e->getMessage()]);
            return ['data' => [], 'total_pos' => 0, 'total_teus' => 0];
        }
    }

    /**
     * POs con retraso según Carga Lista (CL)
     * WHERE date_variable_date > date_theorical_load
     */
    public function getPOsWithDelayCL(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date > date_theorical_load')
                ->with(['vendor', 'kanbanStatus'])
                ->get();

            $totalPOs = $query->count();
            $totalTEUs = 0;
            $totalDelayDays = 0;
            $maxDelay = 0;

            $byVendor = [];
            $details = [];

            foreach ($query as $po) {
                $teus = $this->calculateTEUs($po->container_type);
                $totalTEUs += $teus;

                $delayDays = Carbon::parse($po->date_theorical_load)->diffInDays(Carbon::parse($po->date_variable_date));
                $totalDelayDays += $delayDays;
                $maxDelay = max($maxDelay, $delayDays);

                // Agrupar por proveedor
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['count' => 0, 'teus' => 0, 'delay_days' => 0];
                }
                $byVendor[$vendorName]['count']++;
                $byVendor[$vendorName]['teus'] += $teus;
                $byVendor[$vendorName]['delay_days'] += $delayDays;

                // Detalles para subtabla
                $details[] = [
                    'order_number' => $po->order_number,
                    'vendor' => $vendorName,
                    'delay_days' => $delayDays,
                    'stage' => $po->kanbanStatus->name ?? 'N/A',
                    'teus' => round($teus, 2),
                ];
            }

            // Formatear por proveedor
            $vendorData = [];
            foreach ($byVendor as $vendor => $data) {
                $vendorData[] = [
                    'vendor' => $vendor,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'avg_delay_days' => $data['count'] > 0 ? round($data['delay_days'] / $data['count'], 1) : 0,
                ];
            }

            return [
                'summary' => [
                    'total_pos' => $totalPOs,
                    'total_teus' => round($totalTEUs, 2),
                    'avg_delay_days' => $totalPOs > 0 ? round($totalDelayDays / $totalPOs, 1) : 0,
                    'max_delay_days' => $maxDelay,
                ],
                'by_vendor' => $vendorData,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOsWithDelayCL', ['error' => $e->getMessage()]);
            return ['summary' => [], 'by_vendor' => [], 'details' => []];
        }
    }

    /**
     * POs con adelanto según Carga Lista (CL)
     * WHERE date_variable_date < date_theorical_load
     */
    public function getPOsWithAdvanceCL(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date < date_theorical_load')
                ->with(['vendor', 'kanbanStatus'])
                ->get();

            $totalPOs = $query->count();
            $totalTEUs = 0;
            $totalAdvanceDays = 0;
            $maxAdvance = 0;

            $byVendor = [];
            $details = [];

            foreach ($query as $po) {
                $teus = $this->calculateTEUs($po->container_type);
                $totalTEUs += $teus;

                $advanceDays = Carbon::parse($po->date_variable_date)->diffInDays(Carbon::parse($po->date_theorical_load));
                $totalAdvanceDays += $advanceDays;
                $maxAdvance = max($maxAdvance, $advanceDays);

                // Agrupar por proveedor
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['count' => 0, 'teus' => 0, 'advance_days' => 0];
                }
                $byVendor[$vendorName]['count']++;
                $byVendor[$vendorName]['teus'] += $teus;
                $byVendor[$vendorName]['advance_days'] += $advanceDays;

                // Detalles para subtabla
                $details[] = [
                    'order_number' => $po->order_number,
                    'vendor' => $vendorName,
                    'advance_days' => $advanceDays,
                    'stage' => $po->kanbanStatus->name ?? 'N/A',
                    'teus' => round($teus, 2),
                ];
            }

            // Formatear por proveedor
            $vendorData = [];
            foreach ($byVendor as $vendor => $data) {
                $vendorData[] = [
                    'vendor' => $vendor,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'avg_advance_days' => $data['count'] > 0 ? round($data['advance_days'] / $data['count'], 1) : 0,
                ];
            }

            return [
                'summary' => [
                    'total_pos' => $totalPOs,
                    'total_teus' => round($totalTEUs, 2),
                    'avg_advance_days' => $totalPOs > 0 ? round($totalAdvanceDays / $totalPOs, 1) : 0,
                    'max_advance_days' => $maxAdvance,
                ],
                'by_vendor' => $vendorData,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOsWithAdvanceCL', ['error' => $e->getMessage()]);
            return ['summary' => [], 'by_vendor' => [], 'details' => []];
        }
    }

    /**
     * Capacidad (Allocation) - POs con ETD Real
     * WHERE date_atd IS NOT NULL
     */
    public function getCapacityAllocation(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_atd')
                ->with(['vendor', 'kanbanStatus'])
                ->get();

            $totalPOs = $query->count();
            $totalTEUs = 0;
            $totalTransitDays = 0;
            $posWithTransit = 0;

            $byVendor = [];
            $byServiceProvider = [];
            $details = [];

            foreach ($query as $po) {
                $teus = $this->calculateTEUs($po->container_type);
                $totalTEUs += $teus;

                // Calcular días de tránsito (ATD a ATA)
                $transitDays = 0;
                if ($po->date_atd && $po->date_ata) {
                    $transitDays = Carbon::parse($po->date_atd)->diffInDays(Carbon::parse($po->date_ata));
                    $totalTransitDays += $transitDays;
                    $posWithTransit++;
                }

                // Agrupar por proveedor de mercancía
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['count' => 0, 'teus' => 0, 'transit_days' => 0, 'transit_count' => 0];
                }
                $byVendor[$vendorName]['count']++;
                $byVendor[$vendorName]['teus'] += $teus;
                if ($transitDays > 0) {
                    $byVendor[$vendorName]['transit_days'] += $transitDays;
                    $byVendor[$vendorName]['transit_count']++;
                }

                // Agrupar por proveedor de servicios
                $serviceProvider = $po->service_provider ?? $po->forwarder_name ?? 'Sin Proveedor';
                if (!isset($byServiceProvider[$serviceProvider])) {
                    $byServiceProvider[$serviceProvider] = ['count' => 0, 'teus' => 0];
                }
                $byServiceProvider[$serviceProvider]['count']++;
                $byServiceProvider[$serviceProvider]['teus'] += $teus;

                // Detalles para subtabla
                $details[] = [
                    'order_number' => $po->order_number,
                    'shipping_line' => $po->shipping_line ?? 'N/A',
                    'transit_days' => $transitDays,
                    'teus' => round($teus, 2),
                ];
            }

            // Formatear por proveedor
            $vendorData = [];
            foreach ($byVendor as $vendor => $data) {
                $vendorData[] = [
                    'vendor' => $vendor,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'percentage' => $totalPOs > 0 ? round(($data['count'] / $totalPOs) * 100, 1) : 0,
                    'avg_transit_days' => $data['transit_count'] > 0 ? round($data['transit_days'] / $data['transit_count'], 1) : 0,
                ];
            }

            $serviceProviderData = [];
            foreach ($byServiceProvider as $provider => $data) {
                $serviceProviderData[] = [
                    'service_provider' => $provider,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'percentage' => $totalPOs > 0 ? round(($data['count'] / $totalPOs) * 100, 1) : 0,
                ];
            }

            return [
                'summary' => [
                    'total_pos' => $totalPOs,
                    'total_teus' => round($totalTEUs, 2),
                    'avg_transit_days' => $posWithTransit > 0 ? round($totalTransitDays / $posWithTransit, 1) : 0,
                ],
                'by_vendor' => $vendorData,
                'by_service_provider' => $serviceProviderData,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getCapacityAllocation', ['error' => $e->getMessage()]);
            return ['summary' => [], 'by_vendor' => [], 'by_service_provider' => [], 'details' => []];
        }
    }

    /**
     * POs en Puerto de Transbordo
     * Por ahora retorna vacío - pendiente fix integración Porth
     */
    public function getPOsInTransshipment(array $filters = []): array
    {
        // Por ahora retornar estructura vacía - pendiente integración Porth
        return [
            'summary' => [
                'total_pos' => 0,
                'total_teus' => 0,
            ],
            'by_port' => [],
            'details' => [],
        ];
    }

    /**
     * POs con ATA (Puerto de destino)
     * WHERE date_ata IS NOT NULL
     */
    public function getPOsWithATA(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_ata')
                ->with(['vendor', 'company'])
                ->get();

            $totalPOs = $query->count();
            $totalTEUs = 0;

            $byClient = [];
            $byRoute = [];
            $details = [];

            foreach ($query as $po) {
                $teus = $this->calculateTEUs($po->container_type);
                $totalTEUs += $teus;

                // Agrupar por cliente (company)
                $clientName = $po->company->name ?? 'Sin Cliente';
                if (!isset($byClient[$clientName])) {
                    $byClient[$clientName] = ['count' => 0, 'teus' => 0];
                }
                $byClient[$clientName]['count']++;
                $byClient[$clientName]['teus'] += $teus;

                // Agrupar por ruta logística
                $routeLabel = $po->route_label ?? 'Sin Ruta';
                if (!isset($byRoute[$routeLabel])) {
                    $byRoute[$routeLabel] = ['count' => 0, 'teus' => 0];
                }
                $byRoute[$routeLabel]['count']++;
                $byRoute[$routeLabel]['teus'] += $teus;

                // Detalles para subtabla
                $details[] = [
                    'order_number' => $po->order_number,
                    'vendor' => $po->vendor->name ?? 'N/A',
                    'shipping_line' => $po->shipping_line ?? 'N/A',
                    'arrival_port' => $po->arrival_port ?? 'N/A',
                    'date_ata' => $po->date_ata ? Carbon::parse($po->date_ata)->format('Y-m-d') : 'N/A',
                    'teus' => round($teus, 2),
                ];
            }

            // Formatear por cliente
            $clientData = [];
            foreach ($byClient as $client => $data) {
                $clientData[] = [
                    'client' => $client,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                ];
            }

            // Formatear por ruta
            $routeData = [];
            foreach ($byRoute as $route => $data) {
                $routeData[] = [
                    'route' => $route,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                ];
            }

            return [
                'summary' => [
                    'total_pos' => $totalPOs,
                    'total_teus' => round($totalTEUs, 2),
                ],
                'by_client' => $clientData,
                'by_route' => $routeData,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOsWithATA', ['error' => $e->getMessage()]);
            return ['summary' => [], 'by_client' => [], 'by_route' => [], 'details' => []];
        }
    }

    /**
     * Tiempo de tránsito (ATA vs ATD)
     */
    public function getTransitTime(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_atd')
                ->whereNotNull('date_ata')
                ->with(['vendor', 'company'])
                ->get();

            $totalPOs = $query->count();
            $totalTEUs = 0;
            $totalTransitDays = 0;

            // Agrupación por rangos de días
            $timeRanges = [
                '0-10' => ['count' => 0, 'teus' => 0],
                '11-20' => ['count' => 0, 'teus' => 0],
                '21-30' => ['count' => 0, 'teus' => 0],
                '31-40' => ['count' => 0, 'teus' => 0],
                '41-50' => ['count' => 0, 'teus' => 0],
                '51-60' => ['count' => 0, 'teus' => 0],
                '61+' => ['count' => 0, 'teus' => 0],
            ];

            $byClient = [];
            $byRoute = [];
            $details = [];

            foreach ($query as $po) {
                $teus = $this->calculateTEUs($po->container_type);
                $totalTEUs += $teus;

                $transitDays = Carbon::parse($po->date_atd)->diffInDays(Carbon::parse($po->date_ata));
                $totalTransitDays += $transitDays;

                // Clasificar por rango
                $range = $this->getTimeRange($transitDays);
                if (isset($timeRanges[$range])) {
                    $timeRanges[$range]['count']++;
                    $timeRanges[$range]['teus'] += $teus;
                }

                // Agrupar por cliente
                $clientName = $po->company->name ?? 'Sin Cliente';
                if (!isset($byClient[$clientName])) {
                    $byClient[$clientName] = ['count' => 0, 'teus' => 0, 'transit_days' => 0];
                }
                $byClient[$clientName]['count']++;
                $byClient[$clientName]['teus'] += $teus;
                $byClient[$clientName]['transit_days'] += $transitDays;

                // Agrupar por ruta
                $routeLabel = $po->route_label ?? 'Sin Ruta';
                if (!isset($byRoute[$routeLabel])) {
                    $byRoute[$routeLabel] = ['count' => 0, 'teus' => 0, 'transit_days' => 0];
                }
                $byRoute[$routeLabel]['count']++;
                $byRoute[$routeLabel]['teus'] += $teus;
                $byRoute[$routeLabel]['transit_days'] += $transitDays;

                // Detalles
                $details[] = [
                    'order_number' => $po->order_number,
                    'client' => $clientName,
                    'route' => $routeLabel,
                    'transit_days' => $transitDays,
                    'teus' => round($teus, 2),
                ];
            }

            // Formatear rangos
            $rangeData = [];
            foreach ($timeRanges as $range => $data) {
                $rangeData[] = [
                    'range' => $range . ' días',
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                ];
            }

            // Formatear por cliente
            $clientData = [];
            foreach ($byClient as $client => $data) {
                $clientData[] = [
                    'client' => $client,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'avg_transit_days' => $data['count'] > 0 ? round($data['transit_days'] / $data['count'], 1) : 0,
                ];
            }

            // Formatear por ruta
            $routeData = [];
            foreach ($byRoute as $route => $data) {
                $routeData[] = [
                    'route' => $route,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                    'avg_transit_days' => $data['count'] > 0 ? round($data['transit_days'] / $data['count'], 1) : 0,
                ];
            }

            return [
                'summary' => [
                    'total_pos' => $totalPOs,
                    'total_teus' => round($totalTEUs, 2),
                    'avg_transit_days' => $totalPOs > 0 ? round($totalTransitDays / $totalPOs, 1) : 0,
                ],
                'by_range' => $rangeData,
                'by_client' => $clientData,
                'by_route' => $routeData,
                'details' => $details,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getTransitTime', ['error' => $e->getMessage()]);
            return ['summary' => [], 'by_range' => [], 'by_client' => [], 'by_route' => [], 'details' => []];
        }
    }

    /**
     * Helper para clasificar días de tránsito en rangos
     */
    protected function getTimeRange(int $days): string
    {
        if ($days <= 10) return '0-10';
        if ($days <= 20) return '11-20';
        if ($days <= 30) return '21-30';
        if ($days <= 40) return '31-40';
        if ($days <= 50) return '41-50';
        if ($days <= 60) return '51-60';
        return '61+';
    }

    // ============================================
    // VISTA COMPARATIVO
    // ============================================

    /**
     * Comparativo de POs con ATD entre dos períodos
     */
    public function comparePOsWithATD(array $filters, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;

            // Período 1
            $query1 = PurchaseOrder::query()
                ->whereNotNull('date_atd')
                ->whereBetween('date_atd', [$period1Start, $period1End]);
            if ($companyId) {
                $query1->where('company_id', $companyId);
            }
            $period1Data = $query1->with('vendor')->get();

            // Período 2
            $query2 = PurchaseOrder::query()
                ->whereNotNull('date_atd')
                ->whereBetween('date_atd', [$period2Start, $period2End]);
            if ($companyId) {
                $query2->where('company_id', $companyId);
            }
            $period2Data = $query2->with('vendor')->get();

            // Agrupar por proveedor
            $byVendor = [];

            foreach ($period1Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period1']++;
                $byVendor[$vendorName]['teus1'] += $this->calculateTEUs($po->container_type);
            }

            foreach ($period2Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period2']++;
                $byVendor[$vendorName]['teus2'] += $this->calculateTEUs($po->container_type);
            }

            // Calcular variaciones
            $result = [];
            foreach ($byVendor as $vendor => $data) {
                $variation = 0;
                if ($data['period1'] > 0) {
                    $variation = round((($data['period2'] - $data['period1']) / $data['period1']) * 100, 1);
                } elseif ($data['period2'] > 0) {
                    $variation = 100;
                }

                $result[] = [
                    'vendor' => $vendor,
                    'period1_count' => $data['period1'],
                    'period2_count' => $data['period2'],
                    'period1_teus' => round($data['teus1'], 2),
                    'period2_teus' => round($data['teus2'], 2),
                    'variation_percentage' => $variation,
                ];
            }

            return [
                'period1' => ['start' => $period1Start, 'end' => $period1End, 'total' => $period1Data->count()],
                'period2' => ['start' => $period2Start, 'end' => $period2End, 'total' => $period2Data->count()],
                'by_vendor' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error in comparePOsWithATD', ['error' => $e->getMessage()]);
            return ['period1' => [], 'period2' => [], 'by_vendor' => []];
        }
    }

    /**
     * Comparativo de POs con ATA entre dos períodos
     */
    public function comparePOsWithATA(array $filters, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;

            // Período 1
            $query1 = PurchaseOrder::query()
                ->whereNotNull('date_ata')
                ->whereBetween('date_ata', [$period1Start, $period1End]);
            if ($companyId) {
                $query1->where('company_id', $companyId);
            }
            $period1Data = $query1->with('vendor')->get();

            // Período 2
            $query2 = PurchaseOrder::query()
                ->whereNotNull('date_ata')
                ->whereBetween('date_ata', [$period2Start, $period2End]);
            if ($companyId) {
                $query2->where('company_id', $companyId);
            }
            $period2Data = $query2->with('vendor')->get();

            // Agrupar por proveedor
            $byVendor = [];

            foreach ($period1Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period1']++;
                $byVendor[$vendorName]['teus1'] += $this->calculateTEUs($po->container_type);
            }

            foreach ($period2Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period2']++;
                $byVendor[$vendorName]['teus2'] += $this->calculateTEUs($po->container_type);
            }

            // Calcular variaciones
            $result = [];
            foreach ($byVendor as $vendor => $data) {
                $variation = 0;
                if ($data['period1'] > 0) {
                    $variation = round((($data['period2'] - $data['period1']) / $data['period1']) * 100, 1);
                } elseif ($data['period2'] > 0) {
                    $variation = 100;
                }

                $result[] = [
                    'vendor' => $vendor,
                    'period1_count' => $data['period1'],
                    'period2_count' => $data['period2'],
                    'period1_teus' => round($data['teus1'], 2),
                    'period2_teus' => round($data['teus2'], 2),
                    'variation_percentage' => $variation,
                ];
            }

            return [
                'period1' => ['start' => $period1Start, 'end' => $period1End, 'total' => $period1Data->count()],
                'period2' => ['start' => $period2Start, 'end' => $period2End, 'total' => $period2Data->count()],
                'by_vendor' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error in comparePOsWithATA', ['error' => $e->getMessage()]);
            return ['period1' => [], 'period2' => [], 'by_vendor' => []];
        }
    }

    /**
     * Comparativo de POs con atraso CL entre dos períodos
     */
    public function comparePOsWithDelayCL(array $filters, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;

            // Período 1
            $query1 = PurchaseOrder::query()
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date > date_theorical_load')
                ->whereBetween('date_variable_date', [$period1Start, $period1End]);
            if ($companyId) {
                $query1->where('company_id', $companyId);
            }
            $period1Data = $query1->with('vendor')->get();

            // Período 2
            $query2 = PurchaseOrder::query()
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date > date_theorical_load')
                ->whereBetween('date_variable_date', [$period2Start, $period2End]);
            if ($companyId) {
                $query2->where('company_id', $companyId);
            }
            $period2Data = $query2->with('vendor')->get();

            // Agrupar por proveedor
            $byVendor = [];

            foreach ($period1Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period1']++;
                $byVendor[$vendorName]['teus1'] += $this->calculateTEUs($po->container_type);
            }

            foreach ($period2Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period2']++;
                $byVendor[$vendorName]['teus2'] += $this->calculateTEUs($po->container_type);
            }

            // Calcular variaciones
            $result = [];
            foreach ($byVendor as $vendor => $data) {
                $variation = 0;
                if ($data['period1'] > 0) {
                    $variation = round((($data['period2'] - $data['period1']) / $data['period1']) * 100, 1);
                } elseif ($data['period2'] > 0) {
                    $variation = 100;
                }

                $result[] = [
                    'vendor' => $vendor,
                    'period1_count' => $data['period1'],
                    'period2_count' => $data['period2'],
                    'period1_teus' => round($data['teus1'], 2),
                    'period2_teus' => round($data['teus2'], 2),
                    'variation_percentage' => $variation,
                ];
            }

            return [
                'period1' => ['start' => $period1Start, 'end' => $period1End, 'total' => $period1Data->count()],
                'period2' => ['start' => $period2Start, 'end' => $period2End, 'total' => $period2Data->count()],
                'by_vendor' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error in comparePOsWithDelayCL', ['error' => $e->getMessage()]);
            return ['period1' => [], 'period2' => [], 'by_vendor' => []];
        }
    }

    /**
     * Comparativo de POs con adelanto CL entre dos períodos
     */
    public function comparePOsWithAdvanceCL(array $filters, string $period1Start, string $period1End, string $period2Start, string $period2End): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;

            // Período 1
            $query1 = PurchaseOrder::query()
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date < date_theorical_load')
                ->whereBetween('date_variable_date', [$period1Start, $period1End]);
            if ($companyId) {
                $query1->where('company_id', $companyId);
            }
            $period1Data = $query1->with('vendor')->get();

            // Período 2
            $query2 = PurchaseOrder::query()
                ->whereNotNull('date_variable_date')
                ->whereNotNull('date_theorical_load')
                ->whereRaw('date_variable_date < date_theorical_load')
                ->whereBetween('date_variable_date', [$period2Start, $period2End]);
            if ($companyId) {
                $query2->where('company_id', $companyId);
            }
            $period2Data = $query2->with('vendor')->get();

            // Agrupar por proveedor
            $byVendor = [];

            foreach ($period1Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period1']++;
                $byVendor[$vendorName]['teus1'] += $this->calculateTEUs($po->container_type);
            }

            foreach ($period2Data as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['period1' => 0, 'period2' => 0, 'teus1' => 0, 'teus2' => 0];
                }
                $byVendor[$vendorName]['period2']++;
                $byVendor[$vendorName]['teus2'] += $this->calculateTEUs($po->container_type);
            }

            // Calcular variaciones
            $result = [];
            foreach ($byVendor as $vendor => $data) {
                $variation = 0;
                if ($data['period1'] > 0) {
                    $variation = round((($data['period2'] - $data['period1']) / $data['period1']) * 100, 1);
                } elseif ($data['period2'] > 0) {
                    $variation = 100;
                }

                $result[] = [
                    'vendor' => $vendor,
                    'period1_count' => $data['period1'],
                    'period2_count' => $data['period2'],
                    'period1_teus' => round($data['teus1'], 2),
                    'period2_teus' => round($data['teus2'], 2),
                    'variation_percentage' => $variation,
                ];
            }

            return [
                'period1' => ['start' => $period1Start, 'end' => $period1End, 'total' => $period1Data->count()],
                'period2' => ['start' => $period2Start, 'end' => $period2End, 'total' => $period2Data->count()],
                'by_vendor' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error in comparePOsWithAdvanceCL', ['error' => $e->getMessage()]);
            return ['period1' => [], 'period2' => [], 'by_vendor' => []];
        }
    }

    // ============================================
    // VISTA PO vs TEUs
    // ============================================

    /**
     * PO vs TEUs por etapa
     */
    public function getPOvsTEUsByStage(array $filters = []): array
    {
        return $this->getPOsByStage($filters);
    }

    /**
     * PO vs TEUs por período (semana actual, anterior, mes actual, anterior)
     */
    public function getPOvsTEUsByPeriod(array $filters = []): array
    {
        try {
            $now = Carbon::now();
            $companyId = auth()->user()->company_id ?? null;

            $periods = [
                'current_week' => [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => 'Semana Actual',
                ],
                'last_week' => [
                    'start' => $now->copy()->subWeek()->startOfWeek(),
                    'end' => $now->copy()->subWeek()->endOfWeek(),
                    'label' => 'Semana Anterior',
                ],
                'current_month' => [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'label' => 'Mes Actual',
                ],
                'last_month' => [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth(),
                    'label' => 'Mes Anterior',
                ],
            ];

            $result = [];
            foreach ($periods as $key => $period) {
                $query = PurchaseOrder::query()
                    ->whereBetween('created_at', [$period['start'], $period['end']]);

                if ($companyId) {
                    $query->where('company_id', $companyId);
                }

                $pos = $query->get();
                $poCount = $pos->count();
                $teusCount = 0;

                foreach ($pos as $po) {
                    $teusCount += $this->calculateTEUs($po->container_type);
                }

                $result[] = [
                    'period' => $period['label'],
                    'po_count' => $poCount,
                    'teus' => round($teusCount, 2),
                ];
            }

            // Calcular variaciones
            $weekVariation = 0;
            if ($result[1]['po_count'] > 0) {
                $weekVariation = round((($result[0]['po_count'] - $result[1]['po_count']) / $result[1]['po_count']) * 100, 1);
            }

            $monthVariation = 0;
            if ($result[3]['po_count'] > 0) {
                $monthVariation = round((($result[2]['po_count'] - $result[3]['po_count']) / $result[3]['po_count']) * 100, 1);
            }

            return [
                'periods' => $result,
                'week_variation' => $weekVariation,
                'month_variation' => $monthVariation,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOvsTEUsByPeriod', ['error' => $e->getMessage()]);
            return ['periods' => [], 'week_variation' => 0, 'month_variation' => 0];
        }
    }

    /**
     * PO vs TEUs por proveedor de mercancía
     */
    public function getPOvsTEUsByVendor(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->with('vendor')
                ->get();

            $byVendor = [];
            $totalPOs = 0;
            $totalTEUs = 0;

            foreach ($query as $po) {
                $vendorName = $po->vendor->name ?? 'Sin Proveedor';
                $teus = $this->calculateTEUs($po->container_type);

                if (!isset($byVendor[$vendorName])) {
                    $byVendor[$vendorName] = ['count' => 0, 'teus' => 0];
                }
                $byVendor[$vendorName]['count']++;
                $byVendor[$vendorName]['teus'] += $teus;

                $totalPOs++;
                $totalTEUs += $teus;
            }

            $result = [];
            foreach ($byVendor as $vendor => $data) {
                $result[] = [
                    'vendor' => $vendor,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                ];
            }

            // Ordenar por cantidad de POs descendente
            usort($result, function ($a, $b) {
                return $b['po_count'] <=> $a['po_count'];
            });

            return [
                'data' => $result,
                'total_pos' => $totalPOs,
                'total_teus' => round($totalTEUs, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOvsTEUsByVendor', ['error' => $e->getMessage()]);
            return ['data' => [], 'total_pos' => 0, 'total_teus' => 0];
        }
    }

    /**
     * PO vs TEUs por naviera
     */
    public function getPOvsTEUsByShippingLine(array $filters = []): array
    {
        try {
            $query = $this->getBaseQuery($filters)
                ->whereNotNull('shipping_line')
                ->get();

            $byShippingLine = [];
            $totalPOs = 0;
            $totalTEUs = 0;

            foreach ($query as $po) {
                $shippingLine = $po->shipping_line ?? 'Sin Naviera';
                $teus = $this->calculateTEUs($po->container_type);

                if (!isset($byShippingLine[$shippingLine])) {
                    $byShippingLine[$shippingLine] = ['count' => 0, 'teus' => 0];
                }
                $byShippingLine[$shippingLine]['count']++;
                $byShippingLine[$shippingLine]['teus'] += $teus;

                $totalPOs++;
                $totalTEUs += $teus;
            }

            $result = [];
            foreach ($byShippingLine as $line => $data) {
                $result[] = [
                    'shipping_line' => $line,
                    'po_count' => $data['count'],
                    'teus' => round($data['teus'], 2),
                ];
            }

            // Ordenar por cantidad de POs descendente
            usort($result, function ($a, $b) {
                return $b['po_count'] <=> $a['po_count'];
            });

            return [
                'data' => $result,
                'total_pos' => $totalPOs,
                'total_teus' => round($totalTEUs, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Error in getPOvsTEUsByShippingLine', ['error' => $e->getMessage()]);
            return ['data' => [], 'total_pos' => 0, 'total_teus' => 0];
        }
    }

    // ============================================
    // VISTA PROYECCIÓN
    // ============================================

    /**
     * Llegadas futuras por etapa y semana
     */
    public function getFutureArrivals(array $filters = []): array
    {
        try {
            $companyId = auth()->user()->company_id ?? null;
            $stageMapping = $this->getStageMapping($companyId);

            // Obtener el rango de semanas (próximas 12 semanas)
            $now = Carbon::now();
            $weeks = [];
            for ($i = 0; $i < 12; $i++) {
                $weekStart = $now->copy()->addWeeks($i)->startOfWeek();
                $weeks[] = [
                    'week_number' => $weekStart->weekOfYear,
                    'year' => $weekStart->year,
                    'label' => 'S' . str_pad($weekStart->weekOfYear, 2, '0', STR_PAD_LEFT) . '-' . $weekStart->year,
                    'start' => $weekStart,
                    'end' => $weekStart->copy()->endOfWeek(),
                ];
            }

            // Inicializar estructura por etapa y semana
            $stages = ['Producción', 'Booking', 'Tránsito'];
            $data = [];
            foreach ($stages as $stage) {
                $data[$stage] = [];
                foreach ($weeks as $week) {
                    $data[$stage][$week['label']] = ['po_count' => 0, 'teus' => 0];
                }
            }

            // Obtener POs con vendor y company para calcular tiempos de tránsito
            $query = PurchaseOrder::query()
                ->with(['kanbanStatus', 'vendor', 'company']);
            if ($companyId) {
                $query->where('company_id', $companyId);
            }
            $pos = $query->get();

            foreach ($pos as $po) {
                if (!$po->kanban_status_id || !isset($stageMapping[$po->kanban_status_id])) {
                    continue;
                }

                $stageName = $stageMapping[$po->kanban_status_id];
                $category = $this->mapStageToCategory($stageName);

                if (!$category || !in_array($category, $stages)) {
                    continue;
                }

                // Obtener país de origen y destino para calcular tránsito
                $originCountry = $po->vendor->country ?? null;
                $destinationCountry = $po->company->country ?? null;
                $transitDays = $this->transitTimeService->getTransitDays($originCountry, $destinationCountry) ?? 0;

                // Determinar fecha según etapa
                $targetDate = null;
                switch ($category) {
                    case 'Producción':
                        // Fecha CL Teórica + 15 días + días de tránsito según matriz
                        if ($po->date_theorical_load) {
                            $targetDate = Carbon::parse($po->date_theorical_load)
                                ->addDays(15)
                                ->addDays($transitDays);
                        }
                        break;
                    case 'Booking':
                        // Fecha ETD + días de tránsito según matriz
                        if ($po->date_etd) {
                            $targetDate = Carbon::parse($po->date_etd)->addDays($transitDays);
                        } elseif ($po->date_booking_authorized) {
                            $targetDate = Carbon::parse($po->date_booking_authorized)->addDays($transitDays);
                        }
                        break;
                    case 'Tránsito':
                        // ETA (ya incluye el tiempo de tránsito)
                        if ($po->date_eta) {
                            $targetDate = Carbon::parse($po->date_eta);
                        }
                        break;
                }

                if (!$targetDate) {
                    continue;
                }

                // Encontrar la semana correspondiente
                foreach ($weeks as $week) {
                    if ($targetDate >= $week['start'] && $targetDate <= $week['end']) {
                        $data[$category][$week['label']]['po_count']++;
                        $data[$category][$week['label']]['teus'] += $this->calculateTEUs($po->container_type);
                        break;
                    }
                }
            }

            // Formatear resultado
            $result = [];
            foreach ($stages as $stage) {
                $weekData = [];
                foreach ($weeks as $week) {
                    $weekData[] = [
                        'week' => $week['label'],
                        'po_count' => $data[$stage][$week['label']]['po_count'],
                        'teus' => round($data[$stage][$week['label']]['teus'], 2),
                    ];
                }
                $result[] = [
                    'stage' => $stage,
                    'weeks' => $weekData,
                ];
            }

            return [
                'weeks' => array_map(fn($w) => $w['label'], $weeks),
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getFutureArrivals', ['error' => $e->getMessage()]);
            return ['weeks' => [], 'data' => []];
        }
    }

    // ============================================
    // DATOS PARA DASHBOARD PRINCIPAL
    // ============================================

    /**
     * Obtiene todos los datos para el dashboard KPI
     */
    public function getDashboardData(array $filters = []): array
    {
        return [
            'po_by_stage' => $this->getPOsByStage($filters),
            'po_delay_cl' => $this->getPOsWithDelayCL($filters),
            'po_advance_cl' => $this->getPOsWithAdvanceCL($filters),
            'capacity' => $this->getCapacityAllocation($filters),
            'transshipment' => $this->getPOsInTransshipment($filters),
            'po_with_ata' => $this->getPOsWithATA($filters),
            'transit_time' => $this->getTransitTime($filters),
        ];
    }
}

