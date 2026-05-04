<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\DashboardDomExport;
use App\Exports\DashboardFullReportExport;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            Log::info('Dashboard index called', [
                'user_id' => auth()->id(),
                'user_company_id' => auth()->user()->company_id ?? null,
                'request_data' => $request->all(),
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
                'materials_count' => $filterOptions['materials']->count(),
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
                    'trace' => $filterError->getTraceAsString(),
                ]);
                $filterOptions = $this->getEmptyFilterOptions();
            }

            $dashboardData = $this->getEmptyDashboardData();

            return view('dashboard', compact('dashboardData', 'filterOptions'))
                ->with('error', 'Error al cargar los datos del dashboard: '.$e->getMessage());
        }
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            Log::info('Dashboard getData called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            $filters = $this->getFilters($request);
            Log::info('AJAX Filters processed', ['filters' => $filters]);

            $dashboardData = $this->getDashboardData($filters);
            $filterOptions = $this->dashboardService->getFilterOptions();

            Log::info('AJAX Dashboard data retrieved successfully');

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
                'filterOptions' => $filterOptions,
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
                'message' => 'Error al obtener los datos del dashboard: '.$e->getMessage(),
                'data' => $this->getEmptyDashboardData(),
            ], 500);
        }
    }

    /**
     * Exportación Excel del dashboard.
     *
     * - POST JSON `{ "sheets": [...] }`: libro desde matrices reflejo del DOM (uso normal desde la UI).
     * - GET con `export_scope=full`: reporte completo multi-hoja (reservado / uso interno, no expuesto en UI).
     */
    public function export(Request $request): BinaryFileResponse
    {
        if ($request->isMethod('POST')) {
            return $this->exportFromDom($request);
        }

        if ($request->query('export_scope') === 'full') {
            return $this->exportFullReport($request);
        }

        abort(400, 'Use POST con el payload DOM desde la aplicación, o GET con export_scope=full para el reporte completo.');
    }

    /**
     * Libro Excel generado desde matrices enviadas por el cliente (orden y filas = DOM).
     */
    private function exportFromDom(Request $request): BinaryFileResponse
    {
        try {
            $validated = $request->validate([
                'sheets' => ['required', 'array', 'min:1', 'max:80'],
                'sheets.*.title' => ['required', 'string', 'max:100'],
                'sheets.*.headings' => ['required', 'array', 'max:256'],
                'sheets.*.rows' => ['required', 'array', 'max:25000'],
                'filename_base' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9_-]+$/'],
            ]);

            $normalized = [];
            foreach ($validated['sheets'] as $sheet) {
                $normalized[] = $this->normalizeDomSheet($sheet);
            }

            Log::info('Dashboard DOM export', [
                'user_id' => auth()->id(),
                'sheet_count' => count($normalized),
            ]);

            $base = $validated['filename_base'] ?? 'dashboard_vista';
            $filename = $base.'_'.now()->format('Y-m-d_H-i-s').'.xlsx';

            return Excel::download(new DashboardDomExport($normalized), $filename);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard DOM', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            abort(500, 'Error al exportar los datos: '.$e->getMessage());
        }
    }

    /**
     * Reporte completo (multi-servicio). Activar solo con GET `export_scope=full`.
     */
    private function exportFullReport(Request $request): BinaryFileResponse
    {
        try {
            Log::info('Dashboard full export called', [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            $filters = $this->getExportFilters($request);

            Log::info('Export filters applied', ['filters' => $filters]);

            $context = [
                'active_view' => $request->get('active_view'),
                'active_subtab' => $request->get('active_subtab'),
                'active_filter' => $request->get('active_filter'),
                'period1_start' => $request->get('period1_start'),
                'period1_end' => $request->get('period1_end'),
                'period2_start' => $request->get('period2_start'),
                'period2_end' => $request->get('period2_end'),
                'comparison_period_1' => $request->get('comparison_period_1'),
                'comparison_period_2' => $request->get('comparison_period_2'),
                'projection_week' => $request->get('projection_week'),
                'week_count' => $request->get('week_count'),
            ];

            $filename = 'dashboard_reporte_completo_'.now()->format('Y-m-d_H-i-s').'.xlsx';

            return Excel::download(new DashboardFullReportExport($filters, $context), $filename);
        } catch (\Exception $e) {
            Log::error('Error exporting dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            abort(500, 'Error al exportar los datos: '.$e->getMessage());
        }
    }

    /**
     * @param  array{title: string, headings: array<int, mixed>, rows: array<int, mixed>}  $sheet
     * @return array{title: string, headings: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function normalizeDomSheet(array $sheet): array
    {
        $title = $this->sanitizeDomSheetTitle((string) ($sheet['title'] ?? 'Hoja'));

        $headings = [];
        foreach ((array) ($sheet['headings'] ?? []) as $h) {
            $headings[] = $this->normalizeDomCell($h);
        }

        $rowsIn = (array) ($sheet['rows'] ?? []);
        $rows = [];
        foreach ($rowsIn as $row) {
            if (! is_array($row)) {
                continue;
            }
            $r = [];
            foreach ($row as $cell) {
                $r[] = $this->normalizeDomCell($cell);
            }
            $rows[] = $r;
        }

        $maxCols = max(count($headings), 1, ...array_map(static fn (array $r): int => count($r), $rows));
        while (count($headings) < $maxCols) {
            $headings[] = 'Col '.(count($headings) + 1);
        }
        $headings = array_slice($headings, 0, $maxCols);

        foreach ($rows as $i => $r) {
            while (count($r) < $maxCols) {
                $r[] = '';
            }
            $rows[$i] = array_slice($r, 0, $maxCols);
        }

        return [
            'title' => $title,
            'headings' => $headings,
            'rows' => $rows,
        ];
    }

    private function sanitizeDomSheetTitle(string $title): string
    {
        $t = preg_replace('/[\[\]\:\*\?\/\\\]/u', '-', $title) ?? $title;
        $t = trim($t) !== '' ? trim($t) : 'Hoja';

        return mb_substr($t, 0, 100);
    }

    private function normalizeDomCell(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? $value;

            return mb_substr($s, 0, 8000);
        }

        return '';
    }

    /**
     * Get filters from request for export (includes dashboard-kpi filters)
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

            // Botones adicionales del dashboard-kpi (para contexto del reporte)
            'pos_transbordo' => $request->boolean('pos_transbordo', false),
            'pos_ata' => $request->boolean('pos_ata', false),
        ];

        return $filters;
    }

    /**
     * Get filters from request
     */
    private function getFilters(Request $request): array
    {
        // Mismos criterios que export para vista KPI / tendencia (OLO-019)
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
            'trading_company' => $request->get('trading_company'),
            'stage' => $request->get('stage'),
            'route_label' => $request->get('route_label'),
            'order_number' => $request->get('order_number'),
            'po_retraso_cl' => $request->boolean('po_retraso_cl', false),
            'po_adelanto_cl' => $request->boolean('po_adelanto_cl', false),
            'indicador_capacidad' => $request->boolean('indicador_capacidad', false),
        ];

        return $filters;
    }

    /**
     * Get complete dashboard data
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
