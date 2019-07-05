<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignForPartialOrderIsInStocksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('partial_order_id')->references('id')->on('recipes_partial_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign('stocks_partial_order_id_foreign');
            $table->dropColumn('partial_order_id');
        });
    }
}
