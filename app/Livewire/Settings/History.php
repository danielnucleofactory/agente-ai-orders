<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PurchaseOrderComment;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class History extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Variables para el modal de subir documentos
    public $comment = '';
    public $attachment = null;

    // Reset paginación al buscar
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $query = PurchaseOrderComment::with(['user.roles', 'media', 'authorizations', 'purchaseOrder']);

        // Filtro de búsqueda
        if (!empty($this->search)) {
            $searchTerm = '%' . strtolower($this->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(comment) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(operacion) LIKE ?', [$searchTerm])
                  ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                      $userQuery->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                  })
                  ->orWhereHas('purchaseOrder', function($poQuery) use ($searchTerm) {
                      $poQuery->whereRaw('LOWER(order_number) LIKE ?', [$searchTerm]);
                  });
            });
        }

        // Ordenamiento en la base de datos
        switch ($this->sortField) {
            case 'user_name':
                $query->leftJoin('users', 'purchase_order_comments.user_id', '=', 'users.id')
                      ->orderBy('users.name', $this->sortDirection)
                      ->select('purchase_order_comments.*');
                break;

            case 'purchase_order_number':
                $query->leftJoin('purchase_orders', 'purchase_order_comments.purchase_order_id', '=', 'purchase_orders.id')
                      ->orderBy('purchase_orders.order_number', $this->sortDirection)
                      ->select('purchase_order_comments.*');
                break;

            case 'operacion':
                $query->orderBy('operacion', $this->sortDirection);
                break;

            case 'created_at':
            default:
                $query->orderBy('created_at', $this->sortDirection);
                break;
        }

        // Paginación
        $paginator = $query->paginate($this->perPage);

        // Procesar cada comentario para display
        $comments = $paginator->through(function($comment) {
            $attachment = $comment->getFirstMedia('attachments');
            $pendingAttachment = null;
            if (!$attachment) {
                $pendingAttachment = $comment->getFirstMedia('pending_attachments');
            }
            $displayAttachment = $attachment ?: $pendingAttachment;

            $statusDisplay = 'Pendiente';
            if ($comment->isApproved()) {
                $statusDisplay = 'Aprobado';
            } elseif ($comment->isRejected()) {
                $statusDisplay = 'Rechazado';
            }

            return [
                'id' => $comment->id,
                'purchase_order_number' => $comment->purchaseOrder->order_number ?? 'N/A',
                'purchase_order_id' => $comment->purchase_order_id,
                'user_name' => $comment->user->name ?? 'Usuario',
                'user_role' => $comment->getRole() ?? 'Sin rol',
                'comment' => $comment->comment,
                'created_at' => $comment->created_at,
                'status' => $statusDisplay,
                'operation' => $comment->operacion ?? 'Detalle PO',
                'action_type' => $comment->action_type ?? 'comment',
                'action_type_label' => $comment->getActionTypeLabel(),
                'old_values' => $comment->old_values ?? null,
                'new_values' => $comment->new_values ?? null,
                'has_changes' => !empty($comment->old_values) || !empty($comment->new_values),
                'attachment' => $displayAttachment ? [
                    'name' => $displayAttachment->file_name . ($pendingAttachment ? ' (pendiente de aprobación)' : ''),
                    'url' => $attachment ? route('media.download', $displayAttachment->id) : '#',
                    'type' => strtoupper($displayAttachment->extension),
                    'is_pending' => $pendingAttachment ? true : false
                ] : null
            ];
        });

        return view('livewire.settings.history', [
            'comments' => $comments,
        ])->layout('layouts.settings.audit');
    }
}
