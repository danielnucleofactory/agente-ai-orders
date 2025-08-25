<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'kanban_board_id',
        'position',
        'color',
        'is_default',
        'is_final',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'is_default' => 'boolean',
        'is_final' => 'boolean',
    ];

    /**
     * Get the kanban board that owns the status.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }

    /**
     * Get the purchase orders for this status.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'kanban_status_id');
    }

    /**
     * Get the next status in the board.
     */
    public function nextStatus()
    {
        return $this->board->statuses()
            ->where('position', '>', $this->position)
            ->orderBy('position')
            ->first();
    }

    /**
     * Get the previous status in the board.
     */
    public function previousStatus()
    {
        return $this->board->statuses()
            ->where('position', '<', $this->position)
            ->orderBy('position', 'desc')
            ->first();
    }
}
