<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class AddDesignIdWastageOrdersTable
 */
class AddDesignIdWastageOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('design_id')->after('total_fiddles');
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('wastage_orders', function (Blueprint $table) {
            $table->dropForeign('wastage_orders_design_id_foreign');
            $table->dropColumn('design_id');
        });
    }
}
