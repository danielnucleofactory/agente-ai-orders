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
            'get_orders_with_alerts'          => $this->getOrdersWithAlerts(),
            'get_orders_by_ata'               => $this->getOrdersByAta($args['date'] ?? null),
            'get_orders_by_eta'               => $this->getOrdersByEta($args),
            'get_shipment_eta'                => $this->getShipmentEta($args['shipment_id'] ?? null),
            'get_orders_summary'              => $this->getOrdersSummary(),
            'get_orders_pending_confirmation' => $this->getOrdersPendingConfirmation(),
            'get_orders_delayed_in_transit'   => $this->getOrdersDelayedInTransit(),
            'get_teus_summary'                => $this->getTeusSummary($args['status'] ?? null),
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
    // Tools específicas existentes (sin cambios)
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
                $method === 'shipments'                => $controller->shipments($request),
                $method === 'orders/ata'               => $controller->ordersByAta($request),
                $method === 'orders/eta'               => $controller->ordersByEta($request),
                $method === 'orders/delayed-in-transit'=> $controller->ordersDelayedInTransit($request),
                $method === 'orders/teus'              => $controller->teusSummary($request),
                default                                => $controller->orders($request),
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

    private function getOrdersWithAlerts(): array
    {
        return $this->call('orders', ['flag' => 'alert']);
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

    // -----------------------------------------------------------------------
    // Definiciones de tools para Groq
    // -----------------------------------------------------------------------

    public function getToolDefinitions(): array
    {
        return [
            // ----------------------------------------------------------------
            // Tools específicas existentes
            // ----------------------------------------------------------------
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
                    'description' => 'Obtiene órdenes con alertas o incidencias activas como retrasos.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_by_ata',
                    'description' => 'Busca órdenes por fecha ATA exacta (fecha real de arribo YA confirmado). Solo usar cuando el usuario pregunte por órdenes que ya llegaron.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date' => [
                                'type'        => 'string',
                                'description' => 'Fecha exacta en formato YYYY-MM-DD. Ejemplo: 2026-06-11',
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
                    'description' => 'Busca órdenes por fecha ETA estimada de llegada. Usar cuando el usuario pregunte por órdenes que llegan en un mes, año o rango de fechas.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'month' => [
                                'type'        => 'string',
                                'description' => 'Número del mes (1-12). Usar junto con year. Ejemplo: 8 para agosto.',
                            ],
                            'year' => [
                                'type'        => 'string',
                                'description' => 'Año en formato YYYY. Usar junto con month. Ejemplo: 2026.',
                            ],
                            'date_from' => [
                                'type'        => 'string',
                                'description' => 'Fecha de inicio del rango en formato YYYY-MM-DD.',
                            ],
                            'date_to' => [
                                'type'        => 'string',
                                'description' => 'Fecha de fin del rango en formato YYYY-MM-DD.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_shipment_eta',
                    'description' => 'Obtiene el ETA o ATA de un embarque específico por su número o ID.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'shipment_id' => [
                                'type'        => 'string',
                                'description' => 'Número o ID del embarque',
                            ],
                        ],
                        'required' => ['shipment_id'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_orders_summary',
                    'description' => 'Obtiene un resumen general de las órdenes del usuario: total activas, con retraso y con ATA confirmado.',
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
                    'description' => 'Obtiene el total de TEUs calculado a partir del tipo de contenedor. Usar cuando el usuario pregunte por TEUs, contenedores equivalentes o volumen de carga.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'status' => [
                                'type'        => 'string',
                                'description' => 'Filtro opcional: in_transit para solo órdenes en tránsito.',
                            ],
                        ],
                    ],
                ],
            ],

            // ----------------------------------------------------------------
            // Tool generalista — query_operational_data
            // ----------------------------------------------------------------
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'query_operational_data',
                    'description' => 'Consulta métricas operativas de órdenes de compra usando filtros, agrupaciones y métricas permitidas. Úsala para preguntas analíticas como: agrupar por semana, proveedor, ruta, naviera, cliente, estado o fechas. También úsala para preguntas como: ¿qué proveedor tiene más retrasos?, ¿cuántas PO hay por ruta?, ¿cuántos TEUs por naviera?, ¿cuántas órdenes llegan esta semana?',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'entity' => [
                                'type'        => 'string',
                                'description' => 'Entidad a consultar. Valor permitido: purchase_orders',
                                'enum'        => ['purchase_orders'],
                            ],
                            'metric' => [
                                'type'        => 'string',
                                'description' => 'Métrica a calcular. Valores permitidos: count_orders, sum_teus, avg_delay_days, max_delay_days, sum_delay_days',
                                'enum'        => ['count_orders', 'sum_teus', 'avg_delay_days', 'max_delay_days', 'sum_delay_days'],
                            ],
                            'group_by' => [
                                'type'        => 'string',
                                'description' => 'Agrupar resultados por: date_ata_week, date_eta_week, date_atd_week, shipping_line, vendor, trading_company, route_label, arrival_status, porth_phase, container_type',
                                'enum'        => [
                                    'date_ata_week',
                                    'date_eta_week',
                                    'date_atd_week',
                                    'shipping_line',
                                    'vendor',
                                    'trading_company',
                                    'route_label',
                                    'arrival_status',
                                    'porth_phase',
                                    'container_type',
                                ],
                            ],
                            'filters' => [
                                'type'        => 'object',
                                'description' => 'Filtros opcionales. Cada clave es un campo permitido y el valor puede ser un string directo (ej: "not_null") o un objeto con "operator" y "value". Campos permitidos: date_ata, date_eta, date_atd, shipping_line, vendor_id, vendor_name, trading_company, route_label, arrival_status, delay_days, porth_phase, container_type',
                                'properties'  => new \stdClass(),
                            ],
                            'sort' => [
                                'type'        => 'object',
                                'description' => 'Ordenamiento del resultado.',
                                'properties'  => [
                                    'field'     => [
                                        'type'        => 'string',
                                        'description' => 'Campo por el que ordenar (alias del group_by o de la métrica).',
                                    ],
                                    'direction' => [
                                        'type'        => 'string',
                                        'description' => 'Dirección: asc o desc.',
                                        'enum'        => ['asc', 'desc'],
                                    ],
                                ],
                            ],
                            'limit' => [
                                'type'        => 'string',
                                'description' => 'Límite de resultados a retornar. Máximo 100. Default 30.',
                            ],
                        ],
                        'required' => ['entity'],
                    ],
                ],
            ],
        ];
    }
}