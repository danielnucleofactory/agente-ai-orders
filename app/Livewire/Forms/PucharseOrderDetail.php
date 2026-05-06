<?php

namespace App\Livewire\Forms;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderComment;
use App\Services\AuthorizationService;
use App\Services\PorthTimelineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class PucharseOrderDetail extends Component
{
    use WithFileUploads;

    public $purchaseOrder;

    public $purchaseOrderDetails;

    public $orderProducts = [];

    public $net_total = 0;

    public $additional_cost = 0;

    public $insurance_cost = 0;

    public $total = 0;

    public $loadingTracking = false;

    public $trackingData = [];

    public $shippingDocument;

    public $comments = [];

    public $attachments = [];

    public $commentSortField = 'created_at';

    public $commentSortDirection = 'desc';

    // Search and sorting variables
    public $search = '';

    public $sortField = 'material_id';

    public $sortDirection = 'asc';

    // Variables para sobre costo y comentario
    public $overCostData = [];

    public $totalOverCost = 0;

    public $newFile;

    public $newComment = '';

    public $fileSelected = false;

    public $comment = '';

    public $attachment = null;

    public $fileUploadApproved = false;

    public $commentAttachmentApproved = false;

    public $approvedCommentData = null;

    public $real_lead_time = 0;

    protected AuthorizationService $authorizationService;

    public function boot()
    {
        $this->authorizationService = app(AuthorizationService::class);
    }

    public function mount($id)
    {
        // Cargar la orden de compra con sus productos y hub relacionados
        $this->purchaseOrder = PurchaseOrder::with(['products', 'actualHub', 'shippingDocuments', 'vendor', 'kanbanStatus'])->findOrFail($id);
        $this->purchaseOrderDetails = PurchaseOrder::findOrFail($id);

        // Calcular lead time requerido
        $expectedLeadTime = 0;
        if ($this->purchaseOrder->date_required_in_destination && $this->purchaseOrder->date_planned_pickup) {
            $dateRequired = \Carbon\Carbon::parse($this->purchaseOrder->date_required_in_destination);
            $datePlannedPickup = \Carbon\Carbon::parse($this->purchaseOrder->date_planned_pickup);
            $expectedLeadTime = $datePlannedPickup->diffInDays($dateRequired);
        }
        $this->purchaseOrder->expected_lead_time = $expectedLeadTime;

        // Calcular lead time en tránsito
        $realLeadTime = 0;
        if ($this->purchaseOrder->date_eta && $this->purchaseOrder->date_actual_pickup) {
            $etaDate = \Carbon\Carbon::parse($this->purchaseOrder->date_eta);
            $actualPickupDate = \Carbon\Carbon::parse($this->purchaseOrder->date_actual_pickup);
            $realLeadTime = $actualPickupDate->diffInDays($etaDate);
        }
        $this->purchaseOrder->real_lead_time = $realLeadTime;

        // Cargar el shipping document asociado (si existe)
        $this->shippingDocument = $this->purchaseOrder->shippingDocuments->first();

        // Si hay shipping document, cargar las POs asociadas
        if ($this->shippingDocument) {
            $this->shippingDocument->load(['purchaseOrders']);
        }

        // Cargar los productos en el formato que necesitamos
        $this->loadOrderProducts();

        // Cargar los totales
        $this->loadTotals();

        // Cargar los datos de sobre costo
        $this->loadOverCostData();

        // Cargar tracking si la PO está en "Booking" (etapa 3) o superior
        // y tiene tracking_id, mbl_number o container_number (directamente o en shipping document)
        $kanbanStatusId = $this->purchaseOrder->kanban_status_id ?? 0;
        $hasTrackingData = $this->purchaseOrder->porth_id
            || $this->purchaseOrder->tracking_id
            || $this->purchaseOrder->mbl_number
            || $this->purchaseOrder->container_number
            || ($this->shippingDocument && (
                $this->shippingDocument->tracking_id
                || $this->shippingDocument->mbl_number
                || $this->shippingDocument->container_number
            ));

        if ($kanbanStatusId >= 3 && $hasTrackingData) {
            $this->loadTrackingData();
        }

        // Verificar si hay una aprobación de archivo pendiente
        $this->fileUploadApproved = Session::has('file_upload_approved') &&
                                    Session::get('purchase_order_id') == $id;

        // Verificar si hay una aprobación de comentario con archivo pendiente
        $this->commentAttachmentApproved = Session::has('comment_attachment_approved') &&
                                         Session::get('purchase_order_id') == $id &&
                                         Session::has('comment_id');

        // Log de diagnóstico sobre las variables de sesión
        \Log::info('Variables de sesión en mount', [
            'fileUploadApproved' => $this->fileUploadApproved,
            'commentAttachmentApproved' => $this->commentAttachmentApproved,
            'session_comment_id' => Session::get('comment_id'),
            'session_purchase_order_id' => Session::get('purchase_order_id'),
        ]);

        // Cargar los comentarios y archivos adjuntos después de verificar las aprobaciones
        $this->loadCommentsAndAttachments();
    }

    protected function loadOrderProducts()
    {
        $this->orderProducts = [];

        foreach ($this->purchaseOrder->products as $product) {
            $this->orderProducts[] = [
                'id' => $product->id,
                'material_id' => $product->material_id,
                'short_text' => $product->short_text,
                'price_per_unit' => $product->pivot->unit_price,
                'quantity' => $product->pivot->quantity,
                'subtotal' => $product->pivot->unit_price * $product->pivot->quantity,
            ];
        }
    }

    protected function loadTotals()
    {
        // Cargar los totales desde la orden de compra
        $this->net_total = $this->purchaseOrder->net_total ?? 0;
        $this->additional_cost = $this->purchaseOrder->additional_cost ?? 0;
        $this->insurance_cost = $this->purchaseOrder->insurance_cost ?? 0;
        $this->total = $this->purchaseOrder->total ?? 0;

        // Si no hay totales guardados, calcularlos
        if ($this->net_total == 0) {
            $this->calculateTotals();
        }
    }

    protected function calculateTotals()
    {
        // Calcular el total neto sumando los subtotales de todos los productos
        $this->net_total = 0;
        foreach ($this->orderProducts as $product) {
            $this->net_total += $product['subtotal'];
        }

        // Calcular el total final (neto + adicionales)
        $this->total = $this->net_total + $this->additional_cost + $this->insurance_cost;
    }

    protected function loadOverCostData()
    {
        try {
            $this->overCostData = [];
            $this->totalOverCost = 0;

            // Obtener los IDs de las órdenes de compra que necesitamos consultar
            $purchaseOrderIds = [];

            // Si hay shipping document, obtener todas las POs asociadas
            if ($this->shippingDocument) {
                $purchaseOrderIds = $this->shippingDocument->purchaseOrders->pluck('id')->toArray();
            } else {
                // Si no hay shipping document, usar solo la PO actual
                $purchaseOrderIds = [$this->purchaseOrder->id];
            }

            // Ejecutar la query optimizada para obtener datos de sobre costo
            $overCostResults = DB::select("
                SELECT
                    poc.id,
                    po.order_number AS order_number,
                    'Sobre costo' AS comment,
                    (regexp_match(poc.comment,
                        'Sobre costo[–-]?([0-9]+(?:\.[0-9]+)?)\s*usd',
                        'i'
                    ))[1]::numeric AS amount_usd,
                    (regexp_match(poc.comment,
                        'usd\s*[–-]?\s*(.*)$',
                        'i'
                    ))[1] AS comment_final
                FROM purchase_order_comments poc
                JOIN purchase_orders po
                    ON poc.purchase_order_id = po.id
                WHERE poc.comment ILIKE '%Sobre costo%'
                    AND po.id = ANY(?)
            ", ['{'.implode(',', $purchaseOrderIds).'}']);

            // Procesar los resultados
            foreach ($overCostResults as $result) {
                $this->overCostData[] = [
                    'id' => $result->id,
                    'order_number' => $result->order_number,
                    'comment' => $result->comment,
                    'amount_usd' => $result->amount_usd ? floatval($result->amount_usd) : 0,
                    'comment_final' => $result->comment_final ?? '',
                ];

                // Acumular el total
                if ($result->amount_usd) {
                    $this->totalOverCost += floatval($result->amount_usd);
                }
            }

            Log::info('Datos de sobre costo cargados', [
                'purchase_order_ids' => $purchaseOrderIds,
                'results_count' => count($this->overCostData),
                'total_over_cost' => $this->totalOverCost,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar datos de sobre costo', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $this->purchaseOrder->id ?? null,
            ]);

            // Inicializar con valores por defecto en caso de error
            $this->overCostData = [];
            $this->totalOverCost = 0;
        }
    }

    public function sortBy($field)
    {
        // If clicking on the current sort field, reverse direction
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        // Sort the orderProducts array
        usort($this->orderProducts, function ($a, $b) {
            $fieldA = $a[$this->sortField];
            $fieldB = $b[$this->sortField];

            // Handle numeric fields
            if (is_numeric($fieldA) && is_numeric($fieldB)) {
                return $this->sortDirection === 'asc'
                    ? $fieldA <=> $fieldB
                    : $fieldB <=> $fieldA;
            }

            // Handle string fields
            return $this->sortDirection === 'asc'
                ? strcmp($fieldA, $fieldB)
                : strcmp($fieldB, $fieldA);
        });
    }

    public function loadTrackingData()
    {
        $this->loadingTracking = true;

        try {
            // Obtener identificadores de la PO o del shipping document
            $porthId = $this->purchaseOrder->porth_id ?? null;
            $trackingId = $this->purchaseOrder->tracking_id
                ?? ($this->shippingDocument?->tracking_id ?? null);
            $mblNumber = $this->purchaseOrder->mbl_number
                ?? ($this->shippingDocument?->mbl_number ?? null);
            $containerNumber = $this->purchaseOrder->container_number
                ?? ($this->shippingDocument?->container_number ?? null);

            Log::info('Loading tracking data for purchase order (Porth):', [
                'purchase_order_id' => $this->purchaseOrder->id ?? null,
                'porth_id' => $porthId,
                'tracking_id' => $trackingId,
                'mbl_number' => $mblNumber,
                'container_number' => $containerNumber,
            ]);

            $this->trackingData = app(PorthTimelineService::class)->buildForPurchaseOrder($this->purchaseOrder);

            if ($this->trackingData) {
                Log::info('Tracking data loaded successfully from local Porth snapshot', [
                    'has_timeline' => isset($this->trackingData['timeline']),
                    'milestone' => $this->trackingData['current_phase'] ?? 'none',
                ]);
            } else {
                Log::info('No local Porth tracking data available for this PO', [
                    'purchase_order_id' => $this->purchaseOrder->id ?? null,
                    'porth_id' => $porthId,
                    'tracking_id' => $trackingId,
                    'mbl_number' => $mblNumber,
                    'container_number' => $containerNumber,
                ]);
                $this->trackingData = null; // Asegurar que sea null, no array vacío
            }
        } catch (\Throwable $e) {
            Log::error('Error loading tracking data from local Porth snapshot', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->trackingData = null; // Cambiar de [] a null para consistencia
        }

        $this->loadingTracking = false;
    }

    /**
     * Verificar si debe mostrarse la sección de tracking
     * Se muestra si:
     * 1. La PO está en "Booking" (etapa 3) o superior
     * 2. Hay identificadores de tracking (tracking_id, mbl_number o container_number)
     */
    public function shouldShowTrackingSection()
    {
        $kanbanStatusId = $this->purchaseOrder->kanban_status_id ?? 0;

        // Solo mostrar si está en "Booking" (etapa 3) o superior
        if ($kanbanStatusId < 3) {
            return false;
        }

        // Verificar si hay identificadores de tracking (incluye porth_id)
        $hasTrackingIdentifiers = $this->purchaseOrder->porth_id
            || $this->purchaseOrder->tracking_id
            || $this->purchaseOrder->mbl_number
            || $this->purchaseOrder->container_number
            || ($this->shippingDocument && (
                $this->shippingDocument->tracking_id
                || $this->shippingDocument->mbl_number
                || $this->shippingDocument->container_number
            ));

        return $hasTrackingIdentifiers;
    }

    /**
     * Verificar si debe mostrarse la línea de tiempo (timeline) con datos
     * Solo se muestra si hay datos válidos de tracking cargados
     */
    public function shouldShowTimeline()
    {
        // Verificar que se hayan cargado datos de tracking con timeline válidos
        return $this->trackingData !== null
            && ! empty($this->trackingData)
            && isset($this->trackingData['timeline'])
            && ! empty($this->trackingData['timeline']);
    }

    protected function loadCommentsAndAttachments()
    {
        try {
            // 1. Load all comments from database (both pending and approved)
            \Log::info('Loading comments for PO', [
                'purchase_order_id' => $this->purchaseOrder->id,
            ]);

            // Get all comments for this purchase order
            $comments = PurchaseOrderComment::with(['user.roles', 'media', 'authorizations'])
                ->where('purchase_order_id', $this->purchaseOrder->id)
                ->latest()
                ->get();

            \Log::info('Comments found', [
                'count' => $comments->count(),
                'purchase_order_id' => $this->purchaseOrder->id,
                'comment_ids' => $comments->pluck('id')->toArray(),
            ]);

            // Process comments based on their status
            $processedComments = $comments->map(function ($comment) {
                // Check for approved attachment first
                $attachment = $comment->getFirstMedia('attachments');

                // If no approved attachment, check for pending attachment
                $pendingAttachment = null;
                if (! $attachment) {
                    $pendingAttachment = $comment->getFirstMedia('pending_attachments');
                }

                // Get final attachment for display (either approved or pending)
                $displayAttachment = $attachment ?: $pendingAttachment;

                // Log for diagnostics
                \Log::info('Processing comment', [
                    'comment_id' => $comment->id,
                    'purchase_order_id' => $comment->purchase_order_id,
                    'user_id' => $comment->user_id,
                    'operation' => $comment->operacion,
                    'has_approved_attachment' => $attachment ? true : false,
                    'has_pending_attachment' => $pendingAttachment ? true : false,
                    'attachment_approval_status' => $pendingAttachment ? 'pending' : ($attachment ? 'approved' : 'none'),
                    'comment_text' => $comment->comment,
                ]);

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

                \Log::info('Comment status', [
                    'comment_id' => $comment->id,
                    'status' => $statusDisplay,
                    'icon_class' => $iconClass,
                    'has_pending_attachment' => $pendingAttachment ? true : false,
                    'has_approved_attachment' => $attachment ? true : false,
                ]);

                // Format the comment for display
                return [
                    'id' => $comment->id,
                    'user_name' => $comment->user->name ?? 'Usuario',
                    'user_role' => $comment->getRole() ?? 'Sin rol',
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'status' => $statusDisplay,
                    'status_icon' => $iconClass,
                    'operation' => $comment->operacion ?? 'Detalle PO',
                    'action_type' => $comment->action_type ?? 'comment',
                    'action_type_label' => $comment->getActionTypeLabel(),
                    'old_values' => $comment->old_values ?? null,
                    'new_values' => $comment->new_values ?? null,
                    'has_changes' => ! empty($comment->old_values) || ! empty($comment->new_values),
                    'attachment' => $displayAttachment ? [
                        'name' => $displayAttachment->file_name.($pendingAttachment ? ' (pendiente de aprobación)' : ''),
                        'url' => $attachment ? route('media.download', $displayAttachment->id) : '#', // Usar ruta de descarga con autenticación
                        'type' => strtoupper($displayAttachment->extension),
                        'is_pending' => $pendingAttachment ? true : false,
                    ] : null,
                    'attachment_name' => $displayAttachment ? $displayAttachment->file_name : '',
                ];
            })->toArray();

            $this->comments = $processedComments;

            \Log::info('Total comments loaded', [
                'count' => count($this->comments),
            ]);

            // Aplicar ordenación (por defecto created_at desc = mayor a menor)
            $this->sortCommentsAndAttachments();
        } catch (\Exception $e) {
            \Log::error('Error loading comments and attachments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->comments = [];
        }
    }

    // Función auxiliar para formatear bytes en unidades legibles
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    // Método para buscar en comentarios y archivos
    public function getFilteredCommentsAndAttachments()
    {
        $search = strtolower($this->search);

        $filteredComments = empty($search) ? $this->comments : array_filter($this->comments, function ($comment) use ($search) {
            return str_contains(strtolower($comment['user_name']), $search) ||
                   str_contains(strtolower($comment['comment']), $search);
        });

        $filteredAttachments = empty($search) ? $this->attachments : array_filter($this->attachments, function ($attachment) use ($search) {
            return str_contains(strtolower($attachment['user_name']), $search) ||
                   str_contains(strtolower($attachment['filename']), $search);
        });

        return [
            'comments' => array_values($filteredComments),
            'attachments' => array_values($filteredAttachments),
        ];
    }

    public function sortComments($field)
    {
        if ($this->commentSortField === $field) {
            $this->commentSortDirection = $this->commentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->commentSortField = $field;
            // Por defecto mayor a menor (desc) en todos los campos
            $this->commentSortDirection = 'desc';
        }

        $this->sortCommentsAndAttachments();
    }

    protected function sortCommentsAndAttachments()
    {
        $dir = $this->commentSortDirection === 'asc' ? 1 : -1;

        usort($this->comments, function ($a, $b) use ($dir) {
            $fieldA = $a[$this->commentSortField] ?? '';
            $fieldB = $b[$this->commentSortField] ?? '';

            // Fecha: comparar como datetime
            if ($this->commentSortField === 'created_at') {
                $tsA = $fieldA instanceof \Carbon\Carbon ? $fieldA->timestamp : (is_string($fieldA) ? strtotime($fieldA) : 0);
                $tsB = $fieldB instanceof \Carbon\Carbon ? $fieldB->timestamp : (is_string($fieldB) ? strtotime($fieldB) : 0);

                return $dir * ($tsA <=> $tsB);
            }

            // Resto: comparación alfabética
            $cmp = strcmp((string) $fieldA, (string) $fieldB);

            return $dir * $cmp;
        });
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:3',
        ]);

        try {
            // Crear el comentario en la base de datos
            $this->purchaseOrder->comments()->create([
                'user_id' => auth()->id() ?? 1,
                'comment' => $this->newComment,
            ]);

            session()->flash('message', 'Comentario añadido correctamente');

            // Recargar comentarios
            $this->loadCommentsAndAttachments();

            // Limpiar el campo
            $this->newComment = '';

            // Dispatch webhook event for updated purchase order (comment added)
            if (function_exists('dispatch_webhook')) {
                try {
                    $this->purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                    $freshPo = $this->purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                    // Convertir a array y asegurar que sea JSON serializable
                    $poData = $freshPo->toArray();
                    $poData = json_decode(json_encode($poData), true);

                    dispatch_webhook('purchase_order.updated', [
                        'purchase_order_id' => $this->purchaseOrder->id,
                        'order_number' => $this->purchaseOrder->order_number,
                        'changes' => ['comments' => 'new_comment_added'],
                        'data' => $poData,
                    ]);

                    \Log::info('dispatch_webhook completed after comment creation from PucharseOrderDetail', [
                        'po_id' => $this->purchaseOrder->id,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Error dispatching webhook after comment creation', [
                        'po_id' => $this->purchaseOrder->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error al añadir comentario: '.$e->getMessage());
        }
    }

    public function uploadFile()
    {
        $this->validate([
            'newFile' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            // Subida directa del archivo a la PO (sin solicitud de aprobación)
            $media = $this->purchaseOrder
                ->addMedia($this->newFile->getRealPath())
                ->usingName(pathinfo($this->newFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->usingFileName($this->newFile->getClientOriginalName())
                ->withCustomProperties([
                    'uploaded_by' => auth()->user()->name ?? 'Usuario',
                ])
                ->toMediaCollection('attachments');

            \Log::info('Archivo subido desde vista previa de PO', [
                'media_id' => $media->id,
                'purchase_order_id' => $this->purchaseOrder->id,
                'file_name' => $this->newFile->getClientOriginalName(),
            ]);

            session()->flash('message', 'Archivo subido correctamente');

            $this->newFile = null;

            // Si había sesión de aprobación previa, limpiarla
            if ($this->fileUploadApproved) {
                $this->fileUploadApproved = false;
                Session::forget(['file_upload_approved', 'purchase_order_id', 'approved_file_data']);
            }

            $this->loadCommentsAndAttachments();
        } catch (\Exception $e) {
            \Log::error('Error al subir archivo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'purchase_order_id' => $this->purchaseOrder->id ?? null,
            ]);
            session()->flash('error', 'Error al subir archivo: '.$e->getMessage());
        }
    }

    public function uploadFileAction()
    {
        $this->validate([
            'newFile' => 'required|file|max:10240', // 10MB max
        ]);

        $this->uploadFile();
    }

    public function setComments()
    {
        \Log::info('setComments method called', [
            'po_id' => $this->purchaseOrder->id ?? null,
            'has_comment' => ! empty(trim($this->comment ?? '')),
            'has_attachment' => ! empty($this->attachment),
            'comment_attachment_approved' => $this->commentAttachmentApproved ?? false,
        ]);

        // If we're attaching a file to an approved comment, we only need the file
        if ($this->commentAttachmentApproved) {
            $this->validate([
                'attachment' => 'required|file|max:10240', // 10MB max
            ], [
                'attachment.required' => 'Por favor, seleccione un archivo para adjuntar al comentario aprobado.',
            ]);
        }
        // In normal mode, we need at least a comment or an attachment
        elseif (empty(trim($this->comment)) && ! $this->attachment) {
            \Log::info('setComments: Early return - no comment and no attachment', [
                'po_id' => $this->purchaseOrder->id ?? null,
            ]);

            return;
        }

        try {
            // Log for diagnostics
            \Log::info('setComments called with values', [
                'commentAttachmentApproved' => $this->commentAttachmentApproved,
                'comment_id' => Session::get('comment_id'),
                'attachment' => $this->attachment ? true : false,
                'purchase_order_id' => Session::get('purchase_order_id'),
                'comment_text' => $this->comment,
            ]);

            // If there's an attachment and a previous approval, allow direct upload
            if ($this->attachment && $this->commentAttachmentApproved) {
                // Find the approved comment by ID
                $commentId = Session::get('comment_id');
                if ($commentId) {
                    $commentModel = \App\Models\PurchaseOrderComment::find($commentId);

                    if ($commentModel) {
                        \Log::info('Found comment to attach file to', [
                            'comment_id' => $commentId,
                            'purchase_order_id' => $commentModel->purchase_order_id,
                            'attachment_name' => $this->attachment->getClientOriginalName(),
                        ]);

                        try {
                            // Get filename before using it
                            $fileName = $this->attachment->getClientOriginalName();
                            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);

                            // Attach the file to the comment
                            $media = $commentModel
                                ->addMedia($this->attachment->getRealPath())
                                ->usingName($fileNameWithoutExt)
                                ->usingFileName($fileName)
                                ->toMediaCollection('attachments');

                            \Log::info('File attached to comment', [
                                'media_id' => $media->id,
                                'comment_id' => $commentModel->id,
                                'file_name' => $fileName,
                            ]);

                            session()->flash('message', 'Archivo adjuntado correctamente al comentario');

                            // Clean up fields and flags
                            $this->comment = '';
                            $this->attachment = null;
                            $this->commentAttachmentApproved = false;

                            // Clean up session
                            Session::forget(['comment_attachment_approved', 'comment_id', 'purchase_order_id']);

                            // Reload comments
                            $this->loadCommentsAndAttachments();

                            // Dispatch webhook event for updated purchase order (file attached to comment)
                            if (function_exists('dispatch_webhook')) {
                                try {
                                    $this->purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                                    $freshPo = $this->purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                                    // Convertir a array y asegurar que sea JSON serializable
                                    $poData = $freshPo->toArray();
                                    $poData = json_decode(json_encode($poData), true);

                                    dispatch_webhook('purchase_order.updated', [
                                        'purchase_order_id' => $this->purchaseOrder->id,
                                        'order_number' => $this->purchaseOrder->order_number,
                                        'changes' => ['comments' => 'file_attached_to_comment'],
                                        'data' => $poData,
                                    ]);

                                    \Log::info('dispatch_webhook completed after file attachment to comment', [
                                        'po_id' => $this->purchaseOrder->id,
                                    ]);
                                } catch (\Throwable $e) {
                                    \Log::error('Error dispatching webhook after file attachment to comment', [
                                        'po_id' => $this->purchaseOrder->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }

                            return;
                        } catch (\Exception $mediaException) {
                            \Log::error('Error attaching file to comment', [
                                'error' => $mediaException->getMessage(),
                                'trace' => $mediaException->getTraceAsString(),
                            ]);

                            throw $mediaException;
                        }
                    } else {
                        \Log::error('Comment not found with ID '.$commentId);
                    }

                    session()->flash('error', 'No se encontró el comentario aprobado para adjuntar el archivo');

                    return;
                }
            }

            // Create the comment regardless of attachment status
            $commentModel = new \App\Models\PurchaseOrderComment;
            $commentModel->purchase_order_id = $this->purchaseOrder->id;
            $commentModel->user_id = auth()->id();
            $commentModel->comment = $this->comment;
            $commentModel->operacion = 'Detalle PO';
            $commentModel->save();

            \Log::info('Comment created in setComments', [
                'comment_id' => $commentModel->id,
                'purchase_order_id' => $this->purchaseOrder->id,
                'user_id' => auth()->id(),
                'comment_text' => substr($this->comment, 0, 50),
            ]);

            // Si hay un archivo adjunto, subirlo directamente (sin solicitud de aprobación)
            if ($this->attachment) {
                try {
                    $fileName = $this->attachment->getClientOriginalName();
                    $commentModel
                        ->addMedia($this->attachment->getRealPath())
                        ->usingName(pathinfo($fileName, PATHINFO_FILENAME))
                        ->usingFileName($fileName)
                        ->toMediaCollection('attachments');

                    \Log::info('Archivo adjunto al comentario desde vista previa de PO', [
                        'comment_id' => $commentModel->id,
                        'purchase_order_id' => $this->purchaseOrder->id,
                        'file_name' => $fileName,
                    ]);

                    session()->flash('message', 'Comentario creado con archivo adjunto correctamente.');
                } catch (\Exception $e) {
                    \Log::error('Error al adjuntar archivo al comentario', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    session()->flash('error', 'Se creó el comentario pero hubo un error al guardar el archivo: '.$e->getMessage());
                }
            } else {
                session()->flash('message', 'Comentario agregado correctamente');
            }

            // Clean up fields
            $this->comment = '';
            $this->attachment = null;

            // Reload comments to show the new ones
            $this->loadCommentsAndAttachments();

            // Reload over cost data in case the new comment contains over cost information
            $this->loadOverCostData();

            // Dispatch webhook event for updated purchase order (comment added)
            \Log::info('About to dispatch webhook after comment creation', [
                'po_id' => $this->purchaseOrder->id,
                'function_exists' => function_exists('dispatch_webhook'),
                'webhook_enabled' => config('webhook.enabled', false),
            ]);

            if (function_exists('dispatch_webhook')) {
                try {
                    $this->purchaseOrder->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                    $freshPo = $this->purchaseOrder->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);

                    // Convertir a array y asegurar que sea JSON serializable
                    $poData = $freshPo->toArray();
                    $poData = json_decode(json_encode($poData), true);

                    \Log::info('Calling dispatch_webhook for purchase_order.updated after comment creation', [
                        'po_id' => $this->purchaseOrder->id,
                        'order_number' => $this->purchaseOrder->order_number,
                        'comments_count' => count($poData['comments'] ?? []),
                    ]);

                    dispatch_webhook('purchase_order.updated', [
                        'purchase_order_id' => $this->purchaseOrder->id,
                        'order_number' => $this->purchaseOrder->order_number,
                        'changes' => ['comments' => 'new_comment_added'],
                        'data' => $poData,
                    ]);

                    \Log::info('dispatch_webhook completed after comment creation from setComments', [
                        'po_id' => $this->purchaseOrder->id,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Error dispatching webhook after comment creation in setComments', [
                        'po_id' => $this->purchaseOrder->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Log::warning('dispatch_webhook function does not exist', [
                    'po_id' => $this->purchaseOrder->id,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Error setting comments: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'purchase_order_id' => $this->purchaseOrder->id ?? null,
            ]);
            session()->flash('error', 'Error al agregar el comentario: '.$e->getMessage());
        }
    }

    public function openCommentModal()
    {
        $this->comment = '';
        $this->attachment = null;
    }

    /**
     * Método de diagnóstico para verificar los comentarios de la orden de compra
     */
    public function debugComments()
    {
        try {
            // Consulta directa a la base de datos
            $comments = DB::table('purchase_order_comments')
                ->where('purchase_order_id', $this->purchaseOrder->id)
                ->get();

            // Log para diagnóstico
            \Log::info('Debug comentarios - Consulta directa', [
                'purchase_order_id' => $this->purchaseOrder->id,
                'comment_count' => $comments->count(),
                'comments' => $comments->toArray(),
            ]);

            // Usar relación del modelo
            $modelComments = $this->purchaseOrder->comments()->get();

            \Log::info('Debug comentarios - Relación del modelo', [
                'purchase_order_id' => $this->purchaseOrder->id,
                'comment_count' => $modelComments->count(),
                'comments' => $modelComments->toArray(),
            ]);

            session()->flash('message', 'Verificación de comentarios completada. Revise los logs para más detalles.');
            $this->loadCommentsAndAttachments(); // Recargar después de la verificación
            $this->loadOverCostData(); // Recargar datos de sobre costo

        } catch (\Exception $e) {
            \Log::error('Error en debugComments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error al verificar comentarios: '.$e->getMessage());
        }
    }

    /**
     * Método específico para diagnosticar problemas con adjuntar archivos a comentarios aprobados
     */
    public function debugAttachments()
    {
        try {
            // 1. Verificar si hay un archivo seleccionado
            if ($this->attachment) {
                \Log::info('Archivo seleccionado para diagnóstico', [
                    'file_name' => $this->attachment->getClientOriginalName(),
                    'file_size' => $this->attachment->getSize(),
                    'mime_type' => $this->attachment->getMimeType(),
                ]);
            } else {
                \Log::warning('No hay archivo seleccionado para diagnóstico');
            }

            // 2. Verificar si hay un ID de comentario en la sesión
            $commentId = Session::get('comment_id');
            if ($commentId) {
                \Log::info('ID de comentario encontrado en sesión', [
                    'comment_id' => $commentId,
                ]);

                // 3. Intentar recuperar el comentario de la BD
                $comment = \App\Models\PurchaseOrderComment::find($commentId);
                if ($comment) {
                    \Log::info('Comentario encontrado en la BD', [
                        'comment' => $comment->toArray(),
                        'existing_media' => $comment->getMedia('attachments')->count(),
                    ]);

                    // 4. Verificar si la tabla media tiene registros para este comentario
                    $mediaItems = DB::table('media')
                        ->where('model_type', 'App\\Models\\PurchaseOrderComment')
                        ->where('model_id', $commentId)
                        ->get();

                    \Log::info('Media asociados al comentario según tabla media', [
                        'count' => $mediaItems->count(),
                        'items' => $mediaItems->toArray(),
                    ]);

                    // 5. Si hay archivo seleccionado, intentar adjuntarlo manualmente
                    if ($this->attachment) {
                        try {
                            // 5.1 Método normal
                            $media = $comment->addMedia($this->attachment->getRealPath())
                                ->usingName(pathinfo($this->attachment->getClientOriginalName(), PATHINFO_FILENAME))
                                ->usingFileName($this->attachment->getClientOriginalName())
                                ->toMediaCollection('attachments');

                            \Log::info('Archivo adjuntado manualmente durante diagnóstico', [
                                'media_id' => $media->id,
                                'collection' => 'attachments',
                            ]);

                            // Notificar éxito
                            session()->flash('message', 'Archivo adjuntado manualmente durante diagnóstico');
                            $this->loadCommentsAndAttachments();
                            $this->loadOverCostData(); // Recargar datos de sobre costo
                        } catch (\Exception $e) {
                            \Log::error('Error al adjuntar archivo manualmente durante diagnóstico', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                            session()->flash('error', 'Error al adjuntar archivo: '.$e->getMessage());
                        }
                    }
                } else {
                    \Log::warning('El comentario con ID '.$commentId.' no existe en la BD');
                }
            } else {
                \Log::warning('No hay ID de comentario en la sesión');
            }

            // 6. Verificar variables de sesión relacionadas
            \Log::info('Variables de sesión actuales', [
                'comment_attachment_approved' => Session::has('comment_attachment_approved'),
                'comment_id' => Session::get('comment_id'),
                'purchase_order_id' => Session::get('purchase_order_id'),
            ]);

            session()->flash('message', 'Diagnóstico de archivos adjuntos completado. Verifique los logs.');
        } catch (\Exception $e) {
            \Log::error('Error en debugAttachments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error durante el diagnóstico: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Filter orderProducts if search is provided
        $filteredProducts = $this->orderProducts;
        if (! empty($this->search)) {
            $search = strtolower($this->search);
            $filteredProducts = array_filter($this->orderProducts, function ($product) use ($search) {
                return
                    str_contains(strtolower($product['material_id']), $search) ||
                    str_contains(strtolower($product['description']), $search);
            });
        }

        // Get filtered comments and attachments
        $filteredItems = $this->getFilteredCommentsAndAttachments();

        return view('livewire.forms.pucharse-order-detail', [
            'orderProducts' => $filteredProducts,
            'filteredComments' => $filteredItems['comments'],
            'filteredAttachments' => $filteredItems['attachments'],
        ])->layout('layouts.app');
    }

    public function calculateLoadDateDifference($purchaseOrder = null)
    {
        $po = $purchaseOrder ?? $this->purchaseOrder;

        // Esta función ya no es necesaria ya que se eliminó date_carga_po
        return '-';

        return $difference;
    }
}
