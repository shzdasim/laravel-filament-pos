<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_invoice_id', 'product_id', 'quantity', 'purchase_price', 'sale_price', 'item_discount%', 'margin', 'avg_price', 'sub_total'
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($item) {
            $product = $item->product;
            $product->quantity += $item->quantity;
            $product->purchase_price = $item->purchase_price;
            $product->sale_price = $item->sale_price;
            $product->save();
        });

        static::updated(function ($item) {
            $original = $item->getOriginal();
            $product = $item->product;
            $product->quantity += ($item->quantity - $original['quantity']);
            $product->purchase_price = $item->purchase_price;
            $product->sale_price = $item->sale_price;
            $product->save();
        });

        static::deleted(function ($item) {
            $product = $item->product;
            $product->quantity -= $item->quantity;
            $product->save();
        });
    }
}
