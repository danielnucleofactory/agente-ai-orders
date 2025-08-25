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
    ];

    protected $casts = [
        'creation_date' => 'date',
        'estimated_departure_date' => 'date',
        'estimated_arrival_date' => 'date',
        'actual_departure_date' => 'date',
        'actual_arrival_date' => 'date',
        'release_date' => 'date',
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
