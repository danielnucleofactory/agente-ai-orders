<?php

namespace App\Livewire\Kanban;

use App\Models\KanbanBoard;
use Livewire\Component;

class KanbanBoardList extends Component
{
    public $boards = [];

    public function mount()
    {
        $this->loadBoards();
    }

    public function loadBoards()
    {
        // Obtener los tableros Kanban de la compañía del usuario actual
        $companyId = auth()->user()->company_id ?? null;
        $this->boards = KanbanBoard::where('company_id', $companyId)
            ->where('type', 'purchase_orders')
            ->where('is_active', true)
            ->get();
    }

    public function render()
    {
        return view('livewire.kanban.kanban-board-list')
            ->layout('layouts.app');
    }
}
