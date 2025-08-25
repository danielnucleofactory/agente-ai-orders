<?php

namespace App\Livewire\Tables;

use App\Models\Authorization;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use App\Services\AuthorizationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;


class RequestsTable extends Component {

    use WithPagination;

    public $actions = false;
    public $search = '';
    public $filters = [
        'operation' => ''
    ];

    public $showModal = false;
    public $selectedRequest;
    public $requestId;
    public $buttonType = '';

    protected $listeners = [
        'refreshRequests' => '$refresh'
    ];

    public function mount($actions = false) {
        $this->actions = $actions;
    }

    public function closeModal() {
        $this->requestId = '';
        $this->selectedRequest = null;
        $this->buttonType = '';
        $this->dispatch('close-modal', 'modal-requests');
    }

    public function openModal($id, $buttonType) {
        $this->requestId = $id;
        $this->buttonType = $buttonType;
        $this->selectedRequest = Authorization::find($id);
        $this->dispatch('open-modal', 'modal-requests');
    }

    public function approve($requestId)
    {
        $request = Authorization::findOrFail($requestId);
        $service = app(AuthorizationService::class);

        // Guardar datos originales antes de cambiar el estado
        $originalData = $request->data;
        $operationType = $request->operation_type;
        $authorizable = $request->authorizable;

        // Aprobar la solicitud en el servicio (esto solo cambia el estado)
        if ($service->approve($request)) {
            // Para operaciones que requieren adjuntar un archivo a un comentario
            if ($operationType === 'attach_file_to_comment' &&
                $request->authorizable_type === PurchaseOrder::class &&
                $authorizable) {

                // No necesitamos crear un comentario nuevo, el servicio de autorización
                // ya maneja mover el archivo de 'pending_attachments' a 'attachments'
                Log::info('Solicitud de archivo adjunto aprobada desde Livewire', [
                    'purchase_order_id' => $authorizable->id,
                    'request_id' => $request->id,
                    'operation_type' => $operationType
                ]);
            }
            // Para operaciones de subir archivo
            elseif ($operationType === 'upload_file' &&
                    $request->authorizable_type === PurchaseOrder::class &&
                    $authorizable) {

                // No creamos comentario para subir archivo, solo guardamos la información de sesión
                Session::flash('file_upload_approved', true);
                Session::flash('purchase_order_id', $authorizable->id);
                // Incluir los datos originales
                Session::flash('approved_file_data', $originalData);

                Log::info('Solicitud de archivo aprobada desde Livewire', [
                    'purchase_order_id' => $authorizable->id,
                    'request_id' => $request->id,
                    'file_data' => $originalData
                ]);
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => 'Solicitud aprobada correctamente.'
            ]);
        }

        $this->dispatch('close-modal', 'modal-requests');
    }

    public function reject($requestId)
    {
        $request = Authorization::findOrFail($requestId);
        $service = app(AuthorizationService::class);

        if ($service->reject($request)) {
            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => 'Solicitud rechazada correctamente.'
            ]);
        }

        $this->dispatch('close-modal', 'modal-requests');
    }

    public function render() {
        $query = Authorization::with(['requester', 'authorizer'])
                                   ->where('status', 'pending'); // Solo pendientes

        // Aplicar búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('operation_type', 'like', '%' . $this->search . '%')
                  ->orWhere('operation_id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('requester', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Aplicar filtro de operación
        if ($this->filters['operation']) {
            $query->where('operation_type', $this->filters['operation']);
        }

        $requests = $query->latest()->paginate(10);

        // Obtener todos los IDs de solicitudes relacionadas con PurchaseOrder
        $poAuthorizableIds = $requests->filter(function($request) {
            return $request->authorizable_type === 'App\\Models\\PurchaseOrder';
        })->pluck('authorizable_id')->toArray();

        // Si hay solicitudes relacionadas con PurchaseOrder
        if (!empty($poAuthorizableIds)) {
            // Hacer una consulta directa para obtener los números de orden
            $pos = PurchaseOrder::whereIn('id', $poAuthorizableIds)
                     ->pluck('order_number', 'id')
                     ->toArray();

            // Asignar los números de orden a las solicitudes correspondientes
            foreach ($requests as $request) {
                if ($request->authorizable_type === 'App\\Models\\PurchaseOrder' &&
                    isset($pos[$request->authorizable_id])) {
                    $request->order_number = $pos[$request->authorizable_id];
                }
            }
        }

        // Obtener operaciones únicas solo de solicitudes pendientes para el filtro
        $operationTypes = Authorization::where('status', 'pending')
                                           ->select('operation_type')
                                           ->distinct()
                                           ->pluck('operation_type');

        return view('livewire.tables.requests-table', [
            'requests' => $requests,
            'operationTypes' => $operationTypes
        ]);
    }
}
