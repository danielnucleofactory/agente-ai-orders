<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Exports\DashboardExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard view with initial data
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            Log::info('Dashboard index called', [
                'user_id' => auth()->id(),
                'user_company_id' => auth()->user()->company_id ?? null,
                'request_data' => $request->all()
            ]);

            $filters = $this->getFilters($request);
            Log::info('Filters processed', ['filters' => $filters]);

            $dashboardData = $this->getDashboardData($filters);
            Log::info('Dashboard data retrieved', [
                'metrics_count' => count($dashboardData['metrics'] ?? []),
                'trend_table_year' => $dashboardData['trend_table']['year'] ?? null,
            ]);

            $filterOptions = $this->dashboardService->getFilterOptions();
            Log::info('Filter options retrieved', [
                'products_count' => $filterOptions['products']->count(),
                'hubs_count' => $filterOptions['hubs']->count(),
                'vendors_count' => $filterOptions['vendors']->count(),
                'materials_count' => $filterOptions['materials']->count()
            ]);

            return view('dashboard', compact('dashboardData', 'filterOptions'));
        } catch (\Exception $e) {
            Log::error('Error loading dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Return view with empty data if error occurs
            try {
                $filterOptions = $this->dashboardService->getFilterOptions();
                Log::info('Retrieved filter options after error');
            } catch (\Exception $filterError) {
                Log::error('Error getting filter options', [
                    'error' => $filterError->getMessage(),
                    'trace' => $filterError->getTraceAsString()
                ]);
                $filterOptions = $this->getEmptyFilterOptions();
            }

            $dashboardData = $this->getEmptyDashboardData();

            return view('dashboard', compact('dashboardData', 'filterOptions'))
                ->with('error', 'Error al cargar los datos del dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Get dashboard data via AJAX
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            Log::info('Dashboard getData called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $filters = $this->getFilters($request);
            Log::info('AJAX Filters processed', ['filters' => $filters]);

            $dashboardData = $this->getDashboardData($filters);
            $filterOptions = $this->dashboardService->getFilterOptions();

            Log::info('AJAX Dashboard data retrieved successfully');

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
                'filterOptions' => $filterOptions
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting dashboard data via AJAX', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters ?? [],
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del dashboard: ' . $e->getMessage(),
                'data' => $this->getEmptyDashboardData()
            ], 500);
        }
    }

    /**
     * Export dashboard trend table data as Excel
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        try {
            Log::info('Dashboard export called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            // Obtener filtros aplicados (incluyendo los del dashboard-kpi)
            $filters = $this->getExportFilters($request);
            
            Log::info('Export filters applied', ['filters' => $filters]);
            
            // Obtener datos de la tabla de tendencias con rango de fechas dinámico
            $trendData = $this->dashboardService->getCanceledLinesTrendTable($filters);
            
            // Obtener los meses dinámicos del resultado
            $monthKeys = $trendData['month_keys'] ?? [];
            $monthLabels = $trendData['month_labels'] ?? [];
            
            // Si no hay month_labels, crear etiquetas básicas
            if (empty($monthLabels)) {
                foreach ($monthKeys as $key) {
                    $monthLabels[$key] = $key;
                }
            }
            
            // Preparar datos para el Excel con columnas dinámicas
            $exportData = [];
            $categoryOrder = [
                'Producción',
                'Booking',
                'Transito',
                'Puerto',
                'Recibiendo CDI',
            ];
            
            foreach ($categoryOrder as $category) {
                $row = [$category];
                foreach ($monthKeys as $monthKey) {
                    $value = $trendData['categories'][$category][$monthKey] ?? 0;
                    $row[] = $value === 0 ? '-' : $value;
                }
                $exportData[] = $row;
            }

            // Convertir monthLabels a array ordenado para los headers
            $headerLabels = [];
            foreach ($monthKeys as $key) {
                $headerLabels[] = $monthLabels[$key] ?? $key;
            }

            Log::info('Export data prepared', [
                'rows_count' => count($exportData),
                'columns_count' => count($monthKeys),
                'date_range' => ($trendData['date_from'] ?? 'N/A') . ' - ' . ($trendData['date_to'] ?? 'N/A')
            ]);

            $filename = 'tendencia_etapas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new DashboardExport($exportData, $headerLabels, $filters),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // En caso de error, devolver un archivo vacío con mensaje de error
            abort(500, 'Error al exportar los datos: ' . $e->getMessage());
        }
    }

    /**
     * Get filters from request for export (includes dashboard-kpi filters)
     *
     * @param Request $request
     * @return array
     */
    private function getExportFilters(Request $request): array
    {
        $filters = [
            // Filtros de fecha
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            
            // Filtros del dashboard original
            'customer_type' => $request->get('customer_type'),
            'arrival_status' => $request->get('arrival_status'),
            'vendor_id' => $request->get('vendor_id'),
            'departure_port' => $request->get('departure_port'),
            'arrival_port' => $request->get('arrival_port'),
            'shipping_line' => $request->get('shipping_line'),
            'service_provider' => $request->get('service_provider'),
            
            // Filtros adicionales del dashboard-kpi
            'trading_company' => $request->get('trading_company'),
            'stage' => $request->get('stage'),
            'route_label' => $request->get('route_label'),
            'order_number' => $request->get('order_number'),
            
            // Filtros de botones adicionales
            'po_retraso_cl' => $request->boolean('po_retraso_cl', false),
            'po_adelanto_cl' => $request->boolean('po_adelanto_cl', false),
            'indicador_capacidad' => $request->boolean('indicador_capacidad', false),
        ];
        
        return $filters;
    }

    /**
     * Get filters from request
     *
     * @param Request $request
     * @return array
     */
    private function getFilters(Request $request): array
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'customer_type' => $request->get('customer_type'),
            'arrival_status' => $request->get('arrival_status'),
            'vendor_id' => $request->get('vendor_id'),
            'departure_port' => $request->get('departure_port'),
            'arrival_port' => $request->get('arrival_port'),
            'shipping_line' => $request->get('shipping_line'),
            'service_provider' => $request->get('service_provider'),
            'po_retraso_cl' => $request->boolean('po_retraso_cl', false),
            'po_adelanto_cl' => $request->boolean('po_adelanto_cl', false),
            'indicador_capacidad' => $request->boolean('indicador_capacidad', false),
        ];
        return $filters;
    }

    /**
     * Get complete dashboard data
     *
     * @param array $filters
     * @return array
     */
    private function getDashboardData(array $filters): array
    {
        try {
            Log::info('Getting metrics...');
            $metrics = $this->dashboardService->getMetrics($filters);
            Log::info('Metrics retrieved', ['metrics' => $metrics]);

            Log::info('Getting trend table data...');
            $trendTableData = $this->dashboardService->getCanceledLinesTrendTable($filters);
            Log::info('Trend table data retrieved', ['year' => $trendTableData['year'] ?? null]);

            return [
                'metrics' => $metrics,
                'trend_table' => $trendTableData,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getDashboardData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    /**
     * Get empty dashboard data structure
     *
     * @return array
     */
    private function getEmptyDashboardData(): array
    {
        return [
            'metrics' => [
                'total_pos' => 0,
                'on_time_percentage' => 0,
                'delayed_percentage' => 0,
                'material_count' => 0,
            ],
            'trend_table' => [
                'categories' => [
                    'Producción' => array_fill_keys(range(1, 12), 0),
                    'Booking' => array_fill_keys(range(1, 12), 0),
                    'Transito' => array_fill_keys(range(1, 12), 0),
                    'Puerto' => array_fill_keys(range(1, 12), 0),
                    'Recibiendo CDI' => array_fill_keys(range(1, 12), 0),
                ],
                'year' => now()->year,
            ],
        ];
    }

    /**
     * Get empty filter options
     *
     * @return array
     */
    private function getEmptyFilterOptions(): array
    {
        return [
            'products' => collect([]),
            'hubs' => collect([]),
            'vendors' => collect([]),
            'materials' => collect([]),
        ];
    }
}
