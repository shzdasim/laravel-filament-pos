<?php

use App\Filament\Resources\SaleInvoiceResource;
use App\Http\Controllers\SaleInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('app');
});
Route::get('sale-invoices/{record}/print', [SaleInvoiceController::class, 'print'])->name('sale-invoices.print');