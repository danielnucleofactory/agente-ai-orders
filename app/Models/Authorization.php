<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Authorization extends Model
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

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Polymorphic relationship with the entity requiring authorization
     */
    public function authorizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship with the requesting user
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relationship with the authorizing user
     */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorizer_id');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to filter rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if the authorization is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the authorization is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the authorization is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
