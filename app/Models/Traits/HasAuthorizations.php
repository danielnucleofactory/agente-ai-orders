<?php

namespace App\Models\Traits;

use App\Models\Authorization;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAuthorizations
{
    /**
     * Get all authorizations for this model
     */
    public function authorizations(): MorphMany
    {
        return $this->morphMany(Authorization::class, 'authorizable');
    }

    /**
     * Get all pending authorizations for this model
     */
    public function pendingAuthorizations()
    {
        return $this->authorizations()->pending();
    }

    /**
     * Get all approved authorizations for this model
     */
    public function approvedAuthorizations()
    {
        return $this->authorizations()->approved();
    }

    /**
     * Get all rejected authorizations for this model
     */
    public function rejectedAuthorizations()
    {
        return $this->authorizations()->rejected();
    }

    /**
     * Create a new authorization request for this model
     */
    public function createAuthorizationRequest(string $operationType, array $data = []): Authorization
    {
        return $this->authorizations()->create([
            'operation_id' => \Illuminate\Support\Str::uuid(),
            'requester_id' => auth()->id(),
            'operation_type' => $operationType,
            'status' => Authorization::STATUS_PENDING,
            'data' => $data,
        ]);
    }

    /**
     * Check if there is a pending authorization of a specific type
     */
    public function hasAuthorizationPending(string $operationType): bool
    {
        return $this->authorizations()
            ->where('operation_type', $operationType)
            ->where('status', Authorization::STATUS_PENDING)
            ->exists();
    }

    /**
     * Find the most recent authorization of a specific type
     */
    public function findAuthorizationByType(string $operationType, string $status = null)
    {
        $query = $this->authorizations()
            ->where('operation_type', $operationType)
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->first();
    }
}
