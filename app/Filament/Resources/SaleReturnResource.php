<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleReturnResource\Pages;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleReturn;
use App\Models\SaleInvoiceItem;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleReturnResource extends Resource
{
    protected static ?string $model = SaleReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-minus';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationLabel = 'SALE RETURNS';
    protected static ?string $navigationGroup = 'RETURNS';
    protected static ?string $modelLabel = 'Sale Return';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->required()
                            ->default(auth()->user()->id),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->default(function () {
                                $defaultCustomer = Customer::where('id', 1)->first();
                                return $defaultCustomer ? $defaultCustomer->id : null;
                            }),
                        Forms\Components\Select::make('sale_invoice_id')
                            ->label('Sale Invoice')
                            ->relationship('saleInvoice', 'posted_number')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $saleInvoice = SaleInvoice::find($state);
                                    $set('customer_id', $saleInvoice->customer_id);
                                    $set('discount_percentage', $saleInvoice->discount_percentage);
                                    $set('tax_percentage', $saleInvoice->tax_percentage);
                                    $set('discount_amount', $saleInvoice->discount_amount);
                                    $set('tax_amount', $saleInvoice->tax_amount);
                                } else {
                                    $set('customer_id', 0);
                                    $set('discount_percentage', 0);
                                    $set('tax_percentage', 0);
                                    $set('discount_amount', 0);
                                    $set('tax_amount', 0);
                                }
                            }),
                        Forms\Components\TextInput::make('posted_number')
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->default(SaleReturn::generateCode()),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false),
                    ])->columns(4),
                Forms\Components\Section::make()
                    ->schema([
                        Repeater::make('saleReturnItems')
                            ->relationship('saleReturnItems')
                            ->label('Sale Return Items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->preload()
                                    ->reactive()
                                    ->options(function (callable $get) {
                                        $saleInvoiceId = $get('../../sale_invoice_id');
                                        if ($saleInvoiceId) {
                                            $saleInvoice = SaleInvoice::find($saleInvoiceId);
                                            return $saleInvoice->saleInvoiceItems->pluck('product.name', 'product.id');
                                        }
                                        return Product::all()->pluck('name', 'id');
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $saleInvoiceId = $get('../../sale_invoice_id');
                                        if ($saleInvoiceId && $state) {
                                            $saleInvoiceItem = SaleInvoiceItem::where('sale_invoice_id', $saleInvoiceId)
                                                ->where('product_id', $state)
                                                ->first();
                                            if ($saleInvoiceItem) {
                                                $set('sale_quantity', $saleInvoiceItem->quantity);
                                                $set('price', $saleInvoiceItem->price);
                                                $set('item_discount_percentage', $saleInvoiceItem->item_discount_percentage);
                                            }
                                        } else {
                                            $set('sale_quantity', null);
                                            $set('price', null);
                                            $set('item_discount_percentage', null);
                                        }
                                    })->columnSpan(3),
                                Forms\Components\TextInput::make('sale_quantity')
                                    ->label('SALE.Q')
                                    ->numeric()
                                    ->readOnly()
                                    ->nullable(),
                                Forms\Components\TextInput::make('return_quantity')
                                    ->label('RETURN.Q')
                                    ->required()
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $price = $get('price') ?? 0;
                                        $item_discount = $get('item_discount_percentage') ?? 0;
                                        $quantity = $state ?? 0;
                                        $sub_total = $price * $quantity;
                                        $discount_amount = ($sub_total * $item_discount) / 100;
                                        $sub_total_with_discount = $sub_total - $discount_amount;
                                        $set('sub_total', $sub_total_with_discount);

                                        // Calculate gross amount and item discount
                                        $gross_total = collect($get('../../saleReturnItems'))
                                            ->sum(fn($item) => ($item['price'] ?? 0) * ($item['return_quantity'] ?? 0));

                                        $total_amount = collect($get('../../saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);

                                        $set('../../gross_total', $gross_total);
                                        $set('../../original_total_amount', $total_amount);

                                        $overall_discount = $get('../../discount_percentage') ?? 0;
                                        $tax = $get('../../tax_percentage') ?? 0;
                                        $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('../../total', $total_with_tax);
                                    })
                                    ->rules([
                                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                            $sale_quantity = $get('sale_quantity') ?? 0;
                                            if ($value > $sale_quantity) {
                                                $fail('The quantity cannot exceed the available stock.');
                                            }
                                        }
                                    ]),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('item_discount_percentage')
                                    ->label('DISC%')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $item_discount = $state ?? 0;
                                        $price = $get('price') ?? 0;
                                        $quantity = $get('return_quantity') ?? 0;
                                        $sub_total = $price * $quantity;
                                        $discount_amount = ($sub_total * $item_discount) / 100;
                                        $sub_total_with_discount = $sub_total - $discount_amount;
                                        $set('sub_total', $sub_total_with_discount);

                                        // Calculate gross amount and item discount
                                        $gross_total = collect($get('../../saleReturnItems'))
                                            ->sum(fn($item) => ($item['price'] ?? 0) * ($item['return_quantity'] ?? 0));
                                       
                                        $total_amount = collect($get('../../saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);

                                        $set('../../gross_total', $gross_total);
                                        $set('../../original_total_amount', $total_amount);

                                        $overall_discount = $get('../../discount_percentage') ?? 0;
                                        $tax = $get('../../tax_percentage') ?? 0;
                                        $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('../../total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('sub_total')
                                    ->required()
                                    ->numeric()
                                    ->readOnly(),
                            ])->columns(8)
                            ->reactive(),
                    ]),
                Section::make()
                    ->schema([
                        Forms\Components\Section::make('Tax, Discount & Total')
                            ->schema([
                                Forms\Components\TextInput::make('discount_percentage')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $discount = $state ?? 0;
                                        $original_total_amount = collect($get('saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $discount_amount = ($original_total_amount * $discount) / 100;
                                        $set('discount_amount', $discount_amount);

                                        $tax = $get('tax_percentage') ?? 0;
                                        $total_with_discount = $original_total_amount - $discount_amount;
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('discount_amount')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $discount_amount = $state ?? 0;
                                        $original_total_amount = collect($get('saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $discount_percentage = ($original_total_amount > 0) ? ($discount_amount / $original_total_amount) * 100 : 0;
                                        $set('discount_percentage', $discount_percentage);

                                        $tax = $get('tax_percentage') ?? 0;
                                        $total_with_discount = $original_total_amount - $discount_amount;
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('tax_percentage')
                                    ->label('Tax %')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tax = $state ?? 0;
                                        $original_total_amount = collect($get('saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $total_with_discount = $original_total_amount - ($original_total_amount * ($get('discount_percentage') ?? 0) / 100);
                                        $tax_amount = ($total_with_discount * $tax) / 100;
                                        $set('tax_amount', $tax_amount);
                                        $total_with_tax = $total_with_discount + $tax_amount;
                                        $set('total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('tax_amount')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $tax_amount = $state ?? 0;
                                        $original_total_amount = collect($get('saleReturnItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $discount_amount = $get('discount_amount') ?? 0;
                                        $total_with_discount = $original_total_amount - $discount_amount;
                                        $tax_percentage = ($total_with_discount > 0) ? ($tax_amount / $total_with_discount) * 100 : 0;
                                        $set('tax_percentage', $tax_percentage);

                                        $total_with_tax = $total_with_discount + $tax_amount;
                                        $set('total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('gross_total')
                                    ->numeric()
                                    ->readOnly()
                                    ->reactive(),
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->reactive()
                                    ->readOnly(),
                            ])->columns(6),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('posted_number'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer'),
                Tables\Columns\TextColumn::make('date')->date(),
                Tables\Columns\TextColumn::make('gross_total')->money('usd'),
                Tables\Columns\TextColumn::make('discount_amount')->money('usd'),
                Tables\Columns\TextColumn::make('tax_amount')->money('usd'),
                Tables\Columns\TextColumn::make('total')->money('usd'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSaleReturns::route('/'),
            'create' => Pages\CreateSaleReturn::route('/create'),
            'edit' => Pages\EditSaleReturn::route('/{record}/edit'),
        ];
    }
}
