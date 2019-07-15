<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCostPerMetersFloatInSalesOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->float('cost_per_meter')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->integer('cost_per_meter')->change();
        });

    }
}
