<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\KanbanBoard;
use App\Models\KanbanStatus;
use Illuminate\Support\Facades\Auth;

class Kanban extends Component
{
    public $boards = [];

    // Búsqueda, filtrado y ordenamiento
    public $search = '';
    public $filter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $selectedBoard = null;
    public $editingStage = null;
    public $stageName = '';

    // Modal states
    public $viewingStages = false;
    public $editingStageModal = false;

    protected $listeners = [
        'refreshBoards' => 'loadBoards',
        'viewStages' => 'viewStages'
    ];

    protected $rules = [
        'stageName' => 'required|string|max:255',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->loadBoards();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->loadBoards();
    }

    public function updatedSearch()
    {
        $this->loadBoards();
    }

    public function updatedFilter()
    {
        $this->loadBoards();
    }

    public function loadBoards()
    {
        $companyId = Auth::user()->company_id;

        $query = KanbanBoard::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount('statuses as stages_count');

        // Aplicar búsqueda
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Aplicar filtrado
        if (!empty($this->filter)) {
            $query->where('type', $this->filter);
        }

        // Aplicar ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);

        // Obtener resultados
        $boards = $query->get();

        $this->boards = $boards->map(function ($board) {
            return [
                'id' => $board->id,
                'name' => $board->name,
                'description' => $board->description,
                'type' => $this->getBoardTypeName($board->type),
                'raw_type' => $board->type,
                'stages_count' => $board->stages_count,
            ];
        })->toArray();
    }

    protected function getBoardTypeName($type)
    {
        $types = [
            'po_stages' => 'Etapas PO',
            'shipping_documentation' => 'Documentación de embarque',
        ];

        return $types[$type] ?? $type;
    }

    public function viewStages($boardId)
    {
        $this->selectedBoard = KanbanBoard::with(['statuses' => function ($query) {
            $query->orderBy('position');
        }])->findOrFail($boardId);

        $this->viewingStages = true;
    }

    public function startEditStage($stageId)
    {
        $this->editingStage = KanbanStatus::findOrFail($stageId);
        $this->stageName = $this->editingStage->name;

        $this->editingStageModal = true;
    }

    public function updateStageName()
    {
        $this->validate();

        if ($this->editingStage) {
            $this->editingStage->update([
                'name' => $this->stageName
            ]);

            $this->editingStageModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Nombre de etapa actualizado correctamente.'
            ]);

            $this->reset(['editingStage', 'stageName']);
            if ($this->selectedBoard) {
                $this->viewStages($this->selectedBoard->id);
            }
        }
    }

    public function closeViewingStages()
    {
        $this->viewingStages = false;
    }

    public function closeEditingStage()
    {
        $this->editingStageModal = false;
    }

    public function render()
    {
        return view('livewire.settings.kanban')->layout('layouts.settings.preferences');
    }
}
