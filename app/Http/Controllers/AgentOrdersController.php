<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\DashboardKPIService;

class AgentOrdersController extends Controller
{
    private function getCompanyId(Request $request): int
    {
        return (int) $request->header('X-Company-Id', 0);
    }

    public function orders(Request $request): JsonResponse
    {
        $companyId      = $this->getCompanyId($request);
        $status         = $request->query('status');
        $flag           = $request->query('flag');
        $tradingCompany = $request->query('trading_company');
        $shippingLine   = $request->query('shipping_line');
        $vendorName     = $request->query('vendor_name');

        $query = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->where('status', '!=', 'cancelled');

        if ($tradingCompany) $query->where('trading_company', $tradingCompany);
        if ($shippingLine)   $query->where('shipping_line', $shippingLine);
        if ($vendorName) {
            $query->whereExists(function ($sub) use ($vendorName) {
                $sub->select(DB::raw(1))
                    ->from('vendors')
                    ->whereColumn('vendors.id', 'purchase_orders.vendor_id')
                    ->where('vendors.name', $vendorName);
            });
        }

        if ($status === 'in_transit') {
            $query->whereNotNull('porth_phase')
                  ->whereIn('porth_phase', ['40_in_transit', '30_in_transit', 'in_transit', 'shipped', 'on_vessel']);

            $count  = (clone $query)->count();
            $sample = (clone $query)->select('order_number', 'porth_first_eta', 'porth_phase')->limit(5)->get();

            return response()->json(['total' => $count, 'sample' => $sample, 'message' => "Hay $count órdenes en tránsito."]);
        }

        if ($status === 'pending_confirmation') {
            $query->where('status', 'pending');
            $count  = (clone $query)->count();
            $orders = (clone $query)->select('order_number', 'status', 'order_date', 'vendor_id')->orderBy('order_date', 'asc')->limit(10)->get();
            return response()->json(['total' => $count, 'orders' => $orders, 'message' => "Hay $count órdenes pendientes de confirmar."]);
        }

        if ($flag === 'alert') {
            $alertQuery = $query->where(function ($q) {
                $q->where('arrival_status', 'delayed')
                  ->orWhere('arrival_status', 'Atrasado')
                  ->orWhere('delay_days', '>', 0)
                  ->orWhere('porth_priority', 'high');
            });

            $total = (clone $alertQuery)->count();
            $all   = $request->query('all') === 'true';

            $ordersQuery = (clone $alertQuery)
                ->select('order_number', 'arrival_status', 'delay_days', 'porth_first_eta', 'porth_phase', 'trading_company', 'shipping_line')
                ->orderBy('delay_days', 'desc');

            if (!$all) $ordersQuery->limit(10);
            $orders = $ordersQuery->get();

            $filterLabel = '';
            if ($tradingCompany) $filterLabel = " de $tradingCompany";
            if ($shippingLine)   $filterLabel = " de $shippingLine";
            if ($vendorName)     $filterLabel = " de $vendorName";

            return response()->json([
                'total'   => $total,
                'orders'  => $orders,
                'message' => $all
                    ? "$total órdenes{$filterLabel} tienen alertas activas. Listado completo devuelto."
                    : "$total órdenes{$filterLabel} tienen alertas activas. Se muestran las 10 más críticas.",
            ]);
        }

        $total   = (clone $query)->count();
        $delayed = (clone $query)->where(function ($q) { $q->where('arrival_status', 'delayed')->orWhere('arrival_status', 'Atrasado'); })->count();
        $withAta = (clone $query)->whereNotNull('date_ata')->count();

        return response()->json([
            'total_active' => $total,
            'delayed'      => $delayed,
            'with_ata'     => $withAta,
            'message'      => "Resumen: $total órdenes activas, $delayed con retraso, $withAta con ATA confirmado.",
        ]);
    }

