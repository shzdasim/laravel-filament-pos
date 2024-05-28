<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Exceptions\CategoryDeletionException;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
            ->action(function ($record) {
                try {
                    $record->delete();
                } catch (CategoryDeletionException $e) {
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
