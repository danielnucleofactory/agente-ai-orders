<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
    ];

    public function preferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }
}
