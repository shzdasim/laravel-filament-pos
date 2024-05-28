<?php

namespace App\Models;

use App\Exceptions\CustomerDeletionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'phone', 'address'
    ];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($customer) {
            if ($customer->saleInvoice()->exists()) {
                throw new CustomerDeletionException();
            }
        });
    }

    public function saleInvoice()
    {
        return $this->hasMany(SaleInvoice::class);
    }
}
