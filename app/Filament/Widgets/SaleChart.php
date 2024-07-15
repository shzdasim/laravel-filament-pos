<?php

namespace App\Filament\Widgets;

use App\Models\SaleInvoice;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class SaleChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = 'SALES';
    protected static ?int $sort = 3;

    public function getData(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        $startDate = $start ? Carbon::parse($start): now()->startOfMonth();  // Default start date
        $endDate = $end ? Carbon::parse($end): now()->endOfMonth();      // Default end date

        $data = SaleInvoice::query()
            ->selectRaw('DATE(visit_date) as date, SUM(total) as total_sale')
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(visit_date)'))
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sale',
                    'data' => $data->pluck('total_sale'),
                ],
            ],
            'labels' => $data->pluck('date'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
