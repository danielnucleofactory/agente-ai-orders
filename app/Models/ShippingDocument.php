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

    /**
     * Boot method to add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Disparar sincronización automática cuando se crea o actualiza
        static::saved(function ($document) {
            static::dispatchPorthSync($document);
        });

        static::updated(function ($document) {
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

        foreach ($syncFields as $field) {
            if ($document->isDirty($field) && !empty($document->$field)) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges) {
            \Log::info('Dispatching Porth sync job', [
                'document_id' => $document->id,
                'changed_fields' => array_filter($syncFields, function($field) use ($document) {
                    return $document->isDirty($field) && !empty($document->$field);
                })
            ]);

            // Disparar job de sincronización con delay
            \App\Jobs\PorthSyncJob::dispatch($document->id, get_class($document))
                ->onQueue('porth-sync')
                ->delay(now()->addSeconds(5));
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
