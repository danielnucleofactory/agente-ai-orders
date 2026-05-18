<?php

namespace App\Models;

use App\Models\Traits\HasAuthorizations;
use App\Models\Traits\SoftCascadeDeletes;
use App\Traits\HasPOConfirmationWrapper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PurchaseOrder extends Model implements HasMedia
{
    use HasAuthorizations, HasFactory, HasPOConfirmationWrapper, InteractsWithMedia, SoftCascadeDeletes, SoftDeletes;

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
        'date_eta_initial',
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
        'insurance_type',
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
        'last_email_type_sent',
        'last_email_sent_at',
        'email_sent_history',
        'update_date_po',
        'confirm_update_date_po',

        // New fields for OLO
        'factory_proforma_number',
        'mbl_number',
        'container_type',
        'container_number',
        'shipping_line',
        'is_dropship',
        'applies_tlc',
        'applies_af',
        'date_booking_request',
        'date_booking_authorized',
        'date_theorical_load',
        'date_variable_date',
        'carga_lista_validada',
        'date_received',
        'logistics_incoterm',
        'reason',
        'category',
        'forwarder_name',
        'cargo_invoice_number',
        'tariff_type',
        'route_label',
        'arrival_status',
        'delay_days',
        'date_eta_updated',
        'date_etd_updated',
        'arrival_port',
        'departure_port',
        'port_of_loading_validated',
        'has_facture_merca',
        'used_rate_ok',
        'uses_bonded_warehouse',
        'apply_technical_note',
        'etd_initial_validated',
        'date_etd_initial',
        'inspection_date',
        'vgm_cut_date',
        'balance_payment_date',
        'local_charges_payment_date',
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        'receipt_note_date',
        'estimated_dc_availability_date',
        'retail_group',
        'customer_type',
        'trading_company',
        'service_provider',
        'customs_dua',
        'invoice',
        'factura_merca',
        'case_number_file',
        'receipt_note',
        'visibility_notes',
        'Invoice_amount',
        'freight_amount',
        'total_amount',
        'container_free_days',
        'etd_dates_difference',
        'eta_dates_difference',
        'price_incoterm',
        'date_invoice_received',
        'date_vendor_document_received',
        'cbm',
        'dif_load_date',
        'emision_date_po',
        'vendor_number',
        'consolidator_name',
        'forwader_date',

        // Campos de Porth
        'porth_pol',
        'porth_pol_name',
        'porth_pod',
        'porth_pod_name',
        'porth_origin',
        'porth_final_destination',
        'porth_carrier_code',
        'porth_vessel_voyage',
        'porth_shipment_number',
        'porth_modality',
        'freight_type',
        'porth_first_eta',
        'porth_first_etd',
        'porth_ready',
        'porth_to_origin_port',
        'porth_at_origin_port',
        'porth_in_transit',
        'porth_at_destination_port',
        'porth_to_final_destination',
        'porth_delivered',
        'porth_phase',
        'porth_priority',
        'porth_manual_tracking',
        'porth_free_time_at_destination',
        'porth_itinerary',
        'porth_id',
        'last_porth_sync_at',
        'tracking_not_applicable',
        'tracking_not_applicable_reason',
        'tracking_not_applicable_approved_by',
        'tracking_not_applicable_approved_at',
    ];

    /**
     * Campos excluidos de toArray()/toJson() (webhook, API, etc.).
     * - Computados: arrival_status, delay_days, etd_dates_difference, eta_dates_difference (se calculan, no deben enviarse)
     * - Porth: campos internos de sincronización con Porth
     * - No Intelix: campos del payload que no están en mapeo_definitivo_intelix.md ni son maestros (xxx)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'company_id',
        'arrival_status',
        'delay_days',
        'etd_dates_difference',
        'eta_dates_difference',
        'variable_calculare_weight',
        'Invoice_amount',
        'vendor_number',
        // No Intelix ni maestros (del payload enviado)
        'kanban_status_id',
        'weight_kg',
        'weight_lb',
        'saving_pickup',
        'saving_executed',
        'saving_not_executed',
        'category',
        'invoice',
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
        'date_required_in_destination',
        'date_planned_pickup',
        'date_actual_pickup',
        'date_estimated_hub_arrival',
        'date_actual_hub_arrival',
        'date_consolidation',
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
        'savings_ofr_fcl',
        'planned_hub_id',
        'actual_hub_id',
        'material_type',
        'ensurence_type',
        'insurance_type',
        'tracking_id',
        'pallet_quantity',
        'pallet_quantity_real',
        'bill_of_lading',
        'pallets',
        'length_cm',
        'width_cm',
        'height_cm',
        'confirmation_hash',
        'hash_expires_at',
        'confirmation_email_sent',
        'confirmation_email_sent_at',
        'last_email_type_sent',
        'last_email_sent_at',
        'email_sent_history',
        'update_date_po',
        'confirm_update_date_po',
        'factory_proforma_number',
        'is_dropship',
        'applies_tlc',
        'applies_af',
        'date_received',
        'reason',
        'forwarder_name',
        'route_label',
        'date_eta_updated',
        'date_etd_updated',
        'used_rate_ok',
        'apply_technical_note',
        'balance_payment_date',
        'receipt_note_date',
        'estimated_dc_availability_date',
        'retail_group',
        'customer_type',
        'customs_dua',
        'case_number_file',
        'receipt_note',
        'total_amount',
        'date_vendor_document_received',
        'dif_load_date',
        'emision_date_po',
        'consolidator_name',
        'freight_type',
        'porth_id',
        'porth_shipment_number',
        'porth_carrier_code',
        'porth_pol',
        'porth_pod',
        'porth_pol_name',
        'porth_pod_name',
        'porth_phase',
        'porth_priority',
        'porth_modality',
        'porth_vessel_voyage',
        'porth_origin',
        'porth_final_destination',
        'porth_first_eta',
        'porth_first_etd',
        'porth_ready',
        'porth_to_origin_port',
        'porth_at_origin_port',
        'porth_in_transit',
        'porth_at_destination_port',
        'porth_to_final_destination',
        'porth_delivered',
        'porth_free_time_at_destination',
        'porth_manual_tracking',
        'porth_itinerary',
        'last_porth_sync_at',
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

        // New casts for OLO
        'is_dropship' => 'boolean',
        'applies_tlc' => 'boolean',
        'applies_af' => 'boolean',

        'date_booking_request' => 'datetime',
        'date_booking_authorized' => 'datetime',
        'date_theorical_load' => 'datetime',
        'date_variable_date' => 'datetime',
        'carga_lista_validada' => 'boolean',
        'date_received' => 'datetime',

        'delay_days' => 'integer',

        'date_eta_initial' => 'datetime',
        'date_eta_updated' => 'datetime',
        'date_etd_updated' => 'datetime',

        'consolidator_name' => 'string',
        'port_of_loading_validated' => 'boolean',
        'has_facture_merca' => 'boolean',
        'used_rate_ok' => 'boolean',
        'uses_bonded_warehouse' => 'boolean',
        'apply_technical_note' => 'boolean',
        'etd_initial_validated' => 'boolean',
        'date_etd_initial' => 'datetime',
        'inspection_date' => 'datetime',
        'vgm_cut_date' => 'datetime',
        'balance_payment_date' => 'datetime',
        'local_charges_payment_date' => 'datetime',
        'bonded_warehouse_enter' => 'datetime',
        'bonded_warehouse_exit' => 'datetime',
        'receipt_note_date' => 'datetime',
        'estimated_dc_availability_date' => 'datetime',
        'retail_group' => 'string',
        'customer_type' => 'string',
        'trading_company' => 'string',
        'service_provider' => 'string',
        'customs_dua' => 'string',
        'invoice' => 'string',
        'factura_merca' => 'string',
        'case_number_file' => 'string',
        'receipt_note' => 'string',
        'visibility_notes' => 'string',
        'Invoice_amount' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'container_free_days' => 'integer',
        'etd_dates_difference' => 'integer',
        'eta_dates_difference' => 'integer',
        'date_invoice_received' => 'datetime',
        'date_vendor_document_received' => 'datetime',
        'forwader_date' => 'datetime',
        'cbm' => 'decimal:2',
        'dif_load_date' => 'integer',
        'emision_date_po' => 'date',

        // Casts de Porth
        'porth_vessel_voyage' => 'array',
        'porth_manual_tracking' => 'boolean',
        'porth_first_eta' => 'datetime',
        'porth_first_etd' => 'datetime',
        'porth_ready' => 'datetime',
        'tracking_not_applicable' => 'boolean',
        'tracking_not_applicable_approved_at' => 'datetime',
        'porth_to_origin_port' => 'datetime',
        'porth_at_origin_port' => 'datetime',
        'porth_in_transit' => 'datetime',
        'porth_at_destination_port' => 'datetime',
        'porth_to_final_destination' => 'datetime',
        'porth_delivered' => 'datetime',
        'porth_itinerary' => 'array',
        'last_porth_sync_at' => 'datetime',
    ];

    protected array $softCascade = [
        'comments',
        'shippingDocuments',
        'products',
        'boardingDocuments',
        'trackingData',
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
     * vendor_id es FK a vendors.id (no vendo_code).
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    /**
     * Get the ship-to that owns the purchase order.
     */
    public function shipTo(): BelongsTo
    {
        return $this->belongsTo(ShipTo::class);
    }

    /**
     * Get the bill-to that owns the purchase order.
     */
    public function billTo(): BelongsTo
    {
        return $this->belongsTo(BillTo::class);
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

    public function porthCargos(): HasMany
    {
        return $this->hasMany(PorthCargo::class);
    }

    public function porthPhases(): HasMany
    {
        return $this->hasMany(PorthPhase::class);
    }

    public function porthItineraries(): HasMany
    {
        return $this->hasMany(PorthItinerary::class);
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
     * Indica si la PO está en etapa Kanban "Ingresada" o "Anulada", o anulada por soft delete.
     * Criterio alineado con {@see self::scopeOperationalForDashboard()}.
     */
    public function isKanbanIngresadaOrAnulada(): bool
    {
        if ($this->trashed()) {
            return true;
        }

        $status = $this->relationLoaded('kanbanStatus')
            ? $this->kanbanStatus
            : $this->kanbanStatus()->first();

        if (! $status) {
            return false;
        }

        $slug = strtolower(trim((string) ($status->slug ?? '')));
        $name = strtolower(trim((string) ($status->name ?? '')));
        $blocked = ['ingresada', 'anulada'];

        return in_array($slug, $blocked, true) || in_array($name, $blocked, true);
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
     * Restricciones eliminadas - todas las órdenes son consolidables sin restricciones de peso
     */
    public function isConsolidable(): bool
    {
        // Sin restricciones - todas las órdenes son consolidables
        return true;
    }

    /**
     * Get the total weight in kg.
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->weight_kg ?? 0;
    }

    /**
     * Check if a collection of orders can be consolidated together.
     *
     * Restricciones eliminadas - todas las órdenes pueden consolidarse sin restricciones
     *
     * @param  \Illuminate\Support\Collection  $orders
     * @return bool
     */
    public static function canBeConsolidatedTogether($orders)
    {
        // Sin restricciones - todas las órdenes pueden consolidarse
        return true;
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
     *
     * @deprecated Use authorizations() from HasAuthorizations trait instead
     */
    public function authorizationRequests(): MorphMany
    {
        return $this->morphMany(Authorization::class, 'authorizable');
    }

    /**
     * Calcula cumplimiento de entrega contra la fecha comprometida.
     *
     * @return array{arrival_status: string|null, delay_days: int|null}
     */
    public static function calculateDeliveryCompliance($etaInitial, $ata): array
    {
        if (blank($etaInitial) || blank($ata)) {
            return [
                'arrival_status' => null,
                'delay_days' => null,
            ];
        }

        $committedDate = \Illuminate\Support\Carbon::parse($etaInitial)->startOfDay();
        $deliveredDate = \Illuminate\Support\Carbon::parse($ata)->startOfDay();
        $delayDays = (int) $committedDate->diffInDays($deliveredDate, false);

        if ($delayDays > 0) {
            return [
                'arrival_status' => 'Atrasado',
                'delay_days' => $delayDays,
            ];
        }

        return [
            'arrival_status' => 'A tiempo',
            'delay_days' => 0,
        ];
    }

    /**
     * Calcula automáticamente el estado de cumplimiento y días de retraso.
     *
     * @return array{arrival_status: string|null, delay_days: int|null}
     */
    public function calculateArrivalStatus(): array
    {
        return self::calculateDeliveryCompliance($this->date_eta_initial, $this->date_ata);
    }

    public static function calculateLoadDateDifference($theoreticalLoadDate, $variableLoadDate): ?int
    {
        if (blank($theoreticalLoadDate) || blank($variableLoadDate)) {
            return null;
        }

        $theoretical = \Illuminate\Support\Carbon::parse($theoreticalLoadDate)->startOfDay();
        $variable = \Illuminate\Support\Carbon::parse($variableLoadDate)->startOfDay();

        return (int) $theoretical->diffInDays($variable, false);
    }

    /**
     * Actualiza automáticamente el estado de llegada y días de retraso
     */
    public function updateArrivalStatus(): bool
    {
        $status = $this->calculateArrivalStatus();

        $this->arrival_status = $status['arrival_status'];
        $this->delay_days = $status['delay_days'];

        return $this->save();
    }

    /**
     * Boot method to add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Disparar sincronización automática una sola vez por guardado.
        // El sync con Porth debe ocurrir después del commit y después de responder,
        // para no contaminar la respuesta del API principal de POs.
        static::saved(function ($purchaseOrder) {
            static::dispatchPorthSync($purchaseOrder);
        });
    }

    /**
     * PO operativas para dashboard y export «activas»: excluye etapas Ingresada y Anulada (slug/nombre).
     * Exige fila de kanban asociada, alineado con ActivePurchaseOrdersExport.
     */
    public function scopeOperationalForDashboard(Builder $query): Builder
    {
        return $query->whereHas('kanbanStatus', function (Builder $q) {
            $q->whereRaw("LOWER(COALESCE(slug, '')) NOT IN ('ingresada', 'anulada')")
                ->whereRaw("LOWER(COALESCE(name, '')) NOT IN ('ingresada', 'anulada')");
        });
    }

    /**
     * Disparar sincronización con Porth
     */
    protected static function dispatchPorthSync($purchaseOrder)
    {
        // Verificar si la sincronización está habilitada
        $syncEnabled = config('services.porth.sync_enabled', true);
        if (! $syncEnabled) {
            \Log::info('Porth sync disabled by configuration', [
                'purchase_order_id' => $purchaseOrder->id,
                'sync_enabled' => $syncEnabled,
            ]);

            return;
        }

        // Si la PO fue aprobada como "No aplica tracking", no debe intentar
        // vincular ni crear embarques en Porth al guardar cambios manuales.
        if ((bool) $purchaseOrder->tracking_not_applicable) {
            \Log::info('Porth sync skipped for PurchaseOrder with approved no-tracking', [
                'purchase_order_id' => $purchaseOrder->id,
                'order_number' => $purchaseOrder->order_number,
            ]);

            return;
        }

        // Verificar si hay campos que requieren sincronización
        // Solo container_number activa creación/vinculación en Porth (mbl_number y tracking_id son solo datos de la PO)
        $syncFields = ['container_number'];
        $hasChanges = false;
        $hasValidIdentifier = false;
        $changedFields = [];

        // Verificar si hay algún identificador válido
        foreach ($syncFields as $field) {
            if (! empty($purchaseOrder->$field)) {
                $hasValidIdentifier = true;
                // En el evento saved usamos wasChanged para detectar cambios persistidos.
                if ($purchaseOrder->wasChanged($field)) {
                    $hasChanges = true;
                    $changedFields[] = $field;
                }
            }
        }

        // Sincronizar si:
        // 1. Cambió un campo relevante Y no tiene porth_id, O
        // 2. Tiene un identificador válido pero no tiene porth_id (por si acaso no se sincronizó antes)
        if (($hasChanges || $hasValidIdentifier) && empty($purchaseOrder->porth_id)) {
            $purchaseOrderId = $purchaseOrder->id;
            $purchaseOrderClass = get_class($purchaseOrder);
            DB::afterCommit(function () use ($purchaseOrderId, $purchaseOrderClass, $changedFields) {
                \Log::info('Dispatching Porth sync job for PurchaseOrder', [
                    'purchase_order_id' => $purchaseOrderId,
                    'changed_fields' => $changedFields,
                    'after_response' => true,
                ]);

                \App\Jobs\PorthSyncJob::dispatch($purchaseOrderId, $purchaseOrderClass)
                    ->onQueue('porth-sync')
                    ->delay(now()->addSeconds(5))
                    ->afterResponse();
            });
        }
    }
}
