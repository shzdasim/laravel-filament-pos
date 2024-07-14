<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80mm;
            margin: auto;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            width: 50px;
            height: 50px;
        }
        .invoice-info, .summary-table, .instruction {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .invoice-info th, .invoice-info td, .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .invoice-info th {
            background-color: #f2f2f2;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('storage/' . $application->logo) }}" alt="Application Logo">
            <h2>{{ $application->name }}</h2>
            <p>License Number: {{ $application->licence_number }}</p>
        </div>
        
        <table class="invoice-info">
            <tr>
                <th>Invoice Number</th>
                <td>{{ $invoice->posted_number }}</td>
            </tr>
            <tr>
                <th>Visit Date</th>
                <td>{{ $invoice->visit_date }}</td>
            </tr>
            <tr>
                <th>Next Visit Date</th>
                <td>{{ $invoice->next_visit_date }}</td>
            </tr>
            <tr>
                <th>Visit Reading</th>
                <td>{{ $invoice->visit_reading }}</td>
            </tr>
            <tr>
                <th>Next Visit Reading</th>
                <td>{{ $invoice->next_visit_reading }}</td>
            </tr>
            <tr>
                <th>Remarks</th>
                <td>{{ $invoice->remarks }}</td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td>{{ $invoice->customer->name }}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Disc%</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->saleInvoiceItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->price }}</td>
                        <td>{{ $item->item_discount_percentage }}</td>
                        <td>{{ $item->sub_total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <th>Gross Amount</th>
                <td>{{ $invoice->gross_amount }}</td>
            </tr>
            <tr>
                <th>Item Discount</th>
                <td>{{ $invoice->item_discount }}</td>
            </tr>
            <tr>
                <th>Discount Amount</th>
                <td>{{ $invoice->discount_amount }}</td>
            </tr>
            <tr>
                <th>Tax Amount</th>
                <td>{{ $invoice->tax_amount }}</td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>{{ $invoice->total }}</td>
            </tr>
        </table>

        @if($application->instructions)
            <div class="footer">
                <p>{{ $application->instructions }}</p>
            </div>
        @endif
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
