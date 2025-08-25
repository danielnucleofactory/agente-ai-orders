<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'charts_count' => count($dashboardData['charts'] ?? []),
                'detail_table_count' => count($dashboardData['detail_table'] ?? [])
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
     * Export dashboard data
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Request $request): StreamedResponse
    {
        try {
            Log::info('Dashboard export called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            $filters = $this->getFilters($request);
            $exportData = $this->dashboardService->getExportData($filters);

            Log::info('Export data retrieved', ['records_count' => $exportData->count()]);

            $filename = 'dashboard_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($exportData) {
                $handle = fopen('php://output', 'w');

                // Add BOM for proper UTF-8 encoding in Excel
                fwrite($handle, "\xEF\xBB\xBF");

                // CSV Headers
                fputcsv($handle, [
                    'Número PO',
                    'Fecha Salida',
                    'Fecha Estimada',
                    'Fecha Real Llegada',
                    'Cantidad KG',
                    'Estado',
                    'Hub Planeado',
                    'Hub Real',
                    'Proveedor',
                    'Modo de Transporte'
                ]);

                // CSV Data
                foreach ($exportData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->streamDownload(function () use ($e) {
                echo "Error al exportar los datos: " . $e->getMessage();
            }, 'error.txt');
        }
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
            'product_id' => $request->get('product_id'),
            'material_type' => $request->get('material_type'),
            'hub_id' => $request->get('hub_id'),
            'vendor_id' => $request->get('vendor_id'),
            'status' => $request->get('status'),
            'transport' => $request->get('transport'),
            'stage' => $request->get('stage'),
        ];
        Log::info('Valor recibido en filtro stage:', ['stage' => $filters['stage']]);
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

            Log::info('Getting charts data...');
            $chartsData = $this->dashboardService->getChartsData($filters);
            Log::info('Charts data retrieved', [
                'hub_distribution_count' => $chartsData['hub_distribution']->count(),
                'delivery_status_count' => $chartsData['delivery_status']->count(),
                'transport_type_count' => $chartsData['transport_type']->count(),
                'delay_reasons_count' => $chartsData['delay_reasons']->count(),
                'pos_by_stage_count' => $chartsData['pos_by_stage']->count(),
            ]);

            Log::info('Getting detail table data...');
            $detailData = $this->dashboardService->getDetailTableData($filters);
            Log::info('Detail data retrieved', ['detail_count' => $detailData->count()]);

            return [
                'metrics' => $metrics,
                'charts' => $chartsData,
                'detail_table' => $detailData->toArray(),
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
            'charts' => [
                'hub_distribution' => collect([]),
                'delivery_status' => collect([]),
                'transport_type' => collect([]),
                'delay_reasons' => collect([]),
                'pos_by_stage' => collect([]),
            ],
            'detail_table' => [],
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
