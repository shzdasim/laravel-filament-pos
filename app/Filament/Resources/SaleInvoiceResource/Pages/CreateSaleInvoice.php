<?php

namespace App\Filament\Resources\SaleInvoiceResource\Pages;

use App\Filament\Resources\SaleInvoiceResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSaleInvoice extends CreateRecord
{
    protected static string $resource = SaleInvoiceResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        // Removing the "Create & Create Another" button by not including it
        return [
            Action::make('create')
                ->label('Create Sale Invoice')
                ->submit('store'),
                
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
