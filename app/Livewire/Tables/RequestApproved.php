<?php

namespace App\Livewire\Tables;

use App\Models\Authorization;
use Livewire\Component;
use Livewire\WithPagination;

class RequestApproved extends Component {
    use WithPagination;

    public $search = '';
    public $filters = [
        'status' => '', // 'approved' o 'rejected'
        'operation' => ''
    ];

    public $showModal = false;
    public $selectedRequest;

    public function openModal($id) {
        $this->selectedRequest = Authorization::find($id);
        $this->dispatch('open-modal', 'modal-requests');
    }

    public function closeModal() {
        $this->selectedRequest = null;
        $this->dispatch('close-modal', 'modal-requests');
    }

    public function render() {
        $query = Authorization::with(['requester', 'authorizer'])
                                   ->whereIn('status', ['approved', 'rejected']); // Solo procesadas

        // Aplicar búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('operation_type', 'like', '%' . $this->search . '%')
                  ->orWhere('operation_id', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhereHas('requester', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('authorizer', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Aplicar filtros
        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['operation']) {
            $query->where('operation_type', $this->filters['operation']);
        }

        $requests = $query->latest()->paginate(15);

        // Obtener operaciones únicas para el filtro, solo de solicitudes procesadas
        $operationTypes = Authorization::whereIn('status', ['approved', 'rejected'])
                                           ->select('operation_type')
                                           ->distinct()
                                           ->pluck('operation_type');

        return view('livewire.tables.request-approved', [
            'requests' => $requests,
            'operationTypes' => $operationTypes
        ]);
    }
}
