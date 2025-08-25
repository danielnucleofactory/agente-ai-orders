<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillTo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bill_tos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'contact_person',
        'address',
        'postal_code',
        'country',
        'state',
        'phone',
        'company_id',
        'notes',
    ];

    /**
     * Get the company that owns the bill to.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the purchase orders associated with the bill to.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
