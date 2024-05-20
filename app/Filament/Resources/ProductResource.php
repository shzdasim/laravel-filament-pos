<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

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
                ])->columns(5),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_discount')
                    ->numeric()
                    ->sortable(),
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
