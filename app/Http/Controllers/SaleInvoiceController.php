<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SaleInvoice;
use Illuminate\Http\Request;

class SaleInvoiceController extends Controller
{
    public function print($recordId)
    {
        $invoice = SaleInvoice::with('saleInvoiceItems.product', 'customer')->findOrFail($recordId);
        $application = Application::first(); // Fetch the application data

        return view('sale-invoices.print', compact('invoice', 'application'));
    }
}
