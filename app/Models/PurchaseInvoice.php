<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_id', 'posted_number','posted_date','invoice_number', 'invoice_amount','tax', 'discount','total_amount'
    ];
    protected $guarded = ['purchaseInvoiceItems'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = self::generateCode();
        });
    }

    public static function generateCode()
    {
        $latestProduct = self::orderBy('id', 'desc')->first();
        if (!$latestProduct) {
            return 'PRINV-0001';
        }

        $lastCode = $latestProduct->code;
        $number = (int) substr($lastCode, 3) + 1;
        return 'PRINV-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchaseInvoiceItems(){
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
