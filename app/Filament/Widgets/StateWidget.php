<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SaleInvoice;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StateWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        // Calculate total sales
        $totalSales = SaleInvoice::whereBetween('date', [$startDate, $endDate])->sum('total');

        // Calculate total purchases
        $totalPurchase = PurchaseInvoice::whereBetween('posted_date', [$startDate, $endDate])->sum('total_amount');

        // Calculate total profit
        $totalProfit = SaleInvoice::whereBetween('date', [$startDate, $endDate])->get()->sum(function ($saleInvoice) {
            return $saleInvoice->saleInvoiceItems->sum(function ($saleItem) {
                $purchaseItem = PurchaseInvoiceItem::where('product_id', $saleItem->product_id)->first();
                if ($purchaseItem) {
                    $purchasePrice = $purchaseItem->purchase_price;
                    $salePrice = $saleItem->price;
                    $saleDiscount = $saleItem->price * $saleItem->item_discount_percentage / 100;
                    $saleTax = $saleItem->price * $saleItem->saleInvoice->tax_percentage / 100;
                    $purchaseDiscount = $purchasePrice * $purchaseItem->item_discount_percentage / 100;
                    $purchaseTax = $purchasePrice * $purchaseItem->purchaseInvoice->tax_percentage / 100;
                    $profit = ($salePrice - $purchasePrice) * $saleItem->quantity - $saleDiscount + $saleTax - $purchaseDiscount + $purchaseTax;
                    return $profit;
                }
                return 0;
            });
        });

        return [
            Stat::make('Total Sale', 'Rs. ' . number_format($totalSales, 2)),
            Stat::make('Total Purchase', 'Rs. ' . number_format($totalPurchase, 2)),
            Stat::make('Total Profit', 'Rs. ' . number_format($totalProfit, 2)),
        ];
    }

    protected function getStartDate(): Carbon
    {
        return ($this->filters && $this->filters['startDate']) ? Carbon::parse($this->filters['startDate']) : now()->startOfMonth();
    }

    protected function getEndDate(): Carbon
    {
        return ($this->filters && $this->filters['endDate']) ? Carbon::parse($this->filters['endDate']) : now()->endOfMonth();
    }
}
