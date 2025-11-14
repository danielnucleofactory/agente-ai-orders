<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserCompany extends Pivot
{
    protected $table = 'company_user';

    protected $fillable = [
        'user_id',
        'company_id',
    ];

    public $timestamps = true;
}
