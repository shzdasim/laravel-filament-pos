<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleInvoice extends Model
{
    use HasFactory, Authorizable;
    protected $fillable = [
        'user_id', 'customer_id', 'visit_date', 'next_visit_date', 'visit_reading', 'next_visit_reading', 'remarks', 'posted_number', 'discount_percentage', 'tax_percentage', 'total', 'gross_amount', 'item_discount', 'discount_amount', 'tax_amount'
    ];
    protected $guarded = ['saleInvoiceItems'];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function saleInvoiceItems(){
        return $this->hasMany(SaleInvoiceItem::class);
    }
    // Define the relationship with SaleReturn
    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class, 'sale_invoice_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($saleInvoice) {
            $saleInvoice->saleInvoiceItems()->each(function ($item) {
                $item->delete();
            });
        });
    }

    public static function generateCode()
    {
        DB::beginTransaction();

        try {
            $latestSaleInvoice = self::lockForUpdate()->orderBy('id', 'desc')->first();

            if (!$latestSaleInvoice) {
                $newCode = 'SLINV-0001';
            } else {
                $lastCode = $latestSaleInvoice->posted_number;
                $number = (int) substr($lastCode, 6) + 1;
                $newCode = 'SLINV-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }

            DB::commit();

            return $newCode;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
