<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorizationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'authorizable_id',
        'authorizable_type',
        'requester_id',
        'operation_type',
        'status',
        'data',
        'authorizer_id',
        'authorized_at',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
        'authorized_at' => 'datetime',
    ];

    // Estados posibles
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relación polimórfica con el elemento que requiere autorización
     */
    public function authorizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relación con el usuario que solicita
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relación con el usuario que autoriza
     */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorizer_id');
    }

    public function operation()
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar solicitudes pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
