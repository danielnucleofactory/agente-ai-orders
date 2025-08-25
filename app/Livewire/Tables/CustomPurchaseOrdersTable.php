<?php

namespace App\Livewire\Tables;

use App\Models\PurchaseOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class CustomPurchaseOrdersTable extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $statusFilter = '';
    public $consolidableFilter = '';
    public $selected = [];
    public $selectAll = false;
    public $release_date = '';
    public $comment_release = '';
    public $file = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
        'consolidableFilter' => ['except' => ''],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingConsolidableFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getPurchaseOrdersQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function getHasSelectedOrdersProperty()
    {
        return count($this->selected) > 0;
    }

    public function createShippingDocument()
    {
        if (empty($this->release_date)) {
            session()->flash('error', 'La fecha de release es obligatoria.');
            return;
        }

        if (count($this->selected) === 0) {
            session()->flash('error', 'No hay órdenes seleccionadas para crear el documento de embarque.');
            return;
        }

        // Get the selected purchase orders
        $selectedOrders = PurchaseOrder::whereIn('id', $this->selected)->get();

        // Check if all selected orders can be consolidated together
        if (!PurchaseOrder::canBeConsolidatedTogether($selectedOrders)) {
            // If not, check which orders are not consolidable individually
            $nonConsolidableOrders = $selectedOrders->filter(function($order) {
                return !$order->isConsolidable();
            });

            if ($nonConsolidableOrders->count() > 0) {
                $orderNumbers = $nonConsolidableOrders->pluck('order_number')->join(', ');
                session()->flash('error', "Las siguientes órdenes no son consolidables individualmente: {$orderNumbers}");
            } else {
                // If all orders are consolidable individually, then the total weight is outside the range
                $totalWeight = $selectedOrders->sum('weight_kg');
                session()->flash('error', "El peso total de las órdenes seleccionadas ({$totalWeight} kg) está fuera del rango permitido para consolidación (5001-15000 kg).");
            }
            return;
        }

        // Start a database transaction
        \DB::beginTransaction();

        try {
            // Create a new shipping document
            $shippingDocument = new \App\Models\ShippingDocument();
            $shippingDocument->company_id = $selectedOrders->first()->company_id;
            $shippingDocument->document_number = 'DOC-' . date('YmdHis') . '-' . rand(1000, 9999);
            $shippingDocument->status = 'draft';
            $shippingDocument->creation_date = now();

            // Set the release date from the modal input
            $shippingDocument->release_date = $this->release_date;

            // Set estimated dates based on the first order's dates
            $firstOrder = $selectedOrders->first();
            if ($firstOrder->date_required_in_destination) {
                $shippingDocument->estimated_arrival_date = $firstOrder->date_required_in_destination;
                // Set estimated departure date 7 days before estimated arrival
                $shippingDocument->estimated_departure_date = $firstOrder->date_required_in_destination->copy()->subDays(7);
            }

            // Set hub location if available
            if ($firstOrder->ship_to_nombre) {
                $shippingDocument->hub_location = $firstOrder->ship_to_nombre;
            }

            // Calculate total weight
            $totalWeight = $selectedOrders->sum('weight_kg');
            $shippingDocument->total_weight_kg = $totalWeight;

            // Save the shipping document
            $shippingDocument->save();

            // Guardar el comentario en la tabla shipping_document_comments si existe
            if (!empty($this->comment_release)) {
                // Asumiendo que tienes un modelo ShippingDocumentComment
                $comment = new \App\Models\ShippingDocumentComment();
                $comment->shipping_document_id = $shippingDocument->id;
                $comment->user_id = auth()->id() ?: 1;
                $comment->comment = $this->comment_release;
                $comment->save();
            }

            // Guardar el archivo adjunto si existe
            if ($this->file) {
                // Usar Media Library en lugar del enfoque anterior
                $shippingDocument->addMedia($this->file->getRealPath())
                    ->usingName($this->file->getClientOriginalName())
                    ->withCustomProperties([
                        'stage' => 'creation',
                        'comment' => $this->comment_release ?: null,
                        'uploaded_by' => auth()->id() ?: 'system'
                    ])
                    ->toMediaCollection('shipping_documents');
            }

            // Associate purchase orders with the shipping document
            foreach ($selectedOrders as $order) {
                // Update the order status to 'shipped'
                $order->status = 'shipped';
                $order->save();

                // Associate the order with the shipping document
                $shippingDocument->purchaseOrders()->attach($order->id, [
                    'notes' => 'Agregado automáticamente al crear el documento de embarque'
                ]);
            }

            // Commit the transaction
            \DB::commit();

            // Show success message
            session()->flash('message', 'Documento de embarque ' . $shippingDocument->document_number . ' creado exitosamente con ' . count($this->selected) . ' órdenes y un peso total de ' . number_format($totalWeight, 0) . ' kg.');

            // Reset selection after creating document
            $this->selected = [];
            $this->selectAll = false;

            // Reset the release date and other fields after successful creation
            $this->release_date = '';
            $this->comment_release = '';
            $this->file = null;

        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            \DB::rollBack();

            // Show error message
            session()->flash('error', 'Error al crear el documento de embarque: ' . $e->getMessage());
        }
    }

    public function openReleaseModal()
    {
        if (count($this->selected) === 0) {
            session()->flash('error', 'No hay órdenes seleccionadas para crear el documento de embarque.');
            return;
        }

        // Get the selected purchase orders
        $selectedOrders = PurchaseOrder::whereIn('id', $this->selected)->get();

        // Check if all selected orders can be consolidated together
        if (!PurchaseOrder::canBeConsolidatedTogether($selectedOrders)) {
            // If not, check which orders are not consolidable individually
            $nonConsolidableOrders = $selectedOrders->filter(function($order) {
                return !$order->isConsolidable();
            });

            if ($nonConsolidableOrders->count() > 0) {
                $orderNumbers = $nonConsolidableOrders->pluck('order_number')->join(', ');
                session()->flash('error', "Las siguientes órdenes no son consolidables individualmente: {$orderNumbers}");
            } else {
                // If all orders are consolidable individually, then the total weight is outside the range
                $totalWeight = $selectedOrders->sum('weight_kg');
                session()->flash('error', "El peso total de las órdenes seleccionadas ({$totalWeight} kg) está fuera del rango permitido para consolidación (5001-15000 kg).");
            }
            return;
        }

        // Open the modal
        $this->dispatch('open-modal', 'modal-hub-teorico');
    }

    public function addReleaseDate()
    {
        // Validate release date
        $this->validate([
            'release_date' => 'required|date',
            'file' => 'nullable|file|max:5120|mimes:xls,xlsx,pdf', // Validar archivo: máx 5MB, formatos permitidos
        ], [
            'release_date.required' => 'La fecha de release es obligatoria.',
            'release_date.date' => 'La fecha de release debe ser una fecha válida.',
            'file.file' => 'El archivo adjunto debe ser un archivo válido.',
            'file.max' => 'El archivo adjunto no debe exceder 5MB.',
            'file.mimes' => 'El archivo adjunto debe ser de tipo: xls, xlsx, pdf.',
        ]);

        // Close the modal
        $this->dispatch('close-modal', 'modal-hub-teorico');

        // Proceed with shipping document creation
        $this->createShippingDocument();

        // Show success message
        $this->dispatch('open-modal', 'modal-consolidate-order');
    }

    protected function getPurchaseOrdersQuery()
    {
        return PurchaseOrder::query()
            ->when($this->search, function ($query) {
                $searchTerm = strtolower($this->search);
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(order_number) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(CAST(vendor_id AS TEXT)) LIKE ?', ['%' . $searchTerm . '%'])
                        ->orWhereRaw('LOWER(notes) LIKE ?', ['%' . $searchTerm . '%']);
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->consolidableFilter, function ($query) {
                if ($this->consolidableFilter === 'yes') {
                    $query->where('weight_kg', '>', 5000)->where('weight_kg', '<=', 15000);
                } elseif ($this->consolidableFilter === 'no') {
                    $query->where(function ($query) {
                        $query->where('weight_kg', '<=', 5000)
                            ->orWhere('weight_kg', '>', 15000);
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $purchaseOrders = $this->getPurchaseOrdersQuery()->paginate($this->perPage);

        return view('livewire.tables.custom-purchase-orders-table', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }
}
