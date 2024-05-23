<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseInvoice;
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
        $totalProfit = $totalPurchase-$totalSales;
        
        return [
            Stat::make('Total Sale', 'Rs. '.$totalSales),
            Stat::make('Total Purchase', 'Rs. '.$totalPurchase),
            Stat::make('Total Profit', 'Rs. '.$totalProfit),
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
