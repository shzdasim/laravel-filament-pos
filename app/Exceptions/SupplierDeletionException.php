<?php

namespace App\Exceptions;

use Exception;

class SupplierDeletionException extends Exception
{
    public function __construct($message = "Cannot delete Supplier as it has associated Purchase Invoices")
    {
        parent::__construct($message);
    }

    // Optionally, you can customize the report and render methods
    // if you want to handle the exception differently
    public function report()
    {
        //
    }

    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], 400);
    }
}
