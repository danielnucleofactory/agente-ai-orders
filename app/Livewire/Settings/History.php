<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\PurchaseOrderComment;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class History extends Component
{
    use WithFileUploads;

    public $comments = [];
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Variables para el modal de subir documentos
    public $comment = '';
    public $attachment = null;

    public function mount()
    {
        $this->loadAllComments();
    }

    public function updatedSearch()
    {
        Log::info('Search updated - CALLED', [
            'search_value' => $this->search,
            'search_length' => strlen($this->search),
            'timestamp' => now()->toDateTimeString()
        ]);

        $this->loadAllComments();

        Log::info('Search updated - COMPLETED', [
            'final_comments_count' => count($this->comments)
        ]);
    }

    public function updated($propertyName)
    {
        Log::info('Property updated', [
            'property' => $propertyName,
            'value' => $this->$propertyName ?? 'null'
        ]);

        if ($propertyName === 'search') {
            $this->loadAllComments();
        }
    }

    // Método alternativo para testear el buscador
    public function testSearch($searchTerm = null)
    {
        if ($searchTerm !== null) {
            $this->search = $searchTerm;
        }

        Log::info('Test search called', [
            'search' => $this->search,
            'will_apply_filter' => !empty($this->search)
        ]);

        $this->loadAllComments();
    }

    // Método para debug del buscador
    public function debugSearch()
    {
        Log::info('Debug search called', [
            'search' => $this->search,
            'comments_count' => count($this->comments)
        ]);

        return [
            'search' => $this->search,
            'comments_count' => count($this->comments),
            'search_empty' => empty($this->search)
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->loadAllComments();
    }

    protected function loadAllComments()
    {
        try {
            Log::info('Loading all comments from all POs', [
                'search' => $this->search,
                'search_empty' => empty($this->search),
                'sortField' => $this->sortField,
                'sortDirection' => $this->sortDirection
            ]);

            // Start with base query and load all relationships
            $query = PurchaseOrderComment::with(['user.roles', 'media', 'authorizations', 'purchaseOrder']);

            // Apply search filter if provided
            if (!empty($this->search)) {
                $searchTerm = '%' . strtolower($this->search) . '%';
                Log::info('Applying search filter (case insensitive)', ['searchTerm' => $searchTerm]);

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

            // Get all matching comments first
            $allComments = $query->get();

            Log::info('Comments loaded before sorting', [
                'total_count' => $allComments->count(),
                'search_applied' => !empty($this->search)
            ]);

            // Handle sorting
            switch ($this->sortField) {
                case 'user_name':
                    if ($this->sortDirection === 'desc') {
                        $comments = $allComments->sortByDesc(function($comment) {
                            return $comment->user->name ?? 'Usuario';
                        })->values();
                    } else {
                        $comments = $allComments->sortBy(function($comment) {
                            return $comment->user->name ?? 'Usuario';
                        })->values();
                    }
                    break;

                case 'purchase_order_number':
                    if ($this->sortDirection === 'desc') {
                        $comments = $allComments->sortByDesc(function($comment) {
                            return $comment->purchaseOrder->order_number ?? 'N/A';
                        })->values();
                    } else {
                        $comments = $allComments->sortBy(function($comment) {
                            return $comment->purchaseOrder->order_number ?? 'N/A';
                        })->values();
                    }
                    break;

                case 'operacion':
                    if ($this->sortDirection === 'desc') {
                        $comments = $allComments->sortByDesc('operacion')->values();
                    } else {
                        $comments = $allComments->sortBy('operacion')->values();
                    }
                    break;

                case 'created_at':
                default:
                    if ($this->sortDirection === 'desc') {
                        $comments = $allComments->sortByDesc('created_at')->values();
                    } else {
                        $comments = $allComments->sortBy('created_at')->values();
                    }
                    break;
            }

            Log::info('Comments sorted', [
                'final_count' => $comments->count(),
                'sort_field' => $this->sortField,
                'sort_direction' => $this->sortDirection
            ]);

            // Process comments for display
            $processedComments = $comments->map(function($comment) {
                // Check for approved attachment first
                $attachment = $comment->getFirstMedia('attachments');

                // If no approved attachment, check for pending attachment
                $pendingAttachment = null;
                if (!$attachment) {
                    $pendingAttachment = $comment->getFirstMedia('pending_attachments');
                }

                // Get final attachment for display
                $displayAttachment = $attachment ?: $pendingAttachment;

                // Get status from authorization relationship
                $statusDisplay = 'Pendiente';
                $iconClass = 'warning';
                if ($comment->isApproved()) {
                    $statusDisplay = 'Aprobado';
                    $iconClass = 'success';
                } elseif ($comment->isRejected()) {
                    $statusDisplay = 'Rechazado';
                    $iconClass = 'danger';
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
                    'status_icon' => $iconClass,
                    'operation' => $comment->operacion ?? 'Detalle PO',
                    'attachment' => $displayAttachment ? [
                        'name' => $displayAttachment->file_name . ($pendingAttachment ? ' (pendiente de aprobación)' : ''),
                        'url' => $attachment ? $displayAttachment->getUrl() : '#',
                        'type' => strtoupper($displayAttachment->extension),
                        'is_pending' => $pendingAttachment ? true : false
                    ] : null
                ];
            })->toArray();

            $this->comments = $processedComments;

            Log::info('Comments processing completed', [
                'final_processed_count' => count($this->comments),
                'search_term' => $this->search
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading all comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'search' => $this->search
            ]);

            $this->comments = [];
        }
    }

    public function render()
    {
        return view('livewire.settings.history')
            ->layout('layouts.settings.audit');
    }
}
