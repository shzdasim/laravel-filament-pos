<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Exceptions\ProductDeletionException;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
            ->action(function ($record) {
                try {
                    $record->delete();
                } catch (ProductDeletionException $e) {
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
