<?php

namespace App\Models;

use App\Exceptions\SupplierDeletionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'phone',
    ];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($supplier) {
            if ($supplier->purchaseInvoice()->exists()) {
                throw new SupplierDeletionException();
            }
        });
    }

    public function purchaseInvoice()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }
}
