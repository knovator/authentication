<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddPartialOrderIdInStocksTable
 */
class AddPartialOrderIdInStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('partial_order_id')->nullable()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('partial_order_id')->references('id')->on('recipes_partial_orders');
        });
    }
}
