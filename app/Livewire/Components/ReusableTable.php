<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class ReusableTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Table configuration
    public $headers = [];
    public $sortable = [];
    public $searchable = [];
    public $filterable = [];
    public $filterOptions = [];
    public $relationColumns = [];

    // Actions configuration
    public $showActions = false;
    public $actionsView = false;
    public $actionsEdit = false;
    public $actionsDelete = false;
    public $routeKeyName = 'id';
    public $baseRoute = '';

    // Deletion confirmation
    public $confirmingDelete = false;
    public $deleteId = null;

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

    public function delete()
    {
        // Debugging: Log the state before attempting deletion
        \Log::info('Delete method called', [
            'useModel' => $this->useModel,
            'deleteId' => $this->deleteId,
            'model' => $this->model
        ]);

        if (!$this->useModel || !$this->deleteId) {
            \Log::info('Delete cancelled - not using model or no deleteId');
            return;
        }

        try {
            // Check if this is a Spatie Role model which uses find() instead of findOrFail()
            if ($this->model === 'Spatie\\Permission\\Models\\Role') {
                $record = $this->model::find($this->deleteId);
                if ($record) {
                    \Log::info('Found Spatie role to delete', ['id' => $this->deleteId]);
                    $record->delete();
                    \Log::info('Role deleted successfully');
                } else {
                    \Log::error('Role not found', ['id' => $this->deleteId]);
                }
            }
            // For standard Laravel models that support findOrFail
            elseif (method_exists($this->model, 'findOrFail')) {
                $record = $this->model::findOrFail($this->deleteId);
                \Log::info('Found record to delete', ['id' => $this->deleteId]);
                $record->delete();
                \Log::info('Record deleted successfully');
            }
            // Fallback for other models - try to use find() method
            else {
                \Log::info('Using find() method fallback');
                $record = $this->model::find($this->deleteId);
                if ($record) {
                    $record->delete();
                    \Log::info('Record deleted using find() fallback');
                } else {
                    \Log::error('Record not found with find() fallback', ['id' => $this->deleteId]);
                }
            }

            // Reset pagination if we've deleted the last item on the current page
            if ($this->getProcessedRowsProperty()->count() === 0 && $this->getPage() > 1) {
                $this->resetPage();
            }

            $this->reset(['confirmingDelete', 'deleteId']);

            // Emit event for notification
            $this->dispatch('itemDeleted');

        } catch (\Exception $e) {
            \Log::error('Error deleting record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    public function mount(
        $headers = [],
        $sortable = [],
        $searchable = [],
        $filterable = [],
        $filterOptions = [],
        $model = null,
        $rows = [],
        $relationColumns = [],
        $actions = false,
        $baseRoute = '',
        $routeKeyName = 'id',
        $actionsView = true,
        $actionsEdit = true,
        $actionsDelete = true
    )
    {
        $this->headers = $headers;
        $this->sortable = $sortable;
        $this->searchable = $searchable;
        $this->filterable = $filterable;
        $this->filterOptions = $filterOptions;
        $this->relationColumns = $relationColumns;

        // Configure actions
        $this->showActions = $actions;
        $this->baseRoute = $baseRoute;
        $this->routeKeyName = $routeKeyName;
        $this->actionsView = $actionsView;
        $this->actionsEdit = $actionsEdit;
        $this->actionsDelete = $actionsDelete;

        // If actions is true and headers don't include 'actions', add it
        if ($this->showActions && !isset($this->headers['actions'])) {
            $this->headers['actions'] = 'Acciones';
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
        if (!empty($sortable) && empty($this->sortField)) {
            $this->sortField = $sortable[0];
        }

        // Initialize filters
        foreach ($this->filterable as $field) {
            $this->filters[$field] = '';
        }
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
        if ($this->useModel) {
            return $this->getProcessedModelData();
        } else {
            return $this->getProcessedArrayData();
        }
    }

    protected function getProcessedModelData()
    {
        $query = $this->model::query();

        // Load relationships if provided
        if (!empty($this->relationColumns)) {
            $query->with($this->relationColumns);
        }

        // Apply search if searchable fields are provided
        if (!empty($this->search) && !empty($this->searchable)) {
            $query->where(function (Builder $q) {
                foreach ($this->searchable as $field) {
                    // Handle relationship fields
                    if (strpos($field, '.') !== false) {
                        [$relation, $relationField] = explode('.', $field);
                        $q->orWhereHas($relation, function (Builder $subQ) use ($relationField) {
                            $subQ->where($relationField, 'like', '%' . $this->search . '%');
                        });
                    } else {
                        $q->orWhere($field, 'like', '%' . $this->search . '%');
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

        // Apply sorting
        if (!empty($this->sortField)) {
            // Handle relationship sorting
            if (strpos($this->sortField, '.') !== false) {
                [$relation, $relationField] = explode('.', $this->sortField);

                // Join to the related table and sort
                $relatedTable = (new $this->model)->$relation()->getRelated()->getTable();
                $foreignKey = (new $this->model)->$relation()->getForeignKeyName();
                $localKey = (new $this->model)->$relation()->getQualifiedParentKeyName();

                $query->join($relatedTable, $foreignKey, '=', $localKey)
                    ->orderBy("$relatedTable.$relationField", $this->sortDirection)
                    ->select((new $this->model)->getTable() . '.*'); // Select only from the main table
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
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
            $rows = $this->sortDirection === 'asc'
                ? $rows->sortBy($this->sortField)
                : $rows->sortByDesc($this->sortField);
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

    /**
     * Get the current page from the query string
     */
    public function getPage()
    {
        return $this->page ?? 1;
    }

    public function render()
    {
        return view('livewire.components.reusable-table', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ]);
    }
}
