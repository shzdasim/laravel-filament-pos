<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Exceptions\CustomerDeletionException;
use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
            ->action(function ($record) {
                try {
                    $record->delete();
                } catch (CustomerDeletionException $e) {
                    Notification::make()
                        ->title('Deletion Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            }),
        ];
    }
}
