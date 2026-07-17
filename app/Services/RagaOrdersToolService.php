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
            'get_orders_by_ata'               => $this->getOrdersByAta($args['date'] ?? null),
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

    // -----------------------------------------------------------------------
    // Tool generalista — query_operational_data
    // -----------------------------------------------------------------------

    private function queryOperationalData(array $args): array
    {
        try {
            $service = app(OperationalDataQueryService::class);
            $result  = $service->run($args, $this->companyId);

            if (!$result['success']) {
                return ['error' => $result['error']];
            }

            return $result['data'];

        } catch (\Throwable $e) {
            Log::error('queryOperationalData exception', [
                'error' => $e->getMessage(),
                'args'  => $args,
            ]);
            return ['error' => 'Error al procesar la consulta operativa.'];
        }
    }

    // -----------------------------------------------------------------------
    // Tools específicas
    // -----------------------------------------------------------------------

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
                $method === 'orders/eta'                => $controller->ordersByEta($request),
                $method === 'orders/delayed-in-transit' => $controller->ordersDelayedInTransit($request),
                $method === 'orders/teus'               => $controller->teusSummary($request),
                $method === 'orders/full-summary'       => $controller->fullSummary($request),
                default                                 => $controller->orders($request),
            };

            return $response->getData(true);

        } catch (\Exception $e) {
            Log::error('RagaOrdersToolService exception', [
                'method' => $method,
                'error'  => $e->getMessage(),
            ]);
            return ['error' => 'Error al consultar los datos.'];
        }
    }

    private function getOrdersInTransit(): array
    {
        return $this->call('orders', ['status' => 'in_transit']);
    }

    private function getOrdersInTransshipment(): array
    {
        return $this->call('shipments', ['status' => 'transshipment']);
    }

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

    private function getOrdersByAta(?string $date): array
    {
        if (!$date) return ['error' => 'Necesito una fecha para buscar por ATA.'];
        return $this->call('orders/ata', ['date' => $date]);
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
        if (!$shipmentId) return ['error' => 'Necesito el número o ID del embarque.'];
        return $this->call('shipments/' . $shipmentId);
    }

    private function getOrdersSummary(): array
    {
        return $this->call('orders');
    }

    private function getOrdersPendingConfirmation(): array
    {
        return $this->call('orders', ['status' => 'pending_confirmation']);
    }

    private function getOrdersDelayedInTransit(): array
    {
        return $this->call('orders/delayed-in-transit');
    }

    private function getTeusSummary(?string $status): array
    {
        $query = $status ? ['status' => $status] : [];
        return $this->call('orders/teus', $query);
    }

    private function getFullSummary(): array
    {
        return $this->call('orders/full-summary');
    }

    // -----------------------------------------------------------------------
    // Definiciones de tools para Groq
    // -----------------------------------------------------------------------

    public function getToolDefinitions(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_full_summary',
                    'description' => 'Obtiene un resumen COMPLETO de todas las operaciones logísticas en una sola llamada: total de órdenes activas, retrasadas, con ATA confirmado, en tránsito, en transbordo y TEUs totales. USAR SIEMPRE cuando el usuario pida un resumen general, resumen completo, o pregunte por múltiples métricas al mismo tiempo.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_in_transit',
                    'description' => 'Obtiene el número de órdenes de compra actualmente en tránsito.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_in_transshipment',
                    'description' => 'Obtiene embarques actualmente en puerto de transbordo.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_with_alerts',
                    'description' => 'Obtiene el total de órdenes con alertas o retrasos activos. Acepta filtros opcionales por cliente, naviera o proveedor. Usar para saber CUÁNTAS órdenes están atrasadas, con o sin filtro.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'trading_company' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto del cliente. Ejemplo: PriceSmart',
                            ],
                            'shipping_line' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto de la naviera. Ejemplo: Hapag-Lloyd',
                            ],
                            'vendor_name' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto del proveedor. Ejemplo: Proveedor Asia Pacific',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_all_delayed_orders',
                    'description' => 'Obtiene el LISTADO COMPLETO de todas las órdenes con alertas o retrasos activos, sin límite. Acepta filtros opcionales por cliente, naviera o proveedor. USAR cuando el usuario pida ver todas las órdenes atrasadas, el listado completo, o quiera ver las órdenes atrasadas de un cliente/naviera/proveedor específico.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'trading_company' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto del cliente. Ejemplo: PriceSmart',
                            ],
                            'shipping_line' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto de la naviera. Ejemplo: Hapag-Lloyd',
                            ],
                            'vendor_name' => [
                                'type'        => 'string',
                                'description' => 'Filtrar por nombre exacto del proveedor. Ejemplo: Proveedor Asia Pacific',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_ata',
                    'description' => 'Busca órdenes por fecha ATA exacta (fecha real de arribo YA confirmado). Solo usar cuando el usuario especifique una fecha exacta.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date' => [
                                'type'        => 'string',
                                'description' => 'Fecha exacta en formato YYYY-MM-DD.',
                            ],
                        ],
                        'required' => ['date'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_eta',
                    'description' => 'Busca órdenes por fecha ETA estimada de llegada. USAR SIEMPRE para preguntas de cuántas órdenes llegan en los próximos X días, esta semana, próxima semana, este mes o cualquier rango de fechas.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'month'     => ['type' => 'string', 'description' => 'Número del mes (1-12).'],
                            'year'      => ['type' => 'string', 'description' => 'Año en formato YYYY.'],
                            'date_from' => ['type' => 'string', 'description' => 'Fecha inicio YYYY-MM-DD.'],
                            'date_to'   => ['type' => 'string', 'description' => 'Fecha fin YYYY-MM-DD.'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_shipment_eta',
                    'description' => 'Obtiene el ETA o ATA de un embarque específico por su número DOC-XXXX.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'shipment_id' => ['type' => 'string', 'description' => 'Número DOC-XXXX del embarque.'],
                        ],
                        'required' => ['shipment_id'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_summary',
                    'description' => 'Obtiene un resumen básico: total activas, con retraso y con ATA confirmado.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_pending_confirmation',
                    'description' => 'Obtiene las órdenes de compra pendientes de confirmación.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_delayed_in_transit',
                    'description' => 'Obtiene las órdenes que están retrasadas Y en tránsito al mismo tiempo.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_teus_summary',
                    'description' => 'Obtiene el total de TEUs. Usar cuando el usuario pregunte únicamente por TEUs.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'description' => 'Filtro opcional: in_transit.'],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'query_operational_data',
                    'description' => 'Consulta métricas operativas con agrupaciones. Usar para: proveedor con más retrasos, naviera con más PO, órdenes por ruta, TEUs por naviera, promedio de retraso por cliente. NO usar para: contar total de atrasadas, listado de atrasadas, rangos de fechas ETA. NUNCA usar limit:1 — siempre limit:30 mínimo.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'entity'   => ['type' => 'string', 'enum' => ['purchase_orders']],
                            'metric'   => ['type' => 'string', 'enum' => ['count_orders', 'sum_teus', 'avg_delay_days', 'max_delay_days', 'sum_delay_days']],
                            'group_by' => ['type' => 'string', 'enum' => ['date_ata_week', 'date_eta_week', 'date_atd_week', 'shipping_line', 'vendor', 'trading_company', 'route_label', 'arrival_status', 'porth_phase', 'container_type']],
                            'filters'  => ['type' => 'object', 'properties' => new \stdClass()],
                            'sort'     => ['type' => 'object', 'properties' => ['field' => ['type' => 'string'], 'direction' => ['type' => 'string', 'enum' => ['asc', 'desc']]]],
                            'limit'    => ['type' => 'string', 'description' => 'Mínimo 30, máximo 100.'],
                        ],
                        'required' => ['entity'],
                    ],
                ],
            ],
        ];
    }
}