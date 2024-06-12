<?php


namespace App\Filament\Resources;


use App\Filament\Resources\PurchaseInvoiceResource\Pages;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;


class PurchaseInvoiceResource extends Resource
{
   protected static ?string $model = PurchaseInvoice::class;


   protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';


   public static function getNavigationBadge(): ?string
   {
       return static::getModel()::count();
   }


   protected static ?string $navigationLabel = 'PURCHASE INVOICES';
   protected static ?string $navigationGroup = 'INVOICES';
   protected static ?string $modelLabel = 'Purchase Invoice';
   protected static ?int $navigationSort = 2;


   public static function form(Form $form): Form
   {
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
                           ->numeric()
                           ->rules([
                               fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                   $invoice_amount = $get('invoice_amount');
                                   $total_amount = $get('total_amount');
                                   if (abs($invoice_amount - $total_amount) > 5) {
                                       Notification::make()
                                           ->title('Invoice Amount Error')
                                           ->body($fail('Invoice amount must not differ from the total amount by more than 5.'))
                                           ->danger()
                                           ->send();
                                   }
                               }
                           ])
                   ])->columns(3),
               Section::make()
                   ->schema([
                       Repeater::make('purchaseInvoiceItems')
                           ->relationship('purchaseInvoiceItems')
                           ->label('Purchase Invoice Items')
                           ->schema([
                               Forms\Components\Select::make('product_id')
                                   ->relationship(name: 'product', titleAttribute: 'name')
                                   ->required()
                                   ->label('Select Product')
                                   ->native(false)
                                   ->searchable()
                                   ->preload()
                                   ->reactive()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       if ($state) {
                                           $product = Product::find($state);
                                           if ($product) {
                                               $set('purchase_price', $product->purchase_price);
                                               $set('sale_price', $product->sale_price);
                                           } else {
                                               $set('purchase_price', null);
                                               $set('sale_price', null);
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
                               Forms\Components\TextInput::make('quantity')
                                   ->required()
                                   ->reactive()
                                   ->numeric()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       $purchase_price = (float) $get('purchase_price') ?? 0;
                                       $item_discount = (float) $get('item_discount_percentage') ?? 0;
                                       $quantity = (int) $state ?? 0;
                                       $sub_total = $purchase_price * $quantity;
                                       $discount_amount = ($sub_total * $item_discount) / 100;
                                       $sub_total_with_discount = $sub_total - $discount_amount;
                                       $set('sub_total', $sub_total_with_discount);
                                       $product_id = $get('product_id');
                                       $product = Product::find($product_id);


                                       // Calculate margin (profit in %) and avg_price
                                       $new_purchase_price = (float) $get('purchase_price') ?? 0;
                                       $old_purchase_price = $product ? $product->purchase_price : 0;
                                       $avg_price = ($old_purchase_price + $new_purchase_price) / 2;
                                       $set('avg_price', $avg_price);


                                       $sale_price = (float) $get('sale_price') ?? 0;
                                       if($sale_price >0){
                                           $discounted_purchase_price = $purchase_price - ($purchase_price * $item_discount / 100);
                                           $profit = $sale_price - $discounted_purchase_price;
                                           $margin = ($discounted_purchase_price > 0) ? ($profit / $sale_price) * 100 : 0;
                                           $set('margin', $margin);
                                       } else{
                                           $set('margin', 0);
                                       }


                                       // Update total amount without discount
                                       $total_amount = collect($get('../../purchaseInvoiceItems'))
                                           ->sum(fn($item) => $item['sub_total'] ?? 0);
                                       $set('../../original_total_amount', $total_amount);


                                       // Recalculate the final total amount considering the discount and tax on total
                                       $overall_discount = (float) $get('../../discount_percentage') ?? 0;
                                       $tax = (float) $get('../../tax_percentage') ?? 0;
                                       $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                       $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                       $set('../../total_amount', $total_with_tax);
                                   }),
                               Forms\Components\TextInput::make('purchase_price')
                                   ->label('Pur. Price')
                                   ->required()
                                   ->reactive()
                                   ->numeric()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       $purchase_price = (float) $state ?? 0;
                                       $quantity = (int) $get('quantity') ?? 0;
                                       $item_discount = (float) $get('item_discount_percentage') ?? 0;
                                       $sub_total = $purchase_price * $quantity;
                                       $discount_amount = ($sub_total * $item_discount) / 100;
                                       $sub_total_with_discount = $sub_total - $discount_amount;
                                       $set('sub_total', $sub_total_with_discount);
                                       $product_id = $get('product_id');
                                       $product = Product::find($product_id);


                                       // Calculate margin (profit in %) and avg_price
                                       $new_purchase_price = (float) $get('purchase_price') ?? 0;
                                       $old_purchase_price = $product ? $product->purchase_price : 0;
                                       $avg_price = ($old_purchase_price + $new_purchase_price) / 2;
                                       $set('avg_price', $avg_price);


                                       $sale_price = (float) $get('sale_price') ?? 0;
                                       if($sale_price >0){
                                           $discounted_purchase_price = $purchase_price - ($purchase_price * $item_discount / 100);
                                           $profit = $sale_price - $discounted_purchase_price;
                                           $margin = ($discounted_purchase_price > 0) ? ($profit / $sale_price) * 100 : 0;
                                           $set('margin', $margin);
                                       } else{
                                           $set('margin', 0);
                                       }


                                       // Update total amount without discount
                                       $total_amount = collect($get('../../purchaseInvoiceItems'))
                                           ->sum(fn($item) => $item['sub_total'] ?? 0);
                                       $set('../../original_total_amount', $total_amount);


                                       // Recalculate the final total amount considering the discount and tax on total
                                       $overall_discount = (float) $get('../../discount_percentage') ?? 0;
                                       $tax = (float) $get('../../tax%') ?? 0;
                                       $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                       $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                       $set('../../total_amount', $total_with_tax);
                                   }),
                               Forms\Components\TextInput::make('sale_price')
                                   ->required()
                                   ->numeric()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       // Calculate margin (profit in %) and avg_price
                                       $purchase_price = (float) $get('purchase_price') ?? 0;
                                       $sale_price = (float) $state ?? 0;
                                       $item_discount = (float) $get('item_discount_percentage') ?? 0;
                                       $quantity = (int) $get('quantity') ?? 0;
                                       $product_id = $get('product_id');
                                       $product = Product::find($product_id);
                                       $discount_amount = ($purchase_price * $quantity * $item_discount) / 100;
                                       if($sale_price >0){
                                           $discounted_purchase_price = $purchase_price - ($purchase_price * $item_discount / 100);
                                           $profit = $sale_price - $discounted_purchase_price;
                                           $margin = ($discounted_purchase_price > 0) ? ($profit / $sale_price) * 100 : 0;
                                           $set('margin', $margin);
                                       } else{
                                           $set('margin', 0);
                                       }


                                       $new_purchase_price = (float) $get('purchase_price') ?? 0;
                                       $old_purchase_price = $product ? $product->purchase_price : 0;
                                       $avg_price = ($old_purchase_price + $new_purchase_price) / 2;
                                       $set('avg_price', $avg_price);
                                   }),
                               Forms\Components\TextInput::make('item_discount_percentage')
                                   ->label('disc%')
                                   ->reactive()
                                   ->numeric()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       $item_discount = (float) $state ?? 0;
                                       $purchase_price = (float) $get('purchase_price') ?? 0;
                                       $quantity = (int) $get('quantity') ?? 0;
                                       $sub_total = $purchase_price * $quantity;
                                       $discount_amount = ($sub_total * $item_discount) / 100;
                                       $sub_total_with_discount = $sub_total - $discount_amount;
                                       $set('sub_total', $sub_total_with_discount);
                                       $product_id = $get('product_id');
                                       $product = Product::find($product_id);
                                       // Calculate margin (profit in %) and avg_price
                                       $new_purchase_price = (float) $get('purchase_price') ?? 0;
                                       $old_purchase_price = $product ? $product->purchase_price : 0;
                                       $avg_price = ($old_purchase_price + $new_purchase_price) / 2;
                                       $set('avg_price', $avg_price);


                                       $sale_price = (float) $get('sale_price') ?? 0;
                                       if($sale_price >0){
                                           $discounted_purchase_price = $purchase_price - ($purchase_price * $item_discount / 100);
                                           $profit = $sale_price - $discounted_purchase_price;
                                           $margin = ($discounted_purchase_price > 0) ? ($profit / $sale_price) * 100 : 0;
                                           $set('margin', $margin);
                                       } else{
                                           $set('margin', 0);
                                       }


                                       // Update total amount without discount
                                       $total_amount = collect($get('../../purchaseInvoiceItems'))
                                           ->sum(fn($item) => $item['sub_total'] ?? 0);
                                       $set('../../original_total_amount', $total_amount);


                                       // Recalculate the final total amount considering the discount and tax on total
                                       $overall_discount = (float) $get('../../discount_percentage') ?? 0;
                                       $tax = (float) $get('../../tax_percentage') ?? 0;
                                       $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                       $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                       $set('../../total_amount', $total_with_tax);
                                   }),
                               Forms\Components\TextInput::make('margin')
                                   ->reactive()
                                   ->readOnly(),
                               Forms\Components\TextInput::make('avg_price')
                                   ->reactive()
                                   ->readOnly(),
                               Forms\Components\TextInput::make('sub_total')
                                   ->required()
                                   ->readOnly()
                                   ->numeric()
                                   ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                       // Update total amount without discount
                                       $total_amount = collect($get('../../purchaseInvoiceItems'))
                                           ->sum(fn($item) => $item['sub_total'] ?? 0);
                                       $set('../../original_total_amount', $total_amount);


                                       // Recalculate the final total amount considering the discount and tax on total
                                       $overall_discount = (float) $get('../../discount_percentage') ?? 0;
                                       $tax = (float) $get('../../tax_percentage') ?? 0;
                                       $total_with_discount = $total_amount - ($total_amount * $overall_discount / 100);
                                       $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                                       $set('../../total_amount', $total_with_tax);
                                   }),
                           ])
                           ->columns(10)
                           ->reactive()
                           ->extraAttributes([
                            'x-data' => '{}',
                            'x-init' => '$nextTick(() => { document.addEventListener("keydown", (event) => { if (event.metaKey && event.key === "n") { event.preventDefault(); $dispatch("repeater::addItem", "purchaseInvoiceItems"); } }); })'
                        ]),
                   ]),
               Forms\Components\Section::make('Tax, Discount, Total')
                   ->schema([
                       Forms\Components\TextInput::make('tax_percentage')
                           ->label('Tax%')
                           ->numeric()
                           ->reactive()
                           ->afterStateUpdated(function($state, callable $set, callable $get) {
                               $tax = (float) $state ?? 0;
                               $original_total_amount = collect($get('purchaseInvoiceItems'))
                                   ->sum(fn($item) => $item['sub_total'] ?? 0);
                               $total_with_discount = $original_total_amount - ($original_total_amount * ($get('discount_percentage') ?? 0) / 100);
                               $tax_amount = ($total_with_discount * $tax) / 100;
                               $set('tax_amount', $tax_amount);


                               $total_with_tax = $total_with_discount + $tax_amount;
                               $set('total_amount', $total_with_tax);
                           }),
                       Forms\Components\TextInput::make('tax_amount')
                           ->label('Tax Amount')
                           ->numeric()
                           ->reactive()
                           ->afterStateUpdated(function ($state, callable $set, callable $get) {
                               $tax_amount = (float) $state ?? 0;
                               $original_total_amount = collect($get('purchaseInvoiceItems'))
                                   ->sum(fn($item) => $item['sub_total'] ?? 0);
                               $discount_amount = (float) $get('discount_amount') ?? 0;
                               $total_with_discount = $original_total_amount - $discount_amount;
                               $tax_percentage = ($total_with_discount > 0) ? ($tax_amount / $total_with_discount) * 100 : 0;
                               $set('tax_percentage', $tax_percentage);


                               $total_with_tax = $total_with_discount + $tax_amount;
                               $set('total_amount', $total_with_tax);
                           }),
                       Forms\Components\TextInput::make('discount_percentage')
                           ->label('Discount %')
                           ->numeric()
                           ->reactive()
                           ->afterStateUpdated(function($state, callable $set, callable $get) {
                               $discount = (float) $state ?? 0;
                               $original_total_amount = collect($get('purchaseInvoiceItems'))
                                   ->sum(fn($item) => $item['sub_total'] ?? 0);
                               $discount_amount = ($original_total_amount * $discount) / 100;
                               $set('discount_amount', $discount_amount);


                               $tax = (float) $get('tax_percentage') ?? 0;
                               $total_with_discount = $original_total_amount - $discount_amount;
                               $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                               $set('total_amount', $total_with_tax);
                           }),
                       Forms\Components\TextInput::make('discount_amount')
                           ->label('Discount Amount')
                           ->numeric()
                           ->reactive()
                           ->afterStateUpdated(function ($state, callable $set, callable $get) {
                               $discount_amount = (float) $state ?? 0;
                               $original_total_amount = collect($get('purchaseInvoiceItems'))
                                   ->sum(fn($item) => $item['sub_total'] ?? 0);
                               $discount_percentage = ($original_total_amount > 0) ? ($discount_amount / $original_total_amount) * 100 : 0;
                               $set('discount_percentage', $discount_percentage);


                               $tax = (float) $get('tax_percentage') ?? 0;
                               $total_with_discount = $original_total_amount - $discount_amount;
                               $total_with_tax = $total_with_discount + ($total_with_discount * $tax / 100);
                               $set('total_amount', $total_with_tax);
                           }),
                       Forms\Components\TextInput::make('original_total_amount')
                           ->numeric()
                           ->reactive()
                           ->hidden(),
                       Forms\Components\TextInput::make('total_amount')
                           ->required()
                           ->numeric()
                           ->reactive()
                           ->readOnly(),
                   ])->columns(5),
           ])->extraAttributes(['onkeydown' => 'return event.key != "Enter";']);
   }


   public static function table(Table $table): Table
   {
       $bulkActions = [
           Tables\Actions\DeleteBulkAction::make(),
       ];


       // Conditionally remove the bulk delete action if the user does not have the delete permission
       if (!Gate::allows('deleteAny', PurchaseInvoice::class)) {
           $bulkActions = [];
       }
       return $table
           ->columns([
               Tables\Columns\TextColumn::make('supplier.name')
                   ->numeric()
                   ->sortable(),
               Tables\Columns\TextColumn::make('posted_number')
                   ->label('P.NO')
                   ->searchable(),
               Tables\Columns\TextColumn::make('posted_date')
                   ->label('Date')
                   ->date()
                   ->sortable(),
               Tables\Columns\TextColumn::make('invoice_number')
                   ->label('INV.NO')
                   ->searchable(),
               Tables\Columns\TextColumn::make('invoice_amount')
                   ->label('INV.AMNT')
                   ->numeric()
                   ->sortable(),
               Tables\Columns\TextColumn::make('tax_percentage')
                   ->label('Tax%')
                   ->numeric()
                   ->sortable(),
               Tables\Columns\TextColumn::make('discount_percentage')
                   ->label('DISC.%')
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
           ->bulkActions($bulkActions);
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