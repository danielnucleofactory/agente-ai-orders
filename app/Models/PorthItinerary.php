<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PorthItinerary extends Model
{
    use HasFactory;

    protected $table = 'porth_itineraries';

    protected $fillable = [
        'shipping_document_id',
        'porth_id',
        'porth_itinerary_id',
        'porth_cargo_id',
        'phase',
        'name',
        'place',
        'vessel_voyage',
        'date',
        'created_at_porth',
        'updated_at_porth',
        'done',
        'raw',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at_porth' => 'datetime',
        'updated_at_porth' => 'datetime',
        'done' => 'boolean',
        'raw' => 'array',
    ];

    public function shippingDocument(): BelongsTo
    {
        return $this->belongsTo(ShippingDocument::class);
    }
}
