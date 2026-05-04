<?php

namespace App\Livewire\Settings;

use App\Livewire\Components\ReusableTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Users extends ReusableTable
{
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
    ): void {
        abort_unless(auth()->user()?->can('has_view_users'), 403);

        parent::mount(
            headers: [
                'name' => 'Usuario',
                'email' => 'Correo',
                'created_at' => 'Fecha alta',
                'role_names' => 'Rol',
                'actions' => 'Acciones',
            ],
            sortable: ['name', 'email', 'created_at', 'role_names'],
            searchable: ['name', 'email'],
            filterable: [],
            filterOptions: [],
            withCount: [],
            model: User::class,
            rows: [],
            relationColumns: ['roles'],
            actions: true,
            baseRoute: 'settings.users',
            routeKeyName: 'id',
            actionsView: false,
            actionsEdit: true,
            actionsDelete: true,
            editPermission: 'has_edit_users',
            deletePermission: 'has_delete_users',
        );

        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected function modifyModelQuery(Builder $query): void
    {
        $query->with('roles');
    }

    protected function applySortToModelQuery(Builder $query): bool
    {
        if ($this->sortField !== 'role_names') {
            return false;
        }

        $dir = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'asc';
        $query->orderByRaw(
            '(SELECT MIN(r.name) FROM model_has_roles mhr INNER JOIN roles r ON r.id = mhr.role_id WHERE mhr.model_id = users.id AND mhr.model_type = ?) ' . $dir,
            [User::class]
        );

        return true;
    }

    public function render()
    {
        return view('livewire.settings.users', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ])->layout('layouts.settings.user-management');
    }
}
