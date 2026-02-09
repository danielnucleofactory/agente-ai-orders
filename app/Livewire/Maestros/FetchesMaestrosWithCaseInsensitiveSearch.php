<?php

namespace App\Livewire\Maestros;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait to fetch all maestros from API and filter case-insensitively when the
 * external API does not support case-insensitive search.
 */
trait FetchesMaestrosWithCaseInsensitiveSearch
{
    /**
     * Fetch all pages from the API (without search param) and filter results
     * case-insensitively. Returns a LengthAwarePaginator.
     *
     * @param callable $fetchPage Callable that receives (array $params) and returns API response (with 'data', 'total' or 'meta', etc.)
     * @param string $searchTerm Search term (will be used case-insensitively)
     * @param int $perPage Items per page for the output paginator
     * @param int $currentPage Current page number
     * @param string $sortField Field to sort by
     * @param string $sortDirection 'asc' or 'desc'
     * @param array $baseParams Base params to send (page, per_page, sort, order, active; no search)
     * @return LengthAwarePaginator
     */
    protected function fetchAllAndFilterCaseInsensitive(
        callable $fetchPage,
        string $searchTerm,
        int $perPage,
        int $currentPage,
        string $sortField,
        string $sortDirection,
        array $baseParams
    ): LengthAwarePaginator {
        $all = collect([]);
        $page = 1;
        $perPageRequest = 100;
        $maxPages = 50; // safety limit

        do {
            $params = array_merge($baseParams, [
                'page' => $page,
                'per_page' => $perPageRequest,
            ]);
            $response = $fetchPage($params);
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                break;
            }
            $all = $all->merge($response['data']);
            $total = $response['total'] ?? $response['meta']['total'] ?? 0;
            $lastPage = $response['last_page'] ?? $response['meta']['last_page'] ?? 1;
            $page++;
        } while ($page <= $lastPage && $page <= $maxPages);

        $searchLower = mb_strtolower($searchTerm);

        $filtered = $all->filter(function ($item) use ($searchLower) {
            if (!is_array($item)) {
                return false;
            }
            foreach ($item as $value) {
                if ($value === null) {
                    continue;
                }
                $str = is_scalar($value) ? (string) $value : '';
                if ($str !== '' && mb_strpos(mb_strtolower($str), $searchLower) !== false) {
                    return true;
                }
            }
            return false;
        });

        $sorted = $sortDirection === 'desc'
            ? $filtered->sortByDesc($sortField)
            : $filtered->sortBy($sortField);
        $sorted = $sorted->values();

        $totalFiltered = $sorted->count();
        $slice = $sorted->forPage($currentPage, $perPage);

        return new LengthAwarePaginator(
            $slice,
            $totalFiltered,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'page',
            ]
        );
    }
}
