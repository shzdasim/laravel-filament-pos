<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleInvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'sale_invoice_id',
        'product_id',
        'quantity',
        'current_quantity',
        'price',
        'discount',
        'sub_total',

    ];
    public function saleInvoice(){
        return $this->belongsTo(SaleInvoice::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
