<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Exceptions\SupplierDeletionException;
use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
            ->action(function ($record) {
                try {
                    $record->delete();
                } catch (SupplierDeletionException $e) {
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
