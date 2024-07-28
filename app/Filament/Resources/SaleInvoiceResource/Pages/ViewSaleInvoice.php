<?php

namespace App\Filament\Resources\SaleInvoiceResource\Pages;

use App\Filament\Resources\SaleInvoiceResource;
use App\Models\Application;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewSaleInvoice extends ViewRecord
{
    protected static string $resource = SaleInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        $whatsappUrl = $this->generateWhatsAppUrl();

        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Print Invoice')
                ->color('warning')
                ->url(route('sale-invoices.print', ['record' => $this->record->getKey()]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-printer'),
            Actions\Action::make('sendWhatsApp')
                ->label('Send via WhatsApp')
                ->color('success')
                ->url($whatsappUrl)
                ->openUrlInNewTab()
                ->icon('heroicon-o-chat-bubble-oval-left'),
        ];
    }

    protected function generateWhatsAppUrl(): string
    {
        $customer = $this->record->customer;
        $application = Application::first(); // Assuming you have an Application model to fetch this data
        $invoice = $this->record;
        $items = $invoice->saleInvoiceItems;
        
        $message = "Dear " . $customer->name . ",\n\n"
            . "Thanks for your visit. Here are your invoice details:\n\n"
            . $application->name . "\n"
            . "License Number: " . $application->licence_number . "\n\n"
            . "Invoice Number: " . $invoice->posted_number . "\n"
            . "Visit Date: " . $invoice->visit_date . "\n"
            . "Next Visit Date: " . $invoice->next_visit_date . "\n"
            . "Visit Reading: " . $invoice->visit_reading . "\n"
            . "Next Visit Reading: " . $invoice->next_visit_reading . "\n"
            . "Remarks: " . $invoice->remarks . "\n"
            . "Customer Name: " . $customer->name . "\n\n"
            . "Items:\n";
    
        foreach ($items as $item) {
            $message .= "Product: " . $item->product->name . "\n"
                . "Qty: " . $item->quantity . "\n"
                . "Price: R.S " . $item->price . "\n"
                . "Disc%: " . $item->item_discount_percentage . "%\n"
                . "Subtotal: R.S " . $item->sub_total . "\n\n";
        }
    
        $message .= "Gross Amount: R.S " . $invoice->gross_amount . "\n"
            . "Item Discount: R.S " . $invoice->item_discount . "\n"
            . "Discount Amount: R.S " . $invoice->discount_amount . "\n"
            . "Tax Amount: R.S " . $invoice->tax_amount . "\n"
            . "Total Amount: R.S " . $invoice->total . "\n\n";
    
        if (!empty($application->instructions)) {
            $message .= $application->instructions . "\n\n";
        }
    
        $message .= "Best regards,\n"
            . $application->name;
    
        return "https://wa.me/" . $customer->phone . "?text=" . urlencode($message);
    }
    
}
