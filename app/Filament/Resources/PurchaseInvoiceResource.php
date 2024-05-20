<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Filament\Resources\PurchaseInvoiceResource\RelationManagers;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseInvoiceResource extends Resource
{
    protected static ?string $model = PurchaseInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationBadge(): ?string
        {
            return static::getModel()::count();
        }
    protected static ?string $navigationLabel = 'PURCHASE INVOICES';
    protected static ?string $navigationGroup = 'INVOICES';
    protected static ?string $modelLabel = 'Purchase Invoice';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $products = Product::get();
        return $form
            ->schema([

                Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('posted_number')
                    ->required()
                    ->maxLength(255)
                    ->readOnly()
                    ->default(PurchaseInvoice::generateCode()),
                    Forms\Components\DatePicker::make('posted_date')
                    ->required()
                    ->native(false)
                    ->default(now()),
                ])->columns(2),
                Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->required(),
                    Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
                    Forms\Components\TextInput::make('invoice_amount')
                    ->required()
                    ->numeric(),
                ])->columns(3),
                
              
               Section::make()
               ->schema([
                Repeater::make('purchaseInvoiceItems')
                ->relationship('purchaseInvoiceItems')
                ->label('Purchase Invoice')
                ->schema([
                    // SELECT PRODUCT START
                    Forms\Components\Select::make('product_id')
                                    ->relationship(name: 'product', titleAttribute: 'name')
                                    ->required()
                                    ->label('SELECT PRODUCT')
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get){
                                        if ($state){
                                            $product = Product::find($state);
                                            if ($product){
                                                $set('purchase_price', $product->purchase_price);
                                                $set('sale_price', $product->sale_price);
                                                $quantity = $get('quantity') ?? 0;
                                                $set('sub_total', $product->purchase_price * $quantity);
                                            }
                                            else{
                                                $set('purchase_price', null);
                                                $set('sale_price', null);
                                                $set('sub_total', null);
                                            }
                                        }
                                    })
                                    // Disable options that are already selected in other rows
                                ->disableOptionWhen(function ($value, $state, Get $get) {
                                    return collect($get('../*.product_id'))
                                        ->reject(fn($id) => $id == $state)
                                        ->filter()
                                        ->contains($value);
                                })
                                ->columnSpan(3),
                    // SELECT PRODUCT END
                    // Quantity START
                    Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->reactive()
                    ->numeric()
                    ->afterStateUpdated(function ($state, callable $set, callable $get){
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        $purchase_price = $get('purchase_price') ?? 0;
                        $quantity = $state ?? 0;
                        $sub_total = $purchase_price * $quantity;
                        $set('sub_total', $sub_total);
                    })
                    ,
                    // Quantity END
                    //Start purchase_price
                    Forms\Components\TextInput::make('purchase_price')
                    ->label('Pur. Price')
                    ->required()
                    ->reactive()
                    ->numeric()
                    ->afterStateUpdated(function($state, callable $set, callable $get){
                        $productId = $get('product_id');
                        $product = Product::find($productId);
                        $purchase_price = $state ?? 0;
                        $quantity = $get('quantity') ?? 0;
                        $sub_total = $purchase_price * $quantity;
                        $set('sub_total', $sub_total);
                    })
                    ,
                    //End purchase_price
                    // START sale_price
                    Forms\Components\TextInput::make('sale_price')
                    ->required()
                    ->numeric(),
                    // END sale_price
                    // START discount
                    Forms\Components\TextInput::make('discount')
                    ->label('disc%')
                    ->required()
                    ->numeric(),
                    // END discount
                    // START subtotal
                    Forms\Components\TextInput::make('sub_total')
                    ->required()
                    ->readOnly()
                    ->numeric(),
                    // END subtotal
                ])->columns(8),
               ]),
                
                
                // START SECTION TAX, DISCOUNT, TOTAL
                Forms\Components\Section::make('Tax, Discount, Total')
                ->schema([
                    Forms\Components\TextInput::make('tax')
                        ->numeric(),
                    Forms\Components\TextInput::make('discount')
                        ->numeric(),
                    Forms\Components\TextInput::make('total_amount')
                        ->required()
                        ->numeric()
                        ->readOnly(),
                ])->columns(3),
            ]) ->extraAttributes(['onkeydown' => 'return event.key != "Enter";']); // Prevent Enter key from submitting the form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posted_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('posted_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
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
            'index' => Pages\ListPurchaseInvoices::route('/'),
            'create' => Pages\CreatePurchaseInvoice::route('/create'),
            'view' => Pages\ViewPurchaseInvoice::route('/{record}'),
            'edit' => Pages\EditPurchaseInvoice::route('/{record}/edit'),
        ];
    }
}
