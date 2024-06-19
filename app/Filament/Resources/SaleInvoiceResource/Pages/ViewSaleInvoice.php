<?php

namespace App\Filament\Resources\SaleInvoiceResource\Pages;

use App\Filament\Resources\SaleInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSaleInvoice extends ViewRecord
{
    protected static string $resource = SaleInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Print Invoice')
                ->color('warning')
                ->url(route('sale-invoices.print', ['record' => $this->record->getKey()]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-printer'),
        ];
    }
}
