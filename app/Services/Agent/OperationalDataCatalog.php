<?php

namespace App\Services\Agent;

/**
 * OperationalDataCatalog
 *
 * Define el whitelist de entidades, métricas, filtros, agrupaciones
 * y operadores permitidos para la tool generalista query_operational_data.
 *
 * REGLA: Nada que no esté aquí puede llegar a la base de datos.
 */
class OperationalDataCatalog
{
    public static function entities(): array
    {
        return [
            'purchase_orders' => [
                'label'       => 'Órdenes de compra',
                'table'       => 'purchase_orders',
                'company_col' => 'purchase_orders.company_id',
                'joins'       => [
                    [
                        'table'    => 'vendors',
                        'first'    => 'purchase_orders.vendor_id',
                        'operator' => '=',
                        'second'   => 'vendors.id',
                        'type'     => 'left',
                    ],
                ],
            ],
        ];
    }

    public static function metrics(): array
    {
        return [
            'count_orders'   => [
                'label' => 'Cantidad de órdenes',
                'sql'   => 'COUNT(*)',
                'alias' => 'total',
            ],
            'sum_teus'       => [
                'label' => 'Total TEUs',
                'sql'   => "SUM(CASE WHEN purchase_orders.container_type IN ('40ft','40HC','40HQ') THEN 2 ELSE 1 END)",
                'alias' => 'total_teus',
            ],
            'avg_delay_days' => [
                'label' => 'Promedio días de retraso',
                'sql'   => 'ROUND(AVG(purchase_orders.delay_days), 1)',
                'alias' => 'avg_delay',
            ],
            'max_delay_days' => [
                'label' => 'Máximo días de retraso',
                'sql'   => 'MAX(purchase_orders.delay_days)',
                'alias' => 'max_delay',
            ],
            'sum_delay_days' => [
                'label' => 'Total días de retraso',
                'sql'   => 'SUM(purchase_orders.delay_days)',
                'alias' => 'total_delay',
            ],
        ];
    }

    public static function groupBy(): array
    {
        return [
            'date_ata_week'   => [
                'label' => 'Semana de ATA',
                'sql'   => "TO_CHAR(purchase_orders.date_ata, 'IYYY-\"W\"IW')",
                'alias' => 'week_ata',
            ],
            'date_eta_week'   => [
                'label' => 'Semana de ETA',
                'sql'   => "TO_CHAR(purchase_orders.porth_first_eta, 'IYYY-\"W\"IW')",
                'alias' => 'week_eta',
            ],
            'date_atd_week'   => [
                'label' => 'Semana de ATD',
                'sql'   => "TO_CHAR(purchase_orders.date_atd, 'IYYY-\"W\"IW')",
                'alias' => 'week_atd',
            ],
            'shipping_line'   => [
                'label' => 'Naviera',
                'sql'   => 'purchase_orders.shipping_line',
                'alias' => 'shipping_line',
            ],
            'vendor'          => [
                'label'    => 'Proveedor',
                'sql'      => 'vendors.name',
                'alias'    => 'vendor',
                'requires' => ['vendors'],
            ],
            'trading_company' => [
                'label' => 'Cliente / Trading company',
                'sql'   => 'purchase_orders.trading_company',
                'alias' => 'trading_company',
            ],
            'route_label'     => [
                'label' => 'Ruta',
                'sql'   => 'purchase_orders.route_label',
                'alias' => 'route_label',
            ],
            'arrival_status'  => [
                'label' => 'Estado de llegada',
                'sql'   => 'purchase_orders.arrival_status',
                'alias' => 'arrival_status',
            ],
            'porth_phase'     => [
                'label' => 'Fase logística',
                'sql'   => 'purchase_orders.porth_phase',
                'alias' => 'porth_phase',
            ],
            'container_type'  => [
                'label' => 'Tipo de contenedor',
                'sql'   => 'purchase_orders.container_type',
                'alias' => 'container_type',
            ],
        ];
    }

