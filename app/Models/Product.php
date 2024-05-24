<?php

namespace App\Models;

use App\Exceptions\ProductDeletionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'code','name', 'description','quantity','purchase_price','sale_price','avg_price', 'max_discount','category_id'
    ];
    

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = self::generateCode();
        });

        static::deleting(function ($model) {
            if ($model->quantity > 0) {
                throw new ProductDeletionException();
            }
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
                $newCode = 'PRD-0001';
            } else {
                $lastCode = $latestProduct->code;
                $number = (int) substr($lastCode, 4) + 1;
                $newCode = 'PRD-' . str_pad($number, 4, '0', STR_PAD_LEFT);
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




    public function category(){
        return $this->belongsTo(Category::class);
    }
}
