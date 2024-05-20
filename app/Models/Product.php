<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'description',
        'quantity',
        'purchase_price',
        'sale_price',
        'avg_price',
        'max_discount',
        'category_id'
    ];
    

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
            return 'PRD-0001';
        }

        $lastCode = $latestProduct->code;
        $number = (int) substr($lastCode, 3) + 1;
        return 'PRD-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }


    public function category(){
        return $this->belongsTo(Category::class);
    }
}