    public function ordersByAta(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $date      = $request->query('date');
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');

        $query = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereNotNull('date_ata');

        if ($date) {
            // Fecha exacta
            $query->whereDate('date_ata', $date);
            $label = "el $date";
        } elseif ($dateFrom && $dateTo) {
            // Rango de fechas
            $query->whereBetween('date_ata', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            $label = "entre $dateFrom y $dateTo";
        } elseif ($dateFrom) {
            $query->whereDate('date_ata', '>=', $dateFrom);
            $label = "desde $dateFrom";
        } else {
            return response()->json(['error' => 'Necesito una fecha para buscar por ATA.'], 422);
        }

        $orders = $query->select('order_number', 'date_ata', 'porth_phase', 'arrival_status', 'delay_days', 'trading_company', 'shipping_line')->get();

        return response()->json([
            'total'   => $orders->count(),
            'orders'  => $orders,
            'message' => $orders->count() . " órdenes con ATA $label.",
        ]);
    }

    public function ordersByAtd(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $date      = $request->query('date');
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');

        $query = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereNotNull('date_atd');

        if ($date) {
            $query->whereDate('date_atd', $date);
            $label = "el $date";
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('date_atd', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            $label = "entre $dateFrom y $dateTo";
        } elseif ($dateFrom) {
            $query->whereDate('date_atd', '>=', $dateFrom);
            $label = "desde $dateFrom";
        } else {
            return response()->json(['error' => 'Necesito una fecha para buscar por ATD.'], 422);
        }

        $orders = $query->select('order_number', 'date_atd', 'porth_phase', 'arrival_status', 'delay_days', 'trading_company', 'shipping_line')->get();

        return response()->json([
            'total'   => $orders->count(),
            'orders'  => $orders,
            'message' => $orders->count() . " órdenes con ATD $label.",
        ]);
    }

    public function ordersByEta(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');
        $month     = $request->query('month');
        $year      = $request->query('year');

        $query = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('porth_first_eta');

        if ($month && $year) {
            $query->whereMonth('porth_first_eta', (int)$month)->whereYear('porth_first_eta', (int)$year);
            $label = "en {$month}/{$year}";
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('porth_first_eta', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            $label = "entre $dateFrom y $dateTo";
        } elseif ($dateFrom) {
            $query->whereDate('porth_first_eta', $dateFrom);
            $label = "el $dateFrom";
        } else {
            return response()->json(['error' => 'Necesito al menos una fecha o mes/año para buscar por ETA.'], 422);
        }

        $orders = $query->select('order_number', 'porth_first_eta', 'porth_phase', 'arrival_status', 'delay_days')->orderBy('porth_first_eta', 'asc')->get();

        return response()->json([
            'total'   => $orders->count(),
            'orders'  => $orders,
            'message' => $orders->count() . " órdenes con ETA $label.",
        ]);
    }

    public function shipments(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $status    = $request->query('status');

        if ($status === 'transshipment') {
            try {
                $kpiService = app(DashboardKPIService::class);
                $result     = $kpiService->getPOsInTransshipment(['include_details' => true]);
                $total      = $result['summary']['total_pos'] ?? 0;

                if ($total > 0) {
                    $byPort       = $result['by_port'] ?? [];
                    $portsSummary = collect($byPort)->map(fn($p) => $p['port_label'] ?? $p['port'])->implode(', ');
                    return response()->json([
                        'total'     => $total,
                        'by_port'   => $byPort,
                        'details'   => $result['details'] ?? [],
                        'has_ports' => count($byPort) > 0,
                        'message'   => "$total órdenes en transbordo en los puertos: $portsSummary.",
                    ]);
                }
            } catch (\Exception $e) {}

            $orders = DB::table('purchase_orders')
                ->where('company_id', $companyId)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('porth_phase', 'transshipment')->orWhere('porth_phase', 'in_transshipment')->orWhere('porth_phase', '20_transshipment');
                })
                ->select('order_number', 'porth_phase', 'arrival_port', 'departure_port', 'porth_pol_name', 'porth_pod_name', 'shipping_line', 'arrival_status', 'delay_days')
                ->get();

            $count    = $orders->count();
            $hasPorts = $orders->filter(fn($o) => !empty($o->arrival_port) || !empty($o->porth_pol_name) || !empty($o->porth_pod_name))->count() > 0;

            return response()->json([
                'total'     => $count,
                'orders'    => $orders,
                'has_ports' => $hasPorts,
                'message'   => $hasPorts
                    ? "$count órdenes en transbordo con información de puertos disponible."
                    : "$count órdenes en transbordo. La información de puertos específicos no está disponible en este momento.",
            ]);
        }

        return response()->json(['error' => 'Parámetro status no reconocido.'], 422);
    }

    public function shipmentById(Request $request, string $id): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $query     = DB::table('shipping_documents')->where('company_id', $companyId)->where('document_number', 'like', "%$id%");

        if (is_numeric($id)) {
            $query->orWhere(function ($q) use ($id, $companyId) {
                $q->where('company_id', $companyId)->where('id', (int) $id);
            });
        }

        $shipment = $query->select('document_number', 'porth_first_eta', 'porth_phase', 'estimated_arrival_date', 'actual_arrival_date', 'estimated_departure_date', 'actual_departure_date', 'arrival_port', 'departure_port', 'arrival_status')->first();

        if (!$shipment) return response()->json(['error' => "No encontré el embarque $id."], 404);

        return response()->json(['shipment' => $shipment, 'message' => "Embarque $id encontrado."]);
    }

