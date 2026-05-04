<?php

namespace App\Livewire\Components;

/**
 * Backward-compatible alias: prefer
 * {@see ReusableTable} with {@code maestro-catalog-key="..."} in Blade.
 */
class MaestroCatalogTable extends ReusableTable
{
    public function mount(
        $catalogKey = 'container-types',
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
        $customActionsView = null
    ): void {
        parent::mount(
            headers: $headers,
            sortable: $sortable,
            searchable: $searchable,
            filterable: $filterable,
            filterOptions: $filterOptions,
            withCount: $withCount,
            model: null,
            rows: $rows,
            relationColumns: $relationColumns,
            actions: false,
            baseRoute: $baseRoute,
            routeKeyName: 'olo_id',
            actionsView: false,
            actionsEdit: false,
            actionsDelete: false,
            viewPermission: $viewPermission,
            editPermission: $editPermission,
            deletePermission: $deletePermission,
            showSelectColumn: $showSelectColumn,
            sortFieldAliases: is_array($sortFieldAliases) ? $sortFieldAliases : [],
            customActionsView: $customActionsView,
            maestroCatalogKey: is_string($catalogKey) && $catalogKey !== '' ? $catalogKey : 'container-types',
        );
    }
}
