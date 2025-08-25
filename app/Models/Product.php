<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'material_id',
        'short_text',
        'supplying_plant',
        'unit_of_measure',
        'plant',
        'vendor_name',
        'vendo_code',
        'price_per_unit',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'price_per_uon' => 'decimal:2',
    ];

    /**
     * Get the purchase orders for the product.
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class, 'purchase_order_product')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
