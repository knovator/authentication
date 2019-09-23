<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexForCustomerPoNumberInSalesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index(['customer_po_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex(['sales_orders_customer_po_number_index']);
        });
    }
}
