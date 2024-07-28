<?php

namespace App\Filament\Widgets;

use App\Models\SaleInvoice;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class UpcomingVisitsWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-visits-widget';

    protected function getViewData(): array
    {
        $today = Carbon::now();
        $nextSevenDays = Carbon::now()->addDays(7);

        $upcomingVisits = SaleInvoice::whereBetween('next_visit_date', [$today, $nextSevenDays])
            ->get()
            ->map(function ($invoice) {
                $invoice->next_visit_date = Carbon::parse($invoice->next_visit_date);
                return $invoice;
            });

        return [
            'upcomingVisits' => $upcomingVisits,
        ];
    }
}
