<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorthSyncRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger',
        'scope',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'total',
        'processed',
        'updated',
        'no_change',
        'skipped',
        'failed',
        'dry_run',
        'error_summary',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
        'total' => 'integer',
        'processed' => 'integer',
        'updated' => 'integer',
        'no_change' => 'integer',
        'skipped' => 'integer',
        'failed' => 'integer',
        'dry_run' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Scope para corridas exitosas
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope para corridas fallidas
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para corridas recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('started_at', 'desc')->limit($limit);
    }

    /**
     * Calcula el porcentaje de éxito
     */
    public function getSuccessRateAttribute(): ?float
    {
        if ($this->processed === 0) {
            return null;
        }
        return round(($this->updated / $this->processed) * 100, 2);
    }
}
