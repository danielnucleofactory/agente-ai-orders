<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'vendo_code',
        'email',
        'contact_person',
        'address',
        'postal_code',
        'country',
        'state',
        'phone',
        'status',
        'notes',
    ];

    /**
     * Campos excluidos de toArray()/toJson().
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Resolución desde la API de PO: vendo_code es único global y coincide con el vendor_id enviado.
     * Si ya existe una fila con ese vendo_code, se reutiliza; si no, se crea una nueva.
     *
     * @param  string|int  $vendorIdRaw  Identificador de proveedor en el sistema origen (p. ej. "399")
     */
    public static function findOrCreateFromApiVendorIdentifier(
        string|int $vendorIdRaw,
        int $companyIdForNewRow,
        ?string $vendorName
    ): self {
        $vendoCode = (string) $vendorIdRaw;

        $existing = static::query()->where('vendo_code', $vendoCode)->first();
        if ($existing !== null) {
            return $existing;
        }

        return static::query()->create([
            'company_id' => $companyIdForNewRow,
            'vendo_code' => $vendoCode,
            'name' => ($vendorName !== null && $vendorName !== '')
                ? $vendorName
                : ('Proveedor ' . $vendoCode),
            'status' => 'active',
        ]);
    }

    /**
     * Get the company that owns the vendor.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the purchase orders for the vendor.
     * purchase_orders.vendor_id = vendors.id
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id', 'id');
    }

    /**
     * Check if the vendor has active purchase orders.
     */
    public function hasActivePurchaseOrders(): bool
    {
        return $this->purchaseOrders()->exists();
    }

    /**
     * Boot method to add model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion if vendor has active purchase orders
        static::deleting(function ($vendor) {
            if ($vendor->hasActivePurchaseOrders()) {
                throw new \Exception('No se puede eliminar el proveedor porque tiene órdenes de compra asociadas. Las órdenes de compra mantendrán la referencia al proveedor eliminado.');
            }
        });
    }
}
