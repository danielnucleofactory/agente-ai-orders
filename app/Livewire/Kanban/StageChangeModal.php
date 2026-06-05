<?php

namespace App\Livewire\Kanban;

use App\Models\KanbanBoard as KanbanBoardModel;
use App\Models\PurchaseOrder;
use Database\Seeders\KanbanBoardSeeder;

class StageChangeModal extends KanbanBoard
{
    protected $listeners = [];

    public function mount($boardId = null, ?string $embedBoardType = null): void
    {
        $this->boardType = $this->determineInitialBoardType($embedBoardType);

        if (! $boardId) {
            $companyId = auth()->user()->company_id ?? null;
            $this->board = KanbanBoardModel::where('company_id', $companyId)
                ->where('type', $this->boardType)
                ->where('is_active', true)
                ->first();

            if (! $this->board && $this->boardType === 'po_stages') {
                (new KanbanBoardSeeder)->ensureBoardsForCompanyId((int) $companyId);
                $this->board = KanbanBoardModel::where('company_id', $companyId)
                    ->where('type', $this->boardType)
                    ->where('is_active', true)
                    ->first();
            }

            if ($this->board) {
                $this->boardId = $this->board->id;
            }
        } else {
            $this->boardId = $boardId;
            $this->board = KanbanBoardModel::findOrFail($boardId);
            $this->boardType = $this->board->type;
        }

        $this->loadColumns();
    }

    public function openForTask(int $taskId, int $newColumnId): void
    {
        $po = PurchaseOrder::find($taskId);

        if ($po) {
            $this->currentTask = [
                'id' => $po->id,
                'po' => $po->order_number,
                'status' => $po->kanban_status_id,
            ];
        } else {
            $this->currentTask = [
                'id' => $taskId,
                'po' => (string) $taskId,
                'status' => null,
            ];
        }

        parent::setCurrentTask($taskId, $newColumnId);
    }

    public function cancelModal(): void
    {
        parent::cancelModal();
        $this->dispatch('refreshKanban');
    }

    public function render()
    {
        return view('livewire.kanban.stage-change-modal', [
            'columns' => $this->columns,
        ]);
    }
}
