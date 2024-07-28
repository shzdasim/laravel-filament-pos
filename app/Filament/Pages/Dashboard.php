<?php
 
namespace App\Filament\Pages;

use App\Filament\Widgets\PurchaseChart;
use App\Filament\Widgets\SaleChart;
use App\Filament\Widgets\StateWidget;
use App\Filament\Widgets\TotalSalesWidget;
use App\Filament\Widgets\UpcomingVisitsWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;
    
    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Section::make('FILTER')
            ->schema([
                DatePicker::make('startDate')
                ->prefix('Starts')
                ->prefixIcon('heroicon-m-calendar-days')
                ->prefixIconColor('success')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
                DatePicker::make('endDate')
                ->suffix('Ends')
                ->suffixIcon('heroicon-m-calendar-days')
                ->suffixIconColor('success')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection(),
            ])->columns(2),
        ]);
    }
    public function getWidgets(): array
    {
        return [
            UpcomingVisitsWidget::class,
            StateWidget::class,
            PurchaseChart::class,
            SaleChart::class,
        ];
    }
}
