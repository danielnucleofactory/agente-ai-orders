<?php

namespace App\Http\Controllers;

use App\Services\DashboardKPIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardKPIController extends Controller
{
    protected DashboardKPIService $kpiService;

    public function __construct(DashboardKPIService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    /**
     * Obtiene todos los datos del dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getDashboardData($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ============================================
    // VISTA TENDENCIA - Cantidad de PO
    // ============================================

    /**
     * POs por Etapa
     */
    public function posByStage(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOsByStage($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POs con retraso según Carga Lista
     */
    public function posDelayCL(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOsWithDelayCL($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POs con adelanto según Carga Lista
     */
    public function posAdvanceCL(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOsWithAdvanceCL($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Capacidad (Allocation)
     */
    public function capacity(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getCapacityAllocation($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POs en Puerto de Transbordo
     */
    public function transshipment(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOsInTransshipment($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POs con ATA
     */
    public function posWithATA(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOsWithATA($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Tiempo de tránsito
     */
    public function transitTime(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getTransitTime($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ============================================
    // VISTA COMPARATIVO
    // ============================================

    /**
     * Comparativo de POs con ATD
     */
    public function compareATD(Request $request): JsonResponse
    {
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date',
        ]);

        $filters = $this->extractFilters($request);
        $data = $this->kpiService->comparePOsWithATD(
            $filters,
            $request->period1_start,
            $request->period1_end,
            $request->period2_start,
            $request->period2_end
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Comparativo de POs con ATA
     */
    public function compareATA(Request $request): JsonResponse
    {
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date',
        ]);

        $filters = $this->extractFilters($request);
        $data = $this->kpiService->comparePOsWithATA(
            $filters,
            $request->period1_start,
            $request->period1_end,
            $request->period2_start,
            $request->period2_end
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Comparativo de POs con retraso CL
     */
    public function compareDelayCL(Request $request): JsonResponse
    {
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date',
        ]);

        $filters = $this->extractFilters($request);
        $data = $this->kpiService->comparePOsWithDelayCL(
            $filters,
            $request->period1_start,
            $request->period1_end,
            $request->period2_start,
            $request->period2_end
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Comparativo de POs con adelanto CL
     */
    public function compareAdvanceCL(Request $request): JsonResponse
    {
        $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date',
        ]);

        $filters = $this->extractFilters($request);
        $data = $this->kpiService->comparePOsWithAdvanceCL(
            $filters,
            $request->period1_start,
            $request->period1_end,
            $request->period2_start,
            $request->period2_end
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ============================================
    // VISTA PO vs TEUs
    // ============================================

    /**
     * PO vs TEUs por etapa
     */
    public function poVsTeusByStage(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOvsTEUsByStage($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * PO vs TEUs por período
     */
    public function poVsTeusByPeriod(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOvsTEUsByPeriod($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * PO vs TEUs por proveedor
     */
    public function poVsTeusByVendor(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOvsTEUsByVendor($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * PO vs TEUs por naviera
     */
    public function poVsTeusByShippingLine(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getPOvsTEUsByShippingLine($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // ============================================
    // VISTA PROYECCIÓN
    // ============================================

    /**
     * Llegadas futuras
     */
    public function futureArrivals(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $data = $this->kpiService->getFutureArrivals($filters);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Obtiene las opciones para los filtros
     */
    public function getFilterOptions(): JsonResponse
    {
        try {
            $dashboardService = app(\App\Services\DashboardService::class);
            $filterOptions = $dashboardService->getFilterOptions();

            // Obtener etapas desde KanbanStatus
            $companyId = auth()->user()->company_id ?? null;
            $stagesQuery = \App\Models\KanbanStatus::query();
            
            if ($companyId) {
                $stagesQuery->whereHas('board', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                        ->where('is_active', true)
                        ->where(function($subQ) {
                            $subQ->where('type', 'po_stages')
                                ->orWhere('type', 'purchase_orders');
                        });
                });
            } else {
                $stagesQuery->where('kanban_board_id', 1);
            }

            $stages = $stagesQuery->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(function($stage) {
                    return ['id' => $stage->id, 'name' => $stage->name];
                });

            // Obtener rutas logísticas
            $routes = \App\Models\PurchaseOrder::query()
                ->when($companyId, function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->whereNotNull('route_label')
                ->distinct()
                ->pluck('route_label')
                ->filter()
                ->map(function ($route) {
                    return ['id' => $route, 'name' => $route];
                })
                ->values();

            // Obtener clientes desde las POs (trading_company en purchase_orders)
            $clients = \App\Models\PurchaseOrder::query()
                ->when($companyId, function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->whereNotNull('trading_company')
                ->select('trading_company')
                ->distinct()
                ->pluck('trading_company')
                ->filter()
                ->map(function ($tradingCompany) {
                    return [
                        'id' => $tradingCompany,
                        'name' => $tradingCompany
                    ];
                })
                ->values()
                ->sortBy('name')
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'vendors' => $filterOptions['vendors'] ?? [],
                    'service_providers' => $filterOptions['service_providers'] ?? [],
                    'shipping_lines' => $filterOptions['shipping_lines'] ?? [],
                    'departure_ports' => $filterOptions['departure_ports'] ?? [],
                    'arrival_ports' => $filterOptions['arrival_ports'] ?? [],
                    'stages' => $stages,
                    'routes' => $routes,
                    'clients' => $clients,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getFilterOptions', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener opciones de filtros',
            ], 500);
        }
    }

    /**
     * Obtiene los datos resumidos para los KPI cards
     */
    public function getKPISummary(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        
        try {
            $posByStage = $this->kpiService->getPOsByStage($filters);
            $delayCL = $this->kpiService->getPOsWithDelayCL($filters);
            $advanceCL = $this->kpiService->getPOsWithAdvanceCL($filters);
            $withATA = $this->kpiService->getPOsWithATA($filters);

            $totalPOs = $posByStage['total_pos'] ?? 0;
            $delayCount = $delayCL['summary']['total_pos'] ?? 0;
            $advanceCount = $advanceCL['summary']['total_pos'] ?? 0;
            $ataCount = $withATA['summary']['total_pos'] ?? 0;

            $delayPercentage = $totalPOs > 0 ? round(($delayCount / $totalPOs) * 100, 1) : 0;
            $advancePercentage = $totalPOs > 0 ? round(($advanceCount / $totalPOs) * 100, 1) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_pos' => $totalPOs,
                    'delay_count' => $delayCount,
                    'delay_percentage' => $delayPercentage,
                    'advance_count' => $advanceCount,
                    'advance_percentage' => $advancePercentage,
                    'ata_count' => $ataCount,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getKPISummary', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen KPI',
            ], 500);
        }
    }

    /**
     * Extrae los filtros del request
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'trading_company' => $request->input('trading_company'),
            'vendor_id' => $request->input('vendor_id'),
            'shipping_line' => $request->input('shipping_line'),
            'service_provider' => $request->input('service_provider'),
            'departure_port' => $request->input('departure_port'),
            'arrival_port' => $request->input('arrival_port'),
            'route_label' => $request->input('route_label'),
            'stage' => $request->input('stage'),
            'order_number' => $request->input('order_number'),
        ];
    }
}

