<?php

namespace App\Services\Agent;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OperationalDataQueryService
 *
 * Recibe el JSON estructurado que Groq/Gemini genera para la tool
 * query_operational_data, lo valida contra el catálogo permitido
 * y construye una query segura usando Query Builder de Laravel.
 */
class OperationalDataQueryService
{
    public function run(array $payload, int $companyId): array
    {
        try {
            // 1. Validar estructura básica del payload
            $validated = $this->validatePayload($payload);
            if (!$validated['ok']) {
                return $this->error($validated['message']);
            }

            $entity  = $payload['entity'];
            $metric  = $payload['metric']   ?? 'count_orders';
            $groupBy = $payload['group_by'] ?? null;
            $filters = $payload['filters']  ?? [];
            $sort    = $payload['sort']     ?? null;
            $limit   = $this->resolveLimit($payload['limit'] ?? null);

            // 2. Configuración de entidad, métrica y agrupación
            $entityConfig = OperationalDataCatalog::entities()[$entity];
            $table        = $entityConfig['table'];
            $companyCol   = $entityConfig['company_col'];
            $metricConfig = OperationalDataCatalog::metrics()[$metric];

            // 3. Query base con aislamiento por compañía y soft delete
            $query = DB::table($table)
                ->where($companyCol, $companyId)
                ->whereNull($table . '.deleted_at');

            // 4. Aplicar JOINs definidos en la entidad (siempre el de vendors)
            foreach ($entityConfig['joins'] as $join) {
                if ($join['type'] === 'left') {
                    $query->leftJoin($join['table'], $join['first'], $join['operator'], $join['second']);
                } else {
                    $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
                }
            }

            // 5. Aplicar filtros validados
            $filterResult = $this->applyFilters($query, $filters);
            if (!$filterResult['ok']) {
                return $this->error($filterResult['message']);
            }

            // 6. Construir SELECT y GROUP BY
            $selectRaw = $metricConfig['sql'] . ' as ' . $metricConfig['alias'];

            if ($groupBy) {
                $groupConfig = OperationalDataCatalog::groupBy()[$groupBy];
                $groupSql    = $groupConfig['sql'];
                $groupAlias  = $groupConfig['alias'];

                $query->selectRaw("{$groupSql} as {$groupAlias}, {$selectRaw}")
                      ->groupByRaw($groupSql)
                      ->whereRaw("{$groupSql} IS NOT NULL");

                // Ordenar
                if ($sort && isset($sort['field'], $sort['direction'])) {
                    $direction        = strtolower($sort['direction']) === 'desc' ? 'desc' : 'asc';
                    $allowedSortFields = [$groupAlias, $metricConfig['alias']];
                    $sortField        = in_array($sort['field'], $allowedSortFields, true)
                        ? $sort['field']
                        : $groupAlias;
                    $query->orderBy($sortField, $direction);
                } else {
                    $query->orderBy($metricConfig['alias'], 'desc');
                }
            } else {
                $query->selectRaw($selectRaw);
            }

            // 7. Aplicar límite
            $query->limit($limit);

            // 8. Ejecutar
            $rows = $query->get()->toArray();

            // 9. Log sin datos sensibles
            Log::info('[AgentQuery] Consulta ejecutada', [
                'entity'     => $entity,
                'metric'     => $metric,
                'group_by'   => $groupBy,
                'filters'    => array_keys($filters),
                'limit'      => $limit,
                'company_id' => $companyId,
                'rows_count' => count($rows),
            ]);

            return [
                'success' => true,
                'data'    => [
                    'rows'     => array_map(fn($r) => (array) $r, $rows),
                    'count'    => count($rows),
                    'metric'   => $metricConfig['label'],
                    'group_by' => $groupBy ? OperationalDataCatalog::groupBy()[$groupBy]['label'] : null,
                ],
                'error' => null,
            ];

        } catch (\Throwable $e) {
            Log::error('[AgentQuery] Error inesperado', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return $this->error('Ocurrió un error al procesar la consulta. Por favor intenta de nuevo.');
        }
    }

    // -----------------------------------------------------------------------
    // Validación del payload
    // -----------------------------------------------------------------------

    private function validatePayload(array $payload): array
    {
        if (empty($payload['entity'])) {
            return $this->fail('El campo "entity" es requerido.');
        }
        if (!OperationalDataCatalog::isValidEntity($payload['entity'])) {
            return $this->fail("Entidad no permitida: {$payload['entity']}");
        }
        if (!empty($payload['metric']) && !OperationalDataCatalog::isValidMetric($payload['metric'])) {
            return $this->fail("Métrica no permitida: {$payload['metric']}");
        }
        if (!empty($payload['group_by']) && !OperationalDataCatalog::isValidGroupBy($payload['group_by'])) {
            return $this->fail("Agrupación no permitida: {$payload['group_by']}");
        }
        if (!empty($payload['filters']) && is_array($payload['filters'])) {
            foreach ($payload['filters'] as $filterKey => $filterValue) {
                if (!OperationalDataCatalog::isValidFilter($filterKey)) {
                    return $this->fail("Filtro no permitido: {$filterKey}");
                }
            }
        }
        return ['ok' => true, 'message' => null];
    }

    // -----------------------------------------------------------------------
    // Aplicar filtros
    // -----------------------------------------------------------------------

    private function applyFilters($query, array $filters): array
    {
        $catalog = OperationalDataCatalog::filters();

        foreach ($filters as $filterKey => $filterValue) {
            if (!OperationalDataCatalog::isValidFilter($filterKey)) {
                return $this->fail("Filtro no permitido: {$filterKey}");
            }

            $filterConfig = $catalog[$filterKey];
            $col          = $filterConfig['col'];

            // Resolver operador y valor
            if (is_string($filterValue) || is_numeric($filterValue)) {
                $operator = in_array($filterValue, ['not_null', 'is_null'])
                    ? $filterValue
                    : 'eq';
                $value = in_array($filterValue, ['not_null', 'is_null']) ? null : $filterValue;
            } elseif (is_array($filterValue)) {
                $operator = $filterValue['operator'] ?? 'eq';
                $value    = $filterValue['value']    ?? null;
            } else {
                return $this->fail("Valor inválido para filtro: {$filterKey}");
            }

            // Validar operador
            if (!OperationalDataCatalog::isValidOperatorForFilter($filterKey, $operator)) {
                return $this->fail("Operador '{$operator}' no permitido para filtro '{$filterKey}'");
            }

            // Validar enum
            if ($value !== null && $filterConfig['type'] === 'enum') {
                if (!OperationalDataCatalog::isValidEnumValue($filterKey, (string) $value)) {
                    return $this->fail("Valor '{$value}' no permitido para filtro '{$filterKey}'");
                }
            }

            // Aplicar al query
            switch ($operator) {
                case 'not_null':
                    $query->whereNotNull($col);
                    break;
                case 'is_null':
                    $query->whereNull($col);
                    break;
                case 'like':
                    $query->where($col, 'LIKE', '%' . $value . '%');
                    break;
                case 'between':
                    $from = $filterValue['value_from'] ?? null;
                    $to   = $filterValue['value_to']   ?? null;
                    if ($from && $to) {
                        $query->whereBetween($col, [$from, $to]);
                    }
                    break;
                case 'eq':
                    $query->where($col, '=', $value);
                    break;
                case 'neq':
                    $query->where($col, '!=', $value);
                    break;
                case 'gt':
                    $query->where($col, '>', $value);
                    break;
                case 'lt':
                    $query->where($col, '<', $value);
                    break;
                case 'gte':
                    $query->where($col, '>=', $value);
                    break;
                case 'lte':
                    $query->where($col, '<=', $value);
                    break;
                default:
                    return $this->fail("Operador desconocido: {$operator}");
            }
        }

        return ['ok' => true, 'message' => null];
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function resolveLimit(mixed $requested): int
    {
        $limits = OperationalDataCatalog::limits();
        if ($requested === null) return $limits['default'];
        return min((int) $requested, $limits['max']);
    }

    private function error(string $message): array
    {
        return ['success' => false, 'data' => [], 'error' => $message];
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}