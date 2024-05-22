<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SaleInvoice extends Model
{
    use HasFactory;
    protected $fillable = [
      'user_id', 'customer_id', 'date', 'posted_number', 'discount', 'tax', 'total'
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
