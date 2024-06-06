<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')->rules(['required', 'unique:products,code']),
            ImportColumn::make('name')->rules(['required', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('quantity')->rules(['required', 'integer']),
            ImportColumn::make('purchase_price')->rules(['required', 'numeric']),
            ImportColumn::make('sale_price')->rules(['required', 'numeric']),
            ImportColumn::make('avg_price')->rules(['required', 'numeric']),
            ImportColumn::make('margin')->rules(['required', 'numeric']),
            ImportColumn::make('max_discount')->rules(['required', 'numeric']),
            ImportColumn::make('category_id')->rules(['required', 'exists:categories,id']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        // return Product::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Product();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
