<?php
namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use EightyNine\Reports\Components\Text;

class PurchaseReport extends Report
{
    public ?string $heading = "Purchase Report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Text::make('Purchase Report')
                    ->title()
                    ->primary(),
            ]);
    }

    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Table::make()
                    ->columns([
                        Body\TextColumn::make('purchase_date')->label('Purchase Date'),
                        Body\TextColumn::make('supplier_name')->label('Supplier Name'),
                        Body\TextColumn::make('invoice_amount')->label('Invoice Amount'),
                        Body\TextColumn::make('tax_amount')->label('Tax Amount'),
                        Body\TextColumn::make('discount_amount')->label('Discount Amount'),
                        Body\TextColumn::make('total_amount')->label('Total Amount'),
                    ])
                    ->data(function (?array $filters) {
                        $startDate = Carbon::parse($filters['start_date'] ?? null);
                        $endDate = Carbon::parse($filters['end_date'] ?? null);

                        // Load purchase data within the date range
                        $purchaseData = PurchaseInvoice::whereBetween('posted_date', [$startDate, $endDate])
                            ->with('supplier')
                            ->get()
                            ->groupBy(function ($date) {
                                return Carbon::parse($date->posted_date)->format('Y-m-d');
                            })
                            ->map(function ($items, $date) {
                                $supplierName = $items->first()->supplier->name ?? 'Unknown Supplier';
                                $invoiceAmount = $items->sum('invoice_amount');
                                $taxAmount = $items->sum('tax_amount');
                                $discountAmount = $items->sum('discount_amount');
                                $totalAmount = $items->sum('total_amount');

                                return [
                                    'purchase_date' => $date,
                                    'supplier_name' => $supplierName,
                                    'invoice_amount' => $invoiceAmount,
                                    'tax_amount' => $taxAmount,
                                    'discount_amount' => $discountAmount,
                                    'total_amount' => $totalAmount,
                                ];
                            });

                        $totalSummary = [
                            'purchase_date' => 'Total',
                            'supplier_name' => '',
                            'invoice_amount' => $purchaseData->sum('invoice_amount'),
                            'tax_amount' => $purchaseData->sum('tax_amount'),
                            'discount_amount' => $purchaseData->sum('discount_amount'),
                            'total_amount' => $purchaseData->sum('total_amount'),
                        ];

                        return $purchaseData->values()->push($totalSummary);
                    }),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Text::make('Generated on: ' . now()->format('Y-m-d H:i:s')),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start_date')->label('Start Date')->native(false)->default(now())->closeOnDateSelection(),
                DatePicker::make('end_date')->label('End Date')->native(false)->default(now())->closeOnDateSelection(),
            ]);
    }
}
