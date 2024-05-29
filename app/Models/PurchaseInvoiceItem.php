<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'purchase_invoice_id', 'product_id', 'quantity', 'purchase_price', 'sale_price', 'item_discount_percentage', 'margin', 'avg_price', 'sub_total'
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
            
            // Calculate new average price
            $totalQuantity = $product->quantity + $item->quantity;
            $product->avg_price = (($product->avg_price * $product->quantity) + ($item->avg_price * $item->quantity)) / $totalQuantity;

            // Calculate new margin
            if ($item->sale_price > 0) {
                $discounted_purchase_price = $item->purchase_price - ($item->purchase_price * $item->item_discount_percentage / 100);
                $profit = $item->sale_price - $discounted_purchase_price;
                $product->margin = ($discounted_purchase_price > 0) ? ($profit / $item->sale_price) * 100 : 0;
            } else {
                $product->margin = 0;
            }
            
            $product->quantity = $totalQuantity;
            $product->purchase_price = $item->purchase_price;
            $product->sale_price = $item->sale_price;
            $product->save();
        });

        static::updated(function ($item) {
            $original = $item->getOriginal();
            $product = $item->product;
            
            $quantityDifference = $item->quantity - $original['quantity'];
            $totalQuantity = $product->quantity + $quantityDifference;
            $product->avg_price = (($product->avg_price * $product->quantity) + ($item->avg_price * $quantityDifference)) / $totalQuantity;

            if ($item->sale_price > 0) {
                $discounted_purchase_price = $item->purchase_price - ($item->purchase_price * $item->item_discount_percentage / 100);
                $profit = $item->sale_price - $discounted_purchase_price;
                $product->margin = ($discounted_purchase_price > 0) ? ($profit / $item->sale_price) * 100 : 0;
            } else {
                $product->margin = 0;
            }

            $product->quantity = $totalQuantity;
            $product->purchase_price = $item->purchase_price;
            $product->sale_price = $item->sale_price;
            $product->save();
        });

        static::deleted(function ($item) {
            $product = $item->product;
            $product->quantity -= $item->quantity;
            
            // Recalculate avg_price and margin
            if ($product->quantity > 0) {
                $product->avg_price = (($product->avg_price * ($product->quantity + $item->quantity)) - ($item->avg_price * $item->quantity)) / $product->quantity;
                
                if ($item->sale_price > 0) {
                    $discounted_purchase_price = $item->purchase_price - ($item->purchase_price * $item->item_discount_percentage / 100);
                    $profit = $item->sale_price - $discounted_purchase_price;
                    $product->margin = ($discounted_purchase_price > 0) ? ($profit / $item->sale_price) * 100 : 0;
                } else {
                    $product->margin = 0;
                }
            } else {
                $product->avg_price = 0;
                $product->margin = 0;
            }

            $product->save();
        });
    }
}
