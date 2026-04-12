<?php

namespace App\Livewire\Settings;

use App\Livewire\Components\ReusableTable;
use App\Models\PurchaseOrderComment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithFileUploads;

class History extends ReusableTable
{
    use WithFileUploads;

    public $comment = '';
    public $attachment = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'desc'],
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
                'created_at' => 'Fecha y hora',
                'purchase_order_number' => 'Orden de compra',
                'user_name' => 'Usuario',
                'user_role' => 'Rol',
                'operacion' => 'Operación',
                'action_type' => 'Tipo',
                'attachment' => 'Archivos adjuntos',
                'changes' => 'Cambios',
            ],
            sortable: [],
            searchable: [],
            filterable: [],
            filterOptions: [],
            model: null,
            rows: [],
            actions: false,
            showSelectColumn: true,
        );

        $this->useModel = false;
        $this->showSearch = false;
        $this->showPerPage = true;
        $this->perPage = 25;
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->emptyMessage = 'No se encontraron comentarios';
    }

    protected function applyHistorySort(Builder $query): Builder
    {
        $dir = in_array($this->sortDirection, ['asc', 'desc'], true) ? $this->sortDirection : 'desc';

        switch ($this->sortField) {
            case 'user_name':
                return $query->leftJoin('users', 'purchase_order_comments.user_id', '=', 'users.id')
                    ->orderBy('users.name', $dir)
                    ->select('purchase_order_comments.*');

            case 'user_role':
                return $query->orderByRaw(
                    '(SELECT MIN(r.name) FROM model_has_roles mhr INNER JOIN roles r ON r.id = mhr.role_id WHERE mhr.model_id = purchase_order_comments.user_id AND mhr.model_type = ?) ' . $dir,
                    [User::class]
                );

            case 'purchase_order_number':
                return $query->leftJoin('purchase_orders', 'purchase_order_comments.purchase_order_id', '=', 'purchase_orders.id')
                    ->orderBy('purchase_orders.order_number', $dir)
                    ->select('purchase_order_comments.*');

            case 'operacion':
                return $query->orderBy('purchase_order_comments.operacion', $dir);

            case 'action_type':
                return $query->orderBy('purchase_order_comments.action_type', $dir);

            case 'attachment':
                return $query->orderByRaw(
                    '(SELECT COUNT(*) FROM media WHERE media.model_id = purchase_order_comments.id AND media.model_type = ? AND media.collection_name IN (\'attachments\',\'pending_attachments\')) ' . $dir,
                    [PurchaseOrderComment::class]
                );

            case 'changes':
                return $query->orderByRaw(
                    '(CASE WHEN '
                    . '(purchase_order_comments.old_values IS NOT NULL AND purchase_order_comments.old_values::text NOT IN (\'null\',\'[]\',\'"{}"\')) OR '
                    . '(purchase_order_comments.new_values IS NOT NULL AND purchase_order_comments.new_values::text NOT IN (\'null\',\'[]\',\'"{}"\')) '
                    . 'THEN 1 ELSE 0 END) ' . $dir
                );

            case 'created_at':
            default:
                return $query->orderBy('purchase_order_comments.created_at', $dir);
        }
    }

    public function getProcessedRowsProperty(): LengthAwarePaginator
    {
        $query = PurchaseOrderComment::query()->with(['user.roles', 'media', 'authorizations', 'purchaseOrder']);

        if (!empty($this->search)) {
            $searchTerm = '%' . strtolower($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->whereRaw('LOWER(purchase_order_comments.comment) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(purchase_order_comments.operacion) LIKE ?', [$searchTerm])
                    ->orWhereHas('user', function (Builder $userQuery) use ($searchTerm) {
                        $userQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                    })
                    ->orWhereHas('purchaseOrder', function (Builder $poQuery) use ($searchTerm) {
                        $poQuery->whereRaw('LOWER(order_number) LIKE ?', [$searchTerm]);
                    });
            });
        }

        $query = $this->applyHistorySort($query);

        return $query->paginate($this->perPage)->through(fn (PurchaseOrderComment $comment) => $this->mapCommentRow($comment));
    }

    private function mapCommentRow(PurchaseOrderComment $comment): array
    {
        $attachment = $comment->getFirstMedia('attachments');
        $pendingAttachment = null;
        if (!$attachment) {
            $pendingAttachment = $comment->getFirstMedia('pending_attachments');
        }
        $displayAttachment = $attachment ?: $pendingAttachment;

        $poNumber = e($comment->purchaseOrder->order_number ?? 'N/A');
        $poId = (int) $comment->purchase_order_id;
        $poUrl = e(route('purchase-orders.detail', $comment->purchase_order_id));

        $actionType = $comment->action_type ?? 'comment';
        $actionTypeHtml = match ($actionType) {
            'comment' => '<span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Comentario</span>',
            'field_change' => '<span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Cambio de Datos</span>',
            'status_change' => '<span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">Cambio de Estado</span>',
            'record_create' => '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Creación</span>',
            'porth_sync' => '<span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800">Actualización embarque</span>',
            default => '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Otro</span>',
        };

        $hasChanges = !empty($comment->old_values) || !empty($comment->new_values);
        $commentId = (int) $comment->id;
        $changesHtml = $hasChanges
            ? '<button type="button" wire:click="$dispatchTo(\'partials.activity-detail-modal\', \'openActivityDetail\', ' . $commentId . ')" class="text-[#1AAD8A] hover:text-[#0F614D] hover:underline">Ver cambios</button>'
            : '<span class="text-gray-400">-</span>';

        if ($displayAttachment) {
            $name = e($displayAttachment->file_name . ($pendingAttachment ? ' (pendiente de aprobación)' : ''));
            if ($pendingAttachment) {
                $attachmentHtml = '<span class="flex items-center gap-1 text-orange-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' . $name . '</span>';
            } else {
                $url = e(route('media.download', $displayAttachment->id));
                $attachmentHtml = '<a href="' . $url . '" class="flex items-center gap-1 text-[#1AAD8A] hover:text-[#0F614D]" target="_blank" rel="noopener"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>' . $name . '</a>';
            }
        } else {
            $attachmentHtml = '<span class="text-gray-400">Sin archivos</span>';
        }

        return [
            'id' => $comment->id,
            'created_at' => formatDateTime($comment->created_at),
            'purchase_order_number' => $poNumber,
            'purchase_order_number_html' => '<a href="' . $poUrl . '" class="text-[#1AAD8A] hover:text-[#0F614D] hover:underline">' . $poNumber . '</a>',
            'user_name' => e($comment->user->name ?? 'Usuario'),
            'user_role' => e($comment->getRole() ?? 'Sin rol'),
            'operacion' => e($comment->operacion ?? 'Detalle PO'),
            'action_type' => e($actionType),
            'action_type_html' => $actionTypeHtml,
            'attachment' => '',
            'attachment_html' => $attachmentHtml,
            'changes' => '',
            'changes_html' => $changesHtml,
        ];
    }

    public function render()
    {
        return view('livewire.settings.history', [
            'processedRows' => $this->getProcessedRowsProperty(),
        ])->layout('layouts.settings.audit');
    }
}
