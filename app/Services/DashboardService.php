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
     * Get canceled lines trend table data
     * 
     * @return array
     */
    public function getCanceledLinesTrendTable(): array
    {
        try {
            Log::info('DashboardService::getCanceledLinesTrendTable starting');

            $currentYear = now()->year;
            $companyId = auth()->user()->company_id ?? null;
            
            // Obtener POs anuladas (deleted_at IS NOT NULL) del año actual
            $query = PurchaseOrder::withTrashed()
                ->whereNotNull('deleted_at')
                ->whereYear('order_date', $currentYear);
            
            // Filtrar por company_id si el usuario tiene uno asignado
            if ($companyId) {
                $query->where('company_id', $companyId);
            }
            
            $canceledPOs = $query->with(['kanbanStatus'])->get();

            // Obtener nombres de etapas del kanban para mapeo
            // Filtrar por el kanban board de Purchase Orders de la compañía del usuario
            $kanbanStagesQuery = \App\Models\KanbanStatus::whereHas('board', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                      ->where(function($q) {
                          $q->where('type', 'po_stages')
                            ->orWhere('type', 'purchase_orders');
                      })
                      ->where('is_active', true);
            });
            
            // Si no hay company_id, usar el board_id 1 como fallback
            if (!$companyId) {
                $kanbanStagesQuery = \App\Models\KanbanStatus::where('kanban_board_id', 1);
            }
            
            $kanbanStages = $kanbanStagesQuery->pluck('name', 'id')->toArray();

            // Inicializar estructura de categorías con meses (claves como strings "1" a "12")
            $monthKeys = array_map('strval', range(1, 12));
            $categories = [
                'PO en Produccion' => array_fill_keys($monthKeys, 0),
                'Cumplimiento de Carga lista' => array_fill_keys($monthKeys, 0),
                'PO en booking' => array_fill_keys($monthKeys, 0),
                'PO en transito' => array_fill_keys($monthKeys, 0),
                'Allocation' => array_fill_keys($monthKeys, 0),
                'PO En puerto de transbordo' => array_fill_keys($monthKeys, 0),
                'Tiempo en puerto de transbordo' => array_fill_keys($monthKeys, 0),
                'PO con ETA' => array_fill_keys($monthKeys, 0),
            ];

            // Procesar cada PO anulada
            foreach ($canceledPOs as $po) {
                $month = (int) \Carbon\Carbon::parse($po->order_date)->format('n'); // 1-12
                $monthKey = (string)$month; // Clave como string
                $amountInThousands = ($po->total_amount ?? 0) / 1000;

                // Categorizar por etapa del kanban
                if ($po->kanban_status_id && isset($kanbanStages[$po->kanban_status_id])) {
                    $stageName = $kanbanStages[$po->kanban_status_id];
                    
                    // Mapear nombres de etapas a categorías
                    if (stripos($stageName, 'producción') !== false || stripos($stageName, 'produccion') !== false) {
                        $categories['PO en Produccion'][$monthKey] += $amountInThousands;
                    } elseif (stripos($stageName, 'booking') !== false) {
                        $categories['PO en booking'][$monthKey] += $amountInThousands;
                    } elseif (stripos($stageName, 'tránsito') !== false || stripos($stageName, 'transito') !== false) {
                        $categories['PO en transito'][$monthKey] += $amountInThousands;
                    } elseif (stripos($stageName, 'puerto') !== false || stripos($stageName, 'transbordo') !== false) {
                        $categories['PO En puerto de transbordo'][$monthKey] += $amountInThousands;
                    }
                }

                // PO con ETA (independiente de la etapa)
                if ($po->date_eta) {
                    $categories['PO con ETA'][$monthKey] += $amountInThousands;
                }
            }

            // Redondear valores y asegurar formato correcto
            $formattedCategories = [];
            foreach ($categories as $categoryName => $months) {
                $formattedCategories[$categoryName] = [];
                foreach ($months as $monthNum => $value) {
                    $formattedCategories[$categoryName][$monthNum] = round($value, 2);
                }
            }

            $result = [
                'categories' => $formattedCategories,
                'year' => $currentYear,
            ];

            Log::info('DashboardService::getCanceledLinesTrendTable completed', [
                'year' => $currentYear,
                'total_canceled_pos' => $canceledPOs->count()
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in DashboardService::getCanceledLinesTrendTable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Retornar estructura vacía en caso de error (con claves como strings)
            $emptyCategories = [];
            $categoryNames = [
                'PO en Produccion',
                'Cumplimiento de Carga lista',
                'PO en booking',
                'PO en transito',
                'Allocation',
                'PO En puerto de transbordo',
                'Tiempo en puerto de transbordo',
                'PO con ETA'
            ];
            foreach ($categoryNames as $catName) {
                $emptyCategories[$catName] = array_fill_keys(array_map('strval', range(1, 12)), 0);
            }
            
            return [
                'categories' => $emptyCategories,
                'year' => now()->year,
            ];
        }
    }


    /**
     * Get trend table export data formatted for CSV/Excel
     *
     * @return array
     */
    public function getTrendTableExportData(): array
    {
        try {
            Log::info('DashboardService::getTrendTableExportData starting');

            $trendData = $this->getCanceledLinesTrendTable();
            $categories = $trendData['categories'];
            $year = $trendData['year'];
            
            // Preparar datos para CSV/Excel
            $rows = [];
            
            // Header con meses
            $header = ['Descripción'];
            for ($month = 1; $month <= 12; $month++) {
                $header[] = "$month-$year";
            }
            $rows[] = $header;
            
            // Filas de datos
            $categoryOrder = [
                'PO en Produccion',
                'Cumplimiento de Carga lista',
                'PO en booking',
                'PO en transito',
                'Allocation',
                'PO En puerto de transbordo',
                'Tiempo en puerto de transbordo',
                'PO con ETA'
            ];
            
            foreach ($categoryOrder as $categoryName) {
                $row = [$categoryName];
                $categoryData = $categories[$categoryName] ?? [];
                
                for ($month = 1; $month <= 12; $month++) {
                    $value = $categoryData[(string)$month] ?? 0;
                    // Formatear con punto decimal y mostrar '-' si es 0
                    $row[] = $value === 0 ? '-' : number_format($value, 2, '.', '');
                }
                
                $rows[] = $row;
            }
            
            Log::info('DashboardService::getTrendTableExportData completed', [
                'rows_count' => count($rows),
                'year' => $year
            ]);

            return $rows;
        } catch (\Exception $e) {
            Log::error('Error in getTrendTableExportData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar estructura vacía en caso de error
            return [
                ['Descripción', '1-' . now()->year, '2-' . now()->year, '3-' . now()->year, '4-' . now()->year, 
                 '5-' . now()->year, '6-' . now()->year, '7-' . now()->year, '8-' . now()->year, 
                 '9-' . now()->year, '10-' . now()->year, '11-' . now()->year, '12-' . now()->year],
                ['PO en Produccion', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['Cumplimiento de Carga lista', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['PO en booking', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['PO en transito', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['Allocation', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['PO En puerto de transbordo', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['Tiempo en puerto de transbordo', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
                ['PO con ETA', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'],
            ];
        }
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
