<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Hub;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get dashboard metrics
     *
     * @param array $filters
     * @return array
     */
    public function getMetrics(array $filters): array
    {
        try {
            Log::info('DashboardService::getMetrics starting', ['filters' => $filters]);

            // 1. Total POs
            Log::info('Getting total POs...');
            $totalPOs = $this->getBaseQuery($filters)->count();
            Log::info('Total POs retrieved', ['total' => $totalPOs]);

            // 2. % POs On-Time y Delayed
            Log::info('Getting on-time and delayed metrics...');
            $onTimeDelayedQuery = $this->getBaseQuery($filters)
                ->whereNotNull('date_ata')
                ->whereNotNull('date_required_in_destination');
                
            $totalWithDates = (clone $onTimeDelayedQuery)->count();
            $totalWithoutDates = $totalPOs - $totalWithDates;
            
            $result = $onTimeDelayedQuery
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN date_ata <= date_required_in_destination THEN 1 ELSE 0 END) as on_time_count,
                    SUM(CASE WHEN date_ata > date_required_in_destination THEN 1 ELSE 0 END) as delayed_count
                ')
                ->first();
                
            $total = $result->total ?? 0;
            $onTimeCount = $result->on_time_count ?? 0;
            $delayedCount = $result->delayed_count ?? 0;
            
            // Calculamos los porcentajes sobre el total de órdenes, no solo las que tienen fechas
            $onTimePercentage = $totalPOs > 0 ? round(($onTimeCount / $totalPOs) * 100, 1) : 0;
            $delayedPercentage = $totalPOs > 0 ? round(($delayedCount / $totalPOs) * 100, 1) : 0;
            
            Log::info('On-time/delayed metrics', [
                'total_pos' => $totalPOs,
                'total_with_dates' => $totalWithDates,
                'total_without_dates' => $totalWithoutDates,
                'on_time_count' => $onTimeCount,
                'delayed_count' => $delayedCount,
                'on_time_percentage' => $onTimePercentage,
                'delayed_percentage' => $delayedPercentage
            ]);

            // 3. Material count
            Log::info('Getting material count...');
            $materialCount = $this->getBaseQuery($filters)
                ->join('purchase_order_product', 'purchase_orders.id', '=', 'purchase_order_product.purchase_order_id')
                ->distinct('purchase_order_product.product_id')
                ->count('purchase_order_product.product_id');
            Log::info('Material count result', ['count' => $materialCount]);

            $result = [
                'total_pos' => $totalPOs,
                'on_time_percentage' => $onTimePercentage,
                'delayed_percentage' => $delayedPercentage,
                'material_count' => $materialCount,
            ];

            Log::info('DashboardService::getMetrics completed successfully', $result);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in DashboardService::getMetrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Return default values instead of throwing to prevent dashboard crash
            return [
                'total_pos' => 0,
                'on_time_percentage' => 0,
                'delayed_percentage' => 0,
                'material_count' => 0,
            ];
        }
    }

    /**
     * Get on-time delivery metrics
     *
     * @param array $filters
     * @return array
     */
    private function getOnTimeMetrics(array $filters): array
    {
        try {
            Log::info('Getting on-time metrics query...');

            $query = $this->getBaseQuery($filters)
                ->whereNotNull('date_ata')
                ->whereNotNull('date_eta')
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN date_ata <= date_eta THEN 1 ELSE 0 END) as on_time_count,
                    SUM(CASE WHEN date_ata > date_eta THEN 1 ELSE 0 END) as delayed_count
                ');

            Log::info('Executing on-time metrics query...');
            $result = $query->first();
            Log::info('On-time metrics raw result', [
                'total' => $result->total ?? null,
                'on_time_count' => $result->on_time_count ?? null,
                'delayed_count' => $result->delayed_count ?? null
            ]);

            $total = $result->total ?? 0;
            $onTimeCount = $result->on_time_count ?? 0;
            $delayedCount = $result->delayed_count ?? 0;

            return [
                'on_time_percentage' => $total > 0 ? round(($onTimeCount / $total) * 100, 1) : 0,
                'delayed_percentage' => $total > 0 ? round(($delayedCount / $total) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Error in getOnTimeMetrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'on_time_percentage' => 0,
                'delayed_percentage' => 0,
            ];
        }
    }

    /**
     * Get material count
     *
     * @param array $filters
     * @return int
     */
    private function getMaterialCount(array $filters): int
    {
        try {
            Log::info('Getting material count query...');

            $query = $this->getBaseQuery($filters)
                ->join('purchase_order_product', 'purchase_orders.id', '=', 'purchase_order_product.purchase_order_id')
                ->distinct('purchase_order_product.product_id');

            Log::info('Executing material count query...');
            $count = $query->count('purchase_order_product.product_id');
            Log::info('Material count result', ['count' => $count]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Error in getMaterialCount', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Return 0 instead of throwing to not break the whole dashboard
            return 0;
        }
    }

    /**
     * Get charts data
     *
     * @param array $filters
     * @return array
     */
    public function getChartsData(array $filters): array
    {
        try {
            Log::info('DashboardService::getChartsData starting');

            $result = [
                'hub_distribution' => $this->getHubDistribution($filters),
                'delivery_status' => $this->getDeliveryStatus($filters),
                'transport_type' => $this->getTransportType($filters),
                'delay_reasons' => $this->getDelayReasons($filters),
                'pos_by_stage' => $this->getPosByStage($filters),
            ];

            Log::info('DashboardService::getChartsData completed successfully');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in DashboardService::getChartsData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getHubDistribution(array $filters): Collection
    {
        $filtersSinHub = $filters;
        unset($filtersSinHub['hub_id']);
        \Log::info('getHubDistribution - Filtros usados', $filtersSinHub);
        
        $baseQuery = $this->getBaseQuery($filtersSinHub);
        $total = (clone $baseQuery)->count();
        
        // Obtener TODOS los hubs existentes
        $allHubs = \App\Models\Hub::select('id', 'code', 'name')->get();
        
        // Agregar "Sin Hub" a la lista
        $allHubs->prepend((object)[
            'id' => 0,
            'code' => 'Sin Hub',
            'name' => 'Sin Hub'
        ]);
        
        // Obtener datos de POs agrupados por hub
        $query = $baseQuery
            ->leftJoin('hubs as h', 'purchase_orders.actual_hub_id', '=', 'h.id')
            ->selectRaw("COALESCE(h.code, 'Sin Hub') AS hub, COALESCE(h.id, 0) AS hub_id, COUNT(purchase_orders.id) AS total_pos")
            ->groupBy(DB::raw("COALESCE(h.code, 'Sin Hub')"), DB::raw("COALESCE(h.id, 0)"))
            ->orderBy('total_pos', 'desc');
            
        $rawResult = $query->get()->keyBy('hub_id');
        
        // Construir resultado con TODOS los hubs, incluso los que tienen 0 datos
        $result = $allHubs->map(function($hub) use ($rawResult, $total) {
            $data = $rawResult->get($hub->id);
            
            return [
                'name' => $hub->code,
                'id' => (int)$hub->id,
                'value' => $data ? (int)$data->total_pos : 0,
                'percentage' => $total > 0 && $data ? round(100.0 * $data->total_pos / $total, 1) : 0,
            ];
        })->sortByDesc('value')->values();
        

        
        return $result;
    }

    private function getDeliveryStatus(array $filters): Collection
    {
        $filtersSinStatus = $filters;
        unset($filtersSinStatus['status']);
        \Log::info('getDeliveryStatus - Filtros usados (SIN filtro status)', $filtersSinStatus);
        
        // Primero, obtenemos el total de órdenes con los filtros aplicados
        $baseQueryTotal = $this->getBaseQuery($filtersSinStatus);
        $totalOrders = (clone $baseQueryTotal)->count();
        
        // Luego, filtramos solo las que tienen las fechas necesarias para calcular el estado
        $baseQuery = (clone $baseQueryTotal)
            ->whereNotNull('date_ata')
            ->whereNotNull('date_required_in_destination');
        
        $totalWithDates = (clone $baseQuery)->count();
        $totalWithoutDates = $totalOrders - $totalWithDates;
        
        // NO aplicar filtro de estado aquí - calcular distribución total
        $query = (clone $baseQuery)
            ->selectRaw("CASE WHEN date_ata <= date_required_in_destination THEN 'On Time' ELSE 'Atrasado' END AS estado, COUNT(*) AS total_pos")
            ->groupBy(DB::raw("CASE WHEN date_ata <= date_required_in_destination THEN 'On Time' ELSE 'Atrasado' END"));
        $raw = $query->get();
        $resultByName = collect($raw)->keyBy('estado');
        
        // Definimos todos los estados posibles
        $allStatus = collect([
            (object)['name' => 'On Time', 'color' => '#565aff'],
            (object)['name' => 'Atrasado', 'color' => '#c9cfff'],
            (object)['name' => 'Sin datos', 'color' => '#f0f0f0'],
        ]);
        
        $result = $allStatus->map(function($cat) use ($resultByName, $totalOrders, $totalWithoutDates) {
            if ($cat->name === 'Sin datos') {
                // Para el estado "Sin datos", usamos el contador de órdenes sin fechas
                return [
                    'name' => $cat->name,
                    'value' => $totalWithoutDates,
                    'percentage' => $totalOrders > 0 ? round(100.0 * $totalWithoutDates / $totalOrders, 1) : 0,
                    'color' => $cat->color,
                    'values' => [$cat->name],
                ];
            } else {
                // Para los otros estados, usamos los resultados de la consulta
                $item = $resultByName->get($cat->name);
                return [
                    'name' => $cat->name,
                    'value' => $item ? (int)$item->total_pos : 0,
                    'percentage' => $totalOrders > 0 ? round(100.0 * ($item ? $item->total_pos : 0) / $totalOrders, 1) : 0,
                    'color' => $cat->color,
                    'values' => [$cat->name],
                ];
            }
        })->filter(function($item) {
            // Solo mostrar estados con valores > 0
            return $item['value'] > 0;
        })->values();
        
        return $result;
    }

    private function getTransportType(array $filters): Collection
    {
        $filtersSinMode = $filters;
        unset($filtersSinMode['transport']);
        \Log::info('getTransportType - Filtros usados', $filtersSinMode);
        $baseQuery = $this->getBaseQuery($filtersSinMode);
        $total = (clone $baseQuery)->count();
        $query = (clone $baseQuery)
            ->selectRaw('mode AS transport_mode, COUNT(*) AS total_pos')
            ->groupBy('mode');
        $raw = $query->get();
        // Agrupar solo por los valores válidos
        $counts = [ 'MARITIMO' => 0, 'AEREO' => 0, 'SIN ESPECIFICAR' => 0 ];
        foreach ($raw as $item) {
            $key = trim(strtolower($item->transport_mode ?? ''));
            if ($key === 'maritimo') $cat = 'MARITIMO';
            elseif ($key === 'aereo') $cat = 'AEREO';
            else $cat = 'SIN ESPECIFICAR';
            $counts[$cat] += (int)$item->total_pos;
        }
        $allTypes = collect([
            (object)['name' => 'MARITIMO', 'color' => '#565aff', 'values' => ['maritimo']],
            (object)['name' => 'AEREO', 'color' => '#ff3459', 'values' => ['aereo']],
            (object)['name' => 'SIN ESPECIFICAR', 'color' => '#c9cfff', 'values' => ['SIN_ESPECIFICAR']],
        ]);
        return $allTypes->map(function($cat) use ($counts, $total) {
            return [
                'name' => $cat->name,
                'value' => $counts[$cat->name],
                'percentage' => $total > 0 ? round(100.0 * $counts[$cat->name] / $total, 1) : 0,
                'color' => $cat->color,
                'values' => $cat->values,
            ];
        })->values(); // Mostrar TODOS los tipos de transporte, incluso con 0
    }

    private function getDelayReasons(array $filters): Collection
    {
        $filtersSinDelay = $filters;
        unset($filtersSinDelay['delay_reason']);
        \Log::info('getDelayReasons - Filtros usados', $filtersSinDelay);
        $baseQuery = $this->getBaseQuery($filtersSinDelay);
        $total = (clone $baseQuery)->count();

        // IDs de las órdenes filtradas
        $poIds = (clone $baseQuery)->pluck('id');
        if ($poIds->isEmpty()) {
            // Si no hay órdenes, devuelve solo "Sin motivo" en 100%
            return collect([
                [
                    'name' => 'Sin motivo',
                    'value' => 0,
                    'percentage' => 0,
                    'color' => '#c9cfff',
                ]
            ]);
        }

        // Extraer motivos de atraso de los comentarios de las órdenes filtradas
        $reasons = \DB::table('purchase_order_comments as poc')
            ->selectRaw(
                "regexp_replace(poc.comment, '(?i)^motivo de atraso\\s*[-–—]\\s*(.*)$', '\\1') as motivo"
            )
            ->whereIn('poc.purchase_order_id', $poIds)
            ->whereRaw("poc.comment ~* '^motivo de atraso\\s*[-–—]'")
            ->pluck('motivo');

        // Contar cada motivo
        $counts = collect($reasons)
            ->filter(fn($m) => trim($m) !== '')
            ->countBy();

        $sum_cnt = $counts->sum();
        $result = $counts->map(function($cnt, $motivo) use ($total) {
            return [
                'name' => $motivo,
                'value' => $cnt,
                'percentage' => $total > 0 ? round(100.0 * $cnt / $total, 1) : 0,
                'color' => '#565aff', // color fijo, puedes variar si quieres
            ];
        })->values();

        // Agregar "Sin motivo"
        $sinMotivo = [
            'name' => 'Sin motivo',
            'value' => $total - $sum_cnt,
            'percentage' => $total > 0 ? round(100.0 * ($total - $sum_cnt) / $total, 1) : 0,
            'color' => '#c9cfff',
        ];
        $result = $result->push($sinMotivo)->sortByDesc('percentage')->filter(function($item) {
            return $item['value'] > 0; // Solo mostrar items con datos
        })->values();

        return $result;
    }

    private function getPosByStage(array $filters): Collection
    {
        $filtersSinStage = $filters;
        unset($filtersSinStage['stage']);
        \Log::info('getPosByStage - Filtros usados para gráfico de etapas', $filtersSinStage);
        $baseQuery = $this->getBaseQuery($filtersSinStage);
        $total = (clone $baseQuery)->count();

        // Obtener todas las etapas del board 1
        $allStages = \App\Models\KanbanStatus::where('kanban_board_id', 1)
            ->orderBy('position')
            ->get(['id', 'name', 'color']);

        // Agrupar por etapa real de los POs filtrados (incluyendo las que no tienen etapa)
        $query = $baseQuery
            ->leftJoin('kanban_statuses as ks', 'purchase_orders.kanban_status_id', '=', 'ks.id')
            ->select(
                \DB::raw('COALESCE(ks.name, \'Sin etapa\') as stage'), 
                \DB::raw('COUNT(purchase_orders.id) as total_pos')
            )
            ->groupBy(\DB::raw('COALESCE(ks.name, \'Sin etapa\')'));
        $raw = $query->get()->keyBy('stage');

        // Construir resultado con todas las etapas posibles
        $result = $allStages->map(function($stage) use ($raw, $total) {
            $item = $raw->get($stage->name);
            return [
                'name' => $stage->name,
                'value' => $item ? (int)$item->total_pos : 0,
                'percentage' => $total > 0 ? round(100.0 * ($item ? $item->total_pos : 0) / $total, 1) : 0,
                'color' => $stage->color ?? '#c9cfff',
            ];
        });

        // Agregar la categoría "Sin etapa" si hay POs sin etapa
        $sinEtapaItem = $raw->get('Sin etapa');
        if ($sinEtapaItem && (int)$sinEtapaItem->total_pos > 0) {
            $result->push([
                'name' => 'Sin etapa',
                'value' => (int)$sinEtapaItem->total_pos,
                'percentage' => $total > 0 ? round(100.0 * $sinEtapaItem->total_pos / $total, 1) : 0,
                'color' => '#f0f0f0', // Color gris claro para "Sin etapa"
            ]);
        }

        return $result->sortByDesc('value')->values(); // Mostrar TODAS las etapas, incluso con 0
    }

    /**
     * Get detail table data - Show individual POs with order_number
     *
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function getDetailTableData(array $filters, int $limit = 50): Collection
    {
        try {
            Log::info('Getting detail table data...');

            $companyId = auth()->user()->company_id ?? null;
            Log::info('Company ID for detail table', ['company_id' => $companyId]);

            // --- USAR getBaseQuery PARA FILTROS CONSISTENTES ---
            $baseQuery = $this->getBaseQuery($filters);
            $query = $baseQuery
                ->leftJoin('purchase_order_product as pp', 'purchase_orders.id', '=', 'pp.purchase_order_id')
                ->selectRaw('
                    purchase_orders.order_number,
                    purchase_orders.order_date    AS dispatch_date,
                    purchase_orders.date_eta      AS eta,
                    COALESCE(SUM(pp.quantity), 0) AS total_kgs
                ')
                ->groupBy('purchase_orders.id', 'purchase_orders.order_number', 'purchase_orders.order_date', 'purchase_orders.date_eta')
                ->orderBy('purchase_orders.order_date')
                ->limit($limit);

            // Log the SQL query
            Log::info('Detail table SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $result = $query->get();
            Log::info('Detail table raw result', [
                'result_count' => $result->count(),
                'raw_data' => $result->toArray()
            ]);

            $collection = $result->map(function ($item) {
                Log::info('Processing detail table row', [
                    'order_number' => $item->order_number,
                    'dispatch_date' => $item->dispatch_date,
                    'eta' => $item->eta,
                    'total_kgs' => $item->total_kgs
                ]);

                return [
                    'po_number' => $item->order_number, // Show actual PO number instead of count
                    'fecha_salida' => $item->dispatch_date ? Carbon::parse($item->dispatch_date)->format('d/m/Y') : '-',
                    'fecha_estimada' => $item->eta ? Carbon::parse($item->eta)->format('d/m/Y') : '-',
                    'fecha_real' => '-', // Not used in this aggregated view
                    'cantidad_kg' => number_format((float)($item->total_kgs ?? 0), 2),
                ];
            });

            Log::info('Detail table data retrieved with individual POs', [
                'count' => $collection->count(),
                'sample' => $collection->take(3)->toArray()
            ]);

            return $collection;
        } catch (\Exception $e) {
            Log::error('Error in getDetailTableData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get export data
     *
     * @param array $filters
     * @return Collection
     */
    public function getExportData(array $filters): Collection
    {
        try {
            Log::info('Getting export data...');

            $result = $this->getBaseQuery($filters)
                ->with(['vendor', 'plannedHub', 'actualHub'])
                ->get()
                ->map(function ($po) {
                    return [
                        $po->order_number,
                        $po->date_atd ? Carbon::parse($po->date_atd)->format('d/m/Y') : '',
                        $po->date_eta ? Carbon::parse($po->date_eta)->format('d/m/Y') : '',
                        $po->date_ata ? Carbon::parse($po->date_ata)->format('d/m/Y') : '',
                        $po->weight_kg ?? 0,
                        $po->status ?? '',
                        $po->plannedHub->name ?? '',
                        $po->actualHub->name ?? '',
                        $po->vendor->name ?? '',
                        $po->mode ?? '',
                    ];
                });

            Log::info('Export data retrieved', ['count' => $result->count()]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getExportData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get filter options
     *
     * @return array
     */
    public function getFilterOptions(): array
    {
        try {
            Log::info('Getting filter options...');
            $companyId = auth()->user()->company_id ?? null;
            Log::info('User company ID', ['company_id' => $companyId]);

            Log::info('Getting products...');
            $products = Product::select('id', 'short_text as name', 'material_id')
                ->orderBy('short_text')
                ->get();
            Log::info('Products retrieved', ['count' => $products->count()]);

            Log::info('Getting hubs...');
            $hubs = Hub::select('id', 'name', 'code')
                ->orderBy('name')
                ->get();

            // Agregar la opción "Sin Hub" al inicio de la colección
            $hubs->prepend((object)[
                'id' => 0,
                'name' => 'Sin Hub',
                'code' => 'SIN_HUB'
            ]);

            Log::info('Hubs retrieved with Sin Hub option', ['count' => $hubs->count()]);

            Log::info('Getting vendors...');
            $vendors = Vendor::select('id', 'name')
                ->when($companyId, function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
                ->orderBy('name')
                ->get();
            Log::info('Vendors retrieved', ['count' => $vendors->count()]);

            Log::info('Getting materials...');
            $materials = $this->getMaterialOptions($companyId);
            Log::info('Materials retrieved', ['count' => $materials->count()]);

            $result = [
                'products' => $products,
                'hubs' => $hubs,
                'vendors' => $vendors,
                'materials' => $materials,
            ];

            Log::info('Filter options retrieved successfully');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getFilterOptions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get material options
     *
     * @param int|null $companyId
     * @return Collection
     */
    private function getMaterialOptions(?int $companyId): Collection
    {
        try {
            Log::info('Getting material options for company', ['company_id' => $companyId]);

            $purchaseOrders = PurchaseOrder::when($companyId, function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
                ->whereNotNull('material_type')
                ->get();

            $materialTypes = [];

            foreach ($purchaseOrders as $po) {
                $materialType = $po->material_type;

                if (!empty($materialType)) {
                    // Si es string, intentar decodificar JSON
                    if (is_string($materialType)) {
                        $decoded = json_decode($materialType, true);
                        if (is_array($decoded)) {
                            // Es un JSON válido
                            foreach ($decoded as $type) {
                                if (!empty($type) && is_string($type)) {
                                    $materialTypes[] = trim($type);
                                }
                            }
                        } else {
                            // Es un string simple
                            $materialTypes[] = trim($materialType);
                        }
                    } elseif (is_array($materialType)) {
                        // Ya es array
                        foreach ($materialType as $type) {
                            if (!empty($type) && is_string($type)) {
                                $materialTypes[] = trim($type);
                            }
                        }
                    }
                }
            }

            $result = collect(array_unique($materialTypes))->values()->sort();

            Log::info('Material options retrieved', ['count' => $result->count(), 'materials' => $result->toArray()]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in getMaterialOptions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get base query with filters applied
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBaseQuery(array $filters)
    {
        try {
            Log::info('getBaseQuery - Filtros recibidos:', $filters);
            $query = PurchaseOrder::query()
                ->with(['vendor', 'plannedHub', 'actualHub', 'products']);

            // Apply company filter for current user
            $companyId = auth()->user()->company_id ?? null;
            if ($companyId) {
                Log::info('Applying company filter', ['company_id' => $companyId]);
                $query->where('company_id', $companyId);
            } else {
                Log::warning('No company ID found for user', ['user_id' => auth()->id()]);
            }

            // Date filters
            if (!empty($filters['date_from'])) {
                Log::info('Applying date_from filter', ['date_from' => $filters['date_from']]);
                $query->where('order_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                Log::info('Applying date_to filter', ['date_to' => $filters['date_to']]);
                $query->where('order_date', '<=', $filters['date_to']);
            }

            // Product filter - Updated for multiple values
            if (!empty($filters['product_id'])) {
                Log::info('Applying product filter', ['product_id' => $filters['product_id']]);
                $productIds = is_array($filters['product_id']) ? $filters['product_id'] : [$filters['product_id']];
                $productIds = array_filter($productIds); // Remove empty values

                if (!empty($productIds)) {
                    $query->whereHas('products', function ($q) use ($productIds) {
                        $q->whereIn('product_id', $productIds);
                    });
                }
            }

            // Material type filter - CAST a texto para LIKE sobre json
            if (!empty($filters['material_type'])) {
                Log::info('Applying material_type filter', ['material_type' => $filters['material_type']]);
                $materialTypes = is_array($filters['material_type']) ? $filters['material_type'] : [$filters['material_type']];
                $materialTypes = array_filter($materialTypes); // Remove empty values

                if (!empty($materialTypes)) {
                    $query->where(function($q) use ($materialTypes) {
                        foreach ($materialTypes as $materialType) {
                            $q->orWhereRaw('material_type::text LIKE ?', ['%' . $materialType . '%']);
                        }
                    });
                }
            }

            // Hub filter - Updated for multiple values (actual_hub_id only)
            if (!empty($filters['hub_id'])) {
                Log::info('Applying hub filter', ['hub_id' => $filters['hub_id']]);
                
                $hubIds = is_array($filters['hub_id']) ? $filters['hub_id'] : [$filters['hub_id']];
                $hubIds = array_filter($hubIds, function($value) {
                    return $value !== null && $value !== '';
                }); // Remove empty values but keep 0

                if (!empty($hubIds)) {
                    $query->where(function ($q) use ($hubIds) {
                        $hasZero = in_array('0', $hubIds) || in_array(0, $hubIds);
                        $nonZeroHubIds = array_filter($hubIds, function($id) {
                            return $id != 0;
                        });



                        if ($hasZero) {
                            // Incluir registros sin hub (actual_hub_id es NULL)
                            $q->whereNull('actual_hub_id');
                        }

                        if (!empty($nonZeroHubIds)) {
                            // Incluir registros con hubs específicos (solo actual_hub_id)
                            if ($hasZero) {
                                $q->orWhereIn('actual_hub_id', $nonZeroHubIds);
                            } else {
                                $q->whereIn('actual_hub_id', $nonZeroHubIds);
                            }
                        }
                    });
                    Log::info('Applied hub filter (actual_hub_id only)', ['hub_ids' => $hubIds]);
                }
            }

            // Vendor filter - soporta múltiples valores
            if (!empty($filters['vendor_id'])) {
                Log::info('Applying vendor filter', ['vendor_id' => $filters['vendor_id']]);
                $vendorIds = is_array($filters['vendor_id']) ? $filters['vendor_id'] : [$filters['vendor_id']];
                $vendorIds = array_filter($vendorIds);
                if (count($vendorIds) > 1) {
                    $query->whereIn('vendor_id', $vendorIds);
                } else {
                    $query->where('vendor_id', $vendorIds[0]);
                }
            }

            // Status filter
            if (!empty($filters['status'])) {
                Log::info('Applying status filter', ['status' => $filters['status']]);
                $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
                $statuses = array_filter($statuses);
                $validCalculated = ['Atrasado', 'On Time']; // Invertido para que el filtro coincida correctamente
                if (count(array_intersect($statuses, $validCalculated)) > 0) {
                    $query->whereRaw(
                        "(CASE WHEN date_ata > date_required_in_destination THEN 'Atrasado' ELSE 'On Time' END) IN (" . implode(',', array_fill(0, count($statuses), '?')) . ")",
                        $statuses
                    );
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Transport filter
            if (!empty($filters['transport'])) {
                Log::info('Filtro transport recibido:', ['transport' => $filters['transport']]);
                $transports = is_array($filters['transport']) ? $filters['transport'] : [$filters['transport']];
                $transports = array_filter($transports);
                
                if (!empty($transports)) {
                    $query->where(function($q) use ($transports) {
                        foreach ($transports as $transport) {
                            if ($transport === 'SIN_ESPECIFICAR') {
                                $q->orWhereNull('mode')
                                  ->orWhere('mode', '');
                            } else {
                                $q->orWhere('mode', $transport);
                            }
                        }
                    });
                }
            }

            // Filtro por etapa (nombre de la etapa del kanban_status)
            if (!empty($filters['stage'])) {
                Log::info('Applying stage filter', ['stage' => $filters['stage']]);
                $stages = is_array($filters['stage']) ? $filters['stage'] : [$filters['stage']];
                $stages = array_filter($stages);
                if (!empty($stages)) {
                    $query->whereHas('kanbanStatus', function ($q) use ($stages) {
                        $q->whereIn('name', $stages);
                    });
                }
            }

            Log::info('getBaseQuery - SQL generado:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            return $query;
        } catch (\Exception $e) {
            Log::error('Error en getBaseQuery:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
