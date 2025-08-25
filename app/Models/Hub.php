<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hub extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'country',
        'documentary_cut',
        'zarpe',
        'operation_days',
    ];

    /**
     * Obtener las órdenes de compra que tienen este hub como hub planificado.
     */
    public function plannedPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'planned_hub_id');
    }

    /**
     * Obtener las órdenes de compra que tienen este hub como hub real.
     */
    public function actualPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'actual_hub_id');
    }
}
