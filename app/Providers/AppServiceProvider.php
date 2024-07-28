<?php

namespace App\Providers;
use App\Filament\Widgets\UpcomingVisitsWidget;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::registerWidgets([
            UpcomingVisitsWidget::class,
        ]);
    }
}
