<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_date',
        'material',
        'short_text',
        'purchase_requisition',
        'supplying_plant',
        'qty_real',
        'uom_real',
        'quantity_requested',
        'delivery_date',
        'unit_of_measure',
        'plant',
        'planned_delivery_time',
        'mrp_controller',
        'vendor_name',
        'vendor_code',
    ];

    protected $casts = [
        'release_date' => 'date',
        'delivery_date' => 'date',
        'qty_real' => 'decimal:3',
        'quantity_requested' => 'decimal:3',
        'planned_delivery_time' => 'integer',
    ];
}
