<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddCostPerMeterInWastageOrdersTable
 */
class AddCostPerMeterInWastageOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->double('cost_per_meter')->after('challan_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->dropColumn('cost_per_meter');
        });
    }
}
