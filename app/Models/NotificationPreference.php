<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }
}
