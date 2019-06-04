<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CostPerMeterInSalesOrdersTable
 */
class CostPerMeterInSalesOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('cost_per_meter')->after('delivery_date');
            $table->dropColumn('bill_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('cost_per_meter');
            $table->string('bill_no')->after('delivery_date');
        });
    }
}