    public static function filters(): array
    {
        return [
            'date_ata' => [
                'label'     => 'Fecha ATA',
                'col'       => 'purchase_orders.date_ata',
                'type'      => 'date',
                'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
            ],
            'date_eta' => [
                'label'     => 'Fecha ETA',
                'col'       => 'purchase_orders.porth_first_eta',
                'type'      => 'date',
                'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
            ],
            'date_atd' => [
                'label'     => 'Fecha ATD',
                'col'       => 'purchase_orders.date_atd',
                'type'      => 'date',
                'operators' => ['eq', 'not_null', 'is_null', 'gte', 'lte', 'between'],
            ],
            'shipping_line' => [
                'label'     => 'Naviera',
                'col'       => 'purchase_orders.shipping_line',
                'type'      => 'string',
                'operators' => ['eq', 'like'],
            ],
            'vendor_id' => [
                'label'     => 'ID de proveedor',
                'col'       => 'purchase_orders.vendor_id',
                'type'      => 'integer',
                'operators' => ['eq'],
            ],
            'vendor_name' => [
                'label'     => 'Nombre de proveedor',
                'col'       => 'vendors.name',
                'type'      => 'string',
                'operators' => ['eq', 'like'],
            ],
            'trading_company' => [
                'label'     => 'Cliente / Trading company',
                'col'       => 'purchase_orders.trading_company',
                'type'      => 'string',
                'operators' => ['eq', 'like'],
            ],
            'route_label' => [
                'label'     => 'Ruta',
                'col'       => 'purchase_orders.route_label',
                'type'      => 'string',
                'operators' => ['eq', 'like'],
            ],
            'arrival_status' => [
                'label'     => 'Estado de llegada',
                'col'       => 'purchase_orders.arrival_status',
                'type'      => 'enum',
                'allowed'   => ['delayed', 'Atrasado', 'on_time', 'arrived'],
                'operators' => ['eq'],
            ],
            'delay_days' => [
                'label'     => 'Días de retraso',
                'col'       => 'purchase_orders.delay_days',
                'type'      => 'integer',
                'operators' => ['eq', 'gte', 'lte', 'gt', 'lt'],
            ],
            'porth_phase' => [
                'label'     => 'Fase logística',
                'col'       => 'purchase_orders.porth_phase',
                'type'      => 'enum',
                'allowed'   => [
                    '40_in_transit',
                    '20_transshipment',
                    '50_at_destination_port',
                    '60_to_final_destination',
                    '70_delivered',
                ],
                'operators' => ['eq'],
            ],
            'container_type' => [
                'label'     => 'Tipo de contenedor',
                'col'       => 'purchase_orders.container_type',
                'type'      => 'string',
                'operators' => ['eq'],
            ],
        ];
    }

    public static function operators(): array
    {
        return [
            'eq'       => '=',
            'neq'      => '!=',
            'gt'       => '>',
            'lt'       => '<',
            'gte'      => '>=',
            'lte'      => '<=',
            'like'     => 'LIKE',
            'not_null' => 'IS NOT NULL',
            'is_null'  => 'IS NULL',
            'between'  => 'BETWEEN',
        ];
    }

    public static function limits(): array
    {
        return [
            'default' => 30,
            'max'     => 100,
        ];
    }

    public static function isValidEntity(string $entity): bool
    {
        return array_key_exists($entity, self::entities());
    }

    public static function isValidMetric(string $metric): bool
    {
        return array_key_exists($metric, self::metrics());
    }

    public static function isValidGroupBy(string $group): bool
    {
        return array_key_exists($group, self::groupBy());
    }

    public static function isValidFilter(string $filter): bool
    {
        return array_key_exists($filter, self::filters());
    }

    public static function isValidOperatorForFilter(string $filter, string $operator): bool
    {
        $filters = self::filters();
        if (!isset($filters[$filter])) return false;
        return in_array($operator, $filters[$filter]['operators'], true);
    }

    public static function isValidEnumValue(string $filter, string $value): bool
    {
        $filters = self::filters();
        if (!isset($filters[$filter]) || $filters[$filter]['type'] !== 'enum') return true;
        return in_array($value, $filters[$filter]['allowed'], true);
    }

    public static function describeForPrompt(): string
    {
        $metrics = implode(', ', array_keys(self::metrics()));
        $groups  = implode(', ', array_keys(self::groupBy()));
        $filters = implode(', ', array_keys(self::filters()));

        return <<<TEXT
Catálogo de query_operational_data:
- entity: purchase_orders
- metrics: {$metrics}
- group_by: {$groups}
- filters: {$filters}
- operators: eq, neq, gt, lt, gte, lte, like, not_null, is_null, between
- limit: máximo 100 (default 30)
TEXT;
    }
}