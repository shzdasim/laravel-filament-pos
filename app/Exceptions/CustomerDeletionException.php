<?php

namespace App\Exceptions;

use Exception;

class CustomerDeletionException extends Exception
{
    public function __construct($message = "Cannot delete Customer as it has associated Sale Invoices")
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
