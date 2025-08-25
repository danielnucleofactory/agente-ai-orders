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
        return Carbon::createFromTimestamp($this->attributes['last_activity'])->format('d/m/Y H:i:s');
    }
}
