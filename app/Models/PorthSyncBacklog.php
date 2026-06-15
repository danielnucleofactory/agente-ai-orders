<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorthSyncBacklog extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'porth_id',
        'source',
        'status',
        'priority',
        'discovered_at',
        'next_attempt_at',
        'processing_started_at',
        'processed_at',
        'attempts',
        'last_error',
        'metadata',
    ];

    protected $casts = [
        'priority' => 'integer',
        'discovered_at' => 'datetime',
        'next_attempt_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
        'metadata' => 'array',
    ];
}
