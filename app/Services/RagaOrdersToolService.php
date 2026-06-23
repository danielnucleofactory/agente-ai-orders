<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AgentOrdersController;
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
            'get_shipment_eta'                => $this->getShipmentEta($args['shipment_id'] ?? null),
            'get_orders_summary'              => $this->getOrdersSummary(),
            'get_orders_pending_confirmation' => $this->getOrdersPendingConfirmation(),
            'get_orders_delayed_in_transit'   => $this->getOrdersDelayedInTransit(),
            default                           => ['error' => 'Tool no reconocida: ' . $toolName],
        };
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
                $method === 'shipments'                => $controller->shipments($request),
                $method === 'orders/ata'               => $controller->ordersByAta($request),
                $method === 'orders/delayed-in-transit'=> $controller->ordersDelayedInTransit($request),
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

    public function getToolDefinitions(): array
    {
        return [
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
                    'description' => 'Busca órdenes por fecha ATA (fecha real de arribo).',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'date' => [
                                'type'        => 'string',
                                'description' => 'Fecha en formato YYYY-MM-DD',
                            ],
                        ],
                        'required' => ['date'],
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
                    'description' => 'Obtiene las órdenes que están retrasadas Y en tránsito al mismo tiempo. Usar cuando el usuario pregunte por órdenes retrasadas en tránsito o combine ambos criterios.',
                    'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
        ];
    }
}