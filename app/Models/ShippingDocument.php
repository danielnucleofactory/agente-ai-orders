<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ShippingDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'document_number',
        'status',
        'creation_date',
        'estimated_departure_date',
        'estimated_arrival_date',
        'actual_departure_date',
        'actual_arrival_date',
        'hub_location',
        'total_weight_kg',
        'notes',
        'release_date',
        'booking_code',
        'container_number',
        'mbl_number',
        'hbl_number',

        // --- Nuevos campos por etapas ---
        // Producción
        'date_theorical_load',
        'date_variable_date',
        'service_provider',
        'forwarder_name',
        // Booking
        'date_booking_request',
        'date_booking_authorized',
        'date_etd_updated',   // ETD variable
        'container_type',
        'mode',
        // En Tránsito
        'date_eta_updated',   // ETA variable
        'shipping_line',
        'arrival_status',
        'factura_merca',
        'tracking_id',
        'departure_port',
        'arrival_port',
        'Invoice_amount',
        'bill_of_lading',
        // Almacén Fiscal
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        // Ingresada
        'receipt_note',
    ];

    protected $casts = [
        // existentes
        'creation_date' => 'date',
        'estimated_departure_date' => 'date',
        'estimated_arrival_date' => 'date',
        'actual_departure_date' => 'date',
        'actual_arrival_date' => 'date',
        'release_date' => 'date',

        // nuevos
        'date_theorical_load' => 'datetime',
        'date_variable_date' => 'datetime',
        'date_booking_request' => 'datetime',
        'date_booking_authorized' => 'datetime',
        'date_etd_updated' => 'datetime',
        'date_eta_updated' => 'datetime',
        'bonded_warehouse_enter' => 'datetime',
        'bonded_warehouse_exit' => 'datetime',
        'Invoice_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the shipping document.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the purchase orders associated with this shipping document.
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'purchase_order_shipping_document')
            ->withPivot('notes')
            ->withTimestamps();
    }

    /**
     * Calculate the total weight of all purchase orders in this shipping document.
     */
    public function calculateTotalWeight(): int
    {
        return $this->purchaseOrders->sum('weight_kg');
    }

    /**
     * Update the total weight based on the associated purchase orders.
     */
    public function updateTotalWeight(): void
    {
        $this->total_weight_kg = $this->calculateTotalWeight();
        $this->save();
    }

    /**
     * Relación con el estado de Kanban
     */
    public function kanbanStatus()
    {
        return $this->belongsTo(KanbanStatus::class);
    }

    /**
     * Relación con los comentarios del documento
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'shipping_document_id');
    }

    /**
     * Registrar las colecciones de medios para este modelo
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('shipping_documents');
        $this->addMediaCollection('comment_attachments');
    }

    /**
     * Get the files attached to the shipping document
     */
    public function files()
    {
        return $this->media()->where('collection_name', 'shipping_documents');
    }
}
