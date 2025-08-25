<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ForecastService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ForecastController extends Controller
{
    private ForecastService $forecastService;
    private DashboardService $dashboardService;

    public function __construct(ForecastService $forecastService, DashboardService $dashboardService)
    {
        $this->forecastService = $forecastService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the forecast dashboard
     */
    public function index(Request $request): View
    {
        try {
            Log::info('ForecastController::index starting');

            $filters = $this->getFilters($request);
            Log::info('Forecast filters applied', $filters);

            // Get forecast data
            $forecastData = $this->getForecastData($filters);

            // Get filter options (reuse from dashboard service)
            $filterOptions = $this->dashboardService->getFilterOptions();

            Log::info('Forecast dashboard data retrieved successfully');

            return view('products.forecast-graph', [
                'forecastData' => $forecastData,
                'filterOptions' => $filterOptions,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ForecastController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('products.forecast-graph', [
                'error' => 'Error al cargar el dashboard de forecast: ' . $e->getMessage(),
                'forecastData' => $this->getEmptyForecastData(),
                'filterOptions' => [],
                'filters' => $filters ?? [],
            ]);
        }
    }

    /**
     * Get forecast data via AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            Log::info('ForecastController::getData starting');

            $filters = $this->getFilters($request);
            $forecastData = $this->getForecastData($filters);

            Log::info('Forecast AJAX data retrieved successfully');

            return response()->json([
                'success' => true,
                'data' => $forecastData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ForecastController::getData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del forecast',
                'data' => $this->getEmptyForecastData(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Export forecast data
     */
    public function export(Request $request)
    {
        try {
            Log::info('ForecastController::export starting');

            $filters = $this->getFilters($request);
            $exportData = $this->forecastService->getExportData($filters);

            $filename = 'forecast_export_' . date('Y-m-d_H-i-s') . '.csv';

            return response()->streamDownload(function () use ($exportData) {
                $handle = fopen('php://output', 'w');

                // Headers
                fputcsv($handle, [
                    'Material',
                    'Forecast KG',
                    'Cantidad Real KG',
                    'Desviación KG',
                    'Mes',
                    'Vendor',
                    'Monto'
                ]);

                // Data rows
                foreach ($exportData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ForecastController::export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al exportar los datos',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get filters from request
     */
    private function getFilters(Request $request): array
    {
        // Only include filters that have actual values to avoid empty array issues
        $filters = [];
        
        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->get('date_from');
        }
        
        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->get('date_to');
        }
        
        if ($request->filled('product_id')) {
            $productIds = $request->get('product_id');
            if (is_array($productIds)) {
                $productIds = array_filter($productIds);
                if (!empty($productIds)) {
                    $filters['product_id'] = $productIds;
                }
            } else if ($productIds) {
                $filters['product_id'] = [$productIds];
            }
        }
        
        if ($request->filled('vendor_id')) {
            $vendorIds = $request->get('vendor_id');
            if (is_array($vendorIds)) {
                $vendorIds = array_filter($vendorIds);
                if (!empty($vendorIds)) {
                    $filters['vendor_id'] = $vendorIds;
                }
            } else if ($vendorIds) {
                $filters['vendor_id'] = [$vendorIds];
            }
        }
        
        if ($request->filled('material_type')) {
            $filters['material_type'] = $request->get('material_type');
        }
        
        if ($request->filled('category_id')) {
            $filters['category_id'] = $request->get('category_id');
        }
        
        return $filters;
    }

    /**
     * Get all forecast data
     */
    private function getForecastData(array $filters): array
    {
        // Reuse dashboard metrics for Total POs, % on time, % delayed
        $dashboardMetrics = $this->dashboardService->getMetrics($filters);

        // Get forecast-specific metrics
        $forecastMetrics = $this->forecastService->getMetrics($filters);

        // Combine metrics
        $metrics = [
            'total_pos' => $dashboardMetrics['total_pos'],
            'on_time_percentage' => $dashboardMetrics['on_time_percentage'],
            'delayed_percentage' => $dashboardMetrics['delayed_percentage'],
            'total_amount' => $forecastMetrics['total_amount'],
        ];

        // Get forecast charts data
        $charts = $this->forecastService->getChartsData($filters);

        // Get forecast table data
        $forecastTable = $this->forecastService->getForecastTableData($filters);

        return [
            'metrics' => $metrics,
            'charts' => $charts,
            'forecast_table' => $forecastTable,
        ];
    }

    /**
     * Get empty forecast data structure
     */
    private function getEmptyForecastData(): array
    {
        return [
            'metrics' => [
                'total_pos' => 0,
                'on_time_percentage' => 0,
                'delayed_percentage' => 0,
                'total_amount' => 0,
            ],
            'charts' => [
                'material_deviation' => [],
                'monthly_kgs' => [],
                'vendor_deviation' => [],
            ],
            'forecast_table' => [],
        ];
    }
}
