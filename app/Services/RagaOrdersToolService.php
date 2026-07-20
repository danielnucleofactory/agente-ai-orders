<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AgentOrdersController;
use App\Services\Agent\OperationalDataQueryService;
use Illuminate\Http\Request;

class RagaOrdersToolService
{
    private int $companyId;

    public function __construct()
    {
        $this->companyId = Auth::user()->company_id ?? 0;
    }

    public function execute(string $toolName, array $args): array
    {
        return match($toolName) {
            'get_orders_in_transit'           => $this->getOrdersInTransit(),
            'get_orders_in_transshipment'     => $this->getOrdersInTransshipment(),
            'get_orders_with_alerts'          => $this->getOrdersWithAlerts($args),
            'get_all_delayed_orders'          => $this->getAllDelayedOrders($args),
            'get_orders_by_ata'               => $this->getOrdersByAta($args),
            'get_orders_by_atd'               => $this->getOrdersByAtd($args),
            'get_orders_by_eta'               => $this->getOrdersByEta($args),
            'get_shipment_eta'                => $this->getShipmentEta($args['shipment_id'] ?? null),
            'get_orders_summary'              => $this->getOrdersSummary(),
            'get_orders_pending_confirmation' => $this->getOrdersPendingConfirmation(),
            'get_orders_delayed_in_transit'   => $this->getOrdersDelayedInTransit(),
            'get_teus_summary'                => $this->getTeusSummary($args['status'] ?? null),
            'get_full_summary'                => $this->getFullSummary(),
            'query_operational_data'          => $this->queryOperationalData($args),
            default                           => ['error' => 'Tool no reconocida: ' . $toolName],
        };
    }

    private function queryOperationalData(array $args): array
    {
        try {
            $service = app(OperationalDataQueryService::class);
            $result  = $service->run($args, $this->companyId);
            if (!$result['success']) return ['error' => $result['error']];
            return $result['data'];
        } catch (\Throwable $e) {
            Log::error('queryOperationalData exception', ['error' => $e->getMessage()]);
            return ['error' => 'Error al procesar la consulta operativa.'];
        }
    }

    private function call(string $method, array $query = []): array
    {
        try {
            $request = Request::create('/api/agent/' . $method, 'GET', $query);
            $request->headers->set('X-Company-Id', $this->companyId);
            $request->headers->set('X-Agent-Token', config('services.agent.api_token'));

            $controller = app(AgentOrdersController::class);
            $response   = match(true) {
                str_starts_with($method, 'shipments/') => $controller->shipmentById($request, substr($method, 10)),
                $method === 'shipments'                 => $controller->shipments($request),
                $method === 'orders/ata'                => $controller->ordersByAta($request),
                $method === 'orders/atd'                => $controller->ordersByAtd($request),
                $method === 'orders/eta'                => $controller->ordersByEta($request),
                $method === 'orders/delayed-in-transit' => $controller->ordersDelayedInTransit($request),
                $method === 'orders/teus'               => $controller->teusSummary($request),
                $method === 'orders/full-summary'       => $controller->fullSummary($request),
                default                                 => $controller->orders($request),
            };

            return $response->getData(true);
        } catch (\Exception $e) {
            Log::error('RagaOrdersToolService exception', ['method' => $method, 'error' => $e->getMessage()]);
            return ['error' => 'Error al consultar los datos.'];
        }
    }

    private function getOrdersInTransit(): array { return $this->call('orders', ['status' => 'in_transit']); }
    private function getOrdersInTransshipment(): array { return $this->call('shipments', ['status' => 'transshipment']); }

    private function getOrdersWithAlerts(array $args = []): array
    {
        $query = ['flag' => 'alert'];
        if (!empty($args['trading_company'])) $query['trading_company'] = $args['trading_company'];
        if (!empty($args['shipping_line']))   $query['shipping_line']   = $args['shipping_line'];
        if (!empty($args['vendor_name']))     $query['vendor_name']     = $args['vendor_name'];
        return $this->call('orders', $query);
    }

    private function getAllDelayedOrders(array $args = []): array
    {
        $query = ['flag' => 'alert', 'all' => 'true'];
        if (!empty($args['trading_company'])) $query['trading_company'] = $args['trading_company'];
        if (!empty($args['shipping_line']))   $query['shipping_line']   = $args['shipping_line'];
        if (!empty($args['vendor_name']))     $query['vendor_name']     = $args['vendor_name'];
        return $this->call('orders', $query);
    }

    private function getOrdersByAta(array $args): array
    {
        $query = [];
        if (!empty($args['date']))      $query['date']      = $args['date'];
        if (!empty($args['date_from'])) $query['date_from'] = $args['date_from'];
        if (!empty($args['date_to']))   $query['date_to']   = $args['date_to'];
        if (empty($query)) return ['error' => 'Necesito una fecha para buscar por ATA.'];
        return $this->call('orders/ata', $query);
    }

    private function getOrdersByAtd(array $args): array
    {
        $query = [];
        if (!empty($args['date']))      $query['date']      = $args['date'];
        if (!empty($args['date_from'])) $query['date_from'] = $args['date_from'];
        if (!empty($args['date_to']))   $query['date_to']   = $args['date_to'];
        if (empty($query)) return ['error' => 'Necesito una fecha para buscar por ATD.'];
        return $this->call('orders/atd', $query);
    }

