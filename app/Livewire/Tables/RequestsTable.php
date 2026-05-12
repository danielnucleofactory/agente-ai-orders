<?php

namespace App\Livewire\Tables;

use App\Livewire\Components\ReusableTable;
use App\Support\SelectOptions;
use App\Models\Authorization;
use App\Models\PurchaseOrder;
use App\Services\AuthorizationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RequestsTable extends ReusableTable
{
    public bool $actions = false;

    public $filters = [
        'operation' => '',
        'status' => '',
    ];

    public $showModal = false;

    public $selectedRequest;

    public $requestId;

    public $buttonType = '';

    protected $listeners = [
        'refreshRequests' => '$refresh',
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
        $this->actions = (bool) $actions;
    
        $headers = [
            'created_at' => 'Fecha y hora',
            'operation_id' => 'ID Operación',
            'order_number' => 'Número de PO',
            'requester_name' => 'Usuario',
            'operation_type' => 'Operación',
            'status' => 'Estado',
        ];
    
        if ($this->actions) {
            $headers['actions'] = 'Acciones';
        }
    
        parent::mount(
            headers: $headers,
            sortable: [],
            searchable: ['operation_type', 'operation_id'],
            filterable: ['operation', 'status'],
            filterOptions: [
                'status' => SelectOptions::forSelectAssociative([
                    '' => 'Pendientes (predeterminado)',
                    'pending' => 'Pendientes',
                    'approved' => 'Aprobados',
                    'rejected' => 'Rechazados',
                ]),
                'operation' => [],
            ],
            withCount: [],
            model: Authorization::class,
            rows: [],
            relationColumns: ['requester'],
            actions: $this->actions,
            baseRoute: '',
            routeKeyName: 'id',
            actionsView: false,
            actionsEdit: false,
            actionsDelete: false,
            customActionsView: $this->actions 
                ? 'livewire.tables.partials.requests-row-actions' 
                : null,
        );
    
        $this->useModel = false;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->showSearch = true;
        $this->showPerPage = true;
    }

    public function closeModal(): void
    {
        $this->requestId = '';
        $this->selectedRequest = null;
        $this->buttonType = '';
        $this->dispatch('close-modal', 'modal-requests');
    }

    public function openModal($id, $buttonType): void
    {
        $this->requestId = $id;
        $this->buttonType = $buttonType;
        $this->selectedRequest = Authorization::find($id);
        $this->dispatch('open-modal', 'modal-requests');
    }

    public function approve($requestId): void
    {
        $request = Authorization::findOrFail($requestId);
        $service = app(AuthorizationService::class);

        $originalData = $request->data;
        $operationType = $request->operation_type;
        $authorizable = $request->authorizable;

        if ($service->approve($request)) {
            if ($operationType === 'attach_file_to_comment' &&
                $request->authorizable_type === PurchaseOrder::class &&
                $authorizable) {
                Log::info('Solicitud de archivo adjunto aprobada desde Livewire', [
                    'purchase_order_id' => $authorizable->id,
                    'request_id' => $request->id,
                    'operation_type' => $operationType,
                ]);
            } elseif ($operationType === 'upload_file' &&
                    $request->authorizable_type === PurchaseOrder::class &&
                    $authorizable) {
                Session::flash('file_upload_approved', true);
                Session::flash('purchase_order_id', $authorizable->id);
                Session::flash('approved_file_data', $originalData);

                Log::info('Solicitud de archivo aprobada desde Livewire', [
                    'purchase_order_id' => $authorizable->id,
                    'request_id' => $request->id,
                    'file_data' => $originalData,
                ]);
            }

            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => 'Solicitud aprobada correctamente.',
            ]);
        }

        $this->dispatch('close-modal', 'modal-requests');
    }

    public function reject($requestId): void
    {
        $request = Authorization::findOrFail($requestId);
        $service = app(AuthorizationService::class);

        if ($service->reject($request)) {
            $this->dispatch('showAlert', [
                'type' => 'success',
                'message' => 'Solicitud rechazada correctamente.',
            ]);
        }

        $this->dispatch('close-modal', 'modal-requests');
    }

    protected function syncOperationFilterOptions(): void
    {
        $q = Authorization::query();
        if (($this->filters['status'] ?? '') === '') {
            $q->where('status', Authorization::STATUS_PENDING);
        }
        $ops = $q->distinct()->pluck('operation_type')->filter()->values();
        $map = [];
        foreach ($ops as $op) {
            $map[$op] = Authorization::operationTypeLabel($op);
        }
        $this->filterOptions['operation'] = SelectOptions::sortAssociative($map);
    }

    public function getProcessedRowsProperty(): LengthAwarePaginator
    {
        $this->syncOperationFilterOptions();

        $query = Authorization::with(['requester', 'authorizer']);

        if (($this->filters['status'] ?? '') === '') {
            $query->where('status', Authorization::STATUS_PENDING);
        } else {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['operation'])) {
            $query->where('operation_type', $this->filters['operation']);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('operation_type', 'like', '%' . $this->search . '%')
                    ->orWhere('operation_id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('requester', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $sortCol = $this->resolveSortColumn();
        if ($sortCol === 'order_number') {
            $query->leftJoin('purchase_orders', function ($join) {
                $join->on('authorizations.authorizable_id', '=', 'purchase_orders.id')
                    ->where('authorizations.authorizable_type', '=', PurchaseOrder::class);
            })
                ->orderBy('purchase_orders.order_number', $this->sortDirection)
                ->select('authorizations.*');
        } elseif ($sortCol === 'requester_name') {
            $query->join('users', 'authorizations.requester_id', '=', 'users.id')
                ->orderBy('users.name', $this->sortDirection)
                ->select('authorizations.*');
        } elseif (!empty($this->sortField)) {
            $query->orderBy($sortCol, $this->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $requests = $query->paginate($this->perPage);

        $poAuthorizableIds = $requests->filter(function ($request) {
            return $request->authorizable_type === PurchaseOrder::class;
        })->pluck('authorizable_id')->toArray();

        if (!empty($poAuthorizableIds)) {
            $pos = PurchaseOrder::whereIn('id', $poAuthorizableIds)
                ->pluck('order_number', 'id')
                ->toArray();

            foreach ($requests as $request) {
                if ($request->authorizable_type === PurchaseOrder::class &&
                    isset($pos[$request->authorizable_id])) {
                    $request->order_number = $pos[$request->authorizable_id];
                }
            }
        }

        $statusClasses = [
            'pending' => 'inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800',
            'approved' => 'inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800',
            'rejected' => 'inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800',
        ];
        $statusLabels = [
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
        ];

        return $requests->through(function ($request) use ($statusClasses, $statusLabels) {
            $label = $statusLabels[$request->status] ?? $request->status;
            $cls = $statusClasses[$request->status] ?? 'inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800';

            return [
                'id' => $request->id,
                'created_at' => formatDateTime($request->created_at),
                'operation_id' => $request->operation_id,
                'order_number' => $request->order_number ?? $request->authorizable_id,
                'requester_name' => $request->requester->name ?? 'Usuario desconocido',
                'operation_type' => $request->operation_type_label,
                'status' => $request->status,
                'status_formatted' => '<span class="' . e($cls) . '">' . e($label) . '</span>',
            ];
        });
    }

    public function getRowSelectionKey($row): string
    {
        if (is_array($row)) {
            return (string) ($row['id'] ?? '');
        }

        return parent::getRowSelectionKey($row);
    }

    public function render()
    {
        return view('livewire.tables.requests-table', [
            'processedRows' => $this->getProcessedRowsProperty(),
            'statusClasses' => [
                'pending' => 'inline-flex px-2 text-xs font-semibold leading-5 text-yellow-800 bg-yellow-100 rounded-full',
                'approved' => 'inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full',
                'rejected' => 'inline-flex px-2 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full',
            ],
            'statusLabels' => [
                'pending' => 'Pendiente',
                'approved' => 'Aprobado',
                'rejected' => 'Rechazado',
            ],
        ]);
    }
}
