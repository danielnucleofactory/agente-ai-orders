<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAuthEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'ip_address',
        'user_agent',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el label del tipo de evento en español
     */
    public function getEventTypeLabel(): string
    {
        return match($this->event_type) {
            'login' => 'Login',
            'logout' => 'Logout',
            'failed' => 'Intento Fallido',
            default => 'Desconocido'
        };
    }
}
