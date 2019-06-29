<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddSalesOrderIdDeliveriesTable
 */
class AddSalesOrderIdDeliveriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->after('delivery_date');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign('deliveries_sales_order_id_foreign');
            $table->dropColumn('sales_order_id');
        });
    }
}
