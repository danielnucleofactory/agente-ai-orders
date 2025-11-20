<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Session extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLastActivityAttribute()
    {
        if (!isset($this->attributes['last_activity'])) return null;
        $carbon = Carbon::createFromTimestamp($this->attributes['last_activity']);
        // Usar formatDateTime pero agregar segundos al formato
        $dateFormat = getUserDateFormat();
        $timeFormat = auth()->check() 
            ? (auth()->user()->time_format ?? '24hrs')
            : '24hrs';
        $timePart = $timeFormat === '12hrs' ? 'h:i:s A' : 'H:i:s';
        return $carbon->format($dateFormat . ' ' . $timePart);
    }
}
