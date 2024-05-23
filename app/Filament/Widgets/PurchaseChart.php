<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class PurchaseChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'PURCHASE';
    protected static ?int $sort = 4;

    public function getData(): array
{
    $start = $this->filters['startDate'];
    $end = $this->filters['endDate'];
    $startDate = $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();  // Default start date
    $endDate = $end ? Carbon::parse($end)->endOfDay() : now()->endOfMonth();      // Default end date

    $data = PurchaseInvoice::query()
        ->selectRaw('DATE(posted_date) as date, SUM(total_amount) as total_purchase')
        ->whereBetween('posted_date', [$startDate, $endDate])
        ->groupBy(DB::raw('DATE(posted_date)'))
        ->get();

    // Extracting labels and data
    $labels = $data->pluck('date')->map(function ($date) {
        return Carbon::parse($date)->toDateString();
    });

    $purchaseAmounts = $data->pluck('total_purchase');

    return [
        'datasets' => [
            [
                'label' => 'Total Purchase',
                'data' => $purchaseAmounts,
            ],
        ],
        'labels' => $labels,
    ];
}


    protected function getType(): string
    {
        return 'bar';
    }
}
