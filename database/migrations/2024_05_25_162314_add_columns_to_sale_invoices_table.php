<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSaleInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->decimal('gross_amount', 15, 2)->after('total')->nullable();
            $table->decimal('item_discount', 15, 2)->after('gross_amount')->nullable();
            $table->decimal('discount_amount', 15, 2)->after('item_discount')->nullable();
            $table->decimal('tax_amount', 15, 2)->after('discount_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropColumn('gross_amount');
            $table->dropColumn('item_discount');
            $table->dropColumn('discount_amount');
            $table->dropColumn('tax_amount');
        });
    }
}
