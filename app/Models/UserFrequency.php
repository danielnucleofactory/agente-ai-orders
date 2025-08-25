<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFrequency extends Model
{
    protected $fillable = [
        'user_id',
        'frequency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
