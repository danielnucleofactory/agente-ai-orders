<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthCargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_document_id',
        'purchase_order_id',
        'porth_cargo_id',
        'type',
        'number',
        'seal',
        'amount',
        'width',
        'height',
        'depth',
        'weight',
        'notes',
        'phase',
    ];

    protected $casts = [
        'amount' => 'integer',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'depth' => 'decimal:2',
        'weight' => 'decimal:3',
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
