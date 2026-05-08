<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_document_id',
        'purchase_order_id',
        'porth_phase_id',
        'name',
        'estimated_dates',
        'actual_date',
    ];

    protected $casts = [
        'estimated_dates' => 'array',
        'actual_date' => 'datetime',
    ];

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
