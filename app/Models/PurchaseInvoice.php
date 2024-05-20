<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        // Start a transaction
        DB::beginTransaction();

        try {
            // Lock the table to prevent concurrent writes
            $latestProduct = self::lockForUpdate()->orderBy('id', 'desc')->first();

            if (!$latestProduct) {
                $newCode = 'PRINV-0001';
            } else {
                $lastCode = $latestProduct->code;
                $number = (int) substr($lastCode, 4) + 1;
                $newCode = 'PRINV-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }

            // Commit the transaction
            DB::commit();

            return $newCode;
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();
            throw $e;
        }
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function purchaseInvoiceItems(){
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
