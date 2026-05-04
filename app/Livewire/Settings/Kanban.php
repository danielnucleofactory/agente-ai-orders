<?php

namespace App\Livewire\Settings;

use App\Livewire\Components\ReusableTable;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Kanban extends ReusableTable
{
    public $selectedBoard = null;
    public $editingStage = null;
    public $stageName = '';

    public $viewingStages = false;
    public $editingStageModal = false;

    protected $listeners = [
        'refreshBoards' => '$refresh',
        'viewStages' => 'viewStages',
    ];

    protected $rules = [
        'stageName' => 'required|string|max:255',
    ];

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
        parent::mount(
            headers: [
                'name' => 'Nombre',
                'description' => 'Descripción',
                'type_label' => 'Tipo',
                'stages_count' => 'Número de etapas',
                'actions' => 'Acciones',
            ],
            sortable: [],
            searchable: ['name', 'description'],
            filterable: [],
            filterOptions: [],
            withCount: ['statuses as stages_count'],
            model: KanbanBoard::class,
            rows: [],
            relationColumns: [],
            actions: true,
            baseRoute: '',
            routeKeyName: 'id',
            actionsView: false,
            actionsEdit: false,
            actionsDelete: false,
            sortFieldAliases: [
                'type_label' => 'type',
                'stages_count' => 'stages_count',
            ],
            customActionsView: 'livewire.settings.partials.kanban-board-actions',
        );

        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected function modifyModelQuery(Builder $query): void
    {
        $query->where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->where('type', '!=', 'shipping_documentation');
    }

    public function viewStages($boardId): void
    {
        $this->selectedBoard = KanbanBoard::with(['statuses' => function ($query) {
            $query->orderBy('position');
        }])->findOrFail($boardId);

        $this->viewingStages = true;
    }

    public function startEditStage($stageId): void
    {
        $this->editingStage = KanbanStatus::findOrFail($stageId);
        $this->stageName = $this->editingStage->name;

        $this->editingStageModal = true;
    }

    public function updateStageName(): void
    {
        $this->validate();

        if ($this->editingStage) {
            $this->editingStage->update([
                'name' => $this->stageName,
            ]);

            $this->editingStageModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Nombre de etapa actualizado correctamente.',
            ]);

            $this->reset(['editingStage', 'stageName']);
            if ($this->selectedBoard) {
                $this->viewStages($this->selectedBoard->id);
            }
        }
    }

    public function closeViewingStages(): void
    {
        $this->viewingStages = false;
    }

    public function closeEditingStage(): void
    {
        $this->editingStageModal = false;
    }

    public function render()
    {
        return view('livewire.settings.kanban', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ])->layout('layouts.settings.preferences');
    }
}
