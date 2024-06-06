<?php

namespace App\Filament\Reports;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use App\Models\Supplier;
use App\Models\SaleInvoiceItem;
use Carbon\Carbon;
use EightyNine\Reports\Components\Text;

class PurchaseOrderReport extends Report
{
    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Text::make('Purchase Order Report')
                    ->title()
                    ->primary(),
            ]);
    }
// Made Changes
public function body(Body $body): Body
{
    return $body
        ->schema([
            Body\Table::make()
                ->columns([
                    Body\TextColumn::make('product_code')->label('Product Code'),
                    Body\TextColumn::make('product_name')->label('Product Name'),
                    Body\TextColumn::make('total_sold')->label('Total Sold'),
                    Body\TextColumn::make('projection_required')->label('Projection Required'),
                    Body\TextColumn::make('purchase_price')->label('Purchase Price'),
                    Body\TextColumn::make('total_cost')->label('Total Cost'),
                ])
                ->data(function (?array $filters) {
                    $startDate = Carbon::parse($filters['start_date'] ?? null);
                    $endDate = Carbon::parse($filters['end_date'] ?? null);
                    $projectionPeriod = (int)($filters['projection_period'] ?? 0);
                    $supplierId = $filters['supplier_id'] ?? null;

                    $salesData = SaleInvoiceItem::query()
                        ->join('sale_invoices', 'sale_invoice_items.sale_invoice_id', '=', 'sale_invoices.id')
                        ->join('products', 'sale_invoice_items.product_id', '=', 'products.id')
                        ->join('purchase_invoice_items', 'products.id', '=', 'purchase_invoice_items.product_id')
                        ->join('purchase_invoices', 'purchase_invoice_items.purchase_invoice_id', '=', 'purchase_invoices.id')
                        ->whereBetween('sale_invoices.date', [$startDate, $endDate])
                        ->where('purchase_invoices.supplier_id', $supplierId)
                        ->select('sale_invoice_items.*', 'products.code', 'products.name', 'purchase_invoice_items.purchase_price')
                        ->with('product')
                        ->get()
                        ->groupBy('product_id')
                        ->map(function ($items) use ($projectionPeriod) {
                            $totalSold = $items->sum('quantity');
                            $product = $items->first()->product;
                            $purchasePrice = $items->first()->purchase_price;
                            $averageDailySales = $totalSold / $items->count();
                            $projectionRequired = $averageDailySales * $projectionPeriod;
                            $totalCost = ceil($projectionRequired) * $purchasePrice;

                            return (object) [
                                'product_code' => $product->code,
                                'product_name' => $product->name,
                                'total_sold' => $totalSold,
                                'projection_required' => ceil($projectionRequired),
                                'purchase_price' => $purchasePrice,
                                'total_cost' => $totalCost,
                            ];
                        });

                    $totalCost = $salesData->sum('total_cost');

                    $salesData->push((object)[
                        'product_code' => 'Total',
                        'product_name' => '',
                        'total_sold' => '',
                        'projection_required' => '',
                        'purchase_price' => '',
                        'total_cost' => $totalCost,
                    ]);

                    return $salesData->values();
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
                TextInput::make('projection_period')->label('Projection Period')->numeric(),
                Select::make('supplier_id')->label('Supplier')->native(false)->preload()
                    ->options(
                        Supplier::all()->pluck('name', 'id')->toArray()
                    ),
            ]);
    }
}
