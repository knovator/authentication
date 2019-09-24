<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddTotalQuantityDetailsInOrdersTable
 */
class AddTotalQuantityDetailsInOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
//        Schema::table('sales_orders', function (Blueprint $table) {
//            $table->decimal('total_meters')->nullable()->after('cost_per_meter');
//        });
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->decimal('total_meters')->nullable()->after('cost_per_meter');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('total_kg')->nullable()->after('challan_no');
        });

        Schema::table('yarn_sales_orders', function (Blueprint $table) {
            $table->decimal('total_kg')->nullable()->after('challan_no');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('total_meters');
        });
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->dropColumn('total_meters');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('total_kg');
        });
        Schema::table('yarn_sales_orders', function (Blueprint $table) {
            $table->dropColumn('total_kg');
        });
    }
}
