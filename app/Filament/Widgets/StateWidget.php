<?php
namespace App\Filament\Widgets;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\SaleInvoice;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class StateWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $totalSales = SaleInvoice::whereBetween('date', [$startDate, $endDate])->sum('total');
        $totalPurchase = PurchaseInvoice::whereBetween('posted_date', [$startDate, $endDate])->sum('total_amount');
        $totalProfit = $this->calculateTotalProfit($startDate, $endDate);
        $profitMarginPercentage = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;
        $totalSaleReturn = $this->calculateTotalSaleReturn($startDate, $endDate);
        $totalGrossSale = $this->calculateTotalGrossSale($startDate, $endDate);

        return [
            Card::make('Total Sale', 'Rs. ' . number_format($totalSales, 2))
                ->description('Total sales within the selected period')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),
            Card::make('Total Purchase', 'Rs. ' . number_format($totalPurchase, 2))
                ->description('Total purchases within the selected period')
                ->color('success')
                ->icon('heroicon-o-receipt-percent'),
            Card::make('Total Profit', 'Rs. ' . number_format($totalProfit, 2))
                ->description('Net profit calculated')
                ->color('success')
                ->icon('heroicon-o-chart-pie'),
            Card::make('Profit Margin (%)', number_format($profitMarginPercentage, 2) . '%')
                ->description('Profit margin percentage')
                ->color('info')
                ->icon('heroicon-o-arrow-trending-up'),
            Card::make('Sale Return', 'Rs. ' . number_format($totalSaleReturn, 2))
                ->description('Total sale returns')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left'),
            Card::make('Gross Sale', 'Rs. ' . number_format($totalGrossSale, 2))
                ->description('Gross sales before discounts and returns')
                ->color('gray')
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }

    private function calculateTotalProfit($startDate, $endDate)
    {
        $salesData = SaleInvoice::whereBetween('date', [$startDate, $endDate])
            ->with(['saleInvoiceItems.product', 'saleReturns'])
            ->get();

        $totalProfit = 0;

        foreach ($salesData as $saleInvoice) {
            $totalSale = $saleInvoice->total;
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

    private function calculateTotalSaleReturn($startDate, $endDate)
    {
        return SaleInvoice::whereBetween('date', [$startDate, $endDate])
            ->with('saleReturns')
            ->get()
            ->sum(function ($saleInvoice) {
                return $saleInvoice->saleReturns->sum('total');
            });
    }

    private function calculateTotalGrossSale($startDate, $endDate)
    {
        return SaleInvoice::whereBetween('date', [$startDate, $endDate])->sum('gross_amount');
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
