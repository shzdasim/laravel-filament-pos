<?php

namespace App\Filament\Resources;

use App\Exceptions\ProductDeletionException;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification ;
use App\Filament\Imports\ProductsImport;
use Filament\Tables\Actions\ImportAction as ActionsImportAction;
use Illuminate\Support\Facades\Gate;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    public static function getNavigationBadge(): ?string
        {
            return static::getModel()::count() ;
        }
    protected static ?string $navigationLabel = 'PRODUCTS';
    protected static ?string $navigationGroup = 'ITEM SETUP';
    protected static ?string $modelLabel = 'Product';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {


        return $form
            ->schema([
                Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('code')
                    ->required()
                    ->readOnly()
                    ->maxLength(255)
                    ->default(Product::generateCode()),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                ])->columns(2),
                
                Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->readOnly(),
                Forms\Components\TextInput::make('purchase_price')
                    ->numeric()
                    ->readOnly(),
                Forms\Components\TextInput::make('sale_price')
                    ->numeric()
                    ->readOnly(),
                Forms\Components\TextInput::make('avg_price')
                    ->numeric()
                    ->readOnly(),
                Forms\Components\TextInput::make('max_discount')
                    ->numeric()
                    ->readOnly(),
                    Forms\Components\TextInput::make('margin')
                    ->numeric()
                    ->readOnly(),
                ])->columns(6),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        $bulkActions = [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                ->action(function ($records) {
                    foreach ($records as $record) {
                        try {
                            $record->delete();
                        } catch (ProductDeletionException $e) {
                            Notification::make()
                                ->title('Deletion Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }
                }),
            ])
        ];

        // Conditionally remove the bulk delete action if the user does not have the delete permission
        if (!Gate::allows('deleteAny', Product::class)) {
            $bulkActions = [];
        }
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('P.Price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                ->label('S.Price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_discount')
                    ->label('Max.Disc%')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions($bulkActions)
            ->headerActions([
                ActionsImportAction::make()
                    ->importer(ProductImporter::class)
                    ->label('Import Products'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
