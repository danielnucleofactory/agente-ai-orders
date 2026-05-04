<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Sheets\OloTableSheet;
use App\Services\DashboardKPIService;
use App\Services\DashboardService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class DashboardFullReportExport implements WithMultipleSheets
{
    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly array $filters,
        private readonly array $context = []
    ) {
    }

    public function sheets(): array
    {
        /** @var DashboardKPIService $kpi */
        $kpi = app(DashboardKPIService::class);
        /** @var DashboardService $legacyDashboard */
        $legacyDashboard = app(DashboardService::class);

        $generatedAt = Carbon::now(config('app.timezone'))->format('d/m/Y H:i:s');
        $user = auth()->user();

        $sheets = [];

        // =========================
        // Hoja: Resumen
        // =========================
        $summaryRows = [];
        $summaryRows[] = ['Reporte', 'Dashboard - Reporte Completo'];
        $summaryRows[] = ['Generado', $generatedAt];
        $summaryRows[] = ['Usuario', $user?->email ?? ($user?->name ?? 'N/A')];
        $summaryRows[] = ['Company ID', (string) ($user?->company_id ?? 'N/A')];
        $summaryRows[] = [''];
        $summaryRows[] = ['Filtros aplicados', ''];

        foreach ($this->normalizeFiltersForDisplay($this->filters, $this->context) as $label => $value) {
            $summaryRows[] = [$label, $value];
        }

        // KPI summary (como en UI)
        $summaryRows[] = [''];
        $summaryRows[] = ['KPI (Resumen)', ''];
        // Evitar acoplar al controller: recalcular con el servicio.
        $posByStage = $kpi->getPOsByStage($this->filters);
        $delayCL = $kpi->getPOsWithDelayCL($this->filters);
        $advanceCL = $kpi->getPOsWithAdvanceCL($this->filters);
        $withATA = $kpi->getPOsWithATA($this->filters);
        $totalPOs = (int) ($posByStage['total_pos'] ?? 0);
        $delayCount = (int) (($delayCL['summary']['total_pos'] ?? 0));
        $advanceCount = (int) (($advanceCL['summary']['total_pos'] ?? 0));
        $ataCount = (int) (($withATA['summary']['total_pos'] ?? 0));
        $delayPct = $totalPOs > 0 ? round(($delayCount / $totalPOs) * 100, 1) : 0;
        $advancePct = $totalPOs > 0 ? round(($advanceCount / $totalPOs) * 100, 1) : 0;

        $summaryRows[] = ['Total PO', $totalPOs];
        $summaryRows[] = ['PO con Retraso (CL)', $delayCount . " ({$delayPct}%)"];
        $summaryRows[] = ['PO con Adelanto (CL)', $advanceCount . " ({$advancePct}%)"];
        $summaryRows[] = ['PO con ATA', $ataCount];

        $sheets[] = new OloTableSheet('Resumen', ['Campo', 'Valor'], $summaryRows);

        // =========================
        // Hoja: Tendencia por etapas (KPI)
        // =========================
        $sheets[] = new OloTableSheet(
            'Tendencia Etapas',
            ['Etapa', 'PO', 'TEUs', '% PO', '% TEUs'],
            array_map(
                static fn (array $r) => [
                    (string) ($r['stage'] ?? ''),
                    (int) ($r['po_count'] ?? 0),
                    (float) ($r['teus'] ?? 0),
                    (float) ($r['percentage'] ?? 0),
                    (float) ($r['teus_percentage'] ?? 0),
                ],
                (array) ($posByStage['data'] ?? [])
            )
        );

        // =========================
        // Hojas: Retraso / Adelanto / Capacidad / Transbordo / ATA / Tránsito
        // =========================
        $sheets[] = $this->sheetFromSectionedData('Retraso CL', $delayCL, [
            'summary' => ['Métrica', 'Valor'],
            'by_vendor' => ['Proveedor', 'PO', 'TEUs', 'Prom. días atraso'],
            'details' => ['PO', 'Proveedor', 'Días atraso', 'Etapa', 'TEUs'],
        ]);

        $sheets[] = $this->sheetFromSectionedData('Adelanto CL', $advanceCL, [
            'summary' => ['Métrica', 'Valor'],
            'by_vendor' => ['Proveedor', 'PO', 'TEUs', 'Prom. días adelanto'],
            'details' => ['PO', 'Proveedor', 'Días adelanto', 'Etapa', 'TEUs'],
        ]);

        $capacity = $kpi->getCapacityAllocation($this->filters);
        $sheets[] = $this->sheetFromSectionedData('Capacidad', $capacity, [
            'summary' => ['Métrica', 'Valor'],
            'by_vendor' => ['Proveedor', 'PO', 'TEUs', '%', 'Prom. días tránsito'],
            'by_service_provider' => ['Proveedor Servicio', 'PO', 'TEUs', '%'],
            'details' => ['PO', 'Naviera', 'Días tránsito', 'TEUs'],
        ]);

        // Transbordo es pesado en frío; solo calcularlo si fue solicitado explícitamente.
        $wantsTransshipment =
            (bool) ($this->filters['pos_transbordo'] ?? false)
            || (($this->context['active_filter'] ?? null) === 'pos_transbordo');
        if ($wantsTransshipment) {
            $transshipment = $kpi->getPOsInTransshipment($this->filters);
            $sheets[] = $this->sheetFromSectionedData('Transbordo', $transshipment, [
                'summary' => ['Métrica', 'Valor'],
                'by_port' => ['Puerto', 'Puerto (nombre)', 'PO', 'TEUs'],
                'details' => ['PO', 'Proveedor', 'Naviera', 'Puerto Transbordo', 'Destino', 'TEUs'],
            ]);
        }

        $sheets[] = $this->sheetFromSectionedData('PO con ATA', $withATA, [
            'summary' => ['Métrica', 'Valor'],
            'by_client' => ['Cliente', 'PO', 'TEUs'],
            'by_route' => ['Ruta', 'PO', 'TEUs'],
            'details' => ['Cliente', 'PO', 'Proveedor', 'Naviera', 'Puerto Arribo', 'ATA', 'TEUs'],
        ]);

        $transitTime = $kpi->getTransitTime($this->filters);
        $sheets[] = $this->sheetFromSectionedData('Tiempo Tránsito', $transitTime, [
            'summary' => ['Métrica', 'Valor'],
            'by_range' => ['Rango', 'PO', 'TEUs'],
            'by_client' => ['Cliente', 'PO', 'TEUs', 'Prom. días'],
            'by_route' => ['Ruta', 'PO', 'TEUs', 'Prom. días'],
            'details' => ['PO', 'Cliente', 'Ruta', 'Días tránsito', 'TEUs'],
        ]);

        // =========================
        // Hojas: PO vs TEUs
        // =========================
        $povsteusStage = $kpi->getPOvsTEUsByStage($this->filters);
        $sheets[] = new OloTableSheet(
            'PO vs TEUs - Etapa',
            ['Etapa', 'PO', 'TEUs', '% PO', '% TEUs'],
            array_map(
                static fn (array $r) => [
                    (string) ($r['stage'] ?? ''),
                    (int) ($r['po_count'] ?? 0),
                    (float) ($r['teus'] ?? 0),
                    (float) ($r['percentage'] ?? 0),
                    (float) ($r['teus_percentage'] ?? 0),
                ],
                (array) ($povsteusStage['data'] ?? [])
            )
        );

        $povsteusPeriod = $kpi->getPOvsTEUsByPeriod($this->filters);
        $sheets[] = new OloTableSheet(
            'PO vs TEUs - Período',
            ['Período', 'PO', 'TEUs'],
            array_map(
                static fn (array $r) => [
                    (string) ($r['period'] ?? ''),
                    (int) ($r['po_count'] ?? 0),
                    (float) ($r['teus'] ?? 0),
                ],
                (array) ($povsteusPeriod['periods'] ?? [])
            )
        );

        $povsteusVendor = $kpi->getPOvsTEUsByVendor($this->filters);
        $sheets[] = new OloTableSheet(
            'PO vs TEUs - Proveedor',
            ['Proveedor', 'PO', 'TEUs'],
            array_map(
                static fn (array $r) => [
                    (string) ($r['vendor'] ?? ''),
                    (int) ($r['po_count'] ?? 0),
                    (float) ($r['teus'] ?? 0),
                ],
                (array) ($povsteusVendor['data'] ?? [])
            )
        );

        $povsteusLine = $kpi->getPOvsTEUsByShippingLine($this->filters);
        $sheets[] = new OloTableSheet(
            'PO vs TEUs - Naviera',
            ['Naviera', 'PO', 'TEUs'],
            array_map(
                static fn (array $r) => [
                    (string) ($r['shipping_line'] ?? ''),
                    (int) ($r['po_count'] ?? 0),
                    (float) ($r['teus'] ?? 0),
                ],
                (array) ($povsteusLine['data'] ?? [])
            )
        );

        // =========================
        // Hojas: Comparativo (2 períodos)
        // =========================
        [$p1s, $p1e, $p2s, $p2e] = $this->resolveComparisonPeriods($this->context);

        $compATD = $kpi->comparePOsWithATD($this->filters, $p1s, $p1e, $p2s, $p2e);
        $sheets[] = $this->sheetFromComparison('Comparativo ATD', $compATD);

        $compATA = $kpi->comparePOsWithATA($this->filters, $p1s, $p1e, $p2s, $p2e);
        $sheets[] = $this->sheetFromComparison('Comparativo ATA', $compATA);

        $compDelay = $kpi->comparePOsWithDelayCL($this->filters, $p1s, $p1e, $p2s, $p2e);
        $sheets[] = $this->sheetFromComparison('Comparativo Retraso', $compDelay);

        $compAdvance = $kpi->comparePOsWithAdvanceCL($this->filters, $p1s, $p1e, $p2s, $p2e);
        $sheets[] = $this->sheetFromComparison('Comparativo Adelanto', $compAdvance);

        // =========================
        // Hoja: Proyección
        // =========================
        $projectionFilters = $this->filters;
        if (!empty($this->context['projection_week'])) {
            $projectionFilters['projection_week'] = $this->context['projection_week'];
        }
        if (!empty($this->context['week_count'])) {
            $projectionFilters['week_count'] = $this->context['week_count'];
        }

        $future = $kpi->getFutureArrivals($projectionFilters);
        $weeks = (array) ($future['weeks'] ?? []);
        unset($weeks);
        $futureRows = [];
        foreach ((array) ($future['data'] ?? []) as $stageRow) {
            $stage = (string) ($stageRow['stage'] ?? '');
            $weekData = (array) ($stageRow['weeks'] ?? []);
            foreach ($weekData as $w) {
                $futureRows[] = [
                    $stage,
                    (string) ($w['week'] ?? ''),
                    (int) ($w['po_count'] ?? 0),
                    (float) ($w['teus'] ?? 0),
                ];
            }
        }
        $sheets[] = new OloTableSheet('Proyección', ['Etapa', 'Semana', 'PO', 'TEUs'], $futureRows);

        // =========================
        // Hoja legacy: Tendencia Etapas mensual (DashboardService)
        // =========================
        $trend = $legacyDashboard->getCanceledLinesTrendTable($this->filters);
        $monthKeys = (array) ($trend['month_keys'] ?? []);
        $monthLabelsByKey = (array) ($trend['month_labels'] ?? []);

        $headings = array_merge(['Etapa'], array_map(
            static fn (string $k) => (string) ($monthLabelsByKey[$k] ?? $k),
            $monthKeys
        ));

        $categoryOrder = ['Producción', 'Booking', 'Transito', 'Puerto', 'Recibiendo CDI'];
        $trendRows = [];
        foreach ($categoryOrder as $category) {
            $row = [$category];
            foreach ($monthKeys as $k) {
                $val = (int) (($trend['categories'][$category][$k] ?? 0));
                $row[] = $val === 0 ? '-' : $val;
            }
            $trendRows[] = $row;
        }

        $sheets[] = new OloTableSheet('Tendencia Mensual', $headings, $trendRows);

        return $sheets;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $context
     * @return array<string, string>
     */
    private function normalizeFiltersForDisplay(array $filters, array $context): array
    {
        $labels = [
            'date_from' => 'Fecha inicio',
            'date_to' => 'Fecha fin',
            'trading_company' => 'Cliente',
            'stage' => 'Etapa',
            'vendor_id' => 'Proveedor de Mercancía',
            'service_provider' => 'Proveedor de Servicio',
            'departure_port' => 'Puerto de Embarque',
            'arrival_port' => 'Puerto de Arribo',
            'shipping_line' => 'Naviera',
            'route_label' => 'Ruta Logística',
            'order_number' => 'Número de PO',
            'po_retraso_cl' => 'PO Retraso CL (botón)',
            'po_adelanto_cl' => 'PO Adelanto CL (botón)',
            'indicador_capacidad' => 'Indicador Capacidad (botón)',
            'pos_transbordo' => 'PO en Transbordo (botón)',
            'pos_ata' => 'PO con ATA (botón)',
        ];

        $result = [];
        foreach ($labels as $key => $label) {
            if (!array_key_exists($key, $filters)) {
                continue;
            }
            $val = $filters[$key];
            if ($val === null || $val === '' || $val === []) {
                continue;
            }
            if (is_bool($val)) {
                $result[$label] = $val ? 'Sí' : 'No';
                continue;
            }
            $result[$label] = is_array($val) ? implode(', ', array_map('strval', $val)) : (string) $val;
        }

        // Contexto extra (tabs / botones / períodos)
        $extras = [
            'active_view' => 'Vista activa',
            'active_subtab' => 'Subtab activa',
            'comparison_period_1' => 'Comparativo - Período A',
            'comparison_period_2' => 'Comparativo - Período B',
            'projection_week' => 'Proyección - Semana desde',
            'week_count' => 'Proyección - Semanas',
        ];
        foreach ($extras as $key => $label) {
            $val = $context[$key] ?? null;
            if ($val === null || $val === '') {
                continue;
            }
            if (is_string($val)) {
                $result[$label] = $val;
                continue;
            }
            $encoded = json_encode($val, JSON_UNESCAPED_UNICODE);
            $result[$label] = $encoded !== false ? $encoded : (string) $val;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, array<int, string>> $sectionHeadings
     */
    private function sheetFromSectionedData(string $title, array $data, array $sectionHeadings): OloTableSheet
    {
        $rows = [];

        foreach ($sectionHeadings as $sectionKey => $headings) {
            $rows[] = [$sectionKey];
            $rows[] = $headings;

            $section = $data[$sectionKey] ?? null;
            if (is_array($section) && $sectionKey === 'summary') {
                foreach ($section as $k => $v) {
                    $rows[] = [(string) $k, is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v];
                }
            } elseif (is_array($section)) {
                foreach ($section as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $rows[] = array_values($item);
                }
            }

            $rows[] = [];
        }

        // Normalizar a grilla rectangular (misma cantidad de columnas en todas las filas)
        $maxCols = 1;
        foreach ($rows as $r) {
            $maxCols = max($maxCols, is_array($r) ? count($r) : 1);
        }

        $padded = [];
        foreach ($rows as $r) {
            $r = is_array($r) ? $r : [(string) $r];
            while (count($r) < $maxCols) {
                $r[] = '';
            }
            $padded[] = $r;
        }

        $headings = array_fill(0, $maxCols, '');
        $headings[0] = 'Sección / Campo';
        if ($maxCols > 1) {
            $headings[1] = 'Valor';
        }

        return new OloTableSheet($title, $headings, $padded);
    }

    private function sheetFromComparison(string $title, array $data): OloTableSheet
    {
        $rows = [];
        $p1 = $data['period1'] ?? [];
        $p2 = $data['period2'] ?? [];

        $rows[] = ['Período A', ($p1['start'] ?? '') . ' → ' . ($p1['end'] ?? '')];
        $rows[] = ['Total A', (string) ($p1['total'] ?? '')];
        $rows[] = ['Período B', ($p2['start'] ?? '') . ' → ' . ($p2['end'] ?? '')];
        $rows[] = ['Total B', (string) ($p2['total'] ?? '')];
        $rows[] = [''];
        $rows[] = ['Proveedor', 'A (PO)', 'B (PO)', 'A (TEUs)', 'B (TEUs)', 'Var %'];

        foreach ((array) ($data['by_vendor'] ?? []) as $r) {
            if (!is_array($r)) {
                continue;
            }
            $rows[] = [
                (string) ($r['vendor'] ?? ''),
                (int) ($r['period1_count'] ?? 0),
                (int) ($r['period2_count'] ?? 0),
                (float) ($r['period1_teus'] ?? 0),
                (float) ($r['period2_teus'] ?? 0),
                (float) ($r['variation_percentage'] ?? 0),
            ];
        }

        return new OloTableSheet($title, ['Campo', 'Valor', '', '', '', ''], $rows);
    }

    /**
     * @param array<string, mixed> $context
     * @return array{0:string,1:string,2:string,3:string}
     */
    private function resolveComparisonPeriods(array $context): array
    {
        $p1s = (string) ($context['period1_start'] ?? '');
        $p1e = (string) ($context['period1_end'] ?? '');
        $p2s = (string) ($context['period2_start'] ?? '');
        $p2e = (string) ($context['period2_end'] ?? '');

        if ($p1s !== '' && $p1e !== '' && $p2s !== '' && $p2e !== '') {
            return [$p1s, $p1e, $p2s, $p2e];
        }

        // Default: mes anterior vs mes actual (como UI)
        $now = Carbon::now(config('app.timezone'));
        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth()->format('Y-m-d');
        $currentMonthStart = $now->copy()->startOfMonth()->format('Y-m-d');
        $currentMonthEnd = $now->copy()->endOfMonth()->format('Y-m-d');

        return [$lastMonthStart, $lastMonthEnd, $currentMonthStart, $currentMonthEnd];
    }
}