    public function ordersDelayedInTransit(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $orders    = DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->whereIn('porth_phase', ['40_in_transit', '30_in_transit', 'in_transit', 'shipped', 'on_vessel'])
            ->where(function ($q) { $q->where('arrival_status', 'delayed')->orWhere('arrival_status', 'Atrasado')->orWhere('delay_days', '>', 0); })
            ->select('order_number', 'arrival_status', 'delay_days', 'porth_first_eta', 'porth_phase')
            ->get();

        return response()->json(['total' => $orders->count(), 'orders' => $orders, 'message' => $orders->count() . ' órdenes retrasadas en tránsito.']);
    }

    public function teusSummary(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $status    = $request->query('status');

        $query = DB::table('purchase_orders')->where('company_id', $companyId)->where('status', '!=', 'cancelled')->whereNotNull('container_type');

        if ($status === 'in_transit') {
            $query->whereIn('porth_phase', ['40_in_transit', '30_in_transit', 'in_transit', 'shipped', 'on_vessel']);
        }

        $orders    = $query->select('order_number', 'container_type', 'porth_phase')->get();
        $totalTeus = 0;
        $breakdown = [];

        foreach ($orders as $order) {
            $ct   = strtolower($order->container_type ?? '');
            $teus = (str_contains($ct, '40') || str_contains($ct, 'hc') || str_contains($ct, 'hq')) ? 2 : (str_contains($ct, '45') ? 2.25 : 1);
            $totalTeus += $teus;
            $breakdown[$order->container_type] = ($breakdown[$order->container_type] ?? 0) + $teus;
        }

        $label = $status === 'in_transit' ? 'en tránsito' : 'activas';
        return response()->json(['total_teus' => round($totalTeus, 2), 'total_orders' => $orders->count(), 'breakdown' => $breakdown, 'message' => "Tienes $totalTeus TEUs en " . $orders->count() . " órdenes $label."]);
    }

    public function fullSummary(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId($request);
        $base      = DB::table('purchase_orders')->where('company_id', $companyId)->where('status', '!=', 'cancelled');

        $totalActive     = (clone $base)->count();
        $delayed         = (clone $base)->where(function ($q) { $q->where('arrival_status', 'delayed')->orWhere('arrival_status', 'Atrasado'); })->count();
        $withAta         = (clone $base)->whereNotNull('date_ata')->count();
        $inTransit       = (clone $base)->whereIn('porth_phase', ['40_in_transit', '30_in_transit', 'in_transit', 'shipped', 'on_vessel'])->count();
        $inTransshipment = 0;

        try {
            $result          = app(DashboardKPIService::class)->getPOsInTransshipment();
            $inTransshipment = $result['summary']['total_pos'] ?? 0;
        } catch (\Exception $e) {}

        if ($inTransshipment === 0) {
            $inTransshipment = (clone $base)->whereIn('porth_phase', ['transshipment', 'in_transshipment', '20_transshipment'])->count();
        }

        $orders    = (clone $base)->whereNotNull('container_type')->select('container_type')->get();
        $totalTeus = 0;
        foreach ($orders as $order) {
            $ct = strtolower($order->container_type ?? '');
            $totalTeus += (str_contains($ct, '40') || str_contains($ct, 'hc') || str_contains($ct, 'hq')) ? 2 : (str_contains($ct, '45') ? 2.25 : 1);
        }

        $totalTeus = round($totalTeus, 2);
        return response()->json([
            'total_active'     => $totalActive,
            'delayed'          => $delayed,
            'with_ata'         => $withAta,
            'in_transit'       => $inTransit,
            'in_transshipment' => $inTransshipment,
            'total_teus'       => $totalTeus,
            'message'          => "Resumen completo: $totalActive órdenes activas, $delayed retrasadas, $withAta con ATA confirmado, $inTransit en tránsito, $inTransshipment en transbordo y $totalTeus TEUs totales.",
        ]);
    }
}