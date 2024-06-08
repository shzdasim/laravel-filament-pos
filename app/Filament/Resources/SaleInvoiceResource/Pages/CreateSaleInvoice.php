<?php
namespace App\Filament\Resources\SaleInvoiceResource\Pages;

use App\Filament\Resources\SaleInvoiceResource;
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
        return [
            Action::make('create')
                ->label('Create Sale Invoice')
                ->submit('store')
                ->keyBindings(['option+s', 'alt+s']),

            Action::make('addRepeaterItem')
                ->label('Add Item')
                ->keyBindings(['option+n', 'alt+n'])
                ->action(function () {
                    // Safeguard to ensure the action is not triggered multiple times
                    static $isActionTriggered = false;

                    if (!$isActionTriggered) {
                        $isActionTriggered = true;
                        $this->addRepeaterItem();
                    }
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    private function addRepeaterItem()
    {
        if (!isset($this->data['saleInvoiceItems'])) {
            $this->data['saleInvoiceItems'] = [];
        }
        $this->data['saleInvoiceItems'][] = [];
    }
}
