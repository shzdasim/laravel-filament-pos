<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleInvoiceResource\Pages;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleInvoiceResource extends Resource
{
    protected static ?string $model = SaleInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationLabel = 'SALE INVOICES';
    protected static ?string $navigationGroup = 'INVOICES';
    protected static ?string $modelLabel = 'Sale Invoice';
    protected static ?int $navigationSort = 1;

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
                        Forms\Components\TextInput::make('posted_number')
                            ->required()
                            ->maxLength(255)
                            ->readOnly()
                            ->default(SaleInvoice::generateCode()),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false),
                    ])->columns(3),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('saleInvoiceItems')
                            ->relationship('saleInvoiceItems')
                            ->label('Sale Invoice Items')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('current_quantity', $product->quantity);
                                                $set('price', $product->sale_price);
                                            } else {
                                                $set('current_quantity', 0);
                                                $set('price', 0);
                                            }
                                        }
                                    })
                                    ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.product_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('current_quantity')
                                    ->label('CURRENT.Q')
                                    ->required()
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $current_quantity = $get('current_quantity') ?? 0;
                                        $price = $get('price') ?? 0;
                                        $discount = $get('discount') ?? 0;
                                        $quantity = $state ?? 0;
                                        $sub_total = $price * $quantity;
                                        $discount_amount = ($sub_total * $discount) / 100;
                                        $sub_total_with_discount = $sub_total - $discount_amount;
                                        $set('sub_total', $sub_total_with_discount);

                                        $total_amount = collect($get('../../saleInvoiceItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $set('../../original_total_amount', $total_amount);

                                        $overall_discount = $get('../../discount') ?? 0;
                                        $tax = $get('../../tax') ?? 0;
                                        $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('../../total', $total_with_tax);
                                    })
                                    ->rules([
                                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                            $current_quantity = $get('current_quantity') ?? 0;
                                            if ($value > $current_quantity) {
                                                $fail('The quantity cannot exceed the available stock.');
                                            }
                                        }
                                    ]),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('discount')
                                    ->label('DISC%')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $discount = $state ?? 0;
                                        $price = $get('price') ?? 0;
                                        $quantity = $get('quantity') ?? 0;
                                        $sub_total = $price * $quantity;
                                        $discount_amount = ($sub_total * $discount) / 100;
                                        $sub_total_with_discount = $sub_total - $discount_amount;
                                        $set('sub_total', $sub_total_with_discount);

                                        $total_amount = collect($get('../../saleInvoiceItems'))
                                            ->sum(fn($item) => $item['sub_total'] ?? 0);
                                        $set('../../original_total_amount', $total_amount);

                                        $overall_discount = $get('../../discount') ?? 0;
                                        $tax = $get('../../tax') ?? 0;
                                        $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                        $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                        $set('../../total', $total_with_tax);
                                    }),
                                Forms\Components\TextInput::make('sub_total')
                                    ->required()
                                    ->numeric()
                                    ->readOnly(),
                            ])->columns(8)
                            ->reactive()
                            ->addAction(
                                fn (Action $action) => $action->keybindings('option+n'), // Add keybinding method to repeater add action
                            ),
                    ]),
                Forms\Components\Section::make('Tax, Discount, Total')
                    ->schema([
                        Forms\Components\TextInput::make('tax')
                            ->label('Tax%')
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function($state, callable $set, callable $get) {
                                $tax = $state ?? 0;
                                $original_total_amount = $get('original_total_amount') ?? 0;
                                $overall_discount = $get('discount') ?? 0;
                                $total_with_discount = $original_total_amount - ($original_total_amount * $overall_discount / 100);
                                $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                $set('total', $total_with_tax);
                            }),
                        Forms\Components\TextInput::make('discount')
                            ->label('Discount %')
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function($state, callable $set, callable $get) {
                                $discount = $state ?? 0;
                                $original_total_amount = $get('original_total_amount') ?? 0;
                                $total_with_discount = $original_total_amount - ($original_total_amount * $discount / 100);
                                $tax = $get('tax') ?? 0;
                                $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                $set('total', $total_with_tax);
                            }),
                        Forms\Components\TextInput::make('original_total_amount')
                            ->numeric()
                            ->reactive()
                            ->hidden(),
                        Forms\Components\TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->readOnly(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('posted_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
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
                ])->visible(fn (User $user, $record) => $user->can('delete', $record)),
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
            'index' => Pages\ListSaleInvoices::route('/'),
            'create' => Pages\CreateSaleInvoice::route('/create'),
            'view' => Pages\ViewSaleInvoice::route('/{record}'),
            'edit' => Pages\EditSaleInvoice::route('/{record}/edit'),
        ];
    }
}
