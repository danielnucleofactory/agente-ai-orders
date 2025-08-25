<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Traits\HasAuthorizations;
use App\Traits\HasPOConfirmationWrapper;

class PurchaseOrder extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasAuthorizations, HasPOConfirmationWrapper;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'order_number',
        'status',
        'notes',
        'vendor_id',
        'ship_to_id',
        'bill_to_id',
        'order_date',
        'currency',
        'incoterms',
        'payment_terms',
        'order_place',
        'email_agent',
        'net_total',
        'additional_cost',
        'total',
        'length',
        'width',
        'height',
        'volume',
        'weight_kg',
        'weight_lb',
        'date_required_in_destination',
        'date_planned_pickup',
        'date_actual_pickup',
        'date_estimated_hub_arrival',
        'date_actual_hub_arrival',
        'date_etd',
        'date_atd',
        'date_eta',
        'date_ata',
        'date_consolidation',
        'release_date',
        'insurance_cost',
        'ground_transport_cost_1',
        'ground_transport_cost_2',
        'cost_nationalization',
        'cost_ofr_estimated',
        'cost_ofr_real',
        'estimated_pallet_cost',
        'real_cost_estimated_po',
        'real_cost_real_po',
        'other_costs',
        'other_expenses',
        'variable_calculare_weight',
        'savings_ofr_fcl',
        'saving_pickup',
        'saving_executed',
        'saving_not_executed',
        'comments',
        'planned_hub_id',
        'actual_hub_id',
        'material_type',
        'ensurence_type',
        'mode',
        'tracking_id',
        'pallet_quantity',
        'pallet_quantity_real',
        'bill_of_lading',
        'pallets',
        'kanban_status_id',
        'length_cm',
        'width_cm',
        'height_cm',
        'confirmation_hash',
        'hash_expires_at',
        'confirmation_email_sent',
        'confirmation_email_sent_at',
        'update_date_po',
        'confirm_update_date_po',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => 'string',
        'net_total' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'volume' => 'decimal:3',
        'weight_kg' => 'decimal:2',
        'weight_lb' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'order_date' => 'date',
        'material_type' => 'array',
        'date_required_in_destination' => 'datetime',
        'date_planned_pickup' => 'datetime',
        'date_actual_pickup' => 'datetime',
        'date_estimated_hub_arrival' => 'datetime',
        'date_actual_hub_arrival' => 'datetime',
        'date_etd' => 'datetime',
        'date_atd' => 'datetime',
        'date_eta' => 'datetime',
        'date_ata' => 'datetime',
        'date_consolidation' => 'datetime',
        'release_date' => 'datetime',
        'hash_expires_at' => 'datetime',
        'confirmation_email_sent_at' => 'datetime',
        'update_date_po' => 'date',
        'confirm_update_date_po' => 'boolean',
    ];

    /**
     * Get the company that owns the purchase order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the vendor that owns the purchase order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the ship-to that owns the purchase order.
     */
    public function shipTo(): BelongsTo
    {
        return $this->belongsTo(ShipTo::class);
    }

    /**
     * Get the products for the purchase order.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'purchase_order_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    /**
     * Get the shipping documents associated with this purchase order.
     */
    public function shippingDocuments(): BelongsToMany
    {
        return $this->belongsToMany(ShippingDocument::class, 'purchase_order_shipping_document')
            ->withPivot('notes')
            ->withTimestamps();
    }

    /**
     * Get the boarding documents for the purchase order.
     */
    public function boardingDocuments(): HasMany
    {
        return $this->hasMany(BoardingDocument::class);
    }

    /**
     * Get the tracking data for the purchase order.
     */
    public function trackingData(): HasMany
    {
        return $this->hasMany(TrackingDataPO::class);
    }

    /**
     * Get the kanban status for the purchase order.
     */
    public function kanbanStatus(): BelongsTo
    {
        return $this->belongsTo(KanbanStatus::class);
    }

    /**
     * Move the purchase order to a new kanban status.
     */
    public function moveToKanbanStatus(KanbanStatus $status): self
    {
        $this->update(['kanban_status_id' => $status->id]);
        return $this;
    }

    /**
     * Move the purchase order to the next kanban status.
     */
    public function moveToNextKanbanStatus(): ?self
    {
        if ($this->kanbanStatus && $nextStatus = $this->kanbanStatus->nextStatus()) {
            return $this->moveToKanbanStatus($nextStatus);
        }
        return null;
    }

    /**
     * Move the purchase order to the previous kanban status.
     */
    public function moveToPreviousKanbanStatus(): ?self
    {
        if ($this->kanbanStatus && $prevStatus = $this->kanbanStatus->previousStatus()) {
            return $this->moveToKanbanStatus($prevStatus);
        }
        return null;
    }

    /**
     * Determine if the purchase order is consolidable based on weight.
     *
     * Rules:
     * - 0 to 5000 kg: Not consolidable
     * - 5001 to 15000 kg: Consolidable
     * - 15001+ kg: Not consolidable
     *
     * @return bool
     */
    public function isConsolidable(): bool
    {
        $weight = $this->weight_kg ?? 0;
        return $weight > 1 && $weight <= 20000;
    }

    /**
     * Get the total weight in kg.
     *
     * @return float
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->weight_kg ?? 0;
    }

    /**
     * Check if a collection of orders can be consolidated together.
     *
     * @param \Illuminate\Support\Collection $orders
     * @return bool
     */
    public static function canBeConsolidatedTogether($orders)
    {
        // Check if all orders are consolidable individually
        foreach ($orders as $order) {
            if (!$order->isConsolidable()) {
                return false;
            }
        }

        // Check if the total weight of all orders is within the consolidable range
        $totalWeight = $orders->sum('weight_kg');
        return $totalWeight > 1 && $totalWeight <= 20000;
    }

    /**
     * Obtener el hub planificado para esta orden de compra.
     */
    public function plannedHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'planned_hub_id');
    }

    /**
     * Obtener el hub real para esta orden de compra.
     */
    public function actualHub(): BelongsTo
    {
        return $this->belongsTo(Hub::class, 'actual_hub_id');
    }

    // Definir la colección de medios para los archivos adjuntos
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    // Relación con los comentarios
    public function comments()
    {
        return $this->hasMany(PurchaseOrderComment::class);
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use authorizations() from HasAuthorizations trait instead
     */
    public function authorizationRequests(): MorphMany
    {
        return $this->morphMany(Authorization::class, 'authorizable');
    }
}