    private function getOrdersByEta(array $args): array
    {
        $query = [];
        if (!empty($args['month']))     $query['month']     = $args['month'];
        if (!empty($args['year']))      $query['year']      = $args['year'];
        if (!empty($args['date_from'])) $query['date_from'] = $args['date_from'];
        if (!empty($args['date_to']))   $query['date_to']   = $args['date_to'];
        return $this->call('orders/eta', $query);
    }

    private function getShipmentEta(?string $shipmentId): array
    {
        if (!$shipmentId) return ['error' => 'Necesito el número del embarque.'];
        return $this->call('shipments/' . $shipmentId);
    }

    private function getOrdersSummary(): array { return $this->call('orders'); }
    private function getOrdersPendingConfirmation(): array { return $this->call('orders', ['status' => 'pending_confirmation']); }
    private function getOrdersDelayedInTransit(): array { return $this->call('orders/delayed-in-transit'); }
    private function getTeusSummary(?string $status): array { return $this->call('orders/teus', $status ? ['status' => $status] : []); }
    private function getFullSummary(): array { return $this->call('orders/full-summary'); }

    public function getToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_full_summary',
                    'description' => 'Resumen COMPLETO: total activas, retrasadas, ATA, en tránsito, transbordo y TEUs. USAR SIEMPRE para resumen general o múltiples métricas.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_in_transit',
                    'description' => 'Número de órdenes actualmente en tránsito.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_in_transshipment',
                    'description' => 'Órdenes en puerto de transbordo.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_with_alerts',
                    'description' => 'Total de órdenes con alertas o retrasos (delayed + Atrasado). Acepta filtros opcionales.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'trading_company' => ['type' => 'string'],
                            'shipping_line'   => ['type' => 'string'],
                            'vendor_name'     => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_all_delayed_orders',
                    'description' => 'LISTADO COMPLETO de órdenes atrasadas sin límite. Acepta filtros opcionales por cliente, naviera o proveedor.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'trading_company' => ['type' => 'string'],
                            'shipping_line'   => ['type' => 'string'],
                            'vendor_name'     => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_ata',
                    'description' => 'Busca órdenes por fecha real de llegada (ATA). Acepta fecha exacta o rango. USAR cuando el usuario quiera ver CUÁLES órdenes llegaron en una fecha o mes específico.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date'      => ['type' => 'string', 'description' => 'Fecha exacta YYYY-MM-DD.'],
                            'date_from' => ['type' => 'string', 'description' => 'Fecha inicio del rango YYYY-MM-DD.'],
                            'date_to'   => ['type' => 'string', 'description' => 'Fecha fin del rango YYYY-MM-DD.'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_atd',
                    'description' => 'Busca órdenes por fecha real de salida de origen (ATD). Acepta fecha exacta o rango. USAR cuando el usuario quiera ver CUÁLES órdenes salieron en una fecha o mes específico.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date'      => ['type' => 'string', 'description' => 'Fecha exacta YYYY-MM-DD.'],
                            'date_from' => ['type' => 'string', 'description' => 'Fecha inicio del rango YYYY-MM-DD.'],
                            'date_to'   => ['type' => 'string', 'description' => 'Fecha fin del rango YYYY-MM-DD.'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_eta',
                    'description' => 'Busca órdenes por fecha estimada de llegada (ETA). USAR SIEMPRE para próximos X días, semanas, meses.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'month'     => ['type' => 'string'],
                            'year'      => ['type' => 'string'],
                            'date_from' => ['type' => 'string'],
                            'date_to'   => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_shipment_eta',
                    'description' => 'ETA/ATA de un embarque DOC-XXXX específico.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => ['shipment_id' => ['type' => 'string']],
                        'required'   => ['shipment_id'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_summary',
                    'description' => 'Resumen básico: total activas, con retraso y con ATA.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_pending_confirmation',
                    'description' => 'Órdenes pendientes de confirmación.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_delayed_in_transit',
                    'description' => 'Órdenes retrasadas Y en tránsito simultáneamente.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_teus_summary',
                    'description' => 'Total general de TEUs activos sin filtros.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => ['status' => ['type' => 'string']],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'query_operational_data',
                    'description' => 'Métricas con agrupaciones y filtros. Usar para: distribución por semana, rankings, TEUs despachados, órdenes a tiempo, conteos por mes. NUNCA limit:1. group_by y sort son opcionales.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'entity'   => ['type' => 'string', 'enum' => ['purchase_orders']],
                            'metric'   => ['type' => 'string', 'enum' => ['count_orders', 'sum_teus', 'avg_delay_days', 'max_delay_days', 'sum_delay_days']],
                            'group_by' => ['type' => 'string', 'description' => 'Opcional: date_ata_week, date_eta_week, date_atd_week, shipping_line, vendor, trading_company, route_label, arrival_status, porth_phase, container_type'],
                            'filters'  => ['type' => 'object', 'description' => 'Filtros opcionales. Para between: {campo:{operator:between,value_from:FECHA,value_to:FECHA}}', 'properties' => new \stdClass()],
                            'sort'     => ['type' => 'object', 'description' => 'Opcional.', 'properties' => ['field' => ['type' => 'string'], 'direction' => ['type' => 'string']]],
                            'limit'    => ['type' => 'string', 'description' => 'Mínimo 30, máximo 100.'],
                        ],
                        'required' => ['entity', 'metric'],
                    ],
                ],
            ],
        ];
    }
}