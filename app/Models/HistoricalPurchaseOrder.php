<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalPurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'historical_purchase_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'vendor_id',
        'vendor_name',
        'retail_group',
        'route_label',
        'net_total',
        'currency',
        'emision_date_po',
        'category',
        'mode',
        'mbl_number',
        'container_type',
        'container_number',
        'incoterms',
        'logistics_incoterm',
        'price_incoterm',
        'date_booking_request',
        'date_booking_authorized',
        'date_carga_po',
        'date_theorical_load',
        'carga_lista_validada',
        'dif_load_date',
        'etd_initial_validated',
        'date_etd_initial',
        'date_etd_updated',
        'date_etd',
        'etd_dates_difference',
        'eta_inicial',
        'date_eta_updated',
        'date_eta',
        'eta_dates_difference',
        'case_number_file',
        'consolidator_name',
        'departure_port',
        'port_of_loading_validated',
        'arrival_port',
        'customs_dua',
        'receipt_note',
        'receipt_note_date',
        'factory_proforma_number',
        'cargo_invoice_number',
        'freight_amount',
        'service_provider',
        'cbm',
        'invoice',
        'invoice_amount',
        'factura_merca',
        'has_facture_merca',
        'visibility_notes',
        'applies_tlc',
        'comments',
        'apply_technical_note',
        'applies_af',
        'bonded_warehouse_enter',
        'bonded_warehouse_exit',
        'reason',
        'shipping_line',
        'forwader_date',
        'container_free_days',
        'tariff_type',
        'customer_type',
        'trading_company',
        'company_id',
        'status',
        'total_amount',
        'ensurence_type',
        'bill_to_id',
        'kanban_status_id',
        'ship_to_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'emision_date_po' => 'date',
        'date_booking_request' => 'date',
        'date_booking_authorized' => 'date',
        'date_carga_po' => 'date',
        'date_theorical_load' => 'date',
        'date_etd_initial' => 'date',
        'date_etd_updated' => 'date',
        'date_etd' => 'date',
        'eta_inicial' => 'date',
        'date_eta_updated' => 'date',
        'date_eta' => 'date',
        'receipt_note_date' => 'date',
        'bonded_warehouse_enter' => 'date',
        'bonded_warehouse_exit' => 'date',
        'forwader_date' => 'date',
        'carga_lista_validada' => 'boolean',
        'etd_initial_validated' => 'boolean',
        'port_of_loading_validated' => 'boolean',
        'has_facture_merca' => 'boolean',
        'applies_tlc' => 'boolean',
        'apply_technical_note' => 'boolean',
        'applies_af' => 'boolean',
        'net_total' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'cbm' => 'decimal:3',
        'invoice_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the historical purchase order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the bill to for the historical purchase order.
     */
    public function billTo(): BelongsTo
    {
        return $this->belongsTo(BillTo::class);
    }

    /**
     * Get the ship to for the historical purchase order.
     */
    public function shipTo(): BelongsTo
    {
        return $this->belongsTo(ShipTo::class);
    }

    /**
     * Get the kanban status for the historical purchase order.
     */
    public function kanbanStatus(): BelongsTo
    {
        return $this->belongsTo(KanbanStatus::class);
    }
}
