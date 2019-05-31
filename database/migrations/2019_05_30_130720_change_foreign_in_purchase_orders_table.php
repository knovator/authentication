<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeForeignInPurchaseOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign('purchase_orders_customer_id_foreign');
            $table->dropColumn('customer_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('order_date');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign('sales_orders_customer_id_foreign');
            $table->dropColumn('customer_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('order_date');
            $table->foreign('customer_id')->references('id')->on('customers');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('purchase_orders', function (Blueprint $table) {
            //
        });
    }
}
