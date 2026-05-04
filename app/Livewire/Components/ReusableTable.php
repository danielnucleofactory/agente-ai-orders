<?php

namespace App\Livewire\Components;

use App\Livewire\Maestros\FetchesMaestrosWithCaseInsensitiveSearch;
use App\Services\MaestrosApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ReusableTable extends Component
{
    use FetchesMaestrosWithCaseInsensitiveSearch;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Table configuration
    public $headers = [];
    public $sortable = [];
    /** Map header/sort keys to real DB columns (e.g. type_translated => type) */
    public $sortFieldAliases = [];
    public $searchable = [];
    public $filterable = [];
    public $filterOptions = [];
    public $relationColumns = [];
    public $withCount = [];

    // Actions configuration
    public $showActions = false;
    public $actionsView = false;
    public $actionsEdit = false;
    public $actionsDelete = false;
    public $routeKeyName = 'id';
    public $baseRoute = '';
    public $viewPermission = null;
    public $editPermission = null;
    public $deletePermission = null;

    // Deletion confirmation
    public $confirmingDelete = false;
    public $deleteId = null;
    public $deleteError = null;

    // Data source
    public $model = null;
    public $rows = [];
    public $useModel = false;

    // Table state
    public $search = '';
    public $perPage = 10;
    public $sortField = '';
    public $sortDirection = 'asc';
    public $filters = [];

    // Component configuration
    public $showSearch = true;
    public $showPagination = true;
    public $showPerPage = true;
    public $emptyMessage = 'No se encontraron registros';

    /** Bulk selection (sessions / history) */
    public $showSelectColumn = false;
    public $selectedIds = [];
    public $selectEntireResultSet = false;
    public $showSelectAllModal = false;
    public $selectAllModalTotal = 0;
    public $selectAllPageCount = 0;

    /** Optional Blade for actions column (path as used by view(), e.g. livewire.settings.partials.foo) */
    public $customActionsView = null;

    /** When set, rows load from Maestros API (same behavior as legacy MaestroCatalogTable). */
    public $maestroCatalogKey = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function confirmDelete($id)
    {
        $this->confirmingDelete = true;
        $this->deleteId = $id;
        $this->dispatch('open-modal', 'modal-warning');
    }

    public function cancelDelete()
    {
        $this->reset(['confirmingDelete', 'deleteId']);
    }

    public function dismissDeleteError()
    {
        $this->deleteError = null;
    }

    public function delete()
    {
        if (!$this->useModel || !$this->deleteId) {
            return;
        }

        if ($this->deletePermission && !auth()->user()?->can($this->deletePermission)) {
            abort(403, 'No tienes permisos para eliminar este registro');
        }

        try {
            // Check if this is a Spatie Role model which uses find() instead of findOrFail()
            if ($this->model === 'Spatie\\Permission\\Models\\Role') {
                $record = $this->model::find($this->deleteId);
                if ($record) {
                    $record->delete();
                }
            }
            elseif (method_exists($this->model, 'findOrFail')) {
                $record = $this->model::findOrFail($this->deleteId);
                $record->delete();
            }
            else {
                $record = $this->model::find($this->deleteId);
                if ($record) {
                    $record->delete();
                }
            }

            // Reset pagination if we've deleted the last item on the current page
            if ($this->getProcessedRowsProperty()->count() === 0 && $this->getPage() > 1) {
                $this->resetPage();
            }

            $this->reset(['confirmingDelete', 'deleteId']);

            session()->flash('message', 'Registro eliminado correctamente.');
            $this->dispatch('itemDeleted');

        } catch (\Exception $e) {
            $this->reset(['confirmingDelete', 'deleteId']);
            $this->deleteError = $e->getMessage();
            \Log::error('Error deleting record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    public function mount(
        $headers = [],
        $sortable = [],
        $searchable = [],
        $filterable = [],
        $filterOptions = [],
        $withCount = [],
        $model = null,
        $rows = [],
        $relationColumns = [],
        $actions = false,
        $baseRoute = '',
        $routeKeyName = 'id',
        $actionsView = true,
        $actionsEdit = true,
        $actionsDelete = true,
        $viewPermission = null,
        $editPermission = null,
        $deletePermission = null,
        $showSelectColumn = false,
        $sortFieldAliases = [],
        $customActionsView = null,
        $maestroCatalogKey = null
    )
    {
        $this->maestroCatalogKey = is_string($maestroCatalogKey) && $maestroCatalogKey !== ''
            ? $maestroCatalogKey
            : null;

        if ($this->maestroCatalogKey !== null) {
            abort_unless(auth()->user()?->can('has_view_maestros'), 403);

            if (!is_array($headers) || $headers === []) {
                $headers = [
                    'name' => 'Nombre',
                    'olo_id' => 'OLO ID',
                    'company' => 'Compañía',
                    'active' => 'Estado',
                ];
            }

            if ($searchable === []) {
                $searchable = ['name', 'olo_id', 'company', 'active'];
            }

            $filterable = ['active'];
            $filterOptions = [
                'active' => [
                    'true' => 'Activo',
                    'false' => 'Inactivo',
                ],
            ];
            $model = null;
            $actions = false;
            $actionsView = false;
            $actionsEdit = false;
            $actionsDelete = false;
            $routeKeyName = 'olo_id';
        }

        $this->headers = $headers;
        $this->sortFieldAliases = is_array($sortFieldAliases) ? $sortFieldAliases : [];
        $this->sortable = $sortable;
        $this->searchable = $searchable;
        $this->filterable = $filterable;
        $this->filterOptions = $filterOptions;
        $this->relationColumns = $relationColumns;
        $this->withCount = $withCount;

        // Configure actions
        $this->showActions = $actions;
        $this->baseRoute = $baseRoute;
        $this->routeKeyName = $routeKeyName;
        $this->viewPermission = $viewPermission;
        $this->editPermission = $editPermission;
        $this->deletePermission = $deletePermission;

        $this->actionsView = $actionsView && (!$this->viewPermission || auth()->user()?->can($this->viewPermission));
        $this->actionsEdit = $actionsEdit && (!$this->editPermission || auth()->user()?->can($this->editPermission));
        $this->actionsDelete = $actionsDelete && (!$this->deletePermission || auth()->user()?->can($this->deletePermission));

        $this->showSelectColumn = (bool) $showSelectColumn;
        $this->customActionsView = $customActionsView;

        // If actions is true and headers don't include 'actions', add it
        if ($this->showActions && !isset($this->headers['actions'])) {
            $this->headers['actions'] = 'Acciones';
        }

        if ($this->showSelectColumn) {
            $this->headers = ['_select' => ''] + $this->headers;
        }

        if (empty($this->sortable) && !empty($this->headers)) {
            $this->sortable = $this->inferSortableKeysFromHeaders();
        }

        // Determine if we're using a model or array data
        if ($model) {
            // If model is passed as a string (class name), keep it as string for instantiation
            $this->model = $model;
            $this->useModel = true;
        } else {
            $this->rows = $rows;
            $this->useModel = false;
        }

        // Set default sort if sortable fields are provided
        if (!empty($this->sortable) && empty($this->sortField)) {
            $this->sortField = $this->sortable[0];
        }

        // Initialize filters
        foreach ($this->filterable as $field) {
            $this->filters[$field] = '';
        }

        if ($this->maestroCatalogKey !== null) {
            $this->useModel = false;
            $this->model = null;
            $this->rows = [];
            $this->showActions = false;
            $this->actionsView = false;
            $this->actionsEdit = false;
            $this->actionsDelete = false;
            $this->showPagination = true;
            $canFilter = auth()->user()?->can('filter') ?? false;
            $this->showPerPage = $canFilter;
            $this->showSearch = $canFilter;
            $this->emptyMessage = 'No se encontraron registros.';
            $this->sortField = 'created_at';
            $this->sortDirection = 'desc';
        }
    }

    protected function inferSortableKeysFromHeaders(): array
    {
        $keys = [];
        foreach (array_keys($this->headers) as $k) {
            if ($k === 'actions' || $k === '_select' || str_ends_with((string) $k, '_html')) {
                continue;
            }
            $keys[] = $k;
        }

        return $keys;
    }

    /**
     * Hook for subclasses to constrain the Eloquent query (scopes, company, etc.).
     */
    protected function modifyModelQuery(Builder $query): void
    {
    }

    public function resolveSortColumn(): string
    {
        if (isset($this->sortFieldAliases[$this->sortField])) {
            return $this->sortFieldAliases[$this->sortField];
        }

        return $this->sortField;
    }

    public function getRowSelectionKey($row): string
    {
        if ($this->useModel) {
            return (string) $row->{$this->routeKeyName};
        }

        return (string) ($row[$this->routeKeyName] ?? $row['id'] ?? spl_object_hash((object) $row));
    }

    public function isRowSelected($row): bool
    {
        if ($this->selectEntireResultSet) {
            return true;
        }

        return in_array($this->getRowSelectionKey($row), $this->selectedIds, true);
    }

    public function toggleRowSelection(string $key): void
    {
        if ($this->selectEntireResultSet) {
            $this->selectEntireResultSet = false;
            $page = $this->getProcessedRowsProperty();
            $this->selectedIds = [];
            foreach ($page as $row) {
                $id = $this->getRowSelectionKey($row);
                if ($id !== $key) {
                    $this->selectedIds[] = $id;
                }
            }

            return;
        }
        if (in_array($key, $this->selectedIds, true)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$key]));
        } else {
            $this->selectedIds[] = $key;
        }
    }

    public function toggleHeaderSelect(): void
    {
        $page = $this->getProcessedRowsProperty();
        $pageIds = [];
        foreach ($page as $row) {
            $pageIds[] = $this->getRowSelectionKey($row);
        }

        $exactPageSelection = !empty($pageIds)
            && count(array_diff($pageIds, $this->selectedIds)) === 0
            && count($this->selectedIds) === count($pageIds);

        if ($this->selectEntireResultSet || $exactPageSelection) {
            $this->selectedIds = [];
            $this->selectEntireResultSet = false;
            $this->showSelectAllModal = false;

            return;
        }

        $this->selectEntireResultSet = false;
        $this->selectedIds = $pageIds;
        $this->selectAllModalTotal = (int) $page->total();
        $this->selectAllPageCount = count($pageIds);
        $this->showSelectAllModal = $this->selectAllModalTotal > $this->selectAllPageCount;
    }

    public function dismissSelectAllModal(): void
    {
        $this->showSelectAllModal = false;
    }

    public function confirmSelectEntireResultSet(): void
    {
        $this->selectEntireResultSet = true;
        $this->selectedIds = [];
        $this->showSelectAllModal = false;
    }

    public function headerSelectIndeterminate(): bool
    {
        if ($this->selectEntireResultSet) {
            return false;
        }
        $page = $this->getProcessedRowsProperty();
        $pageIds = [];
        foreach ($page as $row) {
            $pageIds[] = $this->getRowSelectionKey($row);
        }
        if (empty($pageIds)) {
            return false;
        }
        $intersect = count(array_intersect($pageIds, $this->selectedIds));

        return $intersect > 0 && $intersect < count($pageIds);
    }

    public function headerSelectChecked(): bool
    {
        if ($this->selectEntireResultSet) {
            return true;
        }
        $page = $this->getProcessedRowsProperty();
        $pageIds = [];
        foreach ($page as $row) {
            $pageIds[] = $this->getRowSelectionKey($row);
        }
        if (empty($pageIds)) {
            return false;
        }

        return count(array_diff($pageIds, $this->selectedIds)) === 0
            && count($this->selectedIds) === count($pageIds);
    }

    public function getRouteFor($action, $row)
    {
        // Get the route key value from the row
        $routeKey = $this->useModel ? $row->{$this->routeKeyName} : $row[$this->routeKeyName] ?? '';

        if (empty($this->baseRoute)) {
            // Try to guess the base route from the model
            if ($this->useModel) {
                $modelName = class_basename($this->model);
                $baseRouteName = strtolower(\Str::plural($modelName));

                switch ($action) {
                    case 'view':
                        if (Route::has("{$baseRouteName}.show")) {
                            return route("{$baseRouteName}.show", $routeKey);
                        }
                        break;
                    case 'edit':
                        if (Route::has("{$baseRouteName}.edit")) {
                            return route("{$baseRouteName}.edit", $routeKey);
                        }
                        break;
                }
            }

            return '#';
        } else {
            switch ($action) {
                case 'view':
                    if (Route::has("{$this->baseRoute}.show")) {
                        return route("{$this->baseRoute}.show", $routeKey);
                    }
                    break;
                case 'edit':
                    if (Route::has("{$this->baseRoute}.edit")) {
                        return route("{$this->baseRoute}.edit", $routeKey);
                    }
                    break;
            }
        }

        return '#';
    }

    public function sortBy($field)
    {
        if (!in_array($field, $this->sortable)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Override in subclasses to apply non-trivial ORDER BY (subqueries, raw).
     * Return true if sort was applied (skips default orderBy).
     */
    protected function applySortToModelQuery(Builder $query): bool
    {
        return false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function getProcessedRowsProperty()
    {
        if ($this->maestroCatalogKey !== null) {
            return $this->getMaestroCatalogProcessedRows();
        }

        if ($this->useModel) {
            return $this->getProcessedModelData();
        }

        return $this->getProcessedArrayData();
    }

    protected function getMaestrosApiService(): MaestrosApiService
    {
        return app(MaestrosApiService::class);
    }

    protected function callMaestroCatalogApi(array $params): ?array
    {
        $svc = $this->getMaestrosApiService();

        return match ($this->maestroCatalogKey) {
            'container-types' => $svc->getContainerTypes($params),
            'ports' => $svc->getPorts($params),
            'transport-types' => $svc->getTransportTypes($params),
            'shipping-lines' => $svc->getShippingLines($params),
            'service-providers' => $svc->getServiceProviders($params),
            'rate-types' => $svc->getRateTypes($params),
            default => null,
        };
    }

    protected function decorateMaestroRows(Collection $rows): Collection
    {
        return $rows->map(function ($item) {
            if (!is_array($item)) {
                return $item;
            }
            $on = ($item['active'] ?? false) === true || $item['active'] === 'true' || $item['active'] === 1 || $item['active'] === '1';
            $item['active_formatted'] = $on
                ? '<span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Activo</span>'
                : '<span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">Inactivo</span>';

            return $item;
        });
    }

    protected function getMaestroCatalogProcessedRows(): LengthAwarePaginator
    {
        $baseParams = [
            'sort' => $this->sortField,
            'order' => $this->sortDirection,
        ];

        $active = $this->filters['active'] ?? '';
        if ($active !== '') {
            $baseParams['active'] = $active;
        }

        $searchTrimmed = trim($this->search);

        if ($searchTrimmed !== '') {
            $paginator = $this->fetchAllAndFilterCaseInsensitive(
                fn (array $params) => $this->callMaestroCatalogApi($params),
                $searchTrimmed,
                (int) $this->perPage,
                $this->getPage(),
                $this->sortField,
                $this->sortDirection,
                $baseParams
            );
            $paginator->setCollection($this->decorateMaestroRows($paginator->getCollection()));

            return $paginator;
        }

        $params = array_merge($baseParams, [
            'page' => $this->getPage(),
            'per_page' => $this->perPage,
        ]);

        $response = $this->callMaestroCatalogApi($params);

        if (!$response || !isset($response['data'])) {
            $empty = new LengthAwarePaginator(
                collect([]),
                0,
                (int) $this->perPage,
                $this->getPage(),
                ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'page']
            );
            $empty->setCollection($this->decorateMaestroRows($empty->getCollection()));

            return $empty;
        }

        $data = collect($response['data']);
        $total = $response['total'] ?? $response['meta']['total'] ?? $data->count();
        $currentPage = $response['current_page'] ?? $response['meta']['current_page'] ?? $this->getPage();
        $perPage = $response['per_page'] ?? $response['meta']['per_page'] ?? $this->perPage;
        $lastPage = $response['last_page'] ?? $response['meta']['last_page'] ?? (int) ceil($total / max(1, $perPage));

        $paginator = new LengthAwarePaginator(
            $data,
            $total,
            (int) $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'page',
            ]
        );

        if (method_exists($paginator, 'setLastPage')) {
            $paginator->setLastPage($lastPage);
        }

        $paginator->setCollection($this->decorateMaestroRows($paginator->getCollection()));

        return $paginator;
    }

    protected function getProcessedModelData()
    {
        $query = $this->model::query();

        // Load relationships if provided
        if (!empty($this->relationColumns)) {
            $query->with($this->relationColumns);
        }

        // Add relation counts if provided
        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        // Apply search if searchable fields are provided (case-insensitive)
        if (!empty($this->search) && !empty($this->searchable)) {
            $searchTerm = '%' . strtolower(trim($this->search)) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                foreach ($this->searchable as $field) {
                    // Handle relationship fields
                    if (strpos($field, '.') !== false) {
                        [$relation, $relationField] = explode('.', $field);
                        $q->orWhereHas($relation, function (Builder $subQ) use ($relationField, $searchTerm) {
                            $subQ->whereRaw('LOWER(' . $relationField . ') LIKE ?', [$searchTerm]);
                        });
                    } else {
                        $q->orWhereRaw('LOWER(' . $field . ') LIKE ?', [$searchTerm]);
                    }
                }
            });
        }

        // Apply filters
        foreach ($this->filters as $field => $value) {
            if (!empty($value)) {
                // Handle relationship fields
                if (strpos($field, '.') !== false) {
                    [$relation, $relationField] = explode('.', $field);
                    $query->whereHas($relation, function (Builder $q) use ($relationField, $value) {
                        $q->where($relationField, $value);
                    });
                } else {
                    $query->where($field, $value);
                }
            }
        }

        $this->modifyModelQuery($query);

        // Apply sorting
        if (!empty($this->sortField)) {
            $sortApplied = $this->applySortToModelQuery($query);
            if (!$sortApplied) {
                $sortColumn = $this->resolveSortColumn();
                // Handle relationship sorting
                if (strpos($sortColumn, '.') !== false) {
                    [$relation, $relationField] = explode('.', $sortColumn);

                    // Join to the related table and sort
                    $relatedTable = (new $this->model)->$relation()->getRelated()->getTable();
                    $foreignKey = (new $this->model)->$relation()->getForeignKeyName();
                    $localKey = (new $this->model)->$relation()->getQualifiedParentKeyName();

                    $query->join($relatedTable, $foreignKey, '=', $localKey)
                        ->orderBy("$relatedTable.$relationField", $this->sortDirection)
                        ->select((new $this->model)->getTable() . '.*'); // Select only from the main table
                } else {
                    $query->orderBy($sortColumn, $this->sortDirection);
                }
            }
        }

        return $query->paginate($this->perPage);
    }

    protected function getProcessedArrayData()
    {
        $rows = collect($this->rows);

        // Apply search if searchable fields are provided
        if (!empty($this->search) && !empty($this->searchable)) {
            $rows = $rows->filter(function ($row) {
                foreach ($this->searchable as $field) {
                    if (stripos($row[$field] ?? '', $this->search) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Apply filters
        foreach ($this->filters as $field => $value) {
            if (!empty($value)) {
                $rows = $rows->filter(function ($row) use ($field, $value) {
                    return ($row[$field] ?? '') == $value;
                });
            }
        }

        // Apply sorting
        if (!empty($this->sortField)) {
            $sortKey = $this->resolveSortColumn();
            $rows = $this->sortDirection === 'asc'
                ? $rows->sortBy($sortKey)
                : $rows->sortByDesc($sortKey);
        }

        // Manual pagination for collection
        $page = $this->getPage();
        $perPage = $this->perPage;
        $items = $rows->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function render()
    {
        return view('livewire.components.reusable-table', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ]);
    }
}
