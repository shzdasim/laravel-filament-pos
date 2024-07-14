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
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('posted_number')->unique()->required();
            $table->date('visit_date');
            $table->date('next_visit_date');
            $table->string('visit_reading')->nullable();
            $table->string('next_visit_reading')->nullable();
            $table->string('remarks')->nullable();
            $table->decimal('discount_percentage')->nullable();
            $table->decimal('tax_percentage')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->decimal('item_discount', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
