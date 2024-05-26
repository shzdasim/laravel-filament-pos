<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('posted_number')->unique()->required();
            $table->date('posted_date')->required();
            $table->string('invoice_number')->unique()->required();
            $table->integer('invoice_amount')->required();
            $table->integer('tax%')->nullable();
            $table->integer('discount%')->nullable();
            $table->integer('tax_amount')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->integer('total_amount')->required();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
