<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
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
        'porth_shipment_id',
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
        
        // Campos de Porth
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
        'freight_type',
        'porth_vessel_voyage',
        'porth_name',
        'porth_organization_id',
        'porth_origin',
        'porth_final_destination',
        'porth_first_eta',
        'porth_first_etd',
        'porth_free_time_at_destination',
        'porth_manual_tracking',
        'porth_tags',
        'porth_ready',
        'porth_to_origin_port',
        'porth_at_origin_port',
        'porth_in_transit',
        'porth_at_destination_port',
        'porth_to_final_destination',
        'porth_delivered',
        'last_porth_sync_at',
        'porth_raw',
    ];

    /**
     * Campos excluidos de toArray()/toJson().
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'company_id',
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
        
        // Casts de Porth
        'porth_vessel_voyage' => 'array',
        'porth_raw' => 'array',
        'porth_tags' => 'array',
        'porth_manual_tracking' => 'boolean',
        'porth_first_eta' => 'datetime',
        'porth_first_etd' => 'datetime',
        'porth_ready' => 'datetime',
        'porth_to_origin_port' => 'datetime',
        'porth_at_origin_port' => 'datetime',
        'porth_in_transit' => 'datetime',
        'porth_at_destination_port' => 'datetime',
        'porth_to_final_destination' => 'datetime',
        'porth_delivered' => 'datetime',
        'last_porth_sync_at' => 'datetime',
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
     * Relación con los cargos/contenedores de Porth
     */
    public function porthCargos(): HasMany
    {
        return $this->hasMany(PorthCargo::class);
    }

    /**
     * Relación con las fases de Porth
     */
    public function porthPhases(): HasMany
    {
        return $this->hasMany(PorthPhase::class);
    }

    /**
     * Relación con los itinerarios de Porth
     */
    public function porthItineraries(): HasMany
    {
        return $this->hasMany(PorthItinerary::class);
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

    /**
     * Boot method to add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Disparar sincronización automática una sola vez por guardado.
        // El sync con Porth debe ocurrir fuera de la respuesta principal.
        static::saved(function ($document) {
            static::dispatchPorthSync($document);
        });
    }

    /**
     * Disparar sincronización con Porth
     */
    protected static function dispatchPorthSync($document)
    {
        // Verificar si la sincronización está habilitada
        $syncEnabled = config('services.porth.sync_enabled', true);
        if (!$syncEnabled) {
            \Log::info('Porth sync disabled by configuration', [
                'document_id' => $document->id,
                'sync_enabled' => $syncEnabled
            ]);
            return;
        }

        // Verificar si hay campos que requieren sincronización
        $syncFields = ['tracking_id', 'mbl_number', 'container_number', 'booking_code'];
        $hasChanges = false;
        $hasValidIdentifier = false;
        $changedFields = [];

        foreach ($syncFields as $field) {
            if (!empty($document->$field)) {
                $hasValidIdentifier = true;
            }

            if ($document->wasChanged($field) && !empty($document->$field)) {
                $hasChanges = true;
                $changedFields[] = $field;
            }
        }

        if ($hasChanges || ($hasValidIdentifier && empty($document->porth_id))) {
            $documentId = $document->id;
            $documentClass = get_class($document);

            DB::afterCommit(function () use ($documentId, $documentClass, $changedFields) {
                \Log::info('Dispatching Porth sync job', [
                    'document_id' => $documentId,
                    'changed_fields' => $changedFields,
                    'after_response' => true,
                ]);

                \App\Jobs\PorthSyncJob::dispatch($documentId, $documentClass)
                    ->onQueue('porth-sync')
                    ->delay(now()->addSeconds(5))
                    ->afterResponse();
            });
        }
    }

    /**
     * Calcula automáticamente el estado de llegada y días de retraso basándose en la ETA
     * 
     * @return array ['arrival_status' => string, 'delay_days' => int]
     */
    public function calculateArrivalStatus(): array
    {
        // Usar la ETA más reciente disponible (updated > initial)
        $eta = $this->date_eta_updated ?? $this->estimated_arrival_date ?? null;
        
        if (!$eta) {
            return [
                'arrival_status' => null,
                'delay_days' => null
            ];
        }

        $today = now()->startOfDay();
        $etaDate = $eta->startOfDay();
        
        if ($today > $etaDate) {
            // Atrasado
            $delayDays = $etaDate->diffInDays($today);
            return [
                'arrival_status' => 'Atrasado',
                'delay_days' => $delayDays
            ];
        } else {
            // A tiempo
            return [
                'arrival_status' => 'A tiempo',
                'delay_days' => 0
            ];
        }
    }

    /**
     * Actualiza automáticamente el estado de llegada y días de retraso
     * 
     * @return bool
     */
    public function updateArrivalStatus(): bool
    {
        $status = $this->calculateArrivalStatus();
        
        $this->arrival_status = $status['arrival_status'];
        // Nota: ShippingDocument no tiene campo delay_days, solo arrival_status
        
        return $this->save();
    }
}
