<?php

namespace App\Filament\Resources\SaleInvoiceResource\Pages;

use App\Filament\Resources\SaleInvoiceResource;
use App\Filament\Widgets\TotalSalesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaleInvoices extends ListRecords
{
    protected static string $resource = SaleInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->keyBindings(['option+n', 'alt+n']),
        ];
    }
}
