<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ForecastService
{
    /**
     * Get forecast metrics
     *
     * @param array $filters
     * @return array
     */
    public function getMetrics(array $filters): array
    {
        try {
            Log::info('ForecastService::getMetrics starting', ['filters' => $filters]);

            $companyId = auth()->user()->company_id ?? null;
            Log::info('User company ID for forecast', ['company_id' => $companyId]);

            // Get total amount using user's exact query
            $totalAmount = $this->getTotalAmount($filters, $companyId);

            $result = [
                'total_amount' => $totalAmount,
            ];

            Log::info('ForecastService::getMetrics completed successfully', $result);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in ForecastService::getMetrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'total_amount' => 0,
            ];
        }
    }

    /**
     * Get total amount - Using user's exact query
     *
     * @param array $filters
     * @param int|null $companyId
     * @return float
     */
    private function getTotalAmount(array $filters, ?int $companyId): float
    {
        try {
            Log::info('Getting total amount...');

            // Using the user's exact query structure
            $query = DB::table('purchase_order_product as pp')
                ->join('purchase_orders as po', 'pp.purchase_order_id', '=', 'po.id')
                ->selectRaw('SUM(pp.quantity * pp.unit_price) AS total_amount');

            // Apply company filter
            if ($companyId) {
                $query->where('po.company_id', $companyId);
            }

            // Apply additional filters
            if (!empty($filters['date_from'])) {
                $query->where('po.order_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('po.order_date', '<=', $filters['date_to']);
            }

            if (!empty($filters['vendor_id'])) {
                $vendorIds = is_array($filters['vendor_id']) ? $filters['vendor_id'] : [$filters['vendor_id']];
                $vendorIds = array_filter($vendorIds);
                if (!empty($vendorIds)) {
                    $query->whereIn('po.vendor_id', $vendorIds);
                }
            }

            if (!empty($filters['product_id'])) {
                $productIds = is_array($filters['product_id']) ? $filters['product_id'] : [$filters['product_id']];
                $productIds = array_filter($productIds);
                if (!empty($productIds)) {
                    $query->whereIn('pp.product_id', $productIds);
                }
            }

            $result = $query->first();
            $totalAmount = (float)($result->total_amount ?? 0);

            Log::info('Total amount retrieved', ['amount' => $totalAmount]);
            return $totalAmount;
        } catch (\Exception $e) {
            Log::error('Error in getTotalAmount', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            Log::info('ForecastService::getChartsData starting');

            $result = [
                'material_deviation' => $this->getMaterialDeviation($filters),
                'monthly_kgs' => $this->getMonthlyKgs($filters),
                'vendor_deviation' => $this->getVendorDeviation($filters),
            ];

            Log::info('ForecastService::getChartsData completed successfully');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in ForecastService::getChartsData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'material_deviation' => [],
                'monthly_kgs' => [],
                'vendor_deviation' => [],
            ];
        }
    }

    /**
     * Get forecast table data - Using user's exact query
     *
     * @param array $filters
     * @return Collection
     */
    public function getForecastTableData(array $filters): Collection
    {
        try {
            Log::info('Getting forecast table data...');

            $companyId = auth()->user()->company_id ?? null;

            // Using the user's exact query structure (but adapted since date_atd is NULL)
            $query = DB::table('forecasts as f')
                ->leftJoin('purchase_order_product as pp', function ($join) {
                    $join->on('pp.product_id', '=', DB::raw('CAST(f.material AS BIGINT)'));
                })
                ->leftJoin('purchase_orders as po', function ($join) {
                    $join->on('pp.purchase_order_id', '=', 'po.id')
                         ->on(DB::raw("to_char(po.order_date, 'YYYY-MM')"), '=', DB::raw("to_char(f.delivery_date, 'YYYY-MM')"));
                })
                ->selectRaw('
                    f.material                                   AS material,
                    SUM(f.quantity_requested)                    AS forecast_kg,
                    COALESCE(SUM(pp.quantity), 0)                AS actual_kg,
                    COALESCE(SUM(pp.quantity), 0) - SUM(f.quantity_requested) AS deviation_kg
                ')
                ->groupBy('f.material');

            // Apply company filter if we have it
            if ($companyId) {
                $query->where(function ($q) use ($companyId) {
                    $q->whereNull('po.company_id')
                      ->orWhere('po.company_id', $companyId);
                });
            }

            $result = $query->get();

            $collection = $result->map(function ($item) {
                return [
                    'material' => $item->material,
                    'forecast_kg' => number_format((float)($item->forecast_kg ?? 0), 2),
                    'actual_kg' => number_format((float)($item->actual_kg ?? 0), 2),
                    'deviation_kg' => number_format((float)($item->deviation_kg ?? 0), 2),
                ];
            });

            Log::info('Forecast table data retrieved', ['count' => $collection->count()]);
            return $collection;
        } catch (\Exception $e) {
            Log::error('Error in getForecastTableData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get material deviation data - Using user's exact query
     *
     * @param array $filters
     * @return Collection
     */
    private function getMaterialDeviation(array $filters): Collection
    {
        try {
            Log::info('Getting material deviation...');

            // Using the user's exact query structure
            $query = DB::table(DB::raw('(
                SELECT
                    f.material                                   AS material,
                    SUM(f.quantity_requested)                    AS forecast_kg,
                    COALESCE(SUM(pp.quantity), 0)                AS actual_kg,
                    COALESCE(SUM(pp.quantity), 0) - SUM(f.quantity_requested) AS deviation_kg
                FROM forecasts f
                LEFT JOIN purchase_order_product pp
                    ON pp.product_id = CAST(f.material AS BIGINT)
                LEFT JOIN purchase_orders po
                    ON pp.purchase_order_id = po.id
                    AND to_char(po.order_date, \'YYYY-MM\') = to_char(f.delivery_date, \'YYYY-MM\')
                GROUP BY f.material
            ) AS t'))
                ->select('material', 'deviation_kg')
                ->whereRaw('deviation_kg <> 0')
                ->orderByRaw('ABS(deviation_kg) DESC');

            $result = $query->get();

            $collection = $result->map(function ($item) {
                return [
                    'name' => $item->material,
                    'value' => abs((float)$item->deviation_kg),
                    'deviation' => (float)$item->deviation_kg,
                ];
            });

            Log::info('Material deviation retrieved', ['count' => $collection->count()]);
            return $collection;
        } catch (\Exception $e) {
            Log::error('Error in getMaterialDeviation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get monthly kgs data - Using user's exact query (adapted for order_date)
     *
     * @param array $filters
     * @return Collection
     */
    private function getMonthlyKgs(array $filters): Collection
    {
        try {
            Log::info('Getting monthly kgs...');

            $companyId = auth()->user()->company_id ?? null;

            // Using the user's exact query structure but with order_date since date_atd is NULL
            $query = DB::table('purchase_orders as po')
                ->join('purchase_order_product as pp', 'pp.purchase_order_id', '=', 'po.id')
                ->selectRaw("
                    to_char(po.order_date, 'YYYY-MM') AS month,
                    SUM(pp.quantity)                 AS total_kgs
                ")
                ->whereNotNull('po.order_date')
                ->groupByRaw("to_char(po.order_date, 'YYYY-MM')")
                ->orderBy('month');

            // Apply company filter
            if ($companyId) {
                $query->where('po.company_id', $companyId);
            }

            // Apply additional filters
            if (!empty($filters['date_from'])) {
                $query->where('po.order_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('po.order_date', '<=', $filters['date_to']);
            }

            if (!empty($filters['vendor_id'])) {
                $vendorIds = is_array($filters['vendor_id']) ? $filters['vendor_id'] : [$filters['vendor_id']];
                $vendorIds = array_filter($vendorIds);
                if (!empty($vendorIds)) {
                    $query->whereIn('po.vendor_id', $vendorIds);
                }
            }

            if (!empty($filters['product_id'])) {
                $productIds = is_array($filters['product_id']) ? $filters['product_id'] : [$filters['product_id']];
                $productIds = array_filter($productIds);
                if (!empty($productIds)) {
                    $query->whereIn('pp.product_id', $productIds);
                }
            }

            $result = $query->get();

            $collection = $result->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total_kgs' => (float)($item->total_kgs ?? 0),
                ];
            });

            Log::info('Monthly kgs retrieved', ['count' => $collection->count()]);
            return $collection;
        } catch (\Exception $e) {
            Log::error('Error in getMonthlyKgs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }

    /**
     * Get vendor deviation data - Using user's exact query
     *
     * @param array $filters
     * @return Collection
     */
    private function getVendorDeviation(array $filters): Collection
    {
        try {
            Log::info('Getting vendor deviation...');

            $companyId = auth()->user()->company_id ?? null;

            // Using the user's exact query structure
            $query = DB::table('purchase_order_product as pp')
                ->join('purchase_orders as po', 'pp.purchase_order_id', '=', 'po.id')
                ->join('vendors as v', 'po.vendor_id', '=', 'v.id')
                ->selectRaw('
                    v.name AS vendor,
                    SUM(pp.quantity * pp.unit_price)                             AS vendor_amount,
                    100.0 * SUM(pp.quantity * pp.unit_price)
                        / (
                            SELECT SUM(pp2.quantity * pp2.unit_price)
                            FROM purchase_order_product pp2
                            JOIN purchase_orders po2 ON pp2.purchase_order_id = po2.id' .
                            ($companyId ? ' WHERE po2.company_id = ' . $companyId : '') . '
                        )                                                        AS pct_total
                ')
                ->groupBy('v.name')
                ->orderByDesc('vendor_amount');

            // Apply company filter
            if ($companyId) {
                $query->where('po.company_id', $companyId);
            }

            // Apply additional filters
            if (!empty($filters['date_from'])) {
                $query->where('po.order_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('po.order_date', '<=', $filters['date_to']);
            }

            if (!empty($filters['vendor_id'])) {
                $vendorIds = is_array($filters['vendor_id']) ? $filters['vendor_id'] : [$filters['vendor_id']];
                $vendorIds = array_filter($vendorIds);
                if (!empty($vendorIds)) {
                    $query->whereIn('po.vendor_id', $vendorIds);
                }
            }

            if (!empty($filters['product_id'])) {
                $productIds = is_array($filters['product_id']) ? $filters['product_id'] : [$filters['product_id']];
                $productIds = array_filter($productIds);
                if (!empty($productIds)) {
                    $query->whereIn('pp.product_id', $productIds);
                }
            }

            $result = $query->get();

            $collection = $result->map(function ($item) {
                return [
                    'name' => $item->vendor,
                    'value' => (float)($item->vendor_amount ?? 0),
                    'percentage' => round((float)($item->pct_total ?? 0), 1),
                ];
            });

            Log::info('Vendor deviation retrieved', ['count' => $collection->count()]);
            return $collection;
        } catch (\Exception $e) {
            Log::error('Error in getVendorDeviation', [
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
            Log::info('Getting forecast export data...');

            // Combine all data for export
            $forecastTable = $this->getForecastTableData($filters);
            $monthlyKgs = $this->getMonthlyKgs($filters);
            $vendorDeviation = $this->getVendorDeviation($filters);

            $exportData = collect([]);

            // Add forecast table data
            foreach ($forecastTable as $row) {
                $exportData->push([
                    $row['material'],
                    $row['forecast_kg'],
                    $row['actual_kg'],
                    $row['deviation_kg'],
                    '',
                    '',
                    ''
                ]);
            }

            Log::info('Forecast export data retrieved', ['count' => $exportData->count()]);
            return $exportData;
        } catch (\Exception $e) {
            Log::error('Error in getExportData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]);
        }
    }
}
