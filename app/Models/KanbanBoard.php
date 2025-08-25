<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'company_id',
        'type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the kanban board.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the statuses for the kanban board.
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(KanbanStatus::class)->orderBy('position');
    }

    /**
     * Get the default status for the kanban board.
     */
    public function defaultStatus()
    {
        return $this->statuses()->where('is_default', true)->first();
    }

    /**
     * Get the purchase orders for this kanban board.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasManyThrough(
            PurchaseOrder::class,
            KanbanStatus::class,
            'kanban_board_id', // Foreign key on kanban_statuses table
            'kanban_status_id', // Foreign key on purchase_orders table
            'id', // Local key on kanban_boards table
            'id' // Local key on kanban_statuses table
        );
    }
}
