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

        $totalSales = SaleInvoice::whereBetween('date', [$startDate, $endDate])->sum('total');
        $totalPurchase = PurchaseInvoice::whereBetween('posted_date', [$startDate, $endDate])->sum('total_amount');

        $totalProfit = $this->calculateTotalProfit($startDate, $endDate);

        return [
            Stat::make('Total Sale', 'Rs. ' . number_format($totalSales, 2)),
            Stat::make('Total Purchase', 'Rs. ' . number_format($totalPurchase, 2)),
            Stat::make('Total Profit', 'Rs. ' . number_format($totalProfit, 2)),
        ];
    }

    private function calculateTotalProfit($startDate, $endDate)
    {
        $salesData = SaleInvoice::whereBetween('date', [$startDate, $endDate])
            ->with(['saleInvoiceItems.product', 'saleReturns'])
            ->get();

        $totalProfit = 0;

        foreach ($salesData as $saleInvoice) {
            $grossSale = $saleInvoice->gross_amount;
            $itemDiscount = $saleInvoice->item_discount;
            $invoiceDiscount = $saleInvoice->discount_amount;
            $totalSale = $saleInvoice->total;

            $saleReturn = $saleInvoice->saleReturns->sum('total');

            $costOfSale = $saleInvoice->saleInvoiceItems->sum(function ($saleItem) {
                $remainingQuantity = $saleItem->quantity;
                $totalCost = 0;

                $purchaseItems = PurchaseInvoiceItem::where('product_id', $saleItem->product_id)
                    ->orderBy('purchase_invoice_id', 'asc')
                    ->get();

                foreach ($purchaseItems as $purchaseItem) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $purchaseQuantity = $purchaseItem->quantity;
                    $quantityToConsider = min($remainingQuantity, $purchaseQuantity);
                    $remainingQuantity -= $quantityToConsider;

                    $purchasePrice = $purchaseItem->purchase_price;
                    $purchaseDiscount = $purchasePrice * $purchaseItem->item_discount_percentage / 100;
                    $purchaseTax = $purchasePrice * $purchaseItem->purchaseInvoice->tax_percentage / 100;

                    $totalCost += ($purchasePrice - $purchaseDiscount + $purchaseTax) * $quantityToConsider;
                }

                return $totalCost;
            });

            $grossProfitAmount = $totalSale - $costOfSale;
            $totalProfit += $grossProfitAmount;
        }

        return $totalProfit;
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
